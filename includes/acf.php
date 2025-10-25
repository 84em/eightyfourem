<?php

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

// ACF tweaks for security changes in 6.2.5
\add_filter(
    hook_name: 'acf/shortcode/allow_unsafe_html',
    callback: function ( $bool, $atts, $field_type, $field ) {
        return true;
    },
    priority: 10,
    accepted_args: 4 );

// ACF tweaks for security changes in 6.2.5
\add_filter(
    hook_name: 'acf/the_field/allow_unsafe_html',
    callback: function ( $bool, $selector, $post_id, $field_type, $field ) {
        return true;
    },
    priority: 10,
    accepted_args: 5 );


