/**
 * WordPress dependencies
 */
( function ( wp ) {
	const { registerBlockType } = wp.blocks;
	const { useBlockProps } = wp.blockEditor;
	const { __ } = wp.i18n;
	const { createElement: el } = wp.element;

	/**
	 * Block registration
	 */
	registerBlockType( 'eightyfourem/calendly-booking-details', {
		edit: function Edit() {
			const blockProps = useBlockProps( {
				className: 'calendly-booking-details',
			} );

			return el(
				'div',
				blockProps,
				el(
					'div',
					{ className: 'calendly-booking-details__placeholder' },
					el( 'p', null, __( 'Calendly Booking Details', 'eightyfourem' ) ),
					el(
						'p',
						{ style: { fontSize: '0.875em', opacity: 0.7 } },
						__(
							'This block will display the invitee name and email from Calendly URL parameters on the front end.',
							'eightyfourem'
						)
					)
				)
			);
		},

		save: function Save() {
			// Rendered via PHP
			return null;
		},
	} );
} )( window.wp );
