/**
 * Modal Search Script for 84EM Theme
 * Opens search form in a modal overlay when search icon is clicked
 */
(function() {
    'use strict';

    // Type filter configuration matching search.php
    const typeFilters = [
        { value: 'service', label: 'Service', color: 'service' },
        { value: 'case study', label: 'Case Study', color: 'case-study' },
        { value: 'page', label: 'Page', color: 'page' }
    ];

    document.addEventListener('DOMContentLoaded', function() {
        const searchTrigger = document.querySelector('.search-icon a');

        if (!searchTrigger) {
            return;
        }

        // Add ARIA attributes to search trigger
        searchTrigger.setAttribute('aria-haspopup', 'dialog');
        searchTrigger.setAttribute('aria-expanded', 'false');

        searchTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Store reference to trigger element for focus restoration
            const triggerElement = e.currentTarget;

            // Update ARIA expanded state
            searchTrigger.setAttribute('aria-expanded', 'true');

            // Build type filter checkboxes HTML
            const typeFiltersHTML = typeFilters.map(function(filter) {
                return `
                    <label class="search-type-filter search-type-filter--${filter.color}">
                        <input type="checkbox" name="type[]" value="${filter.value}" checked />
                        <span class="search-type-filter__checkbox" aria-hidden="true"></span>
                        <span class="search-type-filter__label">${filter.label}</span>
                    </label>
                `;
            }).join('');

            // Create modal HTML
            const modalHTML = `
                <div class="search-modal-overlay"></div>
                <div class="search-modal-content" role="dialog" aria-modal="true" aria-labelledby="searchModalTitle">
                    <div class="search-modal-announcer" role="status" aria-live="polite" aria-atomic="true"></div>
                    <button class="search-modal-close" aria-label="Close search">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                    <h2 id="searchModalTitle" class="search-modal-title">Search 84EM</h2>
                    <form class="search-modal-form" role="search" method="get" action="${window.location.origin}/">
                        <fieldset class="search-type-filters" role="group" aria-labelledby="typeFiltersLegend">
                            <legend id="typeFiltersLegend" class="search-type-filters__legend">Filter by type</legend>
                            <div class="search-type-filters__options">
                                ${typeFiltersHTML}
                            </div>
                        </fieldset>
                        <div class="search-modal-form-wrapper">
                            <label for="searchModalInput" class="search-modal-label">Search for:</label>
                            <input type="search" id="searchModalInput" name="s" class="search-modal-input" placeholder="Search..." required />
                            <button type="submit" class="search-modal-submit">Search</button>
                        </div>
                    </form>
                </div>
            `;

            // Create and append modal
            const modal = document.createElement('div');
            modal.id = 'searchModal';
            modal.className = 'search-modal';
            modal.innerHTML = modalHTML;
            document.body.appendChild(modal);

            // Announce modal opening and focus input after animation starts
            setTimeout(function() {
                const announcer = modal.querySelector('.search-modal-announcer');
                if (announcer) {
                    announcer.textContent = 'Search dialog opened. Type your search query and press enter to search.';
                }

                const input = modal.querySelector('.search-modal-input');
                if (input) {
                    input.focus();
                }
            }, 100);

            // Get all focusable elements in modal for focus trap
            const focusableElements = modal.querySelectorAll(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            );
            const firstFocusable = focusableElements[0];
            const lastFocusable = focusableElements[focusableElements.length - 1];

            // Close modal function
            function closeModal() {
                modal.classList.add('closing');

                // Update ARIA expanded state
                searchTrigger.setAttribute('aria-expanded', 'false');

                setTimeout(function() {
                    if (modal.parentNode) {
                        modal.remove();
                    }
                    document.removeEventListener('keydown', handleEscape);
                    document.removeEventListener('keydown', trapFocus);

                    // Restore focus to trigger element
                    if (triggerElement) {
                        triggerElement.focus();
                    }
                }, 300);
            }

            // Handle escape key
            function handleEscape(e) {
                if (e.key === 'Escape') {
                    closeModal();
                }
            }

            // Focus trap - keep focus within modal
            function trapFocus(e) {
                if (e.key !== 'Tab') {
                    return;
                }

                // Shift + Tab (backwards)
                if (e.shiftKey) {
                    if (document.activeElement === firstFocusable) {
                        e.preventDefault();
                        lastFocusable.focus();
                    }
                // Tab (forwards)
                } else {
                    if (document.activeElement === lastFocusable) {
                        e.preventDefault();
                        firstFocusable.focus();
                    }
                }
            }

            // Add event listeners
            modal.querySelector('.search-modal-overlay').addEventListener('click', closeModal);
            modal.querySelector('.search-modal-close').addEventListener('click', closeModal);
            document.addEventListener('keydown', handleEscape);
            document.addEventListener('keydown', trapFocus);

            // Close on form submit (let it navigate)
            modal.querySelector('.search-modal-form').addEventListener('submit', function(submitEvent) {
                // If all checkboxes are checked, uncheck them all to avoid adding type[] params
                // This results in cleaner URLs when no filtering is desired
                const checkboxes = modal.querySelectorAll('input[name="type[]"]');
                const checkedBoxes = modal.querySelectorAll('input[name="type[]"]:checked');

                if (checkedBoxes.length === typeFilters.length) {
                    checkboxes.forEach(function(cb) {
                        cb.checked = false;
                    });
                }

                closeModal();
            });
        });
    });
})();
