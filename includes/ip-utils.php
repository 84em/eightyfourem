<?php
/**
 * IP Utility Functions
 * Centralized IP address detection and validation for analytics exclusion.
 *
 * @package EightyFourEM
 */

namespace EightyFourEM\IPUtils;

defined( 'ABSPATH' ) or die;

/**
 * Get list of IP addresses excluded from analytics tracking.
 * Define EIGHTYFOUREM_EXCLUDED_IPS constant externally to configure.
 *
 * @return array<string> Array of excluded IP addresses.
 */
function get_excluded_ips(): array {
	return defined( 'EIGHTYFOUREM_EXCLUDED_IPS' ) ? EIGHTYFOUREM_EXCLUDED_IPS : [];
}

/**
 * Get visitor's real IP address from various proxy headers.
 *
 * @return string Validated IP address or empty string.
 */
function get_visitor_ip(): string {
	$visitor_ip = match ( true ) {
		! empty( $_SERVER['HTTP_CLIENT_IP'] )        => $_SERVER['HTTP_CLIENT_IP'],
		! empty( $_SERVER['HTTP_X_REAL_IP'] )        => $_SERVER['HTTP_X_REAL_IP'],
		! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] )  => trim( explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] )[0] ),
		! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) => $_SERVER['HTTP_CF_CONNECTING_IP'],
		default                                      => $_SERVER['REMOTE_ADDR'] ?? '',
	};

	$validated = filter_var( $visitor_ip, FILTER_VALIDATE_IP );

	return false !== $validated ? $validated : ( $_SERVER['REMOTE_ADDR'] ?? '' );
}

/**
 * Check if current visitor IP is excluded from analytics.
 *
 * @return bool True if IP is excluded, false otherwise.
 */
function is_ip_excluded(): bool {
	return in_array( get_visitor_ip(), get_excluded_ips(), true );
}

/**
 * Check if current session has opted out of analytics tracking.
 * Set via ?notrack query parameter, remembered for browser session.
 *
 * @return bool True if session is excluded, false otherwise.
 */
function is_session_excluded(): bool {
	$cookie_name = 'eightyfourem_notrack';

	// Set cookie if query param present
	if ( isset( $_GET['notrack'] ) ) {
		setcookie( $cookie_name, '1', 0, '/' );
		return true;
	}

	return isset( $_COOKIE[ $cookie_name ] );
}
