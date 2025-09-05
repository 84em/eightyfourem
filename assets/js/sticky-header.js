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

        // Handle anchor link clicks with offset for sticky header
        document.addEventListener('click', function(e) {
            // Check if clicked element is an anchor link
            const link = e.target.closest('a');
            if (link) {
                const href = link.getAttribute('href');
                // Check if it's an anchor link
                if (href && (href.startsWith('#') || href.includes('#'))) {
                    const hashIndex = href.indexOf('#');
                    if (hashIndex !== -1) {
                        const targetId = href.substring(hashIndex + 1);
                        if (targetId) {
                            const targetElement = document.getElementById(targetId);
                            if (targetElement) {
                                e.preventDefault();
                                
                                // Calculate header height for offset
                                const headerHeight = header.offsetHeight;
                                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset;
                                const offsetPosition = targetPosition - headerHeight - 20; // 20px extra padding
                                
                                // Scroll to position with offset
                                window.scrollTo({
                                    top: offsetPosition,
                                    behavior: 'smooth'
                                });
                                
                                // Update URL hash
                                if (history.pushState) {
                                    history.pushState(null, null, href);
                                }
                            }
                        }
                    }
                }
            }
        }, true); // Use capture phase to handle the event before default behavior

        // Initial check
        updateHeader();
    });
})();
