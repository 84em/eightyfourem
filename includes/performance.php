<?php
/**
 * Performance Optimizations
 * Handles critical resource preloading and performance enhancements
 *
 * @package EightyFourEM
 */

namespace EightyFourEM\Performance;

defined( 'ABSPATH' ) || exit;

/**
 * Preload critical fonts
 * Loads fonts as early as possible to prevent FOUT/FOIT
 * Uses highest priority to execute before other wp_head actions
 */
\add_action(
	hook_name: 'wp_head',
	callback: function () {
		$theme_uri = \get_template_directory_uri();

		// Preload Instrument Sans (body font) - regular weight
		echo sprintf(
			'<link rel="preload" href="%s/assets/fonts/instrument-sans/InstrumentSans-VariableFont_wdth,wght.woff2" as="font" type="font/woff2" crossorigin="anonymous">',
			\esc_url( $theme_uri )
		) . PHP_EOL;

		// Preload Jost (heading font) - regular weight
		echo sprintf(
			'<link rel="preload" href="%s/assets/fonts/jost/Jost-VariableFont_wght.woff2" as="font" type="font/woff2" crossorigin="anonymous">',
			\esc_url( $theme_uri )
		) . PHP_EOL;
	},
	priority: 1
);

/**
 * Add DNS prefetch for external resources
 * Helps browser resolve DNS earlier for third-party domains
 */
\add_action(
	hook_name: 'wp_head',
	callback: function () {
		// Add any external domains that fonts or critical resources load from
		// Currently using local fonts, but this is here for future use
	},
	priority: 1
);

/**
 * Add resource hints for font preconnect
 * Establishes early connection to font origins
 */
\add_filter(
	hook_name: 'wp_resource_hints',
	callback: function ( array $hints, string $relation_type ): array {
		// Since fonts are local, we don't need external preconnect
		// but this filter is here for future external font CDN use
		return $hints;
	},
	priority: 10,
	accepted_args: 2
);

/**
 * Inline critical font-face declarations
 * Embeds font declarations directly in HTML to avoid render-blocking CSS
 * This ensures fonts start loading immediately without waiting for CSS parsing
 */
\add_action(
	hook_name: 'wp_head',
	callback: function () {
		$theme_uri = \get_template_directory_uri();
		?>
		<style id="critical-fonts">
			/* Critical font-face declarations for immediate loading */
			@font-face {
				font-family: 'Instrument Sans';
				font-style: normal;
				font-weight: 400 700;
				font-display: optional;
				src: url('<?php echo \esc_url( $theme_uri ); ?>/assets/fonts/instrument-sans/InstrumentSans-VariableFont_wdth,wght.woff2') format('woff2');
			}

			@font-face {
				font-family: 'Jost';
				font-style: normal;
				font-weight: 100 900;
				font-display: optional;
				src: url('<?php echo \esc_url( $theme_uri ); ?>/assets/fonts/jost/Jost-VariableFont_wght.woff2') format('woff2');
			}
		</style>
		<?php
	},
	priority: 2
);
