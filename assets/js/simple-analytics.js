/**
 * Simple Analytics Lazy Loading Script
 *
 * Loads Simple Analytics script asynchronously after user interaction
 * to improve initial page load performance. Includes various interaction
 * triggers and a 10-second fallback for bots and non-interactive sessions.
 *
 * @package EightyFourEM
 */

(function() {
	'use strict';

	// Track if script has been loaded
	let simpleAnalyticsLoaded = false;

	/**
	 * Load Simple Analytics scripts
	 * Creates and appends the analytics and auto-events script elements to the document
	 */
	function loadSimpleAnalytics() {
		if (simpleAnalyticsLoaded) {
			return;
		}

		// Mark as loaded
		simpleAnalyticsLoaded = true;

		// Create and append main analytics script
		const analyticsScript = document.createElement('script');
		analyticsScript.async = true;
		analyticsScript.src = 'https://scripts.simpleanalyticscdn.com/latest.js';
		document.body.appendChild(analyticsScript);

		// Create and append auto-events script
		const autoEventsScript = document.createElement('script');
		autoEventsScript.async = true;
		autoEventsScript.src = 'https://scripts.simpleanalyticscdn.com/auto-events.js';
		document.body.appendChild(autoEventsScript);

		// Remove all event listeners after loading
		removeEventListeners();
	}

	/**
	 * Remove all interaction event listeners
	 * Cleans up listeners once analytics has been loaded
	 */
	function removeEventListeners() {
		window.removeEventListener('scroll', handleInteraction);
		window.removeEventListener('mousemove', handleInteraction);
		window.removeEventListener('touchstart', handleInteraction);
		window.removeEventListener('keydown', handleInteraction);
		document.removeEventListener('click', handleInteraction);
	}

	/**
	 * Handle user interaction events
	 * Debounced handler to prevent multiple triggers
	 */
	let interactionTimeout;
	function handleInteraction() {
		if (simpleAnalyticsLoaded) {
			return;
		}

		// Clear any existing timeout
		clearTimeout(interactionTimeout);

		// Debounce the loading by 100ms to avoid multiple triggers
		interactionTimeout = setTimeout(loadSimpleAnalytics, 100);
	}

	// Listen for various user interaction events
	// Using passive: true for better scroll performance
	window.addEventListener('scroll', handleInteraction, { once: false, passive: true });
	window.addEventListener('mousemove', handleInteraction, { once: false, passive: true });
	window.addEventListener('touchstart', handleInteraction, { once: false, passive: true });
	window.addEventListener('keydown', handleInteraction, { once: false, passive: true });
	document.addEventListener('click', handleInteraction, { once: false, passive: true });

	// Fallback: Load after 10 seconds if no interaction
	// This ensures analytics loads for bots and non-interactive sessions
	setTimeout(function() {
		if (!simpleAnalyticsLoaded) {
			loadSimpleAnalytics();
		}
	}, 10000);
})();