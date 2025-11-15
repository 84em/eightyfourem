<?php

/**
 * Dequeue Unused Scripts and Styles
 *
 * Removes unused Spectra Pro and UAGB scripts/styles to improve performance:
 * - Dequeues UAGB loop builder script on front page (not used)
 * - Dequeues Spectra Pro block CSS globally (not used)
 *
 * @package EightyFourEM
 */

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

