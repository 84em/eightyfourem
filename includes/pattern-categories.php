<?php
/**
 * Pattern Categories
 * Register custom pattern categories for the theme
 *
 * @package EightyFourEM
 */

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

/**
 * Register custom pattern categories
 */
\add_action(
	hook_name: 'init',
	callback: function () {
		\register_block_pattern_category(
			'page',
			[
				'label'       => \_x( 'Pages', 'Block pattern category' ),
				'description' => \__( 'A collection of full page layouts.' ),
			]
		);
	}
);
