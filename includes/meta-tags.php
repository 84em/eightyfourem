<?php

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

// integrates meta tags from legacy Genesis theme that we originally built the site on.
\add_action(
    hook_name: 'wp_head',
    callback: function () {
        $_genesis_description = get_post_meta( get_the_ID(), '_genesis_description', true );
        if ( ! empty( $_genesis_description ) ) {
            echo sprintf( '<meta name="description" content="%s"/>', esc_attr( wp_strip_all_tags( $_genesis_description ) ) ) . PHP_EOL;
        }

        $_genesis_noindex = get_post_meta( get_the_ID(), '_genesis_noindex', true );
        if ( 1 === (int) $_genesis_noindex ) {
            echo '<meta name="robots" content="noindex,nofollow"/>' . PHP_EOL;
        } else {
            echo '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />' . PHP_EOL;
        }
    },
    priority: 1 );

// removes the default robots meta tag
\remove_action(
    hook_name: 'wp_head',
    callback: 'wp_robots',
    priority: 1 );
