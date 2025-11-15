<?php
/**
 * Block Styles
 * Register custom block styles for the theme
 *
 * @package EightyFourEM
 */

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

/**
 * Register custom block styles
 */
\add_action(
	hook_name: 'init',
	callback: function () {
		// Arrow icon style for Details block
		\register_block_style(
			'core/details',
			[
				'name'         => 'arrow-icon-details',
				'label'        => \__( 'Arrow icon', 'eightyfourem' ),
				'inline_style' => '
				.is-style-arrow-icon-details {
					padding-top: var(--wp--preset--spacing--10);
					padding-bottom: var(--wp--preset--spacing--10);
				}

				.is-style-arrow-icon-details summary {
					list-style-type: "\2193\00a0\00a0\00a0";
				}

				.is-style-arrow-icon-details[open]>summary {
					list-style-type: "\2192\00a0\00a0\00a0";
				}',
			]
		);

		// Pill style for Post Terms
		\register_block_style(
			'core/post-terms',
			[
				'name'         => 'pill',
				'label'        => \__( 'Pill', 'eightyfourem' ),
				'inline_style' => '
				.is-style-pill a,
				.is-style-pill span:not([class], [data-rich-text-placeholder]) {
					display: inline-block;
					background-color: var(--wp--preset--color--base-2);
					padding: 0.375rem 0.875rem;
					border-radius: var(--wp--preset--spacing--20);
				}

				.is-style-pill a:hover {
					background-color: var(--wp--preset--color--contrast-3);
				}',
			]
		);

		// Checkmark style for List block
		\register_block_style(
			'core/list',
			[
				'name'         => 'checkmark-list',
				'label'        => \__( 'Checkmark', 'eightyfourem' ),
				'inline_style' => '
				ul.is-style-checkmark-list {
					list-style-type: "\2713";
				}

				ul.is-style-checkmark-list li {
					padding-inline-start: 1ch;
				}',
			]
		);

		// Arrow style for Navigation Link
		\register_block_style(
			'core/navigation-link',
			[
				'name'         => 'arrow-link',
				'label'        => \__( 'With arrow', 'eightyfourem' ),
				'inline_style' => '
				.is-style-arrow-link .wp-block-navigation-item__label:after {
					content: "\2197";
					padding-inline-start: 0.25rem;
					vertical-align: middle;
					text-decoration: none;
					display: inline-block;
				}',
			]
		);

		// Asterisk style for Heading block
		\register_block_style(
			'core/heading',
			[
				'name'         => 'asterisk',
				'label'        => \__( 'With asterisk', 'eightyfourem' ),
				'inline_style' => "
				.is-style-asterisk:before {
					content: '';
					width: 1.5rem;
					height: 3rem;
					background: var(--wp--preset--color--contrast-2, currentColor);
					clip-path: path('M11.93.684v8.039l5.633-5.633 1.216 1.23-5.66 5.66h8.04v1.737H13.2l5.701 5.701-1.23 1.23-5.742-5.742V21h-1.737v-8.094l-5.77 5.77-1.23-1.217 5.743-5.742H.842V9.98h8.162l-5.701-5.7 1.23-1.231 5.66 5.66V.684h1.737Z');
					display: block;
				}

				.is-style-asterisk:empty:before {
					content: none;
				}

				.is-style-asterisk:-moz-only-whitespace:before {
					content: none;
				}

				.is-style-asterisk.has-text-align-center:before {
					margin: 0 auto;
				}

				.is-style-asterisk.has-text-align-right:before {
					margin-left: auto;
				}

				.rtl .is-style-asterisk.has-text-align-left:before {
					margin-right: auto;
				}",
			]
		);
	}
);
