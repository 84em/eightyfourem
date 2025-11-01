<?php
/**
 * Enhanced WordPress Search with Fuzzy Matching and Relevance Scoring
 *
 * Implements comprehensive search enhancements including:
 * - SOUNDEX phonetic matching for sound-alike words
 * - Levenshtein distance character matching for typo tolerance
 * - N-gram partial matching for substring searches
 * - Multi-factor relevance scoring (title, content, recency, engagement)
 * - Transient caching for improved performance
 *
 * @package Eighty Four EM
 * @since 1.0.0
 */

namespace EightyFourEM\EnhancedSearch;

defined( 'ABSPATH' ) || exit;

// Constants
const CACHE_DURATION = 3600; // 1 hour cache duration
const MAX_SEARCH_LENGTH = 100; // Maximum search term length
const FUZZY_THRESHOLD = 100; // Apply fuzzy matching if results < this number
const LEVENSHTEIN_THRESHOLD = 2; // Maximum edit distance for matches
const NGRAM_THRESHOLD = 0.5; // Minimum similarity for n-gram matches
const NGRAM_SIZE = 3; // Trigram matching

// Relevance scoring weights (must total 100%)
const WEIGHT_TITLE = 0.40; // 40% weight for title matches
const WEIGHT_CONTENT = 0.30; // 30% weight for content matches
const WEIGHT_RECENCY = 0.15; // 15% weight for post recency
const WEIGHT_ENGAGEMENT = 0.15; // 15% weight for engagement metrics

// Common stop words to filter from fuzzy matching
const STOP_WORDS = [
	'the', 'be', 'to', 'of', 'and', 'a', 'in', 'that', 'have',
	'i', 'it', 'for', 'not', 'on', 'with', 'he', 'as', 'you',
	'do', 'at', 'this', 'but', 'his', 'by', 'from', 'is', 'was',
	'are', 'been', 'has', 'had', 'were', 'said', 'did', 'will',
	'would', 'could', 'should', 'may', 'might', 'must', 'shall',
	'can', 'need', 'dare', 'ought', 'used', 'an', 'or', 'if'
];

/**
 * Calculate Levenshtein distance between two strings
 *
 * @param string $str1 First string
 * @param string $str2 Second string
 * @return float Normalized score (0-1, where 1 is exact match)
 */
function calculate_levenshtein_score( string $str1, string $str2 ): float {
	// Normalize strings for comparison
	$str1 = strtolower( trim( $str1 ) );
	$str2 = strtolower( trim( $str2 ) );

	// Return 1.0 for exact matches
	if ( $str1 === $str2 ) {
		return 1.0;
	}

	// Calculate Levenshtein distance
	$distance = levenshtein( $str1, $str2 );
	$max_length = max( strlen( $str1 ), strlen( $str2 ) );

	// Return 0 if distance exceeds threshold
	if ( $distance > LEVENSHTEIN_THRESHOLD || $max_length === 0 ) {
		return 0.0;
	}

	// Calculate normalized score (inverse of distance ratio)
	return 1.0 - ( $distance / $max_length );
}

/**
 * Calculate N-gram similarity between two strings
 * Uses trigram matching for partial word matching
 *
 * @param string $str1 First string
 * @param string $str2 Second string
 * @return float Similarity score (0-1)
 */
function calculate_ngram_similarity( string $str1, string $str2 ): float {
	// Normalize strings
	$str1 = strtolower( trim( $str1 ) );
	$str2 = strtolower( trim( $str2 ) );

	// Handle short strings
	if ( strlen( $str1 ) < NGRAM_SIZE || strlen( $str2 ) < NGRAM_SIZE ) {
		// Fall back to simple substring matching for short strings
		if ( strpos( $str1, $str2 ) !== false || strpos( $str2, $str1 ) !== false ) {
			return 0.7;
		}
		return 0.0;
	}

	// Generate n-grams for both strings
	$ngrams1 = generate_ngrams( $str1 );
	$ngrams2 = generate_ngrams( $str2 );

	// Calculate Jaccard similarity coefficient
	$intersection = array_intersect( $ngrams1, $ngrams2 );
	$union = array_unique( array_merge( $ngrams1, $ngrams2 ) );

	if ( count( $union ) === 0 ) {
		return 0.0;
	}

	return count( $intersection ) / count( $union );
}

/**
 * Generate n-grams from a string
 *
 * @param string $str Input string
 * @return array Array of n-grams
 */
function generate_ngrams( string $str ): array {
	$ngrams = [];
	$length = strlen( $str );

	for ( $i = 0; $i <= $length - NGRAM_SIZE; $i++ ) {
		$ngrams[] = substr( $str, $i, NGRAM_SIZE );
	}

	return $ngrams;
}

/**
 * Calculate multi-factor relevance score for a post
 *
 * @param \WP_Post $post Post object to score
 * @param string $search_term Search term
 * @return float Relevance score (0-100)
 */
function calculate_relevance_score( \WP_Post $post, string $search_term ): float {
	$score = 0.0;
	$search_term = strtolower( trim( $search_term ) );
	$search_words = array_filter( explode( ' ', $search_term ) );

	// 1. Title Match Score (40%)
	$title_score = 0.0;
	$title_lower = strtolower( $post->post_title );

	// Exact match in title
	if ( strpos( $title_lower, $search_term ) !== false ) {
		$title_score = 100;
	} else {
		// Check individual words
		$word_matches = 0;
		foreach ( $search_words as $word ) {
			if ( strlen( $word ) > 2 && strpos( $title_lower, $word ) !== false ) {
				$word_matches++;
			}
		}
		if ( $word_matches > 0 ) {
			$title_score = ( $word_matches / count( $search_words ) ) * 80;
		} else {
			// Try fuzzy matching on title
			$levenshtein = calculate_levenshtein_score( $title_lower, $search_term );
			$ngram = calculate_ngram_similarity( $title_lower, $search_term );
			$title_score = max( $levenshtein, $ngram ) * 60;
		}
	}
	$score += $title_score * WEIGHT_TITLE;

	// 2. Content Match Score (30%)
	$content_score = 0.0;
	$content_lower = strtolower( strip_tags( $post->post_content ) );
	$content_length = strlen( $content_lower );

	if ( $content_length > 0 ) {
		// Calculate keyword density
		$total_matches = 0;
		$position_weight = 1.0;

		foreach ( $search_words as $word ) {
			if ( strlen( $word ) > 2 ) {
				$matches = substr_count( $content_lower, $word );
				$total_matches += $matches;

				// Give higher weight to matches near the beginning
				$first_pos = strpos( $content_lower, $word );
				if ( $first_pos !== false ) {
					$position_weight = max( $position_weight, 1.0 - ( $first_pos / $content_length ) * 0.3 );
				}
			}
		}

		if ( $total_matches > 0 ) {
			// Keyword density with diminishing returns
			$density = min( $total_matches / count( $search_words ), 10 ) / 10;
			$content_score = $density * $position_weight * 100;
		}
	}
	$score += $content_score * WEIGHT_CONTENT;

	// 3. Recency Score (15%)
	$post_date = strtotime( $post->post_date );
	$current_date = current_time( 'timestamp' );
	$days_old = ( $current_date - $post_date ) / ( 60 * 60 * 24 );

	// Logarithmic decay: newer posts score higher
	if ( $days_old <= 0 ) {
		$recency_score = 100;
	} elseif ( $days_old < 7 ) {
		$recency_score = 90;
	} elseif ( $days_old < 30 ) {
		$recency_score = 75;
	} elseif ( $days_old < 90 ) {
		$recency_score = 60;
	} elseif ( $days_old < 365 ) {
		$recency_score = 40;
	} else {
		// Logarithmic decay for older posts
		$recency_score = max( 0, 40 - log( $days_old / 365 ) * 10 );
	}
	$score += $recency_score * WEIGHT_RECENCY;

	// 4. Engagement Score (15%)
	$engagement_score = 0.0;

	// Comment count (can be extended with view tracking if available)
	$comment_count = intval( $post->comment_count );
	if ( $comment_count > 0 ) {
		// Logarithmic scale for comments
		$engagement_score = min( 100, log( $comment_count + 1 ) * 25 );
	}

	// Check for view count meta if available
	$view_count = get_post_meta( $post->ID, '_view_count', true );
	if ( $view_count ) {
		$view_score = min( 100, log( intval( $view_count ) / 100 + 1 ) * 20 );
		$engagement_score = max( $engagement_score, $view_score );
	}

	$score += $engagement_score * WEIGHT_ENGAGEMENT;

	return $score;
}

/**
 * Get cached search results
 *
 * @param string $search_term Search term
 * @param array $args Additional query arguments
 * @return array|null Cached results or null if not found
 */
function get_cached_search_results( string $search_term, array $args ): ?array {
	// Generate unique cache key
	$cache_key = 'eightyfourem_search_' . md5( $search_term . serialize( $args ) );

	// Try to get from transient
	$cached = get_transient( $cache_key );

	if ( $cached !== false ) {
		return $cached;
	}

	// Also check object cache if available
	if ( wp_using_ext_object_cache() ) {
		$cached = wp_cache_get( $cache_key, 'eightyfourem_search' );
		if ( $cached !== false ) {
			return $cached;
		}
	}

	return null;
}

/**
 * Set cached search results
 *
 * @param string $search_term Search term
 * @param array $args Additional query arguments
 * @param array $results Search results to cache
 * @return void
 */
function set_cached_search_results( string $search_term, array $args, array $results ): void {
	// Generate unique cache key
	$cache_key = 'eightyfourem_search_' . md5( $search_term . serialize( $args ) );

	// Store in transient
	set_transient( $cache_key, $results, CACHE_DURATION );

	// Also store in object cache if available
	if ( wp_using_ext_object_cache() ) {
		wp_cache_set( $cache_key, $results, 'eightyfourem_search', CACHE_DURATION );
	}
}

/**
 * Clear all search cache
 *
 * @return void
 */
function clear_search_cache(): void {
	global $wpdb;

	// Clear transients
	$wpdb->query(
		"DELETE FROM {$wpdb->options}
		WHERE option_name LIKE '_transient_eightyfourem_search_%'
		OR option_name LIKE '_transient_timeout_eightyfourem_search_%'"
	);

	// Clear object cache group if available
	if ( wp_using_ext_object_cache() ) {
		wp_cache_flush_group( 'eightyfourem_search' );
	}
}

/**
 * Check and create FULLTEXT index if needed
 *
 * @return void
 */
function ensure_fulltext_index(): void {
	global $wpdb;

	// Check if FULLTEXT index exists
	$index_exists = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(1) FROM INFORMATION_SCHEMA.STATISTICS
			WHERE table_schema = %s
			AND table_name = %s
			AND index_name = 'eightyfourem_fulltext'",
			DB_NAME,
			$wpdb->posts
		)
	);

	if ( ! $index_exists ) {
		// Create FULLTEXT index on post_title and post_content
		$wpdb->query(
			"ALTER TABLE {$wpdb->posts}
			ADD FULLTEXT eightyfourem_fulltext (post_title, post_content)"
		);
	}
}

/**
 * Filter common stop words from search term
 *
 * @param string $search_term Original search term
 * @return string Filtered search term
 */
function filter_stop_words( string $search_term ): string {
	$words = explode( ' ', strtolower( $search_term ) );
	$filtered = array_filter( $words, function( $word ) {
		return ! in_array( $word, STOP_WORDS, true ) && strlen( $word ) > 2;
	} );

	// Return original if all words were filtered out
	return ! empty( $filtered ) ? implode( ' ', $filtered ) : $search_term;
}

// Hook into posts_search filter to modify SQL WHERE clause
\add_filter(
	hook_name: 'posts_search',
	callback: function ( string $search, \WP_Query $query ): string {
		global $wpdb;

		// Only modify main search queries
		if ( ! $query->is_search() || ! $query->is_main_query() || empty( $query->get( 's' ) ) ) {
			return $search;
		}

		// Sanitize and limit search term length
		$search_term = sanitize_text_field( $query->get( 's' ) );
		if ( strlen( $search_term ) > MAX_SEARCH_LENGTH ) {
			$search_term = substr( $search_term, 0, MAX_SEARCH_LENGTH );
		}

		// Check cache first
		$cache_args = [
			'post_type' => $query->get( 'post_type' ),
			'meta_query' => $query->get( 'meta_query' ),
		];
		$cached_ids = get_cached_search_results( $search_term, $cache_args );

		if ( $cached_ids !== null ) {
			// Use cached post IDs
			if ( empty( $cached_ids ) ) {
				// No results in cache
				return ' AND 1=0 ';
			}
			$id_list = implode( ',', array_map( 'intval', $cached_ids ) );
			return " AND {$wpdb->posts}.ID IN ({$id_list}) ";
		}

		// Store original search for later use
		$query->set( '_original_search_sql', $search );
		$query->set( '_enhanced_search_term', $search_term );

		// For initial query, use standard search with SOUNDEX enhancement
		$search_words = array_filter( explode( ' ', $search_term ) );
		$search_conditions = [];

		foreach ( $search_words as $word ) {
			$word = $wpdb->esc_like( $word );
			$prepared_word = $wpdb->prepare( '%s', $word );

			$search_conditions[] = $wpdb->prepare(
				"({$wpdb->posts}.post_title LIKE %s
				OR {$wpdb->posts}.post_content LIKE %s
				OR SOUNDEX({$wpdb->posts}.post_title) = SOUNDEX(%s))",
				'%' . $word . '%',
				'%' . $word . '%',
				$word
			);
		}

		if ( ! empty( $search_conditions ) ) {
			$search = ' AND (' . implode( ' OR ', $search_conditions ) . ') ';
		}

		return $search;
	},
	priority: 20,
	accepted_args: 2
);

// Hook into posts_results filter to calculate relevance scores
\add_filter(
	hook_name: 'posts_results',
	callback: function ( array $posts, \WP_Query $query ): array {
		// Only process search queries
		if ( ! $query->is_search() || ! $query->is_main_query() ) {
			return $posts;
		}

		$search_term = $query->get( '_enhanced_search_term' );
		if ( empty( $search_term ) ) {
			return $posts;
		}

		// If we have enough exact results, just score them
		if ( count( $posts ) >= FUZZY_THRESHOLD ) {
			// Calculate scores for existing results
			foreach ( $posts as $post ) {
				$post->relevance_score = calculate_relevance_score( $post, $search_term );
			}
			return $posts;
		}

		// Need more results - apply fuzzy matching
		global $wpdb;

		// Get filtered search term for fuzzy matching
		$filtered_term = filter_stop_words( $search_term );
		$existing_ids = wp_list_pluck( $posts, 'ID' );

		// Build fuzzy query
		$fuzzy_results = [];

		// 1. Try Levenshtein distance matching on titles
		$all_titles = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title FROM {$wpdb->posts}
				WHERE post_status = 'publish'
				AND post_type IN ('post', 'page')
				AND ID NOT IN ('" . implode( "','", $existing_ids ) . "')
				LIMIT 500"
			)
		);

		foreach ( $all_titles as $title_row ) {
			$lev_score = calculate_levenshtein_score( $title_row->post_title, $filtered_term );
			if ( $lev_score > 0.6 ) {
				$fuzzy_results[$title_row->ID] = $lev_score;
			}
		}

		// 2. Try N-gram matching
		foreach ( $all_titles as $title_row ) {
			if ( ! isset( $fuzzy_results[$title_row->ID] ) ) {
				$ngram_score = calculate_ngram_similarity( $title_row->post_title, $filtered_term );
				if ( $ngram_score > NGRAM_THRESHOLD ) {
					$fuzzy_results[$title_row->ID] = $ngram_score * 0.8; // Slightly lower weight
				}
			}
		}

		// Get full post objects for fuzzy matches
		if ( ! empty( $fuzzy_results ) ) {
			$fuzzy_ids = array_keys( $fuzzy_results );
			$fuzzy_posts = get_posts( [
				'post__in' => $fuzzy_ids,
				'post_type' => 'any',
				'post_status' => 'publish',
				'numberposts' => 50,
			] );

			// Merge with existing results
			$posts = array_merge( $posts, $fuzzy_posts );
		}

		// Calculate relevance scores for all posts
		foreach ( $posts as $post ) {
			$post->relevance_score = calculate_relevance_score( $post, $search_term );

			// Boost score if this was a fuzzy match
			if ( isset( $fuzzy_results[$post->ID] ) ) {
				$post->relevance_score *= 0.8; // Reduce fuzzy match scores slightly
			}
		}

		// Cache the results
		$post_ids = wp_list_pluck( $posts, 'ID' );
		$cache_args = [
			'post_type' => $query->get( 'post_type' ),
			'meta_query' => $query->get( 'meta_query' ),
		];
		set_cached_search_results( $search_term, $cache_args, $post_ids );

		return $posts;
	},
	priority: 20,
	accepted_args: 2
);

// Hook into the_posts filter to reorder by relevance score
\add_filter(
	hook_name: 'the_posts',
	callback: function ( array $posts, \WP_Query $query ): array {
		// Only process search queries
		if ( ! $query->is_search() || ! $query->is_main_query() ) {
			return $posts;
		}

		// Check if we have relevance scores
		$has_scores = false;
		foreach ( $posts as $post ) {
			if ( isset( $post->relevance_score ) ) {
				$has_scores = true;
				break;
			}
		}

		if ( ! $has_scores ) {
			return $posts;
		}

		// Sort by relevance score (highest first)
		usort( $posts, function( $a, $b ) {
			$score_a = $a->relevance_score ?? 0;
			$score_b = $b->relevance_score ?? 0;

			if ( $score_a == $score_b ) {
				return 0;
			}

			return $score_a > $score_b ? -1 : 1;
		} );

		return $posts;
	},
	priority: 20,
	accepted_args: 2
);

// Cache invalidation on post save
\add_action(
	hook_name: 'save_post',
	callback: function ( int $post_id ): void {
		// Only clear cache for published posts
		if ( get_post_status( $post_id ) === 'publish' ) {
			clear_search_cache();
		}
	},
	priority: 10,
	accepted_args: 1
);

// Cache invalidation on post delete
\add_action(
	hook_name: 'delete_post',
	callback: function ( int $post_id ): void {
		clear_search_cache();
	},
	priority: 10,
	accepted_args: 1
);

// Cache invalidation on post status transition
\add_action(
	hook_name: 'transition_post_status',
	callback: function ( string $new_status, string $old_status, \WP_Post $post ): void {
		// Clear cache when post is published or unpublished
		if ( $new_status === 'publish' || $old_status === 'publish' ) {
			clear_search_cache();
		}
	},
	priority: 10,
	accepted_args: 3
);

// Ensure FULLTEXT index exists on activation
\add_action(
	hook_name: 'after_setup_theme',
	callback: function (): void {
		// Only check once per day to avoid performance impact
		$last_check = get_option( 'eightyfourem_fulltext_check', 0 );
		if ( time() - $last_check > DAY_IN_SECONDS ) {
			ensure_fulltext_index();
			update_option( 'eightyfourem_fulltext_check', time() );
		}
	},
	priority: 10
);

// Add custom search results info for debugging (optional)
\add_action(
	hook_name: 'wp_footer',
	callback: function (): void {
		if ( is_search() && current_user_can( 'manage_options' ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			global $wp_query;
			$search_term = get_search_query();
			$result_count = $wp_query->found_posts;

			echo "\n<!-- Enhanced Search Debug:\n";
			echo "Search Term: {$search_term}\n";
			echo "Results Found: {$result_count}\n";
			echo "Cache Status: " . ( get_cached_search_results( $search_term, [] ) !== null ? 'HIT' : 'MISS' ) . "\n";
			echo "-->\n";
		}
	},
	priority: 999
);