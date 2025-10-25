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

defined( 'ABSPATH' ) || exit;

\add_shortcode(
    tag: 'last_updated',
    callback: function ( $atts, $content ) {
        global $post;
        return \date( "F j, Y", \strtotime( $post->post_modified ) );
    } );
