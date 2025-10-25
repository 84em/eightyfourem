<?php
/**
 * Breadcrumbs functionality for local pages
 *
 * Generates and displays breadcrumb navigation for hierarchical local pages (states and cities).
 * Breadcrumbs improve UX and SEO by showing the page hierarchy and providing navigation context.
 *
 * @package EightyFourEM
 * @since 1.0.27
 */

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

/**
 * Insert breadcrumbs before content for local pages
 *
 * Hooks into the_content filter to prepend breadcrumbs navigation
 * to local post type single pages.
 *
 * @hook the_content
 * @param string $content Post content
 * @return string Content with breadcrumbs prepended
 */
\add_filter(
	hook_name: 'the_content',
	callback: function ( $content ) {
		// Only show on single local pages
		if ( ! \is_singular( 'local' ) ) {
			return $content;
		}

		$post = \get_post( \get_the_ID() );

		if ( ! $post || 'local' !== $post->post_type ) {
			return $content;
		}

		$breadcrumbs = [];

		// Home link
		$breadcrumbs[] = \sprintf(
			'<a href="%s">Home</a>',
			\esc_url( \home_url( '/' ) )
		);

        # USA Link
        $breadcrumbs[] = \sprintf(
            '<a href="%s">USA</a>',
            \esc_url( \home_url( '/wordpress-development-services-usa//' ) )
        );

		// Parent page (state) if this is a city page
		if ( $post->post_parent ) {
			$parent       = \get_post( $post->post_parent );
			$parent_title = \get_the_title( $parent );

			// Extract just the state name from the title (before the pipe)
			if ( \strpos( $parent_title, '|' ) !== false ) {
				$parent_title = \trim( \explode( '|', $parent_title )[0] );
			}

			// Extract location from the beginning (e.g., "Iowa" from long title)
			// Look for pattern: "... in StateOrCity" or just use first few words
			if ( \preg_match( '/\bin\s+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)?)/i', $parent_title, $matches ) ) {
				$parent_title = $matches[1];
			}

			$breadcrumbs[] = \sprintf(
				'<a href="%s">%s</a>',
				\esc_url( \get_permalink( $parent ) ),
				\esc_html( $parent_title )
			);
		}

		// Current page (not linked)
		$current_title = \get_the_title( $post );

		// Extract just the city/state name from the title (before the pipe)
		if ( \strpos( $current_title, '|' ) !== false ) {
			$current_title = \trim( \explode( '|', $current_title )[0] );
		}

		// Extract location from the beginning
		if ( \preg_match( '/\bin\s+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)?)/i', $current_title, $matches ) ) {
			$current_title = $matches[1];
		}

		$breadcrumbs[] = '<span class="breadcrumb-current">' . \esc_html( $current_title ) . '</span>';

		$breadcrumb_html = \sprintf(
			'<nav class="breadcrumbs" aria-label="Breadcrumb"><p class="breadcrumbs-inner">%s</p></nav>',
			\implode( ' <span class="breadcrumb-separator">/</span> ', $breadcrumbs )
		);

		// Prepend breadcrumbs to content
		return $breadcrumb_html . $content;
	},
	priority: 5
);

/**
 * Enqueue breadcrumbs CSS
 *
 * Loads the breadcrumbs stylesheet on local pages.
 *
 * @hook wp_enqueue_scripts
 */
\add_action(
	hook_name: 'wp_enqueue_scripts',
	callback: function () {
		if ( \is_singular( 'local' ) ) {
			\wp_enqueue_style(
				handle: 'eightyfourem-breadcrumbs',
				src: \get_template_directory_uri() . '/assets/css/breadcrumbs.min.css',
				deps: [],
				ver: \wp_get_theme()->get( 'Version' )
			);
		}
	}
);
