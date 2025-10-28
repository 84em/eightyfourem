<?php
/**
 * WP-CLI commands for EightyFourEM theme
 *
 * @package Eighty Four EM
 */

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

// Only load if WP-CLI is available
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * Custom WP-CLI commands for theme management
 */
class ThemeCLI {
	/**
	 * Regenerate schema for pages and posts
	 *
	 * ## OPTIONS
	 *
	 * [--all]
	 * : Regenerate schema for all posts, pages, and projects
	 *
	 * [--pages]
	 * : Regenerate schema for all pages only
	 *
	 * [--posts]
	 * : Regenerate schema for all posts only
	 *
	 * [--projects]
	 * : Regenerate schema for all projects only
	 *
	 * [--slug=<slug>]
	 * : Regenerate schema for specific page/post by slug (comma-separated for multiple)
	 *
	 * [--service-pages]
	 * : Regenerate schema for all service pages and main pages with updated schema
	 *
	 * ## EXAMPLES
	 *
	 *     # Regenerate schema for all content
	 *     wp 84em regenerate-schema --all
	 *
	 *     # Regenerate schema for all pages
	 *     wp 84em regenerate-schema --pages
	 *
	 *     # Regenerate schema for specific pages
	 *     wp 84em regenerate-schema --slug=services,about,pricing
	 *
	 *     # Regenerate schema for service pages and key pages
	 *     wp 84em regenerate-schema --service-pages
	 *
	 * @when after_wp_load
	 */
	public function regenerate_schema( $args, $assoc_args ) {
		$regenerated = 0;

		if ( isset( $assoc_args['service-pages'] ) ) {
			// Regenerate specific service and key pages
			$pages_to_update = [
				'services',
				'about',
				'pricing',
				'services/custom-wordpress-plugin-development',
				'services/white-label-wordpress-development-for-agencies',
				'services/ai-enhanced-wordpress-development',
				'services/wordpress-consulting-strategy',
				'services/wordpress-maintenance-support',
			];

			\WP_CLI::log( 'Regenerating schema for service pages and key pages...' );

			foreach ( $pages_to_update as $slug ) {
				$page = \get_page_by_path( $slug, OBJECT, 'page' );
				if ( $page ) {
					$this->update_post_schema( $page->ID, $page->post_title );
					$regenerated++;
				} else {
					\WP_CLI::warning( "Page not found: {$slug}" );
				}
			}
		} elseif ( isset( $assoc_args['slug'] ) ) {
			// Regenerate specific pages by slug
			$slugs = explode( ',', $assoc_args['slug'] );
			\WP_CLI::log( 'Regenerating schema for specific pages...' );

			foreach ( $slugs as $slug ) {
				$slug = trim( $slug );
				$post = \get_page_by_path( $slug, OBJECT, [ 'page', 'post', 'project' ] );
				if ( $post ) {
					$this->update_post_schema( $post->ID, $post->post_title );
					$regenerated++;
				} else {
					\WP_CLI::warning( "Post not found: {$slug}" );
				}
			}
		} elseif ( isset( $assoc_args['all'] ) ) {
			// Regenerate all
			\WP_CLI::log( 'Regenerating schema for all posts, pages, and projects...' );
			$regenerated += $this->regenerate_by_post_type( [ 'post', 'page', 'project' ] );
		} elseif ( isset( $assoc_args['pages'] ) ) {
			// Regenerate all pages
			\WP_CLI::log( 'Regenerating schema for all pages...' );
			$regenerated += $this->regenerate_by_post_type( [ 'page' ] );
		} elseif ( isset( $assoc_args['posts'] ) ) {
			// Regenerate all posts
			\WP_CLI::log( 'Regenerating schema for all posts...' );
			$regenerated += $this->regenerate_by_post_type( [ 'post' ] );
		} elseif ( isset( $assoc_args['projects'] ) ) {
			// Regenerate all projects
			\WP_CLI::log( 'Regenerating schema for all projects...' );
			$regenerated += $this->regenerate_by_post_type( [ 'project' ] );
		} else {
			\WP_CLI::error( 'Please specify --all, --pages, --posts, --projects, --slug, or --service-pages' );
			return;
		}

		\WP_CLI::success( "Schema regenerated for {$regenerated} item(s)." );
	}

	/**
	 * Regenerate schema for posts by post type
	 *
	 * @param array $post_types Post types to process
	 *
	 * @return int Number of posts updated
	 */
	private function regenerate_by_post_type( $post_types ) {
		$count = 0;

		foreach ( $post_types as $post_type ) {
			$posts = \get_posts(
				[
					'post_type'      => $post_type,
					'posts_per_page' => -1,
					'post_status'    => 'publish',
				]
			);

			foreach ( $posts as $post ) {
				$this->update_post_schema( $post->ID, $post->post_title );
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Update schema for a single post
	 *
	 * @param int    $post_id    Post ID
	 * @param string $post_title Post title for logging
	 */
	private function update_post_schema( $post_id, $post_title ) {
		// Get the post object
		$post = \get_post( $post_id );

		// Trigger the schema generation by calling wp_after_insert_post action
		\do_action( 'wp_after_insert_post', $post_id, $post, true );

		\WP_CLI::log( "✓ Updated: {$post_title}" );
	}
}

// Register the command
\WP_CLI::add_command( '84em regenerate-schema', [ new ThemeCLI(), 'regenerate_schema' ] );
