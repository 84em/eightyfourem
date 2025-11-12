<?php
/**
 * Enqueue Scripts and Styles
 * Handles theme asset enqueuing
 *
 * @package EightyFourEM
 */

namespace EightyFourEM;

use UAGB_Scripts_Utils;
use function get_theme_file_uri;
use function wp_enqueue_script;

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue main theme scripts and styles
 */
\add_action(
	hook_name: 'wp_enqueue_scripts',
	callback: function () {
		$suffix  = ( ! \defined( 'WP_DEBUG' ) || ! WP_DEBUG ) ? '.min' : '';
		$version = \wp_get_theme()->get( 'Version' );

		// Enqueue customizer CSS
		\wp_enqueue_style(
            handle: 'eightyfourem-customizer',
            src: get_theme_file_uri( "assets/css/customizer{$suffix}.css" ),
            deps: [],
            ver: $version
		);

		// Enqueue sticky header CSS
		\wp_enqueue_style(
            handle: 'eightyfourem-sticky-header',
            src: get_theme_file_uri( "assets/css/sticky-header{$suffix}.css" ),
            deps: [],
            ver: $version
		);

		// Enqueue sticky header JavaScript
        wp_enqueue_script(
            handle: 'eightyfourem-sticky-header',
            src: get_theme_file_uri( "assets/js/sticky-header{$suffix}.js" ),
            deps: [],
            ver: $version,
            args: true
		);

        // Enqueue highlight script
        wp_enqueue_script(
            handle: 'eightyfourem-highlight',
            src: get_theme_file_uri( "assets/js/highlight{$suffix}.js" ),
            deps: [],
        );

        // Enqueue highlight CSS
        wp_enqueue_style(
            handle: 'eightyfourem-highlighter',
            src: get_theme_file_uri( "assets/css/highlight{$suffix}.css" ),
            deps: [],
        );

		// Enqueue modal search CSS
		\wp_enqueue_style(
			handle: 'eightyfourem-modal-search',
			src: get_theme_file_uri( "assets/css/modal-search{$suffix}.css" ),
			deps: [],
			ver: $version
		);

		// Enqueue modal search JavaScript
		wp_enqueue_script(
			handle: 'eightyfourem-modal-search',
			src: get_theme_file_uri( "assets/js/modal-search{$suffix}.js" ),
			deps: [],
			ver: $version,
			args: true
		);
	},
	priority: 10
);

/**
 * Enqueue search results styles
 * Only loads on search results pages
 */
\add_action(
	hook_name: 'wp_enqueue_scripts',
	callback: function () {
		if ( ! \is_search() ) {
			return;
		}

		$suffix  = ( ! \defined( 'WP_DEBUG' ) || ! WP_DEBUG ) ? '.min' : '';
		$version = \wp_get_theme()->get( 'Version' );

		\wp_enqueue_style(
			'eightyfourem-search',
            get_theme_file_uri( "assets/css/search{$suffix}.css" ),
			[],
			$version
		);
	},
	priority: 10
);

/**
 * Enqueue case study filter assets
 * Only loads on case studies page (ID: 4406)
 */
\add_action(
	hook_name: 'wp_enqueue_scripts',
	callback: function () {
		if ( ! \is_page( 4406 ) ) {
			return;
		}

		$suffix  = ( ! \defined( 'WP_DEBUG' ) || ! WP_DEBUG ) ? '.min' : '';
		$version = \wp_get_theme()->get( 'Version' );

		\wp_enqueue_style(
			'eightyfourem-case-study-filter',
            get_theme_file_uri( "assets/css/case-study-filter{$suffix}.css" ),
			[],
			$version
		);

        wp_enqueue_script(
			'eightyfourem-case-study-filter',
            get_theme_file_uri( "assets/js/case-study-filter{$suffix}.js" ),
			[],
			$version,
			true
		);
	},
	priority: 10
);

/**
 * Enqueue UAGB scripts for specific pages
 * Loads on local pages and USA services page
 */
\add_action(
	hook_name: 'wp_enqueue_scripts',
	callback: function () {
		if ( ! \is_singular( 'local' ) && ! \is_page( 'wordpress-development-services-usa' ) ) {
			return;
		}

		if ( ! \class_exists( 'UAGB_Scripts_Utils' ) ) {
			return;
		}

		if ( ! \method_exists( 'UAGB_Scripts_Utils', 'enqueue_blocks_styles' ) ) {
			return;
		}

		UAGB_Scripts_Utils::enqueue_blocks_styles();
	},
	priority: 10
);

/**
 * Add body class to disable sticky TOC on specific pages
 * Allows pages with many headings to opt out of jump navigation
 */
\add_filter(
	hook_name: 'body_class',
	callback: function ( array $classes ): array {
		// Pages where sticky TOC should be disabled
		$disabled_pages = [
			4406, // Case Studies page
		];

		if ( \is_page( $disabled_pages ) ) {
			$classes[] = 'disable-sticky-toc';
		}

		return $classes;
	},
	priority: 10
);
