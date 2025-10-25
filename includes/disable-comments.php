<?php

/**
 * WordPress Comments Disabler
 *
 * This file contains code to completely disable the WordPress comments functionality.
 * It performs the following operations:
 *
 * 1. Removes comment support from all post types
 * 2. Disables comments and trackbacks on the front-end
 * 3. Hides existing comments
 * 4. Removes the comments menu from the admin panel
 *
 * This provides a comprehensive way to disable the entire comments system
 * by modifying default behavior through WordPress hooks and filters.
 */

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

// Disable support for comments
\add_action(
    hook_name: 'init',
    callback: function () {
        $post_types = get_post_types();
        foreach ( $post_types as $post_type ) {
            if ( post_type_supports( $post_type, 'comments' ) ) {
                remove_post_type_support( $post_type, 'comments' );
                remove_post_type_support( $post_type, 'trackbacks' );
            }
        }
    } );

// Close comments on the front-end
\add_filter(
    hook_name: 'comments_open',
    callback: '__return_false',
    priority: 20,
    accepted_args: 2 );

// close Pings on the front-end
\add_filter(
    hook_name: 'pings_open',
    callback: '__return_false',
    priority: 20,
    accepted_args: 2 );

// Hide existing comments
\add_filter(
    hook_name: 'comments_array',
    callback: '__return_empty_array',
    priority: 10,
    accepted_args: 2 );

// removes comments menu from the admin
\add_action(
    hook_name: 'admin_menu',
    callback: function () {
        remove_menu_page( 'edit-comments.php' );
    } );
