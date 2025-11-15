<?php
/**
 * Related Case Studies
 * Automatically displays related case studies on individual case study pages
 *
 * @package EightyFourEM
 */

namespace EightyFourEM\RelatedCaseStudies;

use function add_action;
use function add_filter;
use function array_column;
use function array_intersect;
use function array_slice;
use function count;
use function delete_transient;
use function esc_attr;
use function esc_html;
use function esc_url;
use function get_permalink;
use function get_post;
use function get_posts;
use function get_template_directory_uri;
use function get_the_post_thumbnail_url;
use function get_transient;
use function is_page;
use function is_singular;
use function ob_get_clean;
use function ob_start;
use function set_transient;
use function strtotime;
use function usort;
use function wp_get_theme;
use function wp_strip_all_tags;
use function wp_trim_words;

defined( 'ABSPATH' ) || exit;

/**
 * Get related case studies for a given post
 *
 * @param int $post_id Current case study post ID
 * @param int $limit Maximum number of related studies to return
 * @return array Array of WP_Post objects
 */
function get_related_case_studies( int $post_id, int $limit = 6 ): array {
	$cache_key = 'related_cs_' . $post_id;
	$cached    = get_transient( $cache_key );

	if ( false !== $cached ) {
		return $cached;
	}

	$current_categories = \EightyFourEM\CaseStudyFilters\get_case_study_categories( $post_id );

	if ( empty( $current_categories ) ) {
		return [];
	}

	$all_case_studies = get_posts(
		args: [
			'post_type'    => 'page',
			'post_parent'  => 4406,
			'post__not_in' => [ $post_id ],
			'numberposts'  => -1,
			'orderby'      => 'date',
			'order'        => 'DESC',
		]
	);

	$scored_studies = [];

	foreach ( $all_case_studies as $study ) {
		$study_categories  = \EightyFourEM\CaseStudyFilters\get_case_study_categories( $study->ID );
		$shared_categories = array_intersect( $current_categories, $study_categories );
		$score             = count( $shared_categories );

		if ( $score > 0 ) {
			$scored_studies[] = [
				'post'       => $study,
				'score'      => $score,
				'categories' => $study_categories,
			];
		}
	}

	usort(
		array: $scored_studies,
		callback: function ( array $a, array $b ): int {
			if ( $a['score'] !== $b['score'] ) {
				return $b['score'] - $a['score'];
			}
			return strtotime( $b['post']->post_date ) - strtotime( $a['post']->post_date );
		}
	);

	$related = array_slice( array_column( $scored_studies, 'post' ), 0, $limit );

	set_transient(
		transient: $cache_key,
		value: $related,
		expiration: HOUR_IN_SECONDS
	);

	return $related;
}

/**
 * Render related case studies HTML
 *
 * @param int $post_id Current post ID
 * @return string HTML output
 */
function render_related_case_studies( int $post_id ): string {
	$related = get_related_case_studies(
		post_id: $post_id,
		limit: 6
	);

	if ( empty( $related ) ) {
		return '';
	}

	$filters = \EightyFourEM\CaseStudyFilters\get_filters();

	ob_start();
	?>
	<section class="related-case-studies">
		<div class="related-case-studies-container">
			<h2 class="related-case-studies-heading">Related Case Studies</h2>

			<div class="related-case-studies-grid">
				<?php foreach ( $related as $study ) :
					$study_categories = \EightyFourEM\CaseStudyFilters\get_case_study_categories( $study->ID );
					$permalink        = get_permalink( $study->ID );
					$thumbnail        = get_the_post_thumbnail_url(
						post: $study->ID,
						size: 'medium'
					);
					$excerpt          = wp_trim_words(
						text: wp_strip_all_tags( $study->post_content ),
						num_words: 20
					);
				?>
					<article class="related-case-study-card">
						<a href="<?php echo esc_url( $permalink ); ?>" class="related-case-study-link">
							<?php if ( $thumbnail ) : ?>
								<div class="related-case-study-image">
									<img src="<?php echo esc_url( $thumbnail ); ?>"
									     alt="<?php echo esc_attr( $study->post_title ); ?>"
									     loading="lazy">
								</div>
							<?php endif; ?>

							<div class="related-case-study-content">
								<h3 class="related-case-study-title">
									<?php echo esc_html( $study->post_title ); ?>
								</h3>

								<p class="related-case-study-excerpt">
									<?php echo esc_html( $excerpt ); ?>
								</p>

								<?php if ( ! empty( $study_categories ) ) : ?>
									<div class="related-case-study-categories">
										<?php foreach ( $study_categories as $cat_key ) :
											if ( isset( $filters[ $cat_key ] ) ) :
										?>
											<span class="related-case-study-badge">
												<?php echo esc_html( $filters[ $cat_key ]['label'] ); ?>
											</span>
										<?php
											endif;
										endforeach;
										?>
									</div>
								<?php endif; ?>
							</div>
						</a>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * Inject related case studies into post content
 */
add_filter(
	hook_name: 'the_content',
	callback: function ( string $content ): string {
		if ( ! is_page() || ! is_singular() ) {
			return $content;
		}

		$post = get_post();
		if ( ! $post || 4406 !== $post->post_parent ) {
			return $content;
		}

		$related_html = render_related_case_studies( $post->ID );

		if ( empty( $related_html ) ) {
			return $content;
		}

		return $content . $related_html;
	},
	priority: 20
);

/**
 * Clear related case studies cache when a case study is updated
 */
add_action(
	hook_name: 'save_post_page',
	callback: function ( int $post_id ): void {
		$post = get_post( $post_id );

		if ( ! $post || 4406 !== $post->post_parent ) {
			return;
		}

		delete_transient( 'related_cs_' . $post_id );

		$all_case_studies = get_posts(
			args: [
				'post_type'   => 'page',
				'post_parent' => 4406,
				'numberposts' => -1,
				'fields'      => 'ids',
			]
		);

		foreach ( $all_case_studies as $study_id ) {
			delete_transient( 'related_cs_' . $study_id );
		}
	}
);
