<?php
/**
 * Calendly Booking Details Block Registration
 *
 * @package Eighty Four EM
 */

namespace EightyFourEM\CalendlyBookingDetails;

defined( 'ABSPATH' ) || exit;

/**
 * Register the Calendly Booking Details block
 */
\add_action(
	hook_name: 'init',
	callback: function (): void {
		\register_block_type(
			\get_template_directory() . '/blocks/calendly-booking-details'
		);
	},
	priority: 10
);
