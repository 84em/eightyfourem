/**
 * Sticky Header Script for 84EM Theme
 */
(function() {
    'use strict';

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        const header = document.querySelector('header');
        const header2 = document.querySelector('.header-2');

        if (!header || !header2) {
            return;
        }

        const originalMarkup = header2.innerHTML;
        const tocItems = collectHeadings();
        const tocNav = tocItems.length ? buildToc(tocItems) : null;
        let tocActive = false;
        let ticking = false;
        let isTransitioning = false;
        const tocOffset = 50;

        function collectHeadings() {
            const existingIds = new Set();
            const headings = Array.from(document.querySelectorAll('main h2'));
            const excludeSelectors = [
                '[data-sticky-toc="exclude"]',
                '[data-sticky-toc-exclude]',
                '[data-toc-exclude]',
                '.is-hero-block',
                '.hero',
                '.hero-section',
                '.banner-hero',
                '.ef-hero',
                '.ef-hero__wrapper',
                '.wp-block-cover.is-style-hero',
                '.wp-block-group.is-style-hero'
            ];

            return headings
                .filter(function(heading) {
                    const text = heading.textContent ? heading.textContent.trim() : '';
                    if (text.length === 0 || heading.offsetParent === null) {
                        return false;
                    }

                    for (let i = 0; i < excludeSelectors.length; i++) {
                        if (heading.closest(excludeSelectors[i])) {
                            return false;
                        }
                    }

                    return true;
                })
                .map(function(heading, index) {
                    if (heading.id) {
                        existingIds.add(heading.id);
                    }

                    if (!heading.id) {
                        heading.id = generateId(heading.textContent || '', existingIds, index);
                        existingIds.add(heading.id);
                    }

                    return {
                        id: heading.id,
                        label: formatLabel(heading.textContent || '')
                    };
                });
        }

        function generateId(text, usedIds, index) {
            var base = text.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');

            if (!base) {
                base = 'section-' + (index + 1);
            }

            var candidate = base;
            var suffix = 2;

            while (usedIds.has(candidate)) {
                candidate = base + '-' + suffix;
                suffix++;
            }

            return candidate;
        }

        function formatLabel(text) {
            return text.trim().replace(/\s+/g, ' ');
        }

        function buildToc(items) {
            var wrapper = document.createElement('div');
            wrapper.className = 'ef-sticky-toc alignwide';

            // Create clickable label with arrow
            var label = document.createElement('button');
            label.className = 'ef-sticky-toc__label';
            label.setAttribute('aria-label', 'Open table of contents');
            label.textContent = 'Jump to Section ';
            var arrow = document.createElement('span');
            arrow.className = 'ef-sticky-toc__arrow';
            arrow.innerHTML = '&rarr;';
            label.appendChild(arrow);

            // Create hamburger button
            var button = document.createElement('button');
            button.className = 'ef-sticky-toc__toggle';
            button.setAttribute('aria-label', 'Open table of contents');
            button.setAttribute('aria-expanded', 'false');
            button.innerHTML = '<span class="ef-sticky-toc__icon"></span>';

            // Create menu container
            var menu = document.createElement('div');
            menu.className = 'ef-sticky-toc__menu';
            menu.setAttribute('aria-hidden', 'true');

            var list = document.createElement('ul');
            list.className = 'ef-sticky-toc__list';

            items.forEach(function(item) {
                var li = document.createElement('li');
                var link = document.createElement('a');
                link.className = 'ef-sticky-toc__link';
                link.href = '#' + item.id;
                link.textContent = item.label;
                li.appendChild(link);
                list.appendChild(li);
            });

            menu.appendChild(list);

            // Assemble components
            wrapper.appendChild(label);
            wrapper.appendChild(button);
            wrapper.appendChild(menu);

            // Toggle functionality for both label and button
            function toggleMenu() {
                var isOpen = button.getAttribute('aria-expanded') === 'true';
                button.setAttribute('aria-expanded', !isOpen);
                label.setAttribute('aria-expanded', !isOpen);
                menu.setAttribute('aria-hidden', isOpen);
                wrapper.classList.toggle('is-open', !isOpen);
            }

            button.addEventListener('click', toggleMenu);
            label.addEventListener('click', toggleMenu);

            // Close menu when clicking a link
            list.addEventListener('click', function(e) {
                if (e.target.classList.contains('ef-sticky-toc__link')) {
                    button.setAttribute('aria-expanded', 'false');
                    menu.setAttribute('aria-hidden', 'true');
                    wrapper.classList.remove('is-open');
                }
            });

            // Close menu when clicking outside
            document.addEventListener('click', function(e) {
                if (!wrapper.contains(e.target)) {
                    button.setAttribute('aria-expanded', 'false');
                    menu.setAttribute('aria-hidden', 'true');
                    wrapper.classList.remove('is-open');
                }
            });

            return wrapper;
        }

        function activateToc() {
            if (!tocNav || tocActive || isTransitioning) {
                return;
            }

            isTransitioning = true;
            header2.style.opacity = '0';

            setTimeout(function() {
                header2.classList.add('ef-sticky-toc-active');
                header2.innerHTML = '';
                header2.appendChild(tocNav);
                tocActive = true;
                header2.style.opacity = '1';
                isTransitioning = false;
            }, 150);
        }

        function restoreOriginal() {
            if (!tocActive || isTransitioning) {
                return;
            }

            isTransitioning = true;
            header2.style.opacity = '0';

            setTimeout(function() {
                header2.classList.remove('ef-sticky-toc-active');
                header2.innerHTML = originalMarkup;
                tocActive = false;
                header2.style.opacity = '1';
                isTransitioning = false;
            }, 150);
        }

        function updateHeader() {
            var currentScrollY = window.scrollY || window.pageYOffset || 0;

            if (currentScrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }

            if (tocNav) {
                if (currentScrollY > tocOffset && !tocActive) {
                    activateToc();
                } else if (currentScrollY <= tocOffset && tocActive) {
                    restoreOriginal();
                }
            }

            ticking = false;
        }

        function requestTick() {
            if (!ticking) {
                window.requestAnimationFrame(updateHeader);
                ticking = true;
            }
        }

        window.addEventListener('scroll', requestTick);
        window.addEventListener('resize', requestTick);

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

        updateHeader();
    });
})();
