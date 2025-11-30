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
	 * : Regenerate schema for all posts, pages
	 *
	 * [--pages]
	 * : Regenerate schema for all pages only
	 *
	 * [--posts]
	 * : Regenerate schema for all posts only
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
				$post = \get_page_by_path( $slug, OBJECT, [ 'page', 'post' ] );
				if ( $post ) {
					$this->update_post_schema( $post->ID, $post->post_title );
					$regenerated++;
				} else {
					\WP_CLI::warning( "Post not found: {$slug}" );
				}
			}
		} elseif ( isset( $assoc_args['all'] ) ) {
			// Regenerate all
			\WP_CLI::log( 'Regenerating schema for all posts, pages...');
			$regenerated += $this->regenerate_by_post_type( [ 'post', 'page' ] );
		} elseif ( isset( $assoc_args['pages'] ) ) {
			// Regenerate all pages
			\WP_CLI::log( 'Regenerating schema for all pages...' );
			$regenerated += $this->regenerate_by_post_type( [ 'page' ] );
		} elseif ( isset( $assoc_args['posts'] ) ) {
			// Regenerate all posts
			\WP_CLI::log( 'Regenerating schema for all posts...' );
			$regenerated += $this->regenerate_by_post_type( [ 'post' ] );
		} else {
			\WP_CLI::error( 'Please specify --all, --pages, --posts, --slug, or --service-pages' );
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

	/**
	 * Test Open Graph images functionality
	 *
	 * ## OPTIONS
	 *
	 * [--all]
	 * : Run all tests
	 *
	 * [--global-default]
	 * : Test global default OG image setting
	 *
	 * [--post-override]
	 * : Test post-level OG image override
	 *
	 * [--fallback-logic]
	 * : Test fallback order (override > featured > default)
	 *
	 * [--meta-output]
	 * : Test meta tag output in wp_head
	 *
	 * [--validation]
	 * : Test image validation (dimensions, file type)
	 *
	 * [--cleanup]
	 * : Clean up test posts and attachments
	 *
	 * ## EXAMPLES
	 *
	 *     # Run all OG image tests
	 *     wp 84em test-og-images --all
	 *
	 *     # Test specific functionality
	 *     wp 84em test-og-images --fallback-logic
	 *
	 *     # Clean up after tests
	 *     wp 84em test-og-images --cleanup
	 *
	 * @when after_wp_load
	 */
	public function test_og_images( $args, $assoc_args ) {
		$run_all = isset( $assoc_args['all'] );

		if ( isset( $assoc_args['cleanup'] ) || $run_all ) {
			$this->cleanup_og_tests();
		}

		if ( $run_all || isset( $assoc_args['global-default'] ) ) {
			$this->test_global_default_og_image();
		}

		if ( $run_all || isset( $assoc_args['post-override'] ) ) {
			$this->test_post_override_og_image();
		}

		if ( $run_all || isset( $assoc_args['fallback-logic'] ) ) {
			$this->test_og_fallback_logic();
		}

		if ( $run_all || isset( $assoc_args['meta-output'] ) ) {
			$this->test_og_meta_output();
		}

		if ( $run_all || isset( $assoc_args['validation'] ) ) {
			$this->test_og_image_validation();
		}

		if ( ! $run_all && ! isset( $assoc_args['cleanup'] ) && ! isset( $assoc_args['global-default'] ) &&
		     ! isset( $assoc_args['post-override'] ) && ! isset( $assoc_args['fallback-logic'] ) &&
		     ! isset( $assoc_args['meta-output'] ) && ! isset( $assoc_args['validation'] ) ) {
			\WP_CLI::error( 'Please specify --all or a specific test option' );
			return;
		}

		\WP_CLI::success( 'All OG image tests completed!' );
	}

	/**
	 * Test global default OG image setting
	 */
	private function test_global_default_og_image() {
		\WP_CLI::log( "\n=== Testing Global Default OG Image ===" );

		// Create a test image attachment
		$image_id = $this->create_test_image( 'test-default-og.jpg', 1200, 630 );

		if ( ! $image_id ) {
			\WP_CLI::error( 'Failed to create test image attachment' );
			return;
		}

		\WP_CLI::log( "✓ Created test image attachment (ID: {$image_id})" );

		// Set as global default
		\update_option( 'eightyfourem_default_og_image', $image_id );
		\WP_CLI::log( '✓ Set global default OG image option' );

		// Verify option was saved
		$saved_id = \get_option( 'eightyfourem_default_og_image' );
		if ( (int) $saved_id === $image_id ) {
			\WP_CLI::log( '✓ Global default OG image option verified' );
		} else {
			\WP_CLI::error( 'Failed to verify global default OG image option' );
		}

		// Verify image exists and has correct metadata
		$image_url = \wp_get_attachment_url( $image_id );
		$image_meta = \wp_get_attachment_metadata( $image_id );

		if ( $image_url ) {
			\WP_CLI::log( "✓ Image URL retrieved: {$image_url}" );
		} else {
			\WP_CLI::error( 'Failed to retrieve image URL' );
		}

		if ( isset( $image_meta['width'] ) && $image_meta['width'] === 1200 ) {
			\WP_CLI::log( "✓ Image width verified: {$image_meta['width']}px" );
		} else {
			\WP_CLI::error( 'Image width mismatch' );
		}

		if ( isset( $image_meta['height'] ) && $image_meta['height'] === 630 ) {
			\WP_CLI::log( "✓ Image height verified: {$image_meta['height']}px" );
		} else {
			\WP_CLI::error( 'Image height mismatch' );
		}
	}

	/**
	 * Test post-level OG image override
	 */
	private function test_post_override_og_image() {
		\WP_CLI::log( "\n=== Testing Post-Level OG Image Override ===" );

		// Create test post
		$post_id = \wp_insert_post(
			[
				'post_title'   => 'Test OG Image Override Post',
				'post_content' => 'Testing post-level OG image override functionality.',
				'post_status'  => 'publish',
				'post_type'    => 'post',
			]
		);

		if ( ! $post_id || \is_wp_error( $post_id ) ) {
			\WP_CLI::error( 'Failed to create test post' );
			return;
		}

		\WP_CLI::log( "✓ Created test post (ID: {$post_id})" );

		// Create override image
		$override_image_id = $this->create_test_image( 'test-override-og.jpg', 1200, 630 );

		if ( ! $override_image_id ) {
			\WP_CLI::error( 'Failed to create override image' );
			return;
		}

		\WP_CLI::log( "✓ Created override image (ID: {$override_image_id})" );

		// Set post-level override
		\update_post_meta( $post_id, '_eightyfourem_og_image', $override_image_id );
		\WP_CLI::log( '✓ Set post-level OG image override' );

		// Verify meta was saved
		$saved_override = \get_post_meta( $post_id, '_eightyfourem_og_image', true );
		if ( (int) $saved_override === $override_image_id ) {
			\WP_CLI::log( '✓ Post-level OG image override verified' );
		} else {
			\WP_CLI::error( 'Failed to verify post-level OG image override' );
		}

		// Test clearing override
		\delete_post_meta( $post_id, '_eightyfourem_og_image' );
		$cleared = \get_post_meta( $post_id, '_eightyfourem_og_image', true );
		if ( empty( $cleared ) ) {
			\WP_CLI::log( '✓ Post-level override cleared successfully' );
		} else {
			\WP_CLI::error( 'Failed to clear post-level override' );
		}

		// Clean up
		\wp_delete_post( $post_id, true );
		\wp_delete_attachment( $override_image_id, true );
	}

	/**
	 * Test OG image fallback logic
	 */
	private function test_og_fallback_logic() {
		\WP_CLI::log( "\n=== Testing OG Image Fallback Logic ===" );

		// Create test post
		$post_id = \wp_insert_post(
			[
				'post_title'   => 'Test OG Fallback Post',
				'post_content' => 'Testing OG image fallback order.',
				'post_status'  => 'publish',
				'post_type'    => 'post',
			]
		);

		\WP_CLI::log( "✓ Created test post (ID: {$post_id})" );

		// Create images
		$default_image = $this->create_test_image( 'test-default.jpg', 1200, 630 );
		$featured_image = $this->create_test_image( 'test-featured.jpg', 1200, 630 );
		$override_image = $this->create_test_image( 'test-override.jpg', 1200, 630 );

		\WP_CLI::log( '✓ Created test images (default, featured, override)' );

		// Set global default
		\update_option( 'eightyfourem_default_og_image', $default_image );

		// Test 1: Only default set
		$result = $this->get_og_image_for_test( $post_id );
		if ( $result === $default_image ) {
			\WP_CLI::log( '✓ Fallback to global default works' );
		} else {
			\WP_CLI::error( "Fallback to default failed (expected: {$default_image}, got: {$result})" );
		}

		// Test 2: Featured image should override default
		\set_post_thumbnail( $post_id, $featured_image );
		$result = $this->get_og_image_for_test( $post_id );
		if ( $result === $featured_image ) {
			\WP_CLI::log( '✓ Fallback to featured image works' );
		} else {
			\WP_CLI::error( "Fallback to featured failed (expected: {$featured_image}, got: {$result})" );
		}

		// Test 3: Post override should take priority
		\update_post_meta( $post_id, '_eightyfourem_og_image', $override_image );
		$result = $this->get_og_image_for_test( $post_id );
		if ( $result === $override_image ) {
			\WP_CLI::log( '✓ Post override takes priority' );
		} else {
			\WP_CLI::error( "Override priority failed (expected: {$override_image}, got: {$result})" );
		}

		// Clean up
		\wp_delete_post( $post_id, true );
		\wp_delete_attachment( $default_image, true );
		\wp_delete_attachment( $featured_image, true );
		\wp_delete_attachment( $override_image, true );
		\delete_option( 'eightyfourem_default_og_image' );
	}

	/**
	 * Test OG meta tag output
	 */
	private function test_og_meta_output() {
		\WP_CLI::log( "\n=== Testing OG Meta Tag Output ===" );

		// Create test post with image
		$post_id = \wp_insert_post(
			[
				'post_title'   => 'Test Meta Output Post',
				'post_content' => 'Testing meta tag output.',
				'post_status'  => 'publish',
				'post_type'    => 'post',
			]
		);

		$image_id = $this->create_test_image( 'test-meta-output.jpg', 1200, 630 );
		\update_post_meta( $post_id, '_eightyfourem_og_image', $image_id );

		\WP_CLI::log( '✓ Created test post with OG image' );

		// Simulate wp_head output capture
		global $post;
		$post = \get_post( $post_id );
		\setup_postdata( $post );

		\ob_start();
		\do_action( 'wp_head' );
		$head_output = \ob_get_clean();

		\wp_reset_postdata();

		// Check for required meta tags
		$required_tags = [
			'og:image'         => false,
			'og:image:width'   => false,
			'og:image:height'  => false,
			'og:image:alt'     => false,
			'twitter:image'    => false,
		];

		foreach ( $required_tags as $tag => $found ) {
			if ( \strpos( $head_output, "property=\"{$tag}\"" ) !== false ||
			     \strpos( $head_output, "name=\"{$tag}\"" ) !== false ) {
				$required_tags[ $tag ] = true;
				\WP_CLI::log( "✓ Found meta tag: {$tag}" );
			} else {
				\WP_CLI::warning( "Missing meta tag: {$tag}" );
			}
		}

		// Verify URL escaping
		$image_url = \wp_get_attachment_url( $image_id );
		if ( \strpos( $head_output, \esc_url( $image_url ) ) !== false ) {
			\WP_CLI::log( '✓ Image URL properly escaped' );
		} else {
			\WP_CLI::warning( 'Image URL escaping check failed' );
		}

		// Verify HTTPS (secure_url)
		if ( \strpos( $head_output, 'og:image:secure_url' ) !== false ) {
			\WP_CLI::log( '✓ Secure URL tag present' );
		}

		// Clean up
		\wp_delete_post( $post_id, true );
		\wp_delete_attachment( $image_id, true );
	}

	/**
	 * Test OG image validation
	 */
	private function test_og_image_validation() {
		\WP_CLI::log( "\n=== Testing OG Image Validation ===" );

		// Test 1: Valid image (1200x630)
		$valid_image = $this->create_test_image( 'test-valid.jpg', 1200, 630 );
		$meta = \wp_get_attachment_metadata( $valid_image );

		if ( $meta['width'] >= 1200 && $meta['height'] >= 630 ) {
			\WP_CLI::log( '✓ Valid image dimensions (1200x630)' );
		} else {
			\WP_CLI::error( 'Valid image validation failed' );
		}

		// Test 2: Small image (should warn but not fail)
		$small_image = $this->create_test_image( 'test-small.jpg', 600, 315 );
		$small_meta = \wp_get_attachment_metadata( $small_image );

		if ( $small_meta['width'] < 1200 || $small_meta['height'] < 630 ) {
			\WP_CLI::log( '✓ Small image detected (would show warning in admin)' );
		}

		// Test 3: Check MIME type
		$mime_type = \get_post_mime_type( $valid_image );
		$allowed_types = [ 'image/jpeg', 'image/png', 'image/webp' ];

		if ( \in_array( $mime_type, $allowed_types, true ) ) {
			\WP_CLI::log( "✓ Valid MIME type: {$mime_type}" );
		} else {
			\WP_CLI::error( "Invalid MIME type: {$mime_type}" );
		}

		// Test 4: Check attachment exists
		$attachment = \get_post( $valid_image );
		if ( $attachment && $attachment->post_type === 'attachment' ) {
			\WP_CLI::log( '✓ Attachment post type verified' );
		} else {
			\WP_CLI::error( 'Attachment verification failed' );
		}

		// Test 5: Check file exists on disk
		$file_path = \get_attached_file( $valid_image );
		if ( \file_exists( $file_path ) ) {
			\WP_CLI::log( "✓ File exists on disk: {$file_path}" );
		} else {
			\WP_CLI::error( 'File does not exist on disk' );
		}

		// Clean up
		\wp_delete_attachment( $valid_image, true );
		\wp_delete_attachment( $small_image, true );
	}

	/**
	 * Get OG image ID for testing (simulates the actual function logic)
	 *
	 * @param int $post_id Post ID
	 *
	 * @return int|null Image attachment ID
	 */
	private function get_og_image_for_test( $post_id ) {
		// Check post override first
		$override = \get_post_meta( $post_id, '_eightyfourem_og_image', true );
		if ( $override ) {
			return (int) $override;
		}

		// Check featured image
		$featured = \get_post_thumbnail_id( $post_id );
		if ( $featured ) {
			return (int) $featured;
		}

		// Check global default
		$default = \get_option( 'eightyfourem_default_og_image' );
		if ( $default ) {
			return (int) $default;
		}

		return null;
	}

	/**
	 * Create a test image attachment
	 *
	 * @param string $filename Filename
	 * @param int    $width    Image width
	 * @param int    $height   Image height
	 *
	 * @return int|false Attachment ID or false on failure
	 */
	private function create_test_image( $filename, $width, $height ) {
		// Create a simple test image using GD
		$image = \imagecreatetruecolor( $width, $height );
		$bg_color = \imagecolorallocate( $image, 200, 200, 200 );
		\imagefill( $image, 0, 0, $bg_color );

		// Add text with dimensions
		$text_color = \imagecolorallocate( $image, 50, 50, 50 );
		$text = "{$width}x{$height}";
		\imagestring( $image, 5, $width / 2 - 50, $height / 2 - 10, $text, $text_color );

		// Save to temp file
		$upload_dir = \wp_upload_dir();
		$temp_file = $upload_dir['path'] . '/' . $filename;
		\imagejpeg( $image, $temp_file, 90 );
		\imagedestroy( $image );

		// Insert as attachment
		$attachment = [
			'post_mime_type' => 'image/jpeg',
			'post_title'     => $filename,
			'post_content'   => '',
			'post_status'    => 'inherit',
		];

		$attachment_id = \wp_insert_attachment( $attachment, $temp_file );

		if ( ! \is_wp_error( $attachment_id ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$attachment_data = \wp_generate_attachment_metadata( $attachment_id, $temp_file );
			\wp_update_attachment_metadata( $attachment_id, $attachment_data );

			return $attachment_id;
		}

		return false;
	}

	/**
	 * Clean up test posts and attachments
	 */
	private function cleanup_og_tests() {
		\WP_CLI::log( "\n=== Cleaning Up Test Data ===" );

		// Clean up test posts
		$test_posts = \get_posts(
			[
				'post_type'      => 'post',
				'posts_per_page' => -1,
				'post_status'    => 'any',
				's'              => 'Test OG',
			]
		);

		foreach ( $test_posts as $post ) {
			\wp_delete_post( $post->ID, true );
			\WP_CLI::log( "✓ Deleted test post: {$post->post_title}" );
		}

		// Clean up test attachments
		$test_attachments = \get_posts(
			[
				'post_type'      => 'attachment',
				'posts_per_page' => -1,
				'post_status'    => 'any',
				's'              => 'test-',
			]
		);

		foreach ( $test_attachments as $attachment ) {
			\wp_delete_attachment( $attachment->ID, true );
			\WP_CLI::log( "✓ Deleted test attachment: {$attachment->post_title}" );
		}

		// Clean up test option
		\delete_option( 'eightyfourem_default_og_image' );
		\WP_CLI::log( '✓ Deleted test option' );

		\WP_CLI::success( 'Cleanup complete' );
	}
}

// Register the commands
\WP_CLI::add_command( '84em regenerate-schema', [ new ThemeCLI(), 'regenerate_schema' ] );
\WP_CLI::add_command( '84em test-og-images', [ new ThemeCLI(), 'test_og_images' ] );
