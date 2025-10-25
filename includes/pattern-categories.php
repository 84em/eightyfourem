<?php
/**
 * Pattern Categories
 * Register custom pattern categories for the theme
 *
 * @package EightyFourEM
 */

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'EightyFourEM\eightyfourem_pattern_categories' ) ) :
	/**
	 * Register pattern categories
	 *
	 * @since Eighty Four EM 1.0
	 * @return void
	 */
	function eightyfourem_pattern_categories() {

		\register_block_pattern_category(
			'page',
			array(
				'label'       => \_x( 'Pages', 'Block pattern category' ),
				'description' => \__( 'A collection of full page layouts.' ),
			)
		);
	}
endif;

\add_action( 'init', 'EightyFourEM\eightyfourem_pattern_categories' );
