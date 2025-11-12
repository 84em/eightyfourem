/**
 * Modal Search Script for 84EM Theme
 * Opens search form in a modal overlay when search icon is clicked
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        const searchTrigger = document.querySelector('.search-icon a');

        if (!searchTrigger) {
            return;
        }

        searchTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Create modal HTML
            const modalHTML = `
                <div class="search-modal-overlay"></div>
                <div class="search-modal-content">
                    <button class="search-modal-close" aria-label="Close search">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                    <form class="search-modal-form" role="search" method="get" action="${window.location.origin}/">
                        <div class="search-modal-form-wrapper">
                            <input type="search" name="s" class="search-modal-input" placeholder="Search..." required />
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

            // Focus input after animation starts
            setTimeout(function() {
                const input = modal.querySelector('.search-modal-input');
                if (input) {
                    input.focus();
                }
            }, 100);

            // Close modal function
            function closeModal() {
                modal.classList.add('closing');
                setTimeout(function() {
                    if (modal.parentNode) {
                        modal.remove();
                    }
                    document.removeEventListener('keydown', handleEscape);
                }, 300);
            }

            // Handle escape key
            function handleEscape(e) {
                if (e.key === 'Escape') {
                    closeModal();
                }
            }

            // Add event listeners
            modal.querySelector('.search-modal-overlay').addEventListener('click', closeModal);
            modal.querySelector('.search-modal-close').addEventListener('click', closeModal);
            document.addEventListener('keydown', handleEscape);

            // Close on form submit (let it navigate)
            modal.querySelector('.search-modal-form').addEventListener('submit', function() {
                closeModal();
            });
        });
    });
})();
