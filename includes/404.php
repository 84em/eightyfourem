<?php
/**
 * Custom 404 Error Handling
 *
 * Intercepts 404 responses and performs strategic redirects to improve user experience.
 *
 * @package EightyFourEM
 * @since 1.0.0
 */

/**
 * Handle 404 errors with custom redirect logic
 *
 * Intercepts 404 pages and redirects specific URL patterns:
 * - /project/* redirects to /case-studies/* (maintains slug structure)
 */
add_action(
    hook_name: 'template_redirect',
    callback: function () {
        // Only run on 404 pages
        if ( ! is_404() ) {
            return;
        }

        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

        $parsed_url = wp_parse_url( $request_uri );
        $path       = $parsed_url['path'] ?? '';
        $query      = $parsed_url['query'] ?? '';

        $path = trim( $path, '/' );

        if ( str_starts_with( $path, 'project/' ) ) {

            $slug = str_replace( 'project/', '', $path );

            $redirect_url = home_url( '/case-studies/' . $slug );

            if ( ! empty( $query ) ) {
                $redirect_url .= '?' . $query;
            }

            wp_safe_redirect( $redirect_url, 301, 'EightyFourEM' );
            exit;
        }
    },
    priority: 1
);
