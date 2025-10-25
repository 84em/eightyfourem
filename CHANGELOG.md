# Changelog

All notable changes to the 84EM Block Theme will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
