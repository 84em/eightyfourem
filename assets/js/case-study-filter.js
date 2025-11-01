/**
 * Case Study Filter
 * Client-side filtering for case study pages
 *
 * @package EightyFourEM
 */

(function () {
	'use strict';

	// Get filter keywords from localized data (set in PHP)
	const filters = window.caseStudyFilters || {};

	document.addEventListener('DOMContentLoaded', function () {
		const filterButtons = document.querySelectorAll('[data-filter]');
		const caseStudyItems = document.querySelectorAll('.wp-block-post');
		const resultCounter = document.querySelector('.case-study-result-count');
		const totalItems = caseStudyItems.length;

		if (!filterButtons.length || !caseStudyItems.length) return;

		// Function to update result counter
		function updateCounter(visibleCount) {
			if (resultCounter) {
				if (visibleCount === totalItems) {
					resultCounter.textContent = 'Showing all ' + totalItems + ' projects';
				} else {
					resultCounter.textContent = 'Showing ' + visibleCount + ' of ' + totalItems + ' projects';
				}
				resultCounter.classList.add('is-visible');
			}
		}

		// Function to apply filter
		function applyFilter(filter) {
			let visibleCount = 0;

			// Update active button state
			filterButtons.forEach(function (btn) {
				if (btn.dataset.filter === filter) {
					btn.classList.add('is-active');
				} else {
					btn.classList.remove('is-active');
				}
			});

			// Add brief loading state
			if (resultCounter) {
				resultCounter.classList.add('is-filtering');
			}

			// Small delay to show filtering state
			setTimeout(function () {
				// Filter case study items
				caseStudyItems.forEach(function (item) {
					const title =
						item.querySelector('.wp-block-post-title')?.textContent.toLowerCase() || '';
					const excerpt =
						item.querySelector('.wp-block-post-excerpt')?.textContent.toLowerCase() || '';
					const searchText = title + ' ' + excerpt;

					if (filter === 'all') {
						// Show all items
						item.style.display = '';
						item.classList.remove('filtered-out');
						visibleCount++;
					} else {
						// Check if any keyword matches
						const keywords = filters[filter] || [];
						const matches = keywords.some(function (keyword) {
							return searchText.includes(keyword);
						});

						if (matches) {
							item.style.display = '';
							item.classList.remove('filtered-out');
							visibleCount++;
						} else {
							item.style.display = 'none';
							item.classList.add('filtered-out');
						}
					}
				});

				// Update counter
				updateCounter(visibleCount);

				// Remove filtering state
				if (resultCounter) {
					resultCounter.classList.remove('is-filtering');
				}
			}, 50);
		}

		// Add click handlers to filter buttons
		filterButtons.forEach(function (button) {
			button.addEventListener('click', function (e) {
				e.preventDefault();

				const filter = this.dataset.filter;

				// Update URL hash for shareability
				if (filter === 'all') {
					// Remove hash for "all"
					history.replaceState(null, null, window.location.pathname);
				} else {
					history.replaceState(null, null, '#filter=' + filter);
				}

				// Apply the filter
				applyFilter(filter);
			});
		});

		// Check URL hash on page load
		function checkUrlHash() {
			const hash = window.location.hash;
			if (hash && hash.startsWith('#filter=')) {
				const filter = hash.replace('#filter=', '');
				// Verify the filter exists
				const filterExists = Array.from(filterButtons).some(function (btn) {
					return btn.dataset.filter === filter;
				});
				if (filterExists) {
					applyFilter(filter);
					return;
				}
			}
			// Default: show all
			applyFilter('all');
		}

		// Initialize
		checkUrlHash();
	});
})();
