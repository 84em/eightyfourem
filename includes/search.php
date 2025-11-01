<?php

/**
 * Filters the query used to retrieve posts in WordPress.
 *
 * This filter modifies the query when performing a search request.
 * If the query is a search query, is not executed in the admin context,
 * and is the main query, it restricts the search results to include only
 * posts of type 'page' and excludes local pages (identified by _local_page_state meta key).
 *
 * Hooked to the 'pre_get_posts' hook.
 *
 * @param  \WP_Query  $query  The query object being filtered.
 *
 * @return \WP_Query The modified query object.
 */

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

\add_filter(
    hook_name: 'pre_get_posts',
    callback: function ( \WP_Query $query ) {
        if ( $query->is_search && ! \is_admin() && $query->is_main_query() ) {
            $query->set( 'post_type', [ 'page' ] );
            $query->set( 'post__not_in', [ 2507 ] );
            $query->set( 'meta_query', [
                [
                    'key'     => '_local_page_state',
                    'compare' => 'NOT EXISTS',
                ],
            ] );
        }

        return $query;
    } );

\add_filter(
    hook_name: 'get_the_excerpt',
    callback: function ( string $excerpt, \WP_Post $post ) {
        // Only apply on search pages or case studies (parent 4406)
        if ( ! \is_search() && $post->post_parent !== 4406 ) {
            return $excerpt;
        }

        $blocks = \parse_blocks( $post->post_content );

        if ( empty( $blocks ) ) {
            return $excerpt;
        }

        // Find and remove any Challenge heading block (and everything before it)
        $challenge_index = -1;

        foreach ( $blocks as $index => $block ) {
            if ( isset( $block['blockName'] ) &&
                 $block['blockName'] === 'core/heading' ) {

                $content = $block['innerHTML'] ?? '';
                $text = \wp_strip_all_tags( $content );

                if ( stripos( $text, 'Challenge' ) !== false ) {
                    $challenge_index = $index;
                    break;
                }
            }
        }

        // If we found a Challenge heading, remove everything up to and including it
        if ( $challenge_index !== -1 ) {
            // Rebuild content without blocks up to and including the Challenge heading
            $filtered_blocks = \array_slice( $blocks, $challenge_index + 1 );
            $filtered_content = '';

            foreach ( $filtered_blocks as $block ) {
                $filtered_content .= \render_block( $block );
            }

            // Generate excerpt from filtered content
            return \wp_trim_words( \wp_strip_all_tags( $filtered_content ), 55, '...' );
        }

        return $excerpt;
    },
    priority: 10,
    accepted_args: 2
);
