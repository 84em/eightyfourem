<?php
/**
 * Open Graph Images functionality
 *
 * Provides global default OG image setting and post-level overrides
 * with automatic fallback to featured images.
 *
 * @package Eighty Four EM
 */

namespace EightyFourEM\OpenGraph;

defined( 'ABSPATH' ) || exit;

/**
 * Get OG image data for a post with fallback logic
 *
 * Priority order:
 * 1. Post-level override
 * 2. Featured image
 * 3. Global default
 *
 * @param int $post_id Post ID
 *
 * @return array|null Array with 'id', 'url', 'width', 'height', 'alt', 'type' or null if no image
 */
function get_og_image_data( $post_id ) {
	$image_id = null;

	// Check post override first
	$override = \get_post_meta( $post_id, '_eightyfourem_og_image', true );
	if ( $override ) {
		$image_id = (int) $override;
	}

	// Check featured image
	if ( ! $image_id ) {
		$featured = \get_post_thumbnail_id( $post_id );
		if ( $featured ) {
			$image_id = (int) $featured;
		}
	}

	// Check global default
	if ( ! $image_id ) {
		$default = \get_option( 'eightyfourem_default_og_image' );
		if ( $default ) {
			$image_id = (int) $default;
		}
	}

	// No image found
	if ( ! $image_id ) {
		return null;
	}

	// Validate attachment exists
	$attachment = \get_post( $image_id );
	if ( ! $attachment || $attachment->post_type !== 'attachment' ) {
		return null;
	}

	// Get image data with transient cache
	$cache_key = "og_image_data_{$image_id}";
	$cached = \get_transient( $cache_key );
	if ( $cached !== false ) {
		return $cached;
	}

	// Get image URL and metadata
	$image_url = \wp_get_attachment_url( $image_id );
	$image_meta = \wp_get_attachment_metadata( $image_id );
	$image_alt = \get_post_meta( $image_id, '_wp_attachment_image_alt', true );
	$mime_type = \get_post_mime_type( $image_id );

	if ( ! $image_url || ! $image_meta ) {
		return null;
	}

	$data = [
		'id'     => $image_id,
		'url'    => $image_url,
		'width'  => $image_meta['width'] ?? 0,
		'height' => $image_meta['height'] ?? 0,
		'alt'    => $image_alt ?: \get_the_title( $post_id ),
		'type'   => $mime_type ?: 'image/jpeg',
	];

	// Cache for 1 hour
	\set_transient( $cache_key, $data, HOUR_IN_SECONDS );

	return $data;
}

/**
 * Validate OG image meets recommended dimensions
 *
 * @param int $attachment_id Attachment ID
 *
 * @return array Array with 'valid' boolean and 'message' string
 */
function validate_og_image( $attachment_id ) {
	$attachment = \get_post( $attachment_id );

	if ( ! $attachment || $attachment->post_type !== 'attachment' ) {
		return [
			'valid'   => false,
			'message' => 'Invalid attachment ID',
		];
	}

	$mime_type = \get_post_mime_type( $attachment_id );
	$allowed_types = [ 'image/jpeg', 'image/png', 'image/webp' ];

	if ( ! \in_array( $mime_type, $allowed_types, true ) ) {
		return [
			'valid'   => false,
			'message' => 'Image must be JPG, PNG, or WebP format',
		];
	}

	$meta = \wp_get_attachment_metadata( $attachment_id );

	if ( ! $meta || ! isset( $meta['width'] ) || ! isset( $meta['height'] ) ) {
		return [
			'valid'   => false,
			'message' => 'Unable to read image dimensions',
		];
	}

	if ( $meta['width'] < 1200 || $meta['height'] < 630 ) {
		return [
			'valid'   => true,
			'message' => sprintf(
				'Image is %dx%dpx. Recommended minimum is 1200x630px for best display on social media.',
				$meta['width'],
				$meta['height']
			),
		];
	}

	$file_path = \get_attached_file( $attachment_id );
	$file_size = $file_path && \file_exists( $file_path ) ? \filesize( $file_path ) : 0;

	if ( $file_size > 8 * 1024 * 1024 ) {
		return [
			'valid'   => true,
			'message' => sprintf(
				'Image is %s. Consider optimizing for faster loading (recommended max 8MB).',
				\size_format( $file_size )
			),
		];
	}

	return [
		'valid'   => true,
		'message' => sprintf(
			'Image meets requirements (%dx%dpx, %s)',
			$meta['width'],
			$meta['height'],
			\size_format( $file_size )
		),
	];
}

/**
 * Output Open Graph image meta tags
 */
\add_action(
	hook_name: 'wp_head',
	callback: function () {
		if ( ! \is_singular() ) {
			return;
		}

		$post_id = \get_the_ID();
		$image_data = get_og_image_data( $post_id );

		if ( ! $image_data ) {
			return;
		}

		$url = \esc_url( $image_data['url'] );
		$secure_url = \esc_url( \str_replace( 'http://', 'https://', $image_data['url'] ) );
		$width = \esc_attr( $image_data['width'] );
		$height = \esc_attr( $image_data['height'] );
		$alt = \esc_attr( $image_data['alt'] );
		$type = \esc_attr( $image_data['type'] );

		echo "\n<!-- Open Graph Images -->\n";
		echo "<meta property=\"og:image\" content=\"{$url}\" />\n";
		echo "<meta property=\"og:image:secure_url\" content=\"{$secure_url}\" />\n";
		echo "<meta property=\"og:image:width\" content=\"{$width}\" />\n";
		echo "<meta property=\"og:image:height\" content=\"{$height}\" />\n";
		echo "<meta property=\"og:image:alt\" content=\"{$alt}\" />\n";
		echo "<meta property=\"og:image:type\" content=\"{$type}\" />\n";
		echo "<meta name=\"twitter:card\" content=\"summary_large_image\" />\n";
		echo "<meta name=\"twitter:image\" content=\"{$url}\" />\n";
		echo "<meta name=\"twitter:image:alt\" content=\"{$alt}\" />\n";
		echo "<!-- /Open Graph Images -->\n\n";
	},
	priority: 5
);

/**
 * Add settings page under Settings menu
 */
\add_action(
	hook_name: 'admin_menu',
	callback: function () {
		\add_options_page(
			page_title: 'Open Graph Images',
			menu_title: 'Open Graph Images',
			capability: 'manage_options',
			menu_slug: 'eightyfourem-og-images',
			callback: __NAMESPACE__ . '\\render_settings_page'
		);
	}
);

/**
 * Render settings page
 */
function render_settings_page() {
	if ( ! \current_user_can( 'manage_options' ) ) {
		return;
	}

	$image_id = \get_option( 'eightyfourem_default_og_image' );
	$image_url = $image_id ? \wp_get_attachment_url( $image_id ) : '';
	$validation = $image_id ? validate_og_image( $image_id ) : null;

	?>
	<div class="wrap">
		<h1><?php echo \esc_html( \get_admin_page_title() ); ?></h1>

		<?php if ( isset( $_GET['settings-updated'] ) ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><strong>Settings saved.</strong></p>
			</div>
		<?php endif; ?>

		<p>Set a default Open Graph image to be used across your site. This can be overridden on individual posts, pages, and projects.</p>

		<form method="post" action="">
			<?php \wp_nonce_field( 'eightyfourem_og_settings', 'eightyfourem_og_nonce' ); ?>

			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="eightyfourem_default_og_image">Default OG Image</label>
					</th>
					<td>
						<input type="hidden" id="eightyfourem_default_og_image" name="eightyfourem_default_og_image" value="<?php echo \esc_attr( $image_id ); ?>" />

						<div id="og-image-preview" style="margin-bottom: 10px;">
							<?php if ( $image_url ) : ?>
								<img src="<?php echo \esc_url( $image_url ); ?>" style="max-width: 400px; height: auto; display: block; margin-bottom: 10px;" />
							<?php endif; ?>
						</div>

						<button type="button" class="button" id="select-og-image">
							<?php echo $image_id ? 'Change Image' : 'Select Image'; ?>
						</button>

						<?php if ( $image_id ) : ?>
							<button type="button" class="button" id="remove-og-image">Remove Image</button>
						<?php endif; ?>

						<?php if ( $validation ) : ?>
							<p class="description <?php echo $validation['valid'] ? '' : 'error'; ?>">
								<?php echo \esc_html( $validation['message'] ); ?>
							</p>
						<?php else : ?>
							<p class="description">Recommended: 1200x630px, JPG/PNG/WebP, max 8MB</p>
						<?php endif; ?>
					</td>
				</tr>
			</table>

			<?php \submit_button(); ?>
		</form>
	</div>

	<script>
	jQuery(document).ready(function($) {
		var mediaUploader;

		$('#select-og-image').on('click', function(e) {
			e.preventDefault();

			if (mediaUploader) {
				mediaUploader.open();
				return;
			}

			mediaUploader = wp.media({
				title: 'Select Default Open Graph Image',
				button: {
					text: 'Use this image'
				},
				library: {
					type: 'image'
				},
				multiple: false
			});

			mediaUploader.on('select', function() {
				var attachment = mediaUploader.state().get('selection').first().toJSON();
				$('#eightyfourem_default_og_image').val(attachment.id);
				$('#og-image-preview').html(
					'<img src="' + attachment.url + '" style="max-width: 400px; height: auto; display: block; margin-bottom: 10px;" />'
				);
				$('#remove-og-image').show();
			});

			mediaUploader.open();
		});

		$('#remove-og-image').on('click', function(e) {
			e.preventDefault();
			$('#eightyfourem_default_og_image').val('');
			$('#og-image-preview').html('');
			$(this).hide();
		});
	});
	</script>
	<?php
}

/**
 * Save settings
 */
\add_action(
	hook_name: 'admin_init',
	callback: function () {
		if ( ! isset( $_POST['eightyfourem_og_nonce'] ) ) {
			return;
		}

		if ( ! \wp_verify_nonce( $_POST['eightyfourem_og_nonce'], 'eightyfourem_og_settings' ) ) {
			return;
		}

		if ( ! \current_user_can( 'manage_options' ) ) {
			return;
		}

		$image_id = isset( $_POST['eightyfourem_default_og_image'] ) ? \absint( $_POST['eightyfourem_default_og_image'] ) : 0;

		if ( $image_id ) {
			// Validate it's an actual image attachment
			$attachment = \get_post( $image_id );
			if ( $attachment && $attachment->post_type === 'attachment' ) {
				\update_option( 'eightyfourem_default_og_image', $image_id );
			}
		} else {
			\delete_option( 'eightyfourem_default_og_image' );
		}

		// Redirect to avoid resubmission
		\wp_redirect( \add_query_arg( 'settings-updated', 'true', \wp_get_referer() ) );
		exit;
	}
);

/**
 * Enqueue media uploader scripts
 */
\add_action(
	hook_name: 'admin_enqueue_scripts',
	callback: function ( $hook ) {
		if ( $hook !== 'settings_page_eightyfourem-og-images' && $hook !== 'post.php' && $hook !== 'post-new.php' ) {
			return;
		}

		\wp_enqueue_media();
	}
);

/**
 * Add meta box to posts, pages, and projects
 */
\add_action(
	hook_name: 'add_meta_boxes',
	callback: function () {
		\add_meta_box(
			id: 'eightyfourem_og_image',
			title: 'Open Graph Image',
			callback: __NAMESPACE__ . '\\render_meta_box',
			screen: [ 'post', 'page', 'project' ],
			context: 'side',
			priority: 'default'
		);
	}
);

/**
 * Render meta box
 *
 * @param \WP_Post $post Post object
 */
function render_meta_box( $post ) {
	\wp_nonce_field( 'eightyfourem_og_meta_box', 'eightyfourem_og_meta_nonce' );

	$image_id = \get_post_meta( $post->ID, '_eightyfourem_og_image', true );
	$image_url = $image_id ? \wp_get_attachment_url( $image_id ) : '';
	$featured_id = \get_post_thumbnail_id( $post->ID );
	$default_id = \get_option( 'eightyfourem_default_og_image' );

	// Determine what's being used
	$using = 'none';
	if ( $image_id ) {
		$using = 'override';
	} elseif ( $featured_id ) {
		$using = 'featured';
	} elseif ( $default_id ) {
		$using = 'default';
	}

	?>
	<div class="og-image-meta-box">
		<input type="hidden" id="eightyfourem_og_image_id" name="eightyfourem_og_image_id" value="<?php echo \esc_attr( $image_id ); ?>" />

		<div id="og-meta-image-preview" style="margin-bottom: 10px;">
			<?php if ( $image_url ) : ?>
				<img src="<?php echo \esc_url( $image_url ); ?>" style="max-width: 100%; height: auto; display: block;" />
			<?php endif; ?>
		</div>

		<p>
			<button type="button" class="button button-secondary" id="select-og-meta-image" style="width: 100%;">
				<?php echo $image_id ? 'Change Override' : 'Set Override'; ?>
			</button>
		</p>

		<?php if ( $image_id ) : ?>
			<p>
				<button type="button" class="button button-link-delete" id="remove-og-meta-image" style="width: 100%;">
					Remove Override
				</button>
			</p>
		<?php endif; ?>

		<p class="description">
			<?php
			switch ( $using ) {
				case 'override':
					echo 'Using: <strong>Post override</strong>';
					break;
				case 'featured':
					echo 'Using: <strong>Featured image</strong>';
					break;
				case 'default':
					echo 'Using: <strong>Global default</strong>';
					break;
				default:
					echo 'No image set';
			}
			?>
		</p>
	</div>

	<script>
	jQuery(document).ready(function($) {
		var mediaUploader;

		$('#select-og-meta-image').on('click', function(e) {
			e.preventDefault();

			if (mediaUploader) {
				mediaUploader.open();
				return;
			}

			mediaUploader = wp.media({
				title: 'Select Open Graph Image Override',
				button: {
					text: 'Use this image'
				},
				library: {
					type: 'image'
				},
				multiple: false
			});

			mediaUploader.on('select', function() {
				var attachment = mediaUploader.state().get('selection').first().toJSON();
				$('#eightyfourem_og_image_id').val(attachment.id);
				$('#og-meta-image-preview').html(
					'<img src="' + attachment.url + '" style="max-width: 100%; height: auto; display: block;" />'
				);
				$('#remove-og-meta-image').show();
			});

			mediaUploader.open();
		});

		$('#remove-og-meta-image').on('click', function(e) {
			e.preventDefault();
			$('#eightyfourem_og_image_id').val('');
			$('#og-meta-image-preview').html('');
			$(this).hide();
		});
	});
	</script>
	<?php
}

/**
 * Save meta box data
 */
\add_action(
	hook_name: 'save_post',
	callback: function ( $post_id ) {
		// Check nonce
		if ( ! isset( $_POST['eightyfourem_og_meta_nonce'] ) ) {
			return;
		}

		if ( ! \wp_verify_nonce( $_POST['eightyfourem_og_meta_nonce'], 'eightyfourem_og_meta_box' ) ) {
			return;
		}

		// Check autosave
		if ( \defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions
		if ( ! \current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save or delete meta
		if ( isset( $_POST['eightyfourem_og_image_id'] ) && ! empty( $_POST['eightyfourem_og_image_id'] ) ) {
			$image_id = \absint( $_POST['eightyfourem_og_image_id'] );

			// Validate it's an actual image attachment
			$attachment = \get_post( $image_id );
			if ( $attachment && $attachment->post_type === 'attachment' ) {
				\update_post_meta( $post_id, '_eightyfourem_og_image', $image_id );

				// Clear cache
				\delete_transient( "og_image_data_{$image_id}" );
			}
		} else {
			\delete_post_meta( $post_id, '_eightyfourem_og_image' );
		}
	},
	priority: 10
);
