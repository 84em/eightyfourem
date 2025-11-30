<?php

/**
 * Shortcode Registration
 *
 * Registers WordPress shortcodes for the theme.
 *
 * @package EightyFourEM
 */

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

/**
 * Displays the last modified date of the current post.
 *
 * Usage: [last_updated]
 * Output: "Month Day, Year" format
 */
\add_shortcode(
	tag: 'last_updated',
	callback: function ( $_atts, $_content ) {
		global $post;
		return \date( 'F j, Y', \strtotime( $post->post_modified ) );
	}
);

/**
 * Renders the HTML sitemap.
 *
 * Usage: [html_sitemap]
 * Implementation: includes/html-sitemap.php
 */
\add_shortcode(
	tag: 'html_sitemap',
	callback: 'EightyFourEM\HtmlSitemap\render'
);
