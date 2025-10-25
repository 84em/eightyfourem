<?php

/**
 * Hooks into the `after_setup_theme` action to remove WordPress default shortlink functionality.
 *
 * This functionality is achieved by:
 * 1. Removing the `wp_shortlink_wp_head` action from the `wp_head` hook.
 *    This prevents the shortlink meta tag from being added to the document head.
 * 2. Removing the `wp_shortlink_header` action from the `template_redirect` hook.
 *    This stops the shortlink HTTP header from being sent in the response.
 *
 * Both actions are removed with their respective priorities specified.
 *
 */

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

\add_filter(
    hook_name: 'after_setup_theme',
    callback: function () {
        \remove_action(
            hook_name: 'wp_head',
            callback: 'wp_shortlink_wp_head',
            priority: 10 );

        \remove_action(
            hook_name: 'template_redirect',
            callback: 'wp_shortlink_header',
            priority: 11 );
    } );
