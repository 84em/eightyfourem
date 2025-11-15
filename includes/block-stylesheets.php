<?php
/**
 * Block Stylesheets
 * Enqueue custom block stylesheets for the theme
 *
 * @package EightyFourEM
 */

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue custom block stylesheets
 *
 * The wp_enqueue_block_style() function allows us to enqueue a stylesheet
 * for a specific block. These will only get loaded when the block is rendered
 * (both in the editor and on the front end), improving performance
 * and reducing the amount of data requested by visitors.
 *
 * @see https://make.wordpress.org/core/2021/12/15/using-multiple-stylesheets-per-block/
 */
\add_action(
	hook_name: 'init',
	callback: function () {
		\wp_enqueue_block_style(
			'core/button',
			[
				'handle' => 'eightyfourem-button-style-outline',
				'src'    => \get_parent_theme_file_uri( 'assets/css/button-outline.css' ),
				'ver'    => \wp_get_theme( \get_template() )->get( 'Version' ),
				'path'   => \get_parent_theme_file_path( 'assets/css/button-outline.css' ),
			]
		);
	}
);
