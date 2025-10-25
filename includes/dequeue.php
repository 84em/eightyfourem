<?php

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

// remove unused script on home page
\add_action(
    hook_name: 'wp_enqueue_scripts',
    callback: function () {
        if ( is_front_page() ) {
            wp_dequeue_script( 'uagb-loop-builder' );
        }
    },
    priority: 1000 );

// remove unused style
\add_action(
    hook_name: 'wp_enqueue_scripts',
    callback: function () {

        wp_dequeue_style( 'spectra-pro-block-css' );
    },
    priority: 99999 );

