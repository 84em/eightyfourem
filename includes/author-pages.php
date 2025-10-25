<?php

/**
 * Disables the Author archive pages, redirecting the user to the home page instead.
 */

defined( 'ABSPATH' ) || exit;

add_action(
    hook_name: 'template_redirect',
    callback: function () {
        if ( is_author() ) {
            wp_redirect( location: home_url(), status: 301 );
            exit();
        }
    } );
