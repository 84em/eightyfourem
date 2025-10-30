# Changelog

All notable changes to the 84EM Block Theme will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
- Adjusted padding on mobile nav styling to accomodate shorter screens

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
