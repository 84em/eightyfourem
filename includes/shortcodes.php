<?php

/**
 * Registers a WordPress shortcode 'last_updated' that displays the last modified
 * date of the current post in "Month Day, Year" format.
 *
 * The shortcode retrieves the global `$post` object to access the `post_modified`
 * property and formats the date using PHP's `date` and `strtotime` functions.
 *
 * Shortcode:
 * - Tag: 'last_updated'
 * - Callback: A closure that formats the last modified date of the post.
 */

namespace EightyFourEM;

use function add_action;

defined( 'ABSPATH' ) || exit;

\add_shortcode(
    tag: 'last_updated',
    callback: function ( $atts, $content ) {
        global $post;
        return \date( "F j, Y", \strtotime( $post->post_modified ) );
    } );


add_shortcode(
    tag: 'rlv_didyoumean',
    callback: function () {
        $didyoumean = '';
        if ( function_exists( 'relevanssi_didyoumean' ) ) {
            $didyoumean = relevanssi_didyoumean(
                query: get_search_query( false ),
                pre: '<p>Did you mean: ',
                post: '</p>',
                n: 5,
                echoed: false
            );
        }
        return $didyoumean;
    } );

add_shortcode(
    tag: 'html_sitemap',
    callback: function () {

        $pages = get_transient( 'html_sitemap' );

        if ( false === $pages ) {

            global $wpdb;
            $no_index_post_ids    = $wpdb->get_col( "SELECT distinct post_id FROM {$wpdb->postmeta} WHERE meta_key = '_genesis_noindex' AND meta_value != '0'" );
            $local_pages_post_ids = $wpdb->get_col( "SELECT distinct post_id FROM {$wpdb->postmeta} WHERE meta_key IN('_local_page_state', '_local_page_city')" );
            ob_start();

            // get pages (not local pages, not sitemap)
            $args = [
                'depth'        => 0,
                'show_date'    => '',
                'date_format'  => get_option( 'date_format' ),
                'child_of'     => 0,
                'exclude'      => implode( ',', array_merge( $no_index_post_ids, $local_pages_post_ids, [ 6964 ] ) ),
                'title_li'     => __( 'Pages' ),
                'echo'         => 1,
                'authors'      => '',
                'sort_column'  => 'post_title',
                'link_before'  => '',
                'link_after'   => '',
                'item_spacing' => 'preserve',
                'walker'       => '',
            ];
            wp_list_pages( $args );
            $pages = ob_get_clean();
            $pages = str_replace( 'Custom WordPress Plugin Development, Consulting, and White-Label services in the ', '', $pages );
            $pages = str_replace( 'Custom WordPress Plugin Development, Consulting, and White-Label services in ', '', $pages );

            // get local pages (not sitemap)
            ob_start();
            $args = [
                'depth'        => 0,
                'show_date'    => '',
                'date_format'  => get_option( 'date_format' ),
                'child_of'     => 2507,
                'exclude'      => 6964,
                'title_li'     => __( 'Local Pages' ),
                'echo'         => 1,
                'authors'      => '',
                'sort_column'  => 'post_title',
                'link_before'  => '',
                'link_after'   => '',
                'item_spacing' => 'preserve',
                'walker'       => '',
            ];
            wp_list_pages( $args );
            $pages .= '<hr/>' . ob_get_clean();

            $pages = str_replace( 'WordPress Development, Plugins, Consulting, White-Label in ', '', $pages );
            $pages = str_replace( 'AI-Enhanced WordPress Development, White-Label Services, Plugins, Consulting in ', '', $pages );
            $pages = str_replace( ' | 84EM', '', $pages );
            $pages = str_replace( 'WordPress Development, Plugins, Consulting, Agency Services in ', '', $pages );
            $pages = str_replace( 'WordPress Development, Plugins, Consulting, White-Label services in the ', '', $pages );
            $pages = str_replace( 'Custom WordPress Plugin Development, Consulting, and White-Label services in the ', '', $pages );
            set_transient( 'html_sitemap', $pages, WEEK_IN_SECONDS );
        }

        return $pages;
    }
);

add_action(
    hook_name: "publish_page",
    callback: function ( int $_post_id, \WP_Post $_post, string $_old_status ) {
        delete_transient( 'html_sitemap' );
    },
    accepted_args: 3
);
