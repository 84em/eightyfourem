<?php
/**
 * Google Reviews Block - Functional Implementation
 *
 * @package Eighty Four EM
 */

namespace EightyFourEM\GoogleReviews;

defined( 'ABSPATH' ) || exit;

/**
 * Register the Google Reviews block
 */
\add_action(
	hook_name: 'init',
	callback: function (): void {
		// Register block from block.json
		\register_block_type(
			\get_template_directory() . '/blocks/google-reviews'
		);

		// Localize script for AJAX
		\add_action( 'enqueue_block_editor_assets', function() {
			\wp_localize_script(
				handle: 'eightyfourem-google-reviews-editor-script',
				object_name: 'googleReviewsAjax',
				l10n: [
					'ajax_url' => \admin_url( 'admin-ajax.php' ),
					'nonce'    => \wp_create_nonce( 'google_reviews_nonce' ),
				]
			);
		}, 20 );
	},
	priority: 10
);

/**
 * Add admin menu for Google Reviews settings
 */
\add_action(
	hook_name: 'admin_menu',
	callback: function (): void {
		\add_options_page(
			page_title: 'Google Reviews Settings',
			menu_title: 'Google Reviews',
			capability: 'manage_options',
			menu_slug: 'google-reviews-block',
			callback: __NAMESPACE__ . '\\render_options_page'
		);
	},
	priority: 10
);

/**
 * Initialize settings
 */
\add_action(
	hook_name: 'admin_init',
	callback: function (): void {
		\register_setting(
			option_group: 'google_reviews_block',
			option_name: 'google_reviews_block_settings'
		);

		\add_settings_section(
			id: 'google_reviews_block_section',
			title: 'Google Reviews Configuration',
			callback: function (): void {
				echo 'Configure your Google Reviews settings below:';
			},
			page: 'google_reviews_block'
		);

		\add_settings_field(
			id: 'business_name',
			title: 'Business Name',
			callback: function (): void {
				$options = \get_option( 'google_reviews_block_settings' );
				?>
				<input type='text' name='google_reviews_block_settings[business_name]' value='<?php echo isset( $options['business_name'] ) ? \esc_attr( $options['business_name'] ) : ''; ?>' class='regular-text'>
				<p class='description'>Enter your business name as it appears on Google</p>
				<?php
			},
			page: 'google_reviews_block',
			section: 'google_reviews_block_section'
		);

		\add_settings_field(
			id: 'place_id',
			title: 'Google Place ID',
			callback: function (): void {
				$options = \get_option( 'google_reviews_block_settings' );
				?>
				<input type='text' name='google_reviews_block_settings[place_id]' value='<?php echo isset( $options['place_id'] ) ? \esc_attr( $options['place_id'] ) : ''; ?>' class='regular-text'>
				<p class='description'>Your Google Place ID (find it at <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank">Google Place ID Finder</a>)</p>
				<?php
			},
			page: 'google_reviews_block',
			section: 'google_reviews_block_section'
		);

		\add_settings_field(
			id: 'api_key',
			title: 'Google Places API Key',
			callback: function (): void {
				$options = \get_option( 'google_reviews_block_settings' );
				?>
				<input type='text' name='google_reviews_block_settings[api_key]' value='<?php echo isset( $options['api_key'] ) ? \esc_attr( $options['api_key'] ) : ''; ?>' class='regular-text'>
				<p class='description'>Google Places API key (get one from <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a>)</p>
				<?php
			},
			page: 'google_reviews_block',
			section: 'google_reviews_block_section'
		);
	},
	priority: 10
);

/**
 * Register AJAX endpoints
 */
\add_action(
	hook_name: 'wp_ajax_get_google_reviews',
	callback: __NAMESPACE__ . '\\ajax_get_reviews',
	priority: 10
);

\add_action(
	hook_name: 'wp_ajax_nopriv_get_google_reviews',
	callback: __NAMESPACE__ . '\\ajax_get_reviews',
	priority: 10
);

/**
 * Render the options page
 */
function render_options_page(): void {
	?>
	<form action='options.php' method='post'>
		<h1>Google Reviews Block Settings</h1>
		<?php
		\settings_fields( 'google_reviews_block' );
		\do_settings_sections( 'google_reviews_block' );
		\submit_button();
		?>
	</form>
	<?php
}

/**
 * Fetch Google Reviews data
 *
 * @param string $sort_by Sort option: 'most_relevant' or 'newest'
 * @return mixed Reviews data array or false on failure
 */
function get_google_reviews( string $sort_by = 'most_relevant' ): mixed {
	$options = \get_option( 'google_reviews_block_settings' );

	if ( empty( $options['place_id'] ) || empty( $options['api_key'] ) ) {
		return false;
	}

	$place_id = $options['place_id'];
	$api_key  = $options['api_key'];

	// Validate sort parameter
	if ( ! in_array( $sort_by, [ 'most_relevant', 'newest' ], true ) ) {
		$sort_by = 'most_relevant';
	}

	// Check for cached data (24 hour cache)
	$cache_key   = 'google_reviews_block_' . \md5( $place_id . '_' . $sort_by );
	$cached_data = \get_transient( $cache_key );

	if ( $cached_data !== false ) {
		return $cached_data;
	}

	// Fetch from Google Places API
	$url = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$place_id}&fields=name,rating,user_ratings_total,url,reviews&reviews_sort={$sort_by}&key={$api_key}";

	$response = \wp_remote_get( $url );

	if ( \is_wp_error( $response ) ) {
		return false;
	}

	$body = \wp_remote_retrieve_body( $response );
	$data = \json_decode( $body, true );

	if ( $data['status'] !== 'OK' ) {
		return false;
	}

	$result = $data['result'];

	$reviews_data = [
		'name'          => $options['business_name'] ?? $result['name'] ?? '',
		'rating'        => $result['rating'] ?? 0,
		'total_ratings' => $result['user_ratings_total'] ?? 0,
		'url'           => $result['url'] ?? '',
		'reviews'       => $result['reviews'] ?? [],
	];

	// Cache for 24 hours
	\set_transient( $cache_key, $reviews_data, 24 * \HOUR_IN_SECONDS );

	return $reviews_data;
}

/**
 * Handle AJAX request for reviews
 */
function ajax_get_reviews(): void {
	if ( ! \wp_verify_nonce( $_POST['nonce'], 'google_reviews_nonce' ) ) {
		\wp_die( 'Security check failed' );
	}

	$sort_by = isset( $_POST['sort_by'] ) ? sanitize_text_field( $_POST['sort_by'] ) : 'most_relevant';
	$reviews = get_google_reviews( $sort_by );

	if ( $reviews ) {
		\wp_send_json_success( $reviews );
	} else {
		\wp_send_json_error( 'Unable to fetch reviews' );
	}
}

/**
 * Render star rating HTML
 *
 * @param float  $rating Star rating value
 * @param string $class  CSS class
 * @param string $style  Inline styles
 * @return string Star HTML
 */
function render_stars( float $rating, string $class = '', string $style = '' ): string {
	$rating = floatval( $rating );

	// Parse style for color and font-size
	$star_color = '';
	$font_size  = '';
	if ( preg_match( '/color:\s*([^;]+);?/', $style, $matches ) ) {
		$star_color = $matches[1];
	}
	if ( preg_match( '/font-size:\s*([^;]+);?/', $style, $matches ) ) {
		$font_size = $matches[1];
	}

	$filled_color = $star_color ?: '#ffc107';
	$empty_color  = '#ddd';

	$star_style_base = $font_size ? 'font-size: ' . $font_size . '; ' : '';
	$wrapper_style   = preg_replace( '/font-size:\s*[^;]+;?\s*/', '', $style );

	$output = '<span class="stars-wrapper' . \esc_attr( $class ) . '" style="' . \esc_attr( $wrapper_style ) . '">';

	for ( $i = 1; $i <= 5; $i++ ) {
		if ( $i <= $rating ) {
			$output .= '<span class="star filled" style="' . $star_style_base . 'color: ' . \esc_attr( $filled_color ) . ';">★</span>';
		} elseif ( $i - 0.5 <= $rating ) {
			// Half star with gradient
			$output .= '<span class="star half" style="' . $star_style_base . 'background: linear-gradient(90deg, ' . \esc_attr( $filled_color ) . ' 50%, ' . \esc_attr( $empty_color ) . ' 50%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">★</span>';
		} else {
			$output .= '<span class="star empty" style="' . $star_style_base . 'color: ' . \esc_attr( $empty_color ) . ';">★</span>';
		}
	}

	$output .= '</span>';
	return $output;
}

/**
 * Format review timestamp to human-readable time
 *
 * @param int $timestamp Unix timestamp
 * @return string Formatted time string
 */
function format_review_time( int $timestamp ): string {
	$review_date = \date( 'F j, Y', $timestamp );
	$days_ago    = \floor( ( \time() - $timestamp ) / ( 24 * 60 * 60 ) );

	if ( $days_ago < 7 ) {
		if ( $days_ago == 0 ) {
			return 'Today';
		} elseif ( $days_ago == 1 ) {
			return 'Yesterday';
		} else {
			return $days_ago . ' days ago';
		}
	} else {
		return $review_date;
	}
}
