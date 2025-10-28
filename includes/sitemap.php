<?php

/**
 * XML sitemap generation with batch processing.
 *
 * This module generates XML sitemaps:
 * 1. Coordinator initializes the sitemap file with XML header and /lp/ entry
 * 2. Batches of 200 posts are processed sequentially via Action Scheduler
 * 3. Each batch appends its XML directly to the file using file locking
 * 4. The last batch appends the closing XML tag
 */

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

// Post types to include in sitemap with their priorities
const SITEMAP_POST_TYPES = [
    'post'    => '0.8',
    'page'    => '0.9',
    'project' => '0.7',
    'local'   => '0.9',
];

// Register publish hooks for each post type
foreach ( array_keys( SITEMAP_POST_TYPES ) as $post_type ) {
    \add_action(
        hook_name: "publish_{$post_type}",
        callback: 'EightyFourEM\schedule_xml_sitemap_84em',
        priority: 10,
        accepted_args: 3
    );
}

\add_action(
    hook_name: 'create_xml_sitemap_84em',
    callback: 'EightyFourEM\create_xml_sitemap_84em',
    priority: 10,
    accepted_args: 1 );

\add_action(
    hook_name: 'process_sitemap_batch_84em',
    callback: 'EightyFourEM\process_sitemap_batch_84em',
    priority: 10,
    accepted_args: 1 );

/**
 * Schedules a single action to create an XML sitemap if not already scheduled.
 *
 * @param  int  $post_id  The ID of the post being saved or updated.
 * @param  \WP_Post  $post  The post object associated with the action.
 * @param  string  $old_status  The previous status of the post before the update.
 *
 * @return void
 */
function schedule_xml_sitemap_84em( int $post_id, \WP_Post $post, string $old_status ): void {
    // Check if coordinator is already scheduled
    if ( ! \as_has_scheduled_action( 'create_xml_sitemap_84em', [ 0 => null ] ) ) {
        \as_schedule_single_action(
            \time() + ( \MINUTE_IN_SECONDS * 5 ),
            'create_xml_sitemap_84em',
            [ 0 => null ]
        );
    }
}

/**
 * Coordinator function that initiates batch processing for XML sitemap generation.
 *
 * Uses a simplified file-append approach:
 * 1. Fetches only post IDs (not full objects)
 * 2. Initializes the sitemap file with XML header
 * 3. Splits IDs into batches of 200
 * 4. Schedules batch processing tasks with sequential delays
 *
 * @param  array|null  $args  Optional arguments passed to the function.
 *
 * @return void
 */
function create_xml_sitemap_84em( array|null $args ): void {
    // Fetch all published post IDs
    $post_ids = \get_posts( [
        'numberposts'    => -1,
        'fields'         => 'ids',
        'post_type'      => array_keys( SITEMAP_POST_TYPES ),
        'post_status'    => 'publish',
        'orderby'        => 'ID',
        'order'          => 'ASC',
    ] );

    // If no posts found, exit
    if ( empty( $post_ids ) ) {
        return;
    }

    // Initialize sitemap file with XML header
    $sitemap_path = \ABSPATH . 'sitemap.xml';
    $xml_header = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    // Add static /lp/ entry first
    $lp_index_path = \ABSPATH . 'lp/index.php';
    if ( file_exists( $lp_index_path ) ) {
        $xml_header .= '<url>' .
                      '<loc>' . site_url( '/lp/' ) . '</loc>' .
                      '<lastmod>' . date( 'Y-m-d', filemtime( $lp_index_path ) ) . '</lastmod>' .
                      '<changefreq>daily</changefreq>' .
                      '<priority>1.0</priority>' .
                      '</url>';
    }

    // Write header to file
    file_put_contents( $sitemap_path, $xml_header, LOCK_EX );

    // Split into batches of 200 and schedule sequentially with delays
    $batches = array_chunk( $post_ids, 200 );
    $total_batches = count( $batches );
    $delay = 0;

    foreach ( $batches as $index => $batch_ids ) {
        $is_last = ( $index === $total_batches - 1 );

        \as_schedule_single_action(
            \time() + $delay,
            'process_sitemap_batch_84em',
            [ [
                'post_ids' => $batch_ids,
                'is_last'  => $is_last,
            ] ]
        );

        // Add 5 second delay between batches to guarantee order
        $delay += 5;
    }
}

/**
 * Processes a single batch of posts and appends XML directly to sitemap file.
 *
 * This function is called by Action Scheduler for each batch of 200 posts.
 * It generates the XML entries for the batch and appends them directly to
 * the sitemap file using file locking to prevent concurrent write issues.
 *
 * @param  array  $args  Contains post_ids and is_last flag.
 *
 * @return void
 */
function process_sitemap_batch_84em( array $args ): void {
    $post_ids = $args['post_ids'];
    $is_last  = $args['is_last'];

    // Get full post objects for this batch only
    $posts = \get_posts( [
        'post__in'       => $post_ids,
        'post_type'      => array_keys( SITEMAP_POST_TYPES ),
        'post_status'    => 'publish',
        'numberposts'    => count( $post_ids ),
        'orderby'        => 'post__in',
    ] );

    // Generate XML for this batch
    $xml = '';
    $home = site_url( '/' );

    foreach ( $posts as $post ) {
        // Skip posts marked as noindex
        $_genesis_noindex = (int) \get_post_meta( $post->ID, '_genesis_noindex', true );
        if ( $_genesis_noindex === 1 ) {
            continue;
        }

        $link = \get_permalink( $post->ID );

        // Determine priority based on post type and URL
        if ( $link === $home ) {
            $priority = '1.0';
        } elseif ( str_contains( $link, '/services/' ) ) {
            $priority = '1.0';
        } else {
            $priority = SITEMAP_POST_TYPES[$post->post_type] ?? '0.9';
        }

        $postdate = \explode( ' ', $post->post_modified );
        $xml .= '<url>' .
                '<loc>' . $link . '</loc>' .
                '<lastmod>' . $postdate[0] . '</lastmod>' .
                '<changefreq>daily</changefreq>' .
                '<priority>' . $priority . '</priority>' .
                '</url>';
    }

    // Append to sitemap file with file locking
    $sitemap_path = \ABSPATH . 'sitemap.xml';

    // Open file for appending with file locking
    $fp = fopen( $sitemap_path, 'a' );
    if ( $fp ) {
        // Acquire exclusive lock
        if ( flock( $fp, LOCK_EX ) ) {
            fwrite( $fp, $xml );
            fflush( $fp );
            // Release lock
            flock( $fp, LOCK_UN );
        }
        fclose( $fp );
    }

    // If this is the last batch, append XML footer
    if ( $is_last ) {
        $fp = fopen( $sitemap_path, 'a' );
        if ( $fp ) {
            // Acquire exclusive lock
            if ( flock( $fp, LOCK_EX ) ) {
                fwrite( $fp, '</urlset>' );
                fflush( $fp );
                // Release lock
                flock( $fp, LOCK_UN );
            }
            fclose( $fp );
        }
    }
}
