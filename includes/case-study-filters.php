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
                    'label'    => 'All',
                    'keywords' => [],
            ],
            'financial'   => [
                    'label'    => 'Financial',
                    'keywords' => [ 'financial', 'fintech', 'banking', 'crypto', 'bank', 'lending', 'investment', 'cryptocurrency' ],
            ],
            'security'   => [
                    'label'    => 'Security',
                    'keywords' => [ 'security', 'authentication', 'saml', 'two factor', 'headers', '2fa', 'two-factor', 'two factor'],
            ],
            'healthcare' => [
                    'label'    => 'Healthcare',
                    'keywords' => [ 'senior living', 'healthcare', 'medical', 'health', 'medical' ],
            ],
            'automation' => [
                    'label'    => 'Automation',
                    'keywords' => [ 'scheduler', 'schedule', 'cron', 'automation', 'klaviyo', 'zapier', 'calendly', 'webhook', 'zapier', 'automatic', 'automatically' ],
            ],
            'realestate' => [
                    'label'    => 'Real Estate',
                    'keywords' => [ 'commercial' ],
            ],
            'marketing'         => [
                    'label'    => 'Marketing',
                    'keywords' => [ 'twilio', 'onsignal', 'marketing', 'lead', 'advertising', 'ads', 'leads', 'sms', 'email', 'marketing automation', 'marketing automation software', 'marketing automation software' ],
            ],
            'ai'         => [
                    'label'    => 'AI',
                    'keywords' => [ 'ai-powered', 'ai analysis', 'claude', 'openai', 'chatgpt', 'codex', 'copilot', 'machine learning', 'artificial intelligence' ],
            ],
            'affiliate'   => [
                    'label'    => 'Affiliates',
                    'keywords' => [ 'affiliate', 'affiliates', 'affiliatewp' ],
            ],
            'learning'    => [
                    'label'    => 'Education',
                    'keywords' => [ 'education', 'lms', 'learning' ],
            ],
            'ecommerce'  => [
                    'label'    => 'E-Commerce',
                    'keywords' => [ 'woocommerce', 'ecommerce' ],
            ],
            'reporting'   => [
                    'label'    => 'Reporting',
                    'keywords' => [ 'reporting' ],
            ],
            'api'        => [
                    'label'    => 'API',
                    'keywords' => [ 'api', 'integration' ],
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
