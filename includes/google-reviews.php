<?php

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

class GoogleReviewsBlock {
    /**
     * Constructor method for initializing hooks and actions.
     *
     * @return void
     */
    public function __construct() {
        \add_action( 'init', [ $this, 'init' ] );
        \add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
        \add_action( 'admin_init', [ $this, 'settings_init' ] );
        \add_action( 'wp_ajax_get_google_reviews', [ $this, 'ajax_get_reviews' ] );
        \add_action( 'wp_ajax_nopriv_get_google_reviews', [ $this, 'ajax_get_reviews' ] );
    }

    /**
     * Initializes the block by registering scripts, styles, and attributes.
     *
     * This method is responsible for registering the editor and frontend scripts/styles
     * for the Google Reviews block, setting up its attributes, and ensuring
     * proper block type registration with a render callback. It also localizes
     * the script to enable AJAX functionality.
     *
     * @return void
     */
    public function init(): void {
        // Determine if we should use minified files
        $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

        // Check if minified files exist, otherwise fallback to non-minified
        $js_file = 'block' . $suffix . '.js';
        $js_path = \get_template_directory() . '/assets/google-reviews-block/' . $js_file;
        if ( $suffix && ! file_exists( $js_path ) ) {
            $js_file = 'block.js';
        }

        $editor_css_file = 'editor' . $suffix . '.css';
        $editor_css_path = \get_template_directory() . '/assets/google-reviews-block/' . $editor_css_file;
        if ( $suffix && ! file_exists( $editor_css_path ) ) {
            $editor_css_file = 'editor.css';
        }

        $style_css_file = 'style' . $suffix . '.css';
        $style_css_path = \get_template_directory() . '/assets/google-reviews-block/' . $style_css_file;
        if ( $suffix && ! file_exists( $style_css_path ) ) {
            $style_css_file = 'style.css';
        }

        \wp_register_script(
            handle: 'google-reviews-block-editor',
            src: \get_template_directory_uri() . '/assets/google-reviews-block/' . $js_file,
            deps: [ 'wp-blocks', 'wp-element', 'wp-editor', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-data' ],
            ver: \wp_get_theme()->get( 'Version' )
        );

        \wp_register_style(
            handle: 'google-reviews-block-editor',
            src: \get_template_directory_uri() . '/assets/google-reviews-block/' . $editor_css_file,
            deps: [ 'wp-edit-blocks' ],
            ver: \wp_get_theme()->get( 'Version' )
        );

        \wp_register_style(
            handle: 'google-reviews-block-style',
            src: \get_template_directory_uri() . '/assets/google-reviews-block/' . $style_css_file,
            deps: [],
            ver: \wp_get_theme()->get( 'Version' )
        );

        \register_block_type(
            block_type: 'google-reviews/display',
            args: [
                'editor_script'   => 'google-reviews-block-editor',
                'editor_style'    => 'google-reviews-block-editor',
                'style'           => 'google-reviews-block-style',
                'render_callback' => [ $this, 'render_block' ],
                'attributes'      => [
                    'showLink'          => [
                        'type'    => 'boolean',
                        'default' => true,
                    ],
                    'showReviewContent' => [
                        'type'    => 'boolean',
                        'default' => false,
                    ],
                    'maxReviews'        => [
                        'type'    => 'number',
                        'default' => 5, // Google Places API only returns max 5 reviews
                    ],
                    'alignment'         => [
                        'type'    => 'string',
                        'default' => 'left',
                    ],
                    'backgroundColor'   => [
                        'type'    => 'string',
                        'default' => '#f9f9f9',
                    ],
                    'textColor'         => [
                        'type'    => 'string',
                        'default' => '#333333',
                    ],
                    // Individual color controls
                    'titleTextColor'         => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'titleBackgroundColor'   => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'ratingTextColor'        => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'ratingBackgroundColor'  => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'reviewsTextColor'       => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'reviewsBackgroundColor' => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'reviewsSort'       => [
                        'type'    => 'string',
                        'default' => 'most_relevant',
                        'enum'    => [ 'most_relevant', 'newest' ],
                    ],
                    'overrideUrl'       => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'overrideTitle'     => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'customRatingText'  => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'showTitle'         => [
                        'type'    => 'boolean',
                        'default' => true,
                    ],
                    'showRatingText'    => [
                        'type'    => 'boolean',
                        'default' => true,
                    ],
                    'ratingTextBelow'   => [
                        'type'    => 'boolean',
                        'default' => false,
                    ],
                    // Typography attributes for different elements
                    'titleFontSize'     => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'titleFontSizeCustom' => [
                        'type'    => 'number',
                        'default' => null,
                    ],
                    'ratingTextFontSize' => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'ratingTextFontSizeCustom' => [
                        'type'    => 'number',
                        'default' => null,
                    ],
                    'reviewsFontSize'   => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'reviewsFontSizeCustom' => [
                        'type'    => 'number',
                        'default' => null,
                    ],
                    'reviewTimeFontSize'   => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'reviewTimeFontSizeCustom' => [
                        'type'    => 'number',
                        'default' => null,
                    ],
                    'reviewTimeTextColor'       => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'reviewTimeBackgroundColor' => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    // Stars typography and color
                    'starsFontSize'   => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'starsFontSizeCustom' => [
                        'type'    => 'number',
                        'default' => null,
                    ],
                    'starsTextColor'       => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'starsBackgroundColor' => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                ],
            ] );

        \wp_localize_script(
            handle: 'google-reviews-block-editor',
            object_name: 'googleReviewsAjax',
            l10n: [
                'ajax_url' => \admin_url( 'admin-ajax.php' ),
                'nonce'    => \wp_create_nonce( 'google_reviews_nonce' ),
            ] );
    }

    /**
     * Registers a new admin menu item in the WordPress dashboard under the "Settings" menu.
     *
     * @return void
     */
    public function add_admin_menu(): void {
        \add_options_page(
            page_title: 'Google Reviews Settings',
            menu_title: 'Google Reviews',
            capability: 'manage_options',
            menu_slug: 'google-reviews-block',
            callback: [ $this, 'options_page' ]
        );
    }

    /**
     * Initializes the settings for the Google Reviews block by registering options, sections, and fields.
     *
     * @return void
     */
    public function settings_init(): void {
        \register_setting(
            option_group: 'google_reviews_block',
            option_name: 'google_reviews_block_settings' );

        \add_settings_section(
            id: 'google_reviews_block_section',
            title: 'Google Reviews Configuration',
            callback: [ $this, 'settings_section_callback' ],
            page: 'google_reviews_block'
        );

        \add_settings_field(
            id: 'business_name',
            title: 'Business Name',
            callback: [ $this, 'business_name_render' ],
            page: 'google_reviews_block',
            section: 'google_reviews_block_section'
        );

        \add_settings_field(
            id: 'place_id',
            title: 'Google Place ID',
            callback: [ $this, 'place_id_render' ],
            page: 'google_reviews_block',
            section: 'google_reviews_block_section'
        );

        \add_settings_field(
            id: 'api_key',
            title: 'Google Places API Key',
            callback: [ $this, 'api_key_render' ],
            page: 'google_reviews_block',
            section: 'google_reviews_block_section'
        );
    }

    /**
     * Renders the input field for the business name in the plugin settings.
     *
     * @return void
     */
    public function business_name_render(): void {
        $options = \get_option( 'google_reviews_block_settings' );
        ?>
        <input type='text' name='google_reviews_block_settings[business_name]' value='<?php echo isset( $options['business_name'] ) ? \esc_attr( $options['business_name'] ) : ''; ?>' class='regular-text'>
        <p class='description'>Enter your business name as it appears on Google</p>
        <?php
    }

    /**
     * Renders the input field for entering the Google Place ID in the plugin settings.
     *
     * Provides a text input field for users to specify their Google Place ID and includes
     * a description with a link to the Google Place ID Finder for reference.
     *
     * @return void
     */
    public function place_id_render(): void {
        $options = \get_option( 'google_reviews_block_settings' );
        ?>
        <input type='text' name='google_reviews_block_settings[place_id]' value='<?php echo isset( $options['place_id'] ) ? \esc_attr( $options['place_id'] ) : ''; ?>' class='regular-text'>
        <p class='description'>Your Google Place ID (find it at <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank">Google Place ID Finder</a>)</p>
        <?php
    }

    /**
     * Renders the input field for the Google Places API key in the plugin settings page.
     *
     * @return void
     */
    public function api_key_render(): void {
        $options = \get_option( 'google_reviews_block_settings' );
        ?>
        <input type='text' name='google_reviews_block_settings[api_key]' value='<?php echo isset( $options['api_key'] ) ? \esc_attr( $options['api_key'] ) : ''; ?>' class='regular-text'>
        <p class='description'>Google Places API key (get one from <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a>)</p>
        <?php
    }

    /**
     * Callback function for rendering the content of the settings section in the plugin settings page.
     *
     * @return void
     */
    public function settings_section_callback(): void {
        echo 'Configure your Google Reviews settings below:';
    }

    /**
     * Renders the options page for the Google Reviews Block plugin in the WordPress admin area.
     *
     * @return void
     */
    public function options_page(): void {
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
     * Fetches Google Reviews data for a specific Place ID using the Google Places API.
     * Verifies the presence of API key and Place ID from settings, attempts to retrieve cached data,
     * and if not available, makes an API request to fetch the details. Caches the result for 24 hours.
     *
     * @return mixed Returns an associative array containing reviews data if successful.
     *               Returns false if API key or Place ID is missing, the request fails,
     *               or the API response is invalid.
     */
    public function get_google_reviews( $sort_by = 'most_relevant' ): mixed {
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

        // Check for cached data (24 hour cache) - include sort in cache key
        $cache_key   = 'google_reviews_block_' . \md5( $place_id . '_' . $sort_by );
        $cached_data = \get_transient( $cache_key );

        if ( $cached_data !== false ) {
            return $cached_data;
        }

        // Note: Google Places API only returns a maximum of 5 reviews
        // Sort options: most_relevant (default) or newest
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
     * Handles an AJAX request to retrieve Google reviews.
     * Verifies the request's nonce for security and fetches reviews.
     * Returns the reviews data in JSON format or an error message if the fetch fails.
     *
     * @return void
     */
    public function ajax_get_reviews(): void {
        if ( ! \wp_verify_nonce( $_POST['nonce'], 'google_reviews_nonce' ) ) {
            \wp_die( 'Security check failed' );
        }

        $sort_by = isset( $_POST['sort_by'] ) ? sanitize_text_field( $_POST['sort_by'] ) : 'most_relevant';
        $reviews = $this->get_google_reviews( $sort_by );

        if ( $reviews ) {
            \wp_send_json_success( $reviews );
        } else {
            \wp_send_json_error( 'Unable to fetch reviews' );
        }
    }


    /**
     * Renders a block for displaying Google reviews.
     *
     * @param  array  $attributes  {
     *     Optional. A set of attributes passed to the block.
     *
     * @type bool $showLink Whether to display the link to view reviews on Google. Default true.
     * @type bool $showReviewContent Whether to display the review content for individual reviews. Default false.
     * @type int $maxReviews The maximum number of individual reviews to display. Default 3.
     * @type string $alignment Text alignment for the block. Default 'left'.
     * @type string $backgroundColor Background color for the block. Default '#f9f9f9'.
     * @type string $textColor Text color for the block. Default '#333333'.
     * }
     * @return bool|string The rendered block's HTML as a string, or false if the reviews could not be loaded.
     */
    public function render_block( array $attributes ): bool|string {
        $sort_by = $attributes['reviewsSort'] ?? 'most_relevant';
        $reviews = $this->get_google_reviews( $sort_by );

        if ( ! $reviews ) {
            return '<div class="google-reviews-block error">Unable to load reviews at this time.</div>';
        }

        $show_link           = ! isset( $attributes['showLink'] ) || $attributes['showLink'];
        $show_review_content = $attributes['showReviewContent'] ?? false;
        // Google Places API returns max 5 reviews, so cap the value
        $max_reviews         = ! isset( $attributes['maxReviews'] ) ? 5 : min( 5, \intval( $attributes['maxReviews'] ) );
        $bg_color            = $attributes['backgroundColor'] ?? '#f9f9f9';
        $text_color          = $attributes['textColor'] ?? '#333333';
        $override_url        = $attributes['overrideUrl'] ?? '';
        $override_title      = $attributes['overrideTitle'] ?? '';
        $custom_rating_text  = $attributes['customRatingText'] ?? '';
        $show_title          = $attributes['showTitle'] ?? true;
        $show_rating_text    = $attributes['showRatingText'] ?? true;
        $rating_text_below   = $attributes['ratingTextBelow'] ?? false;
        
        // Individual color attributes
        $title_text_color          = $attributes['titleTextColor'] ?? '';
        $title_background_color    = $attributes['titleBackgroundColor'] ?? '';
        $rating_text_color         = $attributes['ratingTextColor'] ?? '';
        $rating_background_color   = $attributes['ratingBackgroundColor'] ?? '';
        $reviews_text_color        = $attributes['reviewsTextColor'] ?? '';
        $reviews_background_color  = $attributes['reviewsBackgroundColor'] ?? '';
        
        // Typography attributes
        $title_font_size        = $attributes['titleFontSize'] ?? '';
        $title_font_size_custom = $attributes['titleFontSizeCustom'] ?? null;
        $rating_text_font_size        = $attributes['ratingTextFontSize'] ?? '';
        $rating_text_font_size_custom = $attributes['ratingTextFontSizeCustom'] ?? null;
        $reviews_font_size        = $attributes['reviewsFontSize'] ?? '';
        $reviews_font_size_custom = $attributes['reviewsFontSizeCustom'] ?? null;
        $review_time_font_size        = $attributes['reviewTimeFontSize'] ?? '';
        $review_time_font_size_custom = $attributes['reviewTimeFontSizeCustom'] ?? null;
        $review_time_text_color       = $attributes['reviewTimeTextColor'] ?? '';
        $review_time_background_color = $attributes['reviewTimeBackgroundColor'] ?? '';
        $stars_font_size               = $attributes['starsFontSize'] ?? '';
        $stars_font_size_custom        = $attributes['starsFontSizeCustom'] ?? null;
        $stars_text_color              = $attributes['starsTextColor'] ?? '';
        $stars_background_color        = $attributes['starsBackgroundColor'] ?? '';

        $style = "background-color: {$bg_color}; color: {$text_color};";

        // Typography classes and styles for title
        $title_class = '';
        $title_style = '';
        if ( ! empty( $title_font_size ) ) {
            $title_class .= ' has-' . $title_font_size . '-font-size';
            // Apply inline styles using theme.json rem values
            $preset_sizes = [
                'small' => '0.9rem',
                'medium' => '1.05rem',
                'large' => '1.85rem',
                'x-large' => '2.5rem',
                'xx-large' => '3.27rem',
            ];
            if ( isset( $preset_sizes[ $title_font_size ] ) ) {
                $title_style .= 'font-size: ' . $preset_sizes[ $title_font_size ] . '; ';
            }
        } elseif ( $title_font_size_custom ) {
            $title_style .= 'font-size: ' . $title_font_size_custom . 'px; ';
        }
        
        // Add individual title colors
        if ( ! empty( $title_text_color ) ) {
            $title_style .= 'color: ' . $title_text_color . '; ';
        }
        if ( ! empty( $title_background_color ) ) {
            $title_style .= 'background-color: ' . $title_background_color . '; ';
        }
        
        // Typography classes and styles for rating text
        $rating_text_class = '';
        $rating_text_style = '';
        if ( ! empty( $rating_text_font_size ) ) {
            $rating_text_class .= ' has-' . $rating_text_font_size . '-font-size';
            // Apply inline styles using theme.json rem values
            $preset_sizes = [
                'small' => '0.9rem',
                'medium' => '1.05rem',
                'large' => '1.85rem',
                'x-large' => '2.5rem',
                'xx-large' => '3.27rem',
            ];
            if ( isset( $preset_sizes[ $rating_text_font_size ] ) ) {
                $rating_text_style .= 'font-size: ' . $preset_sizes[ $rating_text_font_size ] . '; ';
            }
        } elseif ( $rating_text_font_size_custom ) {
            $rating_text_style .= 'font-size: ' . $rating_text_font_size_custom . 'px; ';
        }
        
        // Add individual rating text colors
        if ( ! empty( $rating_text_color ) ) {
            $rating_text_style .= 'color: ' . $rating_text_color . '; ';
        }
        if ( ! empty( $rating_background_color ) ) {
            $rating_text_style .= 'background-color: ' . $rating_background_color . '; ';
        }
        
        // Typography classes and styles for reviews
        $reviews_class = '';
        $reviews_style = '';
        if ( ! empty( $reviews_font_size ) ) {
            $reviews_class .= ' has-' . $reviews_font_size . '-font-size';
            // Apply inline styles using theme.json rem values
            $preset_sizes = [
                'small' => '0.9rem',
                'medium' => '1.05rem',
                'large' => '1.85rem',
                'x-large' => '2.5rem',
                'xx-large' => '3.27rem',
            ];
            if ( isset( $preset_sizes[ $reviews_font_size ] ) ) {
                $reviews_style .= 'font-size: ' . $preset_sizes[ $reviews_font_size ] . '; ';
            }
        } elseif ( $reviews_font_size_custom ) {
            $reviews_style .= 'font-size: ' . $reviews_font_size_custom . 'px; ';
        }
        
        // Add individual reviews colors
        if ( ! empty( $reviews_text_color ) ) {
            $reviews_style .= 'color: ' . $reviews_text_color . '; ';
        }
        if ( ! empty( $reviews_background_color ) ) {
            $reviews_style .= 'background-color: ' . $reviews_background_color . '; ';
        }
        
        // Typography classes and styles for review time
        $review_time_class = '';
        $review_time_style = '';
        if ( ! empty( $review_time_font_size ) ) {
            $review_time_class .= ' has-' . $review_time_font_size . '-font-size';
            // Apply inline styles using theme.json rem values
            $preset_sizes = [
                'small' => '0.9rem',
                'medium' => '1.05rem',
                'large' => '1.85rem',
                'x-large' => '2.5rem',
                'xx-large' => '3.27rem',
            ];
            if ( isset( $preset_sizes[ $review_time_font_size ] ) ) {
                $review_time_style .= 'font-size: ' . $preset_sizes[ $review_time_font_size ] . '; ';
            }
        } elseif ( $review_time_font_size_custom ) {
            $review_time_style .= 'font-size: ' . $review_time_font_size_custom . 'px; ';
        }
        
        // Add individual review time colors
        if ( ! empty( $review_time_text_color ) ) {
            $review_time_style .= 'color: ' . $review_time_text_color . '; ';
        }
        if ( ! empty( $review_time_background_color ) ) {
            $review_time_style .= 'background-color: ' . $review_time_background_color . '; ';
        }
        
        // Typography classes and styles for stars
        $stars_class = '';
        $stars_style = '';
        if ( ! empty( $stars_font_size ) ) {
            $stars_class .= ' has-' . $stars_font_size . '-font-size';
            $preset_sizes = [
                'small'    => '0.9rem',
                'medium'   => '1.05rem',
                'large'    => '1.85rem',
                'x-large'  => '2.5rem',
                'xx-large' => '3.27rem',
            ];
            if ( isset( $preset_sizes[ $stars_font_size ] ) ) {
                $stars_style .= 'font-size: ' . $preset_sizes[ $stars_font_size ] . '; ';
            }
        } elseif ( $stars_font_size_custom ) {
            $stars_style .= 'font-size: ' . $stars_font_size_custom . 'px; ';
        }
        
        // Add individual stars colors
        if ( ! empty( $stars_text_color ) ) {
            $stars_style .= 'color: ' . $stars_text_color . '; ';
        }
        if ( ! empty( $stars_background_color ) ) {
            $stars_style .= 'background-color: ' . $stars_background_color . '; ';
        }
        
        // Create a style for the rating section that includes background color
        $rating_section_style = '';
        if ( ! empty( $rating_background_color ) ) {
            $rating_section_style .= 'background-color: ' . $rating_background_color . '; ';
        }

        // Display sort info for transparency
        $sort_label = ( $sort_by === 'newest' ) ? 'Most Recent' : 'Most Relevant';

        \ob_start();

        ?>
        <div class="google-reviews-block" style="<?php echo \esc_attr( $style ); ?>">
            <?php if ( $show_title ): ?>
                <div class="review-header">
                    <h3 class="<?php echo \esc_attr( trim( $title_class ) ); ?>" style="<?php echo \esc_attr( $title_style ); ?>"><?php 
                        $title_text = ! empty( $override_title ) ? $override_title : $reviews['name'];
                        // Allow specific HTML tags in title
                        $allowed_html = [
                            'p'      => [],
                            'a'      => [
                                'href'   => true,
                                'title'  => true,
                                'target' => true,
                                'rel'    => true,
                            ],
                            'br'     => [],
                            'strong' => [],
                            'em'     => [],
                        ];
                        echo \wp_kses( $title_text, $allowed_html );
                    ?></h3>
                </div>
            <?php endif; ?>
            <div class="review-rating" style="<?php echo \esc_attr( $rating_section_style ); ?>">
                <span class="rating-number"><?php echo number_format( $reviews['rating'], 1 ); ?></span>
                <span class="stars"><?php echo $this->render_stars( $reviews['rating'], $stars_class, $stars_style ); ?></span>
                <?php if ( $show_rating_text && ! $rating_text_below ): ?>
                    <span class="rating-count<?php echo \esc_attr( $rating_text_class ); ?>" style="<?php echo \esc_attr( $rating_text_style ); ?>"><?php 
                        if ( ! empty( $custom_rating_text ) ) {
                            $rating_text = str_replace( '$review_count', $reviews['total_ratings'], $custom_rating_text );
                            // Allow specific HTML tags in custom rating text
                            $allowed_html = [
                                'p'      => [],
                                'a'      => [
                                    'href'   => true,
                                    'title'  => true,
                                    'target' => true,
                                    'rel'    => true,
                                ],
                                'br'     => [],
                                'strong' => [],
                                'em'     => [],
                            ];
                            echo \wp_kses( $rating_text, $allowed_html );
                        } else {
                            echo '(' . \esc_html( $reviews['total_ratings'] ) . ' reviews)';
                        }
                    ?></span>
                <?php endif; ?>
            </div>
            <?php if ( $show_rating_text && $rating_text_below ): ?>
                <div class="rating-count-below<?php echo \esc_attr( $rating_text_class ); ?>" style="<?php echo \esc_attr( $rating_text_style ); ?>"><?php 
                    if ( ! empty( $custom_rating_text ) ) {
                        $rating_text = str_replace( '$review_count', $reviews['total_ratings'], $custom_rating_text );
                        // Allow specific HTML tags in custom rating text
                        $allowed_html = [
                            'p'      => [],
                            'a'      => [
                                'href'   => true,
                                'title'  => true,
                                'target' => true,
                                'rel'    => true,
                            ],
                            'br'     => [],
                            'strong' => [],
                            'em'     => [],
                        ];
                        echo \wp_kses( $rating_text, $allowed_html );
                    } else {
                        echo '(' . \esc_html( $reviews['total_ratings'] ) . ' reviews)';
                    }
                ?></div>
            <?php endif; ?>
            <?php if ( $show_review_content && ! empty( $reviews['reviews'] ) ): ?>
                <div class="individual-reviews<?php echo \esc_attr( $reviews_class ); ?>" style="<?php echo \esc_attr( $reviews_style ); ?>">
                    <?php
                    $individual_reviews = \array_slice( $reviews['reviews'], 0, $max_reviews );
                    foreach ( $individual_reviews as $review ):
                        ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <?php if ( isset( $review['profile_photo_url'] ) ): ?>
                                        <img src="<?php echo esc_url( $review['profile_photo_url'] ); ?>" alt="<?php echo esc_attr( $review['author_name'] ); ?>" class="reviewer-photo"/>
                                    <?php endif; ?>
                                    <div class="reviewer-details">
                                        <span class="reviewer-name"><?php echo \esc_html( $review['author_name'] ); ?></span>
                                        <div class="review-rating-individual">
                                            <?php echo $this->render_stars( $review['rating'], $stars_class, $stars_style ); ?>
                                            <span class="review-time<?php echo \esc_attr( $review_time_class ); ?>" style="<?php echo \esc_attr( $review_time_style ); ?>"><?php echo \esc_html( $this->format_review_time( $review['time'] ) ); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php if ( ! empty( $review['text'] ) ): ?>
                                <div class="review-text">
                                    <?php echo \nl2br( \esc_html( $review['text'] ) ); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ( $show_link && ( ! empty( $override_url ) || ! empty( $reviews['url'] ) ) ): ?>
                <div class="review-link">
                    <?php 
                    $link_url = ! empty( $override_url ) ? $override_url : $reviews['url'];
                    // Validate URL for security
                    if ( filter_var( $link_url, FILTER_VALIDATE_URL ) !== false ) : ?>
                        <a href="<?php echo \esc_url( $link_url ); ?>" target="_blank" rel="noopener">See All Reviews on Google</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return \ob_get_clean();
    }

    /**
     * Renders a string of star icons based on the provided rating value.
     *
     * @param  float  $rating  The rating value, typically between 0 and 5. Supports decimals for half stars.
     *
     * @return string A string containing the HTML representation of the star icons.
     */
    private function render_stars( float $rating, string $class = '', string $style = '' ): string {
        $rating = \floatval( $rating );
        
        // Parse the style to extract color and font-size
        $star_color = '';
        $font_size = '';
        if ( preg_match( '/color:\s*([^;]+);?/', $style, $matches ) ) {
            $star_color = $matches[1];
        }
        if ( preg_match( '/font-size:\s*([^;]+);?/', $style, $matches ) ) {
            $font_size = $matches[1];
        }
        
        // If no color specified, use default gold for filled stars
        $filled_color = $star_color ?: '#ffc107';
        $empty_color = '#ddd';
        
        // Build star style with font-size if provided
        $star_style_base = $font_size ? 'font-size: ' . $font_size . '; ' : '';
        
        // Remove font-size from wrapper style to avoid duplication
        $wrapper_style = preg_replace( '/font-size:\s*[^;]+;?\s*/', '', $style );
        
        $output = '<span class="stars-wrapper' . \esc_attr( $class ) . '" style="' . \esc_attr( $wrapper_style ) . '">';

        for ( $i = 1; $i <= 5; $i ++ ) {
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
     * Formats a given timestamp into a human-readable review time.
     *
     * @param  int  $timestamp  The Unix timestamp of the review.
     *
     * @return string The formatted review time. Returns 'Today', 'Yesterday', or '{n} days ago'
     *                if the review is within the last 7 days. Otherwise, returns the date in 'F j, Y' format.
     */
    private function format_review_time( int $timestamp ): string {
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
}

new GoogleReviewsBlock();
