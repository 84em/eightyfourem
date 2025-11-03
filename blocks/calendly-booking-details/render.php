<?php
/**
 * Calendly Booking Details Block Template
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 */

namespace EightyFourEM\CalendlyBookingDetails;

// Get URL parameters
$invitee_full_name = isset( $_GET['invitee_full_name'] ) ? sanitize_text_field( wp_unslash( $_GET['invitee_full_name'] ) ) : '';

// Extract first name (everything before the first space)
$first_name = '';
if ( ! empty( $invitee_full_name ) ) {
	$name_parts = explode( ' ', $invitee_full_name, 2 );
	$first_name = $name_parts[0];
}

// Get block wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes( [
	'class' => 'calendly-booking-details',
] );

// Only display if we have booking data
if ( empty( $first_name ) ) {
	// Return null to display nothing on front-end (placeholder only shows in editor)
	return;
}
?>

<div <?php echo $wrapper_attributes; ?>>
	<div class="calendly-booking-details__content">
		<h2 class="calendly-booking-details__heading">
			<?php
			/* translators: %s: Invitee first name */
			printf( esc_html__( 'Thanks, %s.', 'eightyfourem' ), esc_html( $first_name ) );
			?>
		</h2>
	</div>
</div>
