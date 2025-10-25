<?php

/**
 * Permalink management for enforcing trailing slashes on all content types.
 *
 * This file contains two hooks:
 * 1. 'user_trailingslashit' filter - ensures WordPress generates URLs with trailing slashes
 * 2. 'template_redirect' action - redirects visitors from non-trailing-slash URLs to trailing-slash URLs
 */

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

/**
 * Filter URLs to add trailing slashes when WordPress generates them.
 *
 * @param  string  $url  The original URL to be filtered.
 * @param  string  $type  The type of URL being processed (e.g., 'single', 'page').
 * @return string The updated URL with a trailing slash if applicable.
 */
\add_filter(
    hook_name: 'user_trailingslashit',
    callback: function ( $url, $type ) {
        $types_to_slash = [
            'single',
            'page',
            'post_type_archive',
            'archive',
            'category',
            'tag',
            'author',
        ];

        if ( in_array( $type, $types_to_slash, true ) ) {
            $url = trailingslashit( $url );
        }

        return $url;
    },
    accepted_args: 2
);

/**
 * Redirect visitors from non-trailing-slash URLs to trailing-slash URLs.
 * This ensures that even if someone types or visits a URL without a trailing slash,
 * they will be automatically redirected to the version with a trailing slash.
 */
\add_action(
    hook_name: 'template_redirect',
    callback: function () {
        // Don't redirect in admin, for AJAX requests, or for feed URLs
        if ( is_admin() || is_feed() || wp_doing_ajax() ) {
            return;
        }

        // Get the current request URI
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';

        // Skip if empty
        if ( empty( $request_uri ) ) {
            return;
        }

        // Parse the URL to separate path from query string
        $parsed_url = parse_url( $request_uri );
        $path = $parsed_url['path'] ?? '/';
        $query = $parsed_url['query'] ?? '';

        // Skip if path already has trailing slash
        if ( substr( $path, -1 ) === '/' ) {
            return;
        }

        // Skip if URL contains query parameters (e.g., ?fbclid=... from Facebook)
        // to avoid redirect loops with tracking parameters
        if ( ! empty( $query ) ) {
            return;
        }

        // Skip if URL contains a file extension (e.g., .xml, .txt, .php)
        if ( preg_match( '/\.[a-z0-9]{2,4}$/i', $path ) ) {
            return;
        }

        // Only redirect for pages, posts, and archives
        if ( is_singular() || is_archive() || is_page() ) {
            // Construct redirect URL with trailing slash
            $redirect_url = trailingslashit( home_url( $request_uri ) );

            // Perform 301 permanent redirect
            wp_safe_redirect( $redirect_url, 301 );
            exit;
        }
    },
    priority: 1
);
