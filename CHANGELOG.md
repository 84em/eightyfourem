# Changelog

All notable changes to the 84EM Block Theme will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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