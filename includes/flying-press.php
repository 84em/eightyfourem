<?php
/**
 * FlyingPress Customizations
 * Modifies FlyingPress behavior for 84EM
 *
 * @package EightyFourEM
 */

namespace EightyFourEM\FlyingPress;

defined( 'ABSPATH' ) || exit;

/**
 * Convert FlyingPress cache footprint timestamp to human-readable Central US time
 */
\add_filter(
	hook_name: 'flying_press_footprint',
	callback: function ( string $footprint ): string {
		// Extract the Unix timestamp from the footprint
		if ( preg_match( '/Cached at (\d+)/', $footprint, $matches ) ) {
			$timestamp = (int) $matches[1];

			// Convert to Central US time (America/Chicago handles CST/CDT automatically)
			$timezone = new \DateTimeZone( 'America/Chicago' );
			$datetime = new \DateTime( "@{$timestamp}" );
			$datetime->setTimezone( $timezone );

			// Format: "Dec 2, 2025 at 3:45 PM CST"
			$human_readable = $datetime->format( 'M j, Y \a\t g:i A T' );

			// Replace the timestamp with human-readable format
			$footprint = str_replace(
				"Cached at {$timestamp}",
				"Cached at {$human_readable}",
				$footprint
			);

            // Add affiliate link
            $footprint = str_replace(
                'https://flyingpress.com',
                'https://flyingpress.com/?campaign=84emcom&ref=vyhz',
                $footprint,
            );
		}

		return $footprint;
	}
);
