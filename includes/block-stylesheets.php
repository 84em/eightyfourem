<?php
/**
 * Block Stylesheets
 * Enqueue custom block stylesheets for the theme
 *
 * @package EightyFourEM
 */

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'EightyFourEM\eightyfourem_block_stylesheets' ) ) :
	/**
	 * Enqueue custom block stylesheets
	 *
	 * @since Eighty Four EM 1.0
	 * @return void
	 */
	function eightyfourem_block_stylesheets() {
		/**
		 * The wp_enqueue_block_style() function allows us to enqueue a stylesheet
		 * for a specific block. These will only get loaded when the block is rendered
		 * (both in the editor and on the front end), improving performance
		 * and reducing the amount of data requested by visitors.
		 *
		 * See https://make.wordpress.org/core/2021/12/15/using-multiple-stylesheets-per-block/ for more info.
		 */
		\wp_enqueue_block_style(
			'core/button',
			array(
				'handle' => 'eightyfourem-button-style-outline',
				'src'    => \get_parent_theme_file_uri( 'assets/css/button-outline.css' ),
				'ver'    => \wp_get_theme( \get_template() )->get( 'Version' ),
				'path'   => \get_parent_theme_file_path( 'assets/css/button-outline.css' ),
			)
		);
	}
endif;

\add_action( 'init', 'EightyFourEM\eightyfourem_block_stylesheets' );
