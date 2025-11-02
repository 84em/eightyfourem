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

/**
 * Get post type indicator label based on URL path.
 *
 * @param \WP_Post $post The post object.
 * @return string The post type label (Service, Case Study, or Page).
 */
function get_post_type_indicator( \WP_Post $post ): string {
	$permalink = \get_permalink( $post->ID );
	$path = \wp_parse_url( $permalink, PHP_URL_PATH );

	if ( strpos( $path, '/services/' ) !== false ) {
		return 'Service';
	}

	if ( strpos( $path, '/case-studies/' ) !== false ) {
		return 'Case Study';
	}

	return 'Page';
}

\add_filter(
    hook_name: 'pre_get_posts',
    callback: function ( \WP_Query $query ) {
        if ( $query->is_search && ! \is_admin() && $query->is_main_query() ) {
            $query->set( 'post_type', [ 'page' ] );

            // Exclude wordpress-development-services-usa parent page
            $query->set( 'post__not_in', [ 2507 ] );

            // Exclude all local pages (state and city pages) via meta query
            $query->set( 'meta_query', [
                'relation' => 'AND',
                [
                    'key'     => '_local_page_state',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'     => '_local_page_city',
                    'compare' => 'NOT EXISTS',
                ],
            ] );

            // Custom ordering will be applied via posts_orderby filter
            $query->set( 'orderby', 'custom_search_order' );
        }

        return $query;
    } );

\add_filter(
	hook_name: 'posts_orderby',
	callback: function ( string $orderby, \WP_Query $query ) {
		if ( ! $query->is_search || \is_admin() || ! $query->is_main_query() ) {
			return $orderby;
		}

		global $wpdb;

		// Custom ordering: Services (2129) first, then Case Studies (4406), then Pages
		$custom_orderby = "
			CASE
				WHEN {$wpdb->posts}.ID = 2129 OR {$wpdb->posts}.post_parent = 2129 THEN 1
				WHEN {$wpdb->posts}.ID = 4406 OR {$wpdb->posts}.post_parent = 4406 THEN 2
				ELSE 3
			END ASC,
			{$wpdb->posts}.post_date DESC
		";

		return $custom_orderby;
	},
	priority: 10,
	accepted_args: 2
);

\add_filter(
	hook_name: 'render_block',
	callback: function ( string $block_content, array $parsed_block, $block ) {
		// Only apply to post title blocks in Query Loop
		if ( $parsed_block['blockName'] !== 'core/post-title' ) {
			return $block_content;
		}

		// Only on search results
		if ( ! \is_search() ) {
			return $block_content;
		}

		// Get post ID from WP_Block context (used in Query Loop)
		if ( ! isset( $block->context['postId'] ) ) {
			return $block_content;
		}

		$post_id = $block->context['postId'];
		$post = \get_post( $post_id );

		if ( ! $post || $post->post_type !== 'page' ) {
			return $block_content;
		}

		$indicator = get_post_type_indicator( $post );
		$badge_class = 'post-type-badge post-type-' . strtolower( str_replace( ' ', '-', $indicator ) );

		$badge = sprintf(
			'<span class="%s">%s</span> ',
			\esc_attr( $badge_class ),
			\esc_html( $indicator )
		);

		// Insert badge after the opening tag (after <h1>, <h2>, etc.)
		if ( preg_match( '/^(<h[1-6][^>]*>)(.*)$/s', $block_content, $matches ) ) {
			$block_content = $matches[1] . $badge . $matches[2];
		}

		return $block_content;
	},
	priority: 10,
	accepted_args: 3
);

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
