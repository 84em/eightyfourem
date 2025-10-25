<?php

/**
 * Improves LCP on the home page by preloading an image
 */

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

\add_action(
    hook_name: 'wp_head',
    callback: function () {
        if ( is_front_page() ) {
            echo '<link rel="preload" as="image" href="' . esc_url( site_url( '/wp-content/uploads/2017/07/84embackground.jpg' ) ) . '">';
        }
    },
    priority: 1 );
