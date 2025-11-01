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

/**
 * Enqueue main theme scripts and styles
 */
\add_action(
	hook_name: 'wp_enqueue_scripts',
	callback: function () {
		$suffix  = ( ! \defined( 'WP_DEBUG' ) || ! WP_DEBUG ) ? '.min' : '';
		$version = \wp_get_theme()->get( 'Version' ) . '.' . \time();

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
			true
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
		$version = \wp_get_theme()->get( 'Version' ) . '.' . \time();

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
