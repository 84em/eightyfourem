<?php
/**
 * Enqueue Scripts and Styles
 * Handles theme asset enqueuing
 *
 * @package EightyFourEM
 */

namespace EightyFourEM;

use UAGB_Scripts_Utils;

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'EightyFourEM\eightyfourem_enqueue_scripts' ) ) :
    /**
     * Enqueue scripts and styles.
     *
     * @return void
     * @since Eighty Four EM 1.0
     */
    function eightyfourem_enqueue_scripts() {
        $suffix  = ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) ? '.min' : '';
        $version = \wp_get_theme()->get( 'Version' ) . '.' . time();

        // Enqueue customizer CSS
        \wp_enqueue_style(
            'eightyfourem-customizer',
            \get_theme_file_uri( "assets/css/customizer{$suffix}.css" ),
            [],
            $version
        );

        // Enqueue sticky header CSS
        \wp_enqueue_style(
            'eightyfourem-sticky-header',
            \get_theme_file_uri( "assets/css/sticky-header{$suffix}.css" ),
            [],
            $version
        );

        // Enqueue sticky header JavaScript
        \wp_enqueue_script(
            'eightyfourem-sticky-header',
            \get_theme_file_uri( "assets/js/sticky-header{$suffix}.js" ),
            [],
            $version,
            true // Load in footer
        );
    }
endif;

\add_action( 'wp_enqueue_scripts', 'EightyFourEM\eightyfourem_enqueue_scripts' );

// Case Study Filter Assets
\add_action( 'wp_enqueue_scripts', function () {
    if ( \is_page( 4406 ) ) { // Case Studies page ID
        $suffix  = ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) ? '.min' : '';
        $version = \wp_get_theme()->get( 'Version' ) . '.' . time();

        \wp_enqueue_style(
            'eightyfourem-case-study-filter',
            \get_theme_file_uri( "assets/css/case-study-filter{$suffix}.css" ),
            [],
            $version
        );

        \wp_enqueue_script(
            'eightyfourem-case-study-filter',
            \get_theme_file_uri( "assets/js/case-study-filter{$suffix}.js" ),
            [],
            $version,
            true
        );
    }
} );

// UAGB Scripts for specific pages
\add_action( 'wp_enqueue_scripts', function () {
    if ( \is_singular( 'local' ) || \is_page( 'wordpress-development-services-usa' ) ) {
        if ( class_exists( 'UAGB_Scripts_Utils' ) ) {
            if ( method_exists( 'UAGB_Scripts_Utils', 'enqueue_blocks_styles' ) ) {
                UAGB_Scripts_Utils::enqueue_blocks_styles();
            }
        }
    }
} );
