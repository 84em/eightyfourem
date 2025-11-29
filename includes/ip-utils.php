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
 * Cloudflare header is checked first as 84em.com uses Cloudflare CDN.
 *
 * @return string Validated IP address or empty string.
 */
function get_visitor_ip(): string {
	$visitor_ip = match ( true ) {
		! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) => $_SERVER['HTTP_CF_CONNECTING_IP'],
		! empty( $_SERVER['HTTP_CLIENT_IP'] )        => $_SERVER['HTTP_CLIENT_IP'],
		! empty( $_SERVER['HTTP_X_REAL_IP'] )        => $_SERVER['HTTP_X_REAL_IP'],
		! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] )  => trim( explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] )[0] ),
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

/**
 * Get list of known bot user agent patterns for analytics exclusion.
 * Patterns are case-insensitive substrings matched against User-Agent header.
 *
 * @return array<string> Array of bot user agent patterns.
 */
function get_bot_ua_patterns(): array {
	return [
		// Search engine crawlers
		'googlebot',
		'google-inspectiontool',
		'adsbot-google',
		'mediapartners-google',
		'feedfetcher-google',
		'bingbot',
		'bingpreview',
		'slurp',
		'duckduckbot',
		'yandexbot',
		'baiduspider',
		'sogou',
		'exabot',
		'qwantify',

		// AI crawlers (2025)
		'gptbot',
		'chatgpt-user',
		'oai-searchbot',
		'claudebot',
		'claude-web',
		'anthropic-ai',
		'google-extended',
		'perplexitybot',
		'meta-externalagent',
		'meta-externalfetcher',
		'amazonbot',
		'cohere-ai',
		'bytespider',
		'ccbot',
		'omgili',
		'diffbot',

		// Social media crawlers
		'facebookexternalhit',
		'facebot',
		'twitterbot',
		'linkedinbot',
		'pinterestbot',
		'slackbot',
		'telegrambot',
		'discordbot',
		'whatsapp',
		'snapchat',

		// SEO and analytics tools
		'ahrefsbot',
		'semrushbot',
		'mj12bot',
		'dotbot',
		'rogerbot',
		'screaming frog',
		'sistrix',
		'seokicks',
		'blexbot',
		'petalbot',

		// Monitoring and performance tools
		'pingdom',
		'uptimerobot',
		'gtmetrix',
		'pagespeed',
		'site24x7',
		'statuscake',

		// Generic bot patterns
		'bot/',
		'crawler',
		'spider',
		'scraper',
		'headless',
		'phantom',
		'selenium',
		'puppeteer',
		'playwright',

		// WordPress internal requests (wp_remote_get, wp_remote_post, pingbacks)
		'wordpress/',

		// HTTP libraries and CLI tools
		'curl/',
		'wget/',
		'libwww-perl',
		'python-requests',
		'python-urllib',
		'go-http-client',
		'java/',
		'apache-httpclient',
		'okhttp',
		'axios/',
		'node-fetch',
		'undici',
	];
}

/**
 * Check if current request is from a known bot based on user agent.
 *
 * @return bool True if user agent matches a known bot pattern, false otherwise.
 */
function is_ua_excluded(): bool {
	$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

	if ( '' === $user_agent ) {
		return true; // Empty UA is likely a bot
	}

	$user_agent_lower = strtolower( $user_agent );

	foreach ( get_bot_ua_patterns() as $pattern ) {
		if ( str_contains( $user_agent_lower, $pattern ) ) {
			return true;
		}
	}

	return false;
}
