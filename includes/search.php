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
            $query->set( 'meta_query', [
                [
                    'key'     => '_local_page_state',
                    'compare' => 'NOT EXISTS',
                ],
            ] );
        }

        return $query;
    } );
