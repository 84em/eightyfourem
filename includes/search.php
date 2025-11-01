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
        if ( ! \is_search() ) {
            return $excerpt;
        }

        $blocks = \parse_blocks( $post->post_content );

        if ( empty( $blocks ) ) {
            return $excerpt;
        }

        $first_block = $blocks[0];

        // Check if first block is a heading containing "Challenge"
        if ( isset( $first_block['blockName'] ) &&
             $first_block['blockName'] === 'core/heading' ) {

            $content = $first_block['innerHTML'] ?? '';
            $text = \wp_strip_all_tags( $content );

            // If first heading contains "Challenge", remove it from excerpt generation
            if ( stripos( $text, 'Challenge' ) !== false ) {
                // Rebuild content without the first block
                $filtered_blocks = \array_slice( $blocks, 1 );
                $filtered_content = '';

                foreach ( $filtered_blocks as $block ) {
                    $filtered_content .= \render_block( $block );
                }

                // Generate excerpt from filtered content
                return \wp_trim_words( \wp_strip_all_tags( $filtered_content ), 55, '...' );
            }
        }

        return $excerpt;
    },
    priority: 10,
    accepted_args: 2
);
