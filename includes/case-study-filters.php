<?php
/**
 * Case Study Filters Configuration
 * Manages filter buttons and keywords for the case studies page
 *
 * @package EightyFourEM
 */

namespace EightyFourEM\CaseStudyFilters;

use function add_action;
use function add_shortcode;
use function is_page;
use function wp_localize_script;

defined( 'ABSPATH' ) || exit;

/**
 * Get all filter configurations
 *
 * @return array Filter configuration with labels and keywords
 */
function get_filters() {
    return [
            'all'         => [
                    'label'    => 'All Projects',
                    'keywords' => [],
            ],
            'api'         => [
                    'label'    => 'API',
                    'keywords' => [ 'api', 'integration' ],
            ],
            'financial'   => [
                    'label'    => 'Financial',
                    'keywords' => [ 'financial', 'fintech', 'banking', 'crypto' ],
            ],
            'gravity'     => [
                    'label'    => 'Gravity Forms',
                    'keywords' => [ 'gravity forms' ],
            ],
            'affiliate'   => [
                    'label'    => 'Affiliates',
                    'keywords' => [ 'affiliate' ],
            ],
            'woocommerce' => [
                    'label'    => 'WooCommerce',
                    'keywords' => [ 'woocommerce' ],
            ],
            'security'    => [
                    'label'    => 'Security',
                    'keywords' => [ 'security', 'authentication', 'saml', 'two factor', 'headers' ],
            ],
            'reporting'   => [
                    'label'    => 'Reporting',
                    'keywords' => [ 'reporting' ],
            ],
            'automation'  => [
                    'label'    => 'Automation',
                    'keywords' => [ 'automation', 'klaviyo', 'zapier', 'calendly', 'webhook', 'zapier', 'automatic', 'automatically' ],
            ],
    ];
}

/**
 * Render filter buttons HTML
 *
 * @return string HTML output
 */
function render_filters() {
    $filters = get_filters();

    ob_start();
    ?>
    <div class="case-study-filters">
        <?php foreach ( $filters as $key => $filter ) : ?>
            <button class="case-study-filter-btn <?php echo $key === 'all' ? 'is-active' : ''; ?>" data-filter="<?php echo esc_attr( $key ); ?>">
                <?php echo esc_html( $filter['label'] ); ?>
            </button>
        <?php endforeach; ?>
    </div>
    <div class="case-study-result-count"></div>
    <?php
    return ob_get_clean();
}

// Register shortcode
add_shortcode( 'case_study_filters', __NAMESPACE__ . '\render_filters' );

/**
 * Localize filter keywords to JavaScript
 */
add_action( 'wp_enqueue_scripts', function () {
    if ( is_page( 4406 ) ) {
        $filters  = get_filters();
        $keywords = [];

        foreach ( $filters as $key => $filter ) {
            if ( ! empty( $filter['keywords'] ) ) {
                $keywords[ $key ] = $filter['keywords'];
            }
        }

        wp_localize_script(
                'eightyfourem-case-study-filter',
                'caseStudyFilters',
                $keywords
        );
    }
}, 20 ); // Priority 20 to run after script is enqueued
