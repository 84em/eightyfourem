/**
 * Sticky Header Script for 84EM Theme
 */
(function() {
    'use strict';

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        const header = document.querySelector('header');
        const header2 = document.querySelector('.header-2');

        if (!header) {
            console.log('Header element not found');
            return;
        }

        if (!header2) {
            console.log('Header-2 element not found');
            return;
        }

        let lastScrollY = 0;
        let ticking = false;
        let isHidden = false;

        function updateHeader() {
            const currentScrollY = window.scrollY;
            
            // Calculate actual scroll difference
            const scrollDiff = currentScrollY - lastScrollY;
            
            // Hide header-2 when scrolling down past 5px
            if (currentScrollY > 5) {
                // Scrolling down with at least 3px movement
                if (scrollDiff > 3 && !isHidden) {
                    header.classList.add('scrolling-down');
                    isHidden = true;
                } 
                // Scrolling up with at least 3px movement
                else if (scrollDiff < -3 && isHidden) {
                    header.classList.remove('scrolling-down');
                    isHidden = false;
                }
            } else {
                // At very top - ensure header-2 is visible
                header.classList.remove('scrolling-down');
                isHidden = false;
            }

            // Add scrolled class when scrolled more than 50px (for padding/shadow)
            if (currentScrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }

            lastScrollY = currentScrollY;
            ticking = false;
        }

        function requestTick() {
            if (!ticking) {
                requestAnimationFrame(updateHeader);
                ticking = true;
            }
        }

        // Listen for scroll events
        window.addEventListener('scroll', requestTick);

        // Also update on resize to handle orientation changes
        window.addEventListener('resize', function() {
            // Optionally reset on resize
            // header.classList.remove('scrolling-down');
        });

        // Initial check
        updateHeader();
    });
})();
