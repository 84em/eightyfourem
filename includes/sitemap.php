<?php

/**
 * Registers actions to schedule and generate the XML sitemap when posts, pages,
 * or projects are published.
 */

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

\add_action(
    hook_name: 'publish_post',
    callback: 'EightyFourEM\schedule_xml_sitemap_84em',
    priority: 10,
    accepted_args: 3 );

\add_action(
    hook_name: 'publish_page',
    callback: 'EightyFourEM\schedule_xml_sitemap_84em',
    priority: 10,
    accepted_args: 3 );

\add_action(
    hook_name: 'publish_project',
    callback: 'EightyFourEM\schedule_xml_sitemap_84em',
    priority: 10,
    accepted_args: 3 );

\add_action(
    hook_name: 'create_xml_sitemap_84em',
    callback: 'EightyFourEM\create_xml_sitemap_84em',
    priority: 10,
    accepted_args: 1 );

/**
 * Schedules a single action to create an XML sitemap if not already scheduled.
 *
 * @param  int  $post_id  The ID of the post being saved or updated.
 * @param  object  $post_object  The post object associated with the action.
 * @param  string  $old_status  The previous status of the post before the update.
 *
 * @return void
 */
function schedule_xml_sitemap_84em( int $post_id, object $post_object, string $old_status ): void {
    if ( ! \as_has_scheduled_action( 'create_xml_sitemap_84em', [ 0 => null ] ) ) {
        \as_schedule_single_action( \time() + ( \MINUTE_IN_SECONDS * 5 ), 'create_xml_sitemap_84em', [ 0 => null ] );
    }
}

/**
 * Generates an XML sitemap from published posts, pages, and projects,
 * and writes it to the sitemap.xml file in the site's root directory.
 *
 * @param  array|null  $args  Optional arguments passed to the function.
 *                       This typically allows additional customization or future extension.
 *
 * @return void
 */
function create_xml_sitemap_84em( array|null $args ): void {
    $posts = \get_posts( [
        'numberposts'    => 9999,
        'posts_per_page' => 9999,
        'orderby'        => 'ID',
        'post_type'      => [ 'page', 'project', 'post' ],
        'post_status'    => 'publish',
        'order'          => 'ASC',
    ] );

    $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
    $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    $sitemap .= '<url>' .
                '<loc>' . site_url( '/lp/' ) . '</loc>' .
                '<lastmod>' . date( 'Y-m-d', filemtime( ABSPATH . ( '/lp/index.php' ) ) ) . '</lastmod>' .
                '<changefreq>daily</changefreq>' .
                '<priority>1.0</priority>' .
                '</url>';
    foreach ( $posts as $post ) {
        $_genesis_noindex = (int) \get_post_meta( $post->ID, '_genesis_noindex', true );
        if ( $_genesis_noindex === 1 ) {
            continue;
        }
        $link = \get_permalink( $post->ID );

        $home = site_url( '/' );

        if ( $link === $home ) {
            $priority = '1.0';
        }
        elseif ( str_contains( $link, '/services/' ) ) {
            $priority = '1.0';
        }
        elseif ( 'project' === $post->post_type ) {
            $priority = '0.7';
        }
        elseif ( 'post' === $post->post_type ) {
            $priority = '0.8';
        }
        else {
            $priority = '0.9';
        }

        $postdate = \explode( " ", $post->post_modified );
        $sitemap  .= '<url>' .
                     '<loc>' . $link . '</loc>' .
                     '<lastmod>' . $postdate[0] . '</lastmod>' .
                     '<changefreq>daily</changefreq>' .
                     '<priority>' . $priority . '</priority>' .
                     '</url>';
    }
    $sitemap .= '</urlset>';

    global $wp_filesystem;
    $wp_filesystem->put_contents(
        \ABSPATH . "sitemap.xml",
        $sitemap,
        \FS_CHMOD_FILE
    );
}
