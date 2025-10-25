/**
 * Element Highlight Script
 * Highlights elements when navigating to anchor links
 */

function highlightElement(elementId) {
    document.querySelectorAll('.highlight').forEach(el => {
        el.classList.remove('highlight');
    });

    const target = document.getElementById(elementId);

    if (target) {
        target.classList.add('highlight');

        setTimeout(() => {
            target.classList.remove('highlight');
        }, 2000);
    }
}

// Run on page load if hash exists
if (window.location.hash) {
    const hash = window.location.hash.substring(1);
    highlightElement(hash);
}

// Listen for clicks on anchor links
document.addEventListener('click', function(e) {
    const link = e.target.closest('a[href*="#"]');
    if (link) {
        const href = link.getAttribute('href');
        const hashIndex = href.indexOf('#');

        if (hashIndex !== -1) {
            const hash = href.substring(hashIndex + 1);
            setTimeout(() => {
                highlightElement(hash);
            }, 300);
        }
    }
});
