# Changelog

All notable changes to the 84EM Block Theme will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.10.0] - 2025-11-05
### Added
- **Open Graph Images** - Complete Open Graph meta tag implementation for social media sharing (`includes/open-graph-images.php`)
  - Global default OG image setting via Settings → Open Graph Images admin page
  - Post-level meta box for overriding OG image on individual posts, pages, and projects
  - Automatic three-tier fallback system: post override → featured image → global default
  - Meta tag output includes og:image, og:image:width, og:image:height, og:image:alt, og:image:type, og:image:secure_url
  - Twitter Card meta tags for proper Twitter/X integration
  - Image validation with dimension and file size recommendations (1200x630px minimum, 8MB max)
  - Transient caching for image metadata (1 hour) for improved performance
  - Media library integration with WordPress media uploader
  - Proper security implementation: nonces, capability checks, input sanitization, output escaping

- **WP-CLI Testing Suite** - Comprehensive integration tests for Open Graph functionality (`includes/cli.php`)
  - Command: `wp 84em test-og-images` with multiple test options
  - Tests global default image setting and retrieval
  - Tests post-level override functionality
  - Tests three-tier fallback logic with real WordPress data
  - Tests meta tag output generation and escaping
  - Tests image validation (dimensions, MIME types, file existence)
  - Real image creation using GD library (no mocks)
  - Automatic cleanup functionality
  - All tests use actual WordPress core functions and database operations

### Changed
- **Functions.php** - Added require statement for open-graph-images.php include file
- **Settings Pages** - Improved admin notice handling to prevent duplicate success messages

## [2.9.0] - 2025-11-04
### Added
- **Google Reviews Block Migration** - Migrated Google Reviews block to modern WordPress block structure (`blocks/google-reviews/`)
  - Created `block.json` with all 30+ attributes for modern block registration (API v3)
  - Created `render.php` for server-side rendering (290 lines extracted from class)
  - Modified `index.js` from 908 to 763 lines by removing inline attributes
  - Refactored `includes/google-reviews.php` from 849-line class to ~290 lines functional approach
  - Updated all gulpfile.js paths from `assets/google-reviews-block/` to `blocks/google-reviews/`
  - Total code reduction: 1757 lines to 1053 lines (40% reduction)

- **Brand Colors** - Added brand colors to theme palette (`theme.json`)
  - New Brand Green color: #4f7705
  - New Brand Orange color: #D45304
  - Updated color names for clarity (White, Gray, Black, Beige, Brand Blue, Brand Green, Brand Orange)
  - Maintained all existing slugs to avoid breaking changes

### Changed
- **Google Reviews Block Namespace** - Breaking change from 'google-reviews/display' to 'eightyfourem/google-reviews'
  - Old blocks will show as unsupported in editor (intentional)
  - Clean migration without backward compatibility to avoid technical debt
  - Modern block.json-based registration following WordPress standards

### Removed
- **Google Reviews Legacy Code** - Removed class-based implementation
  - Removed 849-line GoogleReviewsBlock class
  - Removed inline attribute definitions from JavaScript
  - Cleaned up deprecated registration methods

## [2.8.8] - 2025-11-03
### Added
- **Calendly Booking Details Block** - New custom Gutenberg block for Calendly redirects (`blocks/calendly-booking-details/`)
  - Displays personalized thank you message using first name from URL parameter
  - Parses `invitee_full_name` parameter from Calendly redirect URLs
  - Shows "Thanks, {FIRST NAME}." on booking confirmation page
  - Placeholder message only visible in block editor, not on front-end
  - Fully responsive with centered text alignment
  - Added block registration in `includes/calendly-booking-details.php`
  - Integrated into gulp build process for CSS/JS minification
  - Added to `/booked/` page (post ID 4507) for Calendly booking confirmations

### Changed
- **Build Process** - Updated gulpfile to compile Calendly block assets (`gulpfile.js`)
  - Added Calendly block CSS and JS to build pipeline
  - Added to clean, watch, and build tasks
  - Generates minified assets with source maps

- **Theme Functions** - Added Calendly block include (`functions.php`)
  - Loads `includes/calendly-booking-details.php` on theme initialization

### Fixed
- **GitHub Issue #67** - Created enhancement issue for future improvements to use block editor settings instead of hard-coded CSS values

## [2.8.7] - 2025-11-03
### Added
- **Font Loading Performance** - New performance module to eliminate FOUT/FOIT (`includes/performance.php`)
  - Added font preloading for Instrument Sans and Jost variable fonts
  - Inlined critical font-face declarations in HTML head for immediate loading
  - Changed font-display from swap to optional in theme.json to prevent layout shifts
  - Updated AGENTS.md with performance module documentation and testing guidelines
  - Fonts now load before other resources, preventing flash of unstyled text

## [2.8.6] - 2025-11-02
### Changed
- **Case Study Filters** - Expanded Financial filter keywords (`includes/case-study-filters.php`)
  - Added industry keywords: bank, lending, investment
  - Added alternative format: cryptocurrency (in addition to crypto)
  - Improves discoverability of banking, lending, investment, and cryptocurrency case studies

## [2.8.5] - 2025-11-02
### Changed
- **Case Study Filters** - Enhanced Security filter keywords (`includes/case-study-filters.php`)
  - Added alternative keyword formats: 2fa, two-factor
  - Improves discoverability of two-factor authentication and 2FA case studies

## [2.8.4] - 2025-11-02
### Changed
- **Case Study Filters** - Expanded Marketing filter keywords (`includes/case-study-filters.php`)
  - Added platform-specific keywords: Twilio, OnSignal
  - Added channel keywords: SMS, email
  - Added category keywords: marketing automation, marketing automation software
  - Improves discoverability of marketing technology case studies

## [2.8.3] - 2025-11-02
### Changed
- **Case Study Filters** - Reordered filters and enhanced keyword matching (`includes/case-study-filters.php`, `assets/css/case-study-filter.css`)
  - Reordered filters: Financial, Security, Automation, Marketing, AI, Affiliates, Education, E-Commerce, Reporting, API
  - Enhanced Automation keywords: added scheduler, schedule, cron
  - Added new Marketing filter with keywords: marketing, lead, advertising, ads, leads
  - Reduced filter button padding from 1.5rem to 0.75rem for more compact layout

- **Search Exclusions** - Enhanced search filtering to exclude additional pages and respect noindex meta (`includes/search.php`)
  - Added exclusion for post ID 4507
  - Added meta query to exclude posts with `_genesis_noindex` meta set to 1
  - Improved code formatting for better readability

## [2.8.2] - 2025-11-02
### Changed
- **Search Result Badges** - Improved post type badge styling for better visual consistency (`assets/css/search.css`)
  - Changed display from `inline-block` to `block` for cleaner layout
  - Updated padding from `0.25rem 0.5rem` to `.25rem .75rem` for better proportions
  - Added `max-width: 80px` to ensure consistent badge sizing
  - Added `text-align: center` for centered text within badges
  - Changed margin from `margin-right: 0.5rem` to `margin: .5rem 0` for vertical spacing
  - Badges now display as self-contained blocks rather than inline elements

## [2.8.1] - 2025-11-02
### Fixed
- **Performance Optimization** - Fixed cache busting to enable proper browser caching (`includes/enqueue.php`)
  - Removed `time()` from version string in all asset enqueuing functions
  - Now uses theme version only for cache busting
  - CSS/JS files now properly cached by browsers until theme version changes
  - Significantly improves page load performance by eliminating unnecessary re-downloads

- **Search Performance** - Optimized post type detection to use post relationships instead of URL parsing (`includes/search.php`)
  - Replaced `get_permalink()` and `wp_parse_url()` with direct `post_parent` checks
  - Checks against known parent IDs (2129 for Services, 4406 for Case Studies)
  - Much faster and more reliable than string matching on URLs
  - Reduces database queries per search result

- **Code Quality** - Removed unnecessary `orderby` parameter (`includes/search.php`)
  - Removed non-standard `orderby` value that could cause issues if filter doesn't execute
  - `posts_orderby` filter completely replaces ORDER BY clause regardless of orderby parameter
  - Cleaner, more predictable code

### Security
- **XSS Protection** - Added HTML class sanitization for defensive coding (`includes/search.php`)
  - Added `sanitize_html_class()` to badge class name generation
  - Prevents potential XSS vulnerability if `get_post_type_indicator()` function is modified in future
  - Follows WordPress security best practices

## [2.8.0] - 2025-11-02
### Added
- **Search Result Type Indicators** - Visual badges for search results (`includes/search.php`, `assets/css/search.css`)
  - Service pages display blue badge
  - Case Study pages display green badge
  - Regular pages display gray badge
  - Badges appear before post titles in search results
  - Uses `render_block` filter for FSE compatibility

- **Search Result Ordering** - Prioritized search results by content type (`includes/search.php`)
  - Service pages (ID 2129 and children) appear first
  - Case Study pages (ID 4406 and children) appear second
  - Regular pages appear last
  - Within each group, results sorted by post date (newest first)
  - Uses `posts_orderby` filter with custom SQL CASE statement

### Changed
- **Search Exclusion Optimization** - Improved performance of local page exclusion (`includes/search.php`)
  - Replaced recursive parent ID lookup with efficient meta query
  - Excludes pages with `_local_page_state` meta key (state pages)
  - Excludes pages with `_local_page_city` meta key (city pages)
  - Single database query instead of multiple recursive lookups
  - Excludes 350+ local pages from search results

- **Build Configuration** - Added search.css to build pipeline (`gulpfile.js`)
  - Processes and minifies search.css
  - Generates sourcemaps for debugging
  - Enqueued only on search results pages for performance (`includes/enqueue.php`)

### Fixed
- **Search Filter Registration** - Removed erroneous `return;` statement that prevented filters from loading
  - All search-related filters now properly registered
  - Search customizations now active on search results pages

## [2.7.1] - 2025-11-02
### Changed
- **Deployment Optimization** - Exclude documentation files from production deployment (`.github/workflows/deploy-theme.yml`)
  - Added `--exclude '*.md'` to rsync command
  - Prevents unnecessary documentation files (README.md, CHANGELOG.md, CLAUDE.md, AGENTS.md) from being deployed to production
  - Reduces deployment payload and keeps production lean

## [2.7.0] - 2025-11-02
### Changed
- **Search Functionality** - Disabled search query filtering and excerpt modifications (`includes/search.php`)
  - Added early return to disable all search customizations
  - Search results no longer restricted to pages only
  - "Challenge" heading removal from excerpts no longer applied
  - Reverts to default WordPress search behavior

- **Sitemap Generation** - Simplified sitemap to include only pages (`includes/sitemap.php`)
  - Removed `post` post type (0.8 priority)
  - Removed `project` post type (0.7 priority)
  - Removed `local` post type (0.9 priority)
  - Removed static `/lp/` entry from sitemap header
  - Now only includes `page` post type (0.9 priority)
  - Reduces sitemap size and focuses on primary page content

## [2.6.0] - 2025-11-01
### Added
- **Sticky TOC Disable Option** - Added ability to disable sticky table of contents on specific pages
  - New `body_class` filter in `includes/enqueue.php` applies `disable-sticky-toc` class to designated pages
  - Updated `assets/js/sticky-header.js` to detect body class and skip TOC generation while maintaining header scroll behavior
  - Case studies page (ID: 4406) now disabled by default due to high heading count
  - Easily extensible by adding page IDs to `$disabled_pages` array
  - Prevents navigation clutter on pages with many sections

## [2.5.4] - 2025-11-01
### Changed
- **Architecture Standardization** - Refactored 4 include files to use anonymous functions with PHP 8.0+ named parameters
  - `includes/enqueue.php` - Split into 3 focused callbacks, removed function_exists wrapper
  - `includes/block-styles.php` - Converted to anonymous function, uses short array syntax
  - `includes/block-stylesheets.php` - Converted to anonymous function
  - `includes/pattern-categories.php` - Converted to anonymous function
  - All files now follow consistent modern PHP patterns with named parameters (`hook_name:`, `callback:`, `priority:`)
  - Replaced `array()` with `[]` throughout
  - Better code organization with early returns and focused logic

- **Navigation Styles** - Simplified navigation font size CSS (`assets/css/customizer.css`)
  - Removed redundant media query and duplicate selector
  - Navigation labels now consistently use 1.5rem font size
  - Cleaner, more maintainable stylesheet

### Added
- **Documentation** - Updated `AGENTS.md` with comprehensive PHP architecture guidelines
  - Purpose-based approach for when to use anonymous vs named functions
  - Code examples for both patterns
  - Clear decision criteria and benefits

## [2.5.3] - 2025-11-01
### Removed
- **Unused Patterns** - Removed 22 unused block patterns inherited from Twenty Twenty-Four theme
  - Patterns were not used anywhere in templates, parts, or page content
  - Kept only 2 patterns actually used: `posts-3-col` and `template-home-business`
  - Removed: banner-hero, cta-content-image-on-right, cta-pricing, cta-services-image-left, cta-subscribe-centered, footer-centered-logo-nav, footer-colophon-3-col, footer, gallery-offset-images-grid-2-col, gallery-offset-images-grid-3-col, gallery-offset-images-grid-4-col, page-about-business, page-home-business, team-4-col, testimonial-centered, text-alternating-images, text-centered-statement, text-centered-statement-small, text-faq, text-feature-grid-3-col, text-project-details, text-title-left-image-right

### Added
- **AI Filter** - Added new AI filter to case study filters (`includes/case-study-filters.php`)
  - Comprehensive AI/ML keywords: ai-powered, ai analysis, claude, openai, chatgpt, codex, copilot, machine learning, artificial intelligence
  - Reordered filters for better logical grouping (API and AI filters moved near top)

## [2.5.2] - 2025-11-01
### Removed
- **Test Images** - Removed 6 test PNG files from theme root directory that should never have been committed
  - `menu-closed-fixed.png`
  - `menu-closed.png`
  - `privacy-policy-1024.png`
  - `privacy-policy-check.png`
  - `scroll-50px.png`
  - `test-padding-mobile.png`

### Changed
- **Git Ignore** - Updated `.gitignore` to prevent future accidental commits of test images
  - Added rules to ignore all image files in root theme directory (PNG, JPG, JPEG, GIF, SVG, WEBP)
  - Exception added for `screenshot.png` (required WordPress theme file)
  - Prevents test images from being committed while preserving theme screenshot

## [2.5.1] - 2025-11-01
### Fixed
- **Case Study Excerpts** - Fixed Challenge heading filter to work on case studies archive page (`includes/search.php`)
  - Extended `get_the_excerpt` filter to apply on case studies page (parent ID 4406) in addition to search results
  - Updated logic to find and remove Challenge heading regardless of position (not just first block)
  - Now properly removes separator blocks and other content before Challenge heading from excerpts
  - Ensures case study excerpts on `/case-studies/` page start with actual project description instead of "Challenge"

## [2.5.0] - 2025-11-01
### Added
- **Case Study Filters** - Interactive filtering system for case studies page (`includes/case-study-filters.php`)
  - PHP-based filter configuration with shortcode `[case_study_filters]`
  - Client-side keyword filtering with smooth fade/scale animations
  - Real-time result counter showing "X of Y projects"
  - Shareable URL hash support (e.g., `#filter=woocommerce`)
  - Filter keywords localized to JavaScript via `wp_localize_script()`
  - Filters: All Projects, API, Financial, Gravity Forms, Affiliates, WooCommerce, Security, Reporting, Automation
  - Custom CSS for filter buttons and transitions (`assets/css/case-study-filter.css`)
  - Custom JavaScript for filtering logic (`assets/js/case-study-filter.js`)
  - Updated Query Loop to display 50 case studies (removed pagination)
  - Centralized filter management in single PHP file

## [2.4.2] - 2025-11-01
### Fixed
- **Search Results Display** - Filter out "Challenge" headings from search result excerpts (`includes/search.php`)
  - Added `get_the_excerpt` filter to remove first block when it's a heading containing "Challenge"
  - Pages with "Challenge" headings still appear in search results, but excerpt is generated from subsequent blocks
  - Improves search results display by showing relevant content instead of case study section headings

## [2.4.1] - 2025-11-01
### Fixed
- **Search Page Title** - Fixed bug where search results page displayed the title of the first search result instead of "Search Results for: [query]"
  - Added `is_singular()` check to `document_title` filter in `includes/document-title.php`
  - Custom `_genesis_title` meta field now only applies to individual posts/pages, not archives or search results
  - Resolves issue where `?s=woo` showed "Woonsocket, Rhode Island" title instead of proper search title

### Changed
- **Search Results Filtering** - Updated search query to exclude local pages and specific content (`includes/search.php`)
  - Changed search to only include `page` post type (previously included `project` post type)
  - Added meta query to exclude pages with `_local_page_state` meta key (local pages converted from custom post type)
  - Excluded post 2507 (WordPress Development Services USA landing page) from search results
  - Fixed `post_parent` typo (corrected to `post_type`)
  - Updated docblock to reflect current functionality

## [2.4.0] - 2025-11-01
### Removed
- **Style Variations** - Removed all 7 unused style variation files from `styles/` directory
  - `ember.json`, `fossil.json`, `ice.json`, `maelstrom.json`, `mint.json`, `onyx.json`, `rust.json`
  - These style variations were never activated or used on the site
  - Site uses custom global styles instead

- **Unused Fonts** - Removed 3 font families never referenced in theme (776KB savings)
  - `assets/fonts/cardo/` (392KB) - Serif font
  - `assets/fonts/inter/` (332KB) - Sans-serif font
  - `assets/fonts/spectra/` (52KB) - Outfit font files
  - Only Instrument Sans (body) and Jost (headings) are now included

- **Unused Templates** - Removed 3 template files never assigned to any pages
  - `templates/page-wide.html`
  - `templates/page-with-sidebar.html`
  - `templates/single-with-sidebar.html`

- **Blogging/Portfolio Patterns** - Removed 32 unused pattern files
  - 9 template patterns (blogging/portfolio archive, index, search, single templates)
  - 6 page patterns (blogging/portfolio home pages, newsletter, RSVP landing pages)
  - 5 post display patterns (1-col, grid, images-only variations)
  - 8 hidden component patterns (404, comments, portfolio hero, post meta, etc.)
  - 4 gallery/project patterns (full-screen, project layouts, banners, RSVP CTAs)
  - Site is business-focused with 0 blog posts, these patterns were inherited from Twenty Twenty-Four

- **Documentation Updates**
  - Updated README.md to reflect streamlined font selection and pattern library
  - Updated CHANGELOG.md with comprehensive removal details

**Total Impact**: 45 files removed, ~800KB size reduction, significantly cleaner theme structure

## [2.3.5] - 2025-10-31
### Fixed
- Fixed TOC hamburger icon color inheritance for dark mode support (`assets/css/sticky-header.css`)
  - Changed from hardcoded white color to `currentColor` to inherit from header-2 text color
  - Toggle button now uses `color: inherit` instead of `color: #ffffff!important`
  - Icon bars use `background-color: currentColor` for automatic color adaptation
  - Matches WordPress navigation hamburger behavior for consistent styling

## [2.3.4] - 2025-10-31
### Changed
- Increased scroll indicator arrow size to 2rem and made it bold (`assets/css/sticky-header.css`)

## [2.3.3] - 2025-10-31
### Changed
- TOC menu refinements (`assets/css/sticky-header.css`, `assets/js/sticky-header.js`)
  - Removed drop shadow from header-2
  - Menu now scrollable when content exceeds viewport height
  - Desktop menu matches page content width
  - Mobile menu spans full screen width (100vw)
  - Removed spacing between menu and header-2
  - Custom scrollbar styled with brand color (#004C7E)
  - Down arrow scroll indicator positioned at bottom-right (#4f7606 color)
  - Used CSS display toggle instead of innerHTML swap to eliminate glitches
  - Added hysteresis (40px-60px range) to prevent flickering at scroll threshold
  - Removed scroll-based padding changes for consistent header height
  - Set header-1 and header-2 to consistent 10px padding always
  - Added Jost font family to TOC label

### Fixed
- Eliminated visual glitching during content swap by using pure CSS visibility toggle
- Fixed mobile menu height issues with max-height transitions
- Prevented constant class toggling with scroll hysteresis

## [2.3.1] - 2025-10-31
### Changed
- Removed padding from header-2 when scrolled (`assets/css/sticky-header.css`)
  - Changed from 10px to 0px top and bottom padding for tighter TOC spacing

## [2.3.0] - 2025-10-31
### Added
- Sticky header table of contents navigation (`assets/js/sticky-header.js`, `assets/css/sticky-header.css`)
  - Replaces header-2 content with hamburger menu TOC after 50px scroll
  - Auto-generates navigation from H2 headings on the page
  - Excludes hero headings and first H2 from TOC
  - "Jump to Section →" clickable label with hamburger icon
  - Dropdown menu with full heading text (no truncation)
  - Smooth scroll to sections with header offset
  - Auto-closes menu when clicking link or outside menu

### Changed
- Updated sticky header behavior to show TOC instead of hiding header-2
- Header-2 overflow changed from `hidden` to `visible` to accommodate dropdown menu
- Hamburger icon styled to match main navigation (48px, white color)
- TOC menu link font sizes match main navigation (1rem mobile, 1.5rem desktop)
- Updated `AGENTS.md` testing guidelines to reflect 50px scroll threshold for TOC

## [2.2.1] - 2025-10-30
### Changed
- Updated color scheme to match 84EM logo

## [2.2.0] - 2025-10-30
### Added
- Custom 404 error handling with automatic redirects (`includes/404.php`)
  - Redirects `/project/*` URLs to `/case-studies/*` with 301 permanent redirects
  - Maintains URL structure and query parameters
  - Uses anonymous function with PHP 8.0+ named parameters

### Changed
- Updated `AGENTS.md` to document GitHub Actions automated build process

## [2.1.2] - 2025-10-29
### Fixed
- Adjusted padding on mobile nav styling to accommodate shorter screens

## [2.1.1] - 2025-10-29
### Fixed
- `includes/sitemap.php` - Added proper line breaks and indentation to XML sitemap output for improved readability and debugging

## [2.1.0] - 2025-10-28
### Added
- Added 'local' custom post type to XML sitemap generation
- Implemented batch processing for sitemap generation using Action Scheduler
  - Processes posts in batches of 200 for better performance and reliability
  - Sequential scheduling with 5-second delays to guarantee processing order
  - File locking to prevent concurrent write conflicts
  - Automatic retry mechanism via exception throwing when file operations fail
  - Ensures no partial data is written and sitemap remains valid or doesn't exist

### Changed
- Refactored sitemap generation to use constant array for post types and priorities
- Replaced direct file write with append-based approach using file locking
- Split sitemap generation into coordinator and batch processor functions
- Updated to use `as_schedule_single_action()` instead of `as_enqueue_async_action()` for guaranteed execution order
- Improved error handling: file operation failures now throw exceptions to trigger Action Scheduler retries
- Fixed path concatenation to avoid double slash in `/lp/index.php` reference

## [2.0.2] - 2025-10-28
### Added
- WP-CLI command `wp 84em regenerate-schema` to manually regenerate schema.org structured data
  - Supports `--all`, `--pages`, `--posts`, `--projects`, `--slug`, and `--service-pages` flags
  - Added `includes/cli.php` with ThemeCLI class for command registration

### Changed
- Enhanced pricing schema to include both standard ($150/hr) and after-hours ($225/hr) rates across all service pages
  - `custom-wordpress-plugin-development`
  - `white-label-wordpress-development-for-agencies`
  - `ai-enhanced-wordpress-development`
  - `wordpress-consulting-strategy`
  - `wordpress-maintenance-support`
- Each service now has array of offers instead of single offer for better pricing visibility
- Updated permission check in `includes/schema.php` to support WP-CLI execution context

## [2.0.1] - 2025-10-25
### Changed
- `includes/schema.php` - Updated schema.org structured data for service page
### Removed
- `includes/performance.php` - Removed unused performance optimizations

## [2.0.0] - 2025-10-25

### Added
- Migrated all functionality from 84em-custom plugin into theme
- New `includes/` directory structure for organized theme functionality
  - `includes/acf.php` - ACF customizations
  - `includes/block-styles.php` - Custom block styles registration
  - `includes/block-stylesheets.php` - Block-specific stylesheet enqueuing
  - `includes/breadcrumbs.php` - Breadcrumb functionality for local pages
  - `includes/dequeue.php` - Script/style dequeuing
  - `includes/disable-comments.php` - Comments disabling functionality
  - `includes/document-title.php` - Document title filters
  - `includes/enqueue.php` - Theme script and style enqueuing
  - `includes/footer.php` - Footer functionality and UAGB scripts
  - `includes/google-reviews.php` - Google Reviews Gutenberg block
  - `includes/gravity-forms.php` - Gravity Forms integration
  - `includes/meta-tags.php` - SEO meta tags
  - `includes/pattern-categories.php` - Pattern category registration
  - `includes/permalinks.php` - Permalink customizations
  - `includes/schema.php` - Schema.org structured data
  - `includes/search.php` - Search customizations
  - `includes/shortcode-last-updated.php` - Last updated shortcode
  - `includes/shortlinks.php` - Shortlink handling
  - `includes/sitemap.php` - XML sitemap generation
- Google Reviews block assets in `assets/google-reviews-block/`
- Expanded Gulp build system to handle:
  - Google Reviews block CSS/JS minification
  - Breadcrumbs CSS
  - Highlight CSS/JS
- `AGENTS.md` contributor guide covering structure, builds, testing, and PR workflow
- `CLAUDE.md` pointer that redirects AI agents to maintain documentation inside `AGENTS.md`

### Changed
- Refactored theme architecture - all functionality now self-contained in theme
- Updated `functions.php` to load files from `includes/` directory
- Moved theme-specific functions from functions.php into organized include files
- Updated build process to compile Google Reviews block assets
- Updated CLAUDE.md and README.md with new architecture documentation
- **Minimum PHP requirement raised to 8.0** (uses named arguments, union types, mixed type)
- Tested up to WordPress 6.8.3
- Reinstated the original GNU GPL v2-or-later licensing across LICENSE, `package*.json`, README, and `style.css`
- Updated the theme screenshot to reflect the latest visual refinements

### Removed
- Dependency on 84em-custom plugin (functionality moved to theme)

## [1.3.0] - 2025-10-25

### Added
- Gulp build system for automated asset optimization
  - `package.json` with npm scripts for development and production builds
  - `gulpfile.js` with tasks for CSS and JavaScript minification
  - Autoprefixer support targeting last 2 browser versions
  - Sourcemap generation for easier debugging
- Automated build process in GitHub Actions deployment workflow
  - Assets are now built fresh during deployment
  - Node.js 22 (latest LTS) setup with npm caching for faster CI builds

### Changed
- Migrated from Prepros to Gulp for build tooling
- Build artifacts (`.min.css`, `.min.js`, sourcemaps) now excluded from git
- Updated `.gitignore` to exclude `node_modules/` and minified files
- Updated deployment workflow to include `npm ci` and `npm run build` steps
- Updated documentation in README.md and DEPLOYMENT_SETUP.md

### Removed
- `prepros.config` file (replaced by Gulp configuration)
- Prepros dependency for local development

## [1.2.11] - 2025-10-25
### Changed
- Reduce footer menu font size

## [1.2.10] - 2025-10-25
### Changed
- Updated desktop and mobile styling for the main menu

## [1.2.9] - 2025-10-22

### Fixed
- Fixed sticky header not working on pages with overflow-x:hidden CSS from Spectra plugin
- Changed overflow-x from hidden to clip to maintain sticky positioning functionality

### Changed
- Updated sticky header CSS to override plugin overflow rules that interfere with position:sticky

## [1.2.8] - 2025-09-05

### Added
- Anchor link navigation with header offset for smooth scrolling
- Mobile responsive hiding for UAG elements
- Left padding to WordPress block lists
- Custom link styling for main content area

### Changed
- Adjusted mobile breakpoint and removed default navigation styling
- Updated mobile header layout and regenerated CSS
- Excluded checkmark lists from default padding
- Excluded block button links and button elements from custom styling
- Updated theme color scheme to custom palette
- Changed theme color variable from contrast to custom-color-1

### Fixed
- Improved anchor link navigation with proper header offset calculation

## [1.2.5] - 2025-09-05

### Added
- Header auto-hide on anchor link clicks for better user experience
- Minified asset compilation for CSS and JavaScript
- Sticky header functionality with scroll-based behavior

### Changed
- Removed header shadow and adjusted scroll padding
- Fixed rsync path in GitHub Actions workflow
- Removed deploy script exclusion from rsync

## [1.2.0] - 2025-01-07

### Added
- GitHub Actions workflow for automated theme deployment
  - Triggers on pull request merge to main branch
  - Uses rsync over SSH with custom port configuration
  - Requires only `DEPLOY_SSH_KEY_84EM_THEME` secret
  - Includes manual workflow dispatch option
- Deployment documentation in `.github/DEPLOYMENT_SETUP.md`

### Changed
- Replaced shell script deployment with GitHub Actions CI/CD pipeline
- Deployment now requires pull request approval before production deployment
- Updated deployment to use hardcoded server configuration for improved security

### Security
- SSH credentials now stored as GitHub secrets instead of local configuration
- Deployment restricted to merged pull requests only

## [1.1.1] - 2025-08-07

### Changed
- Updated README.md Version History section to link to CHANGELOG.md for better version tracking

## [1.1.0] - 2025-08-07

### Added
- Font display swap property to all web fonts for improved performance
  - Added `fontDisplay: "swap"` to all fontFace declarations in theme.json
  - Updated all style variations (ember, fossil, ice, maelstrom, mint) with font display swap
  - Improves Core Web Vitals scores by preventing flash of invisible text (FOIT)
  - Ensures text remains visible during web font loading

## [1.0.1] - 2025-08-07

### Fixed
- Corrected theme directory path in deployment script

## [1.0.0] - 2025-07-26

### Added
- Initial release of 84EM Block Theme
- Full Site Editing (FSE) support based on Twenty Twenty-Four
- 40+ custom block patterns organized by category:
  - Banners and hero sections
  - Call-to-actions (pricing, RSVP, subscription)
  - Gallery layouts (full-screen, grid, offset images)
  - Content patterns (FAQ, testimonials, feature grids)
  - Footer variations
- 7 style variations with unique color schemes:
  - Ember (warm oranges)
  - Fossil (earth tones)
  - Ice (cool blues)
  - Maelstrom (dark theme)
  - Mint (green accents)
  - Onyx (monochrome)
  - Rust (rich browns)
- Custom web fonts:
  - Instrument Sans (body text)
  - Jost (headings)
  - Cardo (serif option)
  - Inter (alternate sans-serif)
  - Outfit (display font)
- Page templates:
  - Standard page layouts
  - Page with sidebar
  - Page without title
  - Wide image page
  - Single post with sidebar
- Archive templates for blog and portfolio
- Custom template for local pages post type
- Responsive design optimized for all devices
- Accessibility-ready features
- WordPress 6.0+ compatibility
- PHP 5.7+ support

### Theme Features
- Block editor styles
- Custom colors and gradients
- Editor font sizes
- Wide blocks support
- RTL language support
- Threaded comments
- Translation ready

### Technical Implementation
- Clean theme.json configuration
- Organized file structure
- Deployment script for production
- Optimized web font loading
- Custom CSS for button outlines
- Semantic HTML structure
