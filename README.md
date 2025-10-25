# 84EM Block Theme

A modern WordPress block theme for 84EM, based on Twenty Twenty-Four with custom enhancements and styling.

## Overview

The 84EM Block Theme is a full site editing (FSE) WordPress theme that provides a flexible foundation for building modern websites. Built on the WordPress block editor, it offers extensive customization options through block patterns, style variations, and custom templates.

## Features

- **Full Site Editing (FSE)** - Complete control over your site's layout and design
- **Block Patterns** - Pre-designed content layouts for quick page building
- **Style Variations** - Multiple color schemes (Ember, Fossil, Ice, Maelstrom, Mint, Onyx, Rust)
- **Custom Templates** - Specialized page layouts including sidebar and wide image options
- **Typography** - Custom web fonts including Cardo, Instrument Sans, Inter, Jost, and Outfit
- **Responsive Design** - Optimized for all device sizes
- **Accessibility Ready** - Built with web accessibility standards in mind
- **SEO Optimized** - Built-in meta tags, schema.org structured data, and XML sitemap
- **Google Reviews Block** - Custom Gutenberg block for displaying Google Business reviews
- **Performance Optimized** - Asset minification, lazy loading, and caching support

## Requirements

- WordPress 6.0 or higher
- PHP 8.0 or higher
- Tested up to WordPress 6.8.3

## Installation

1. Upload the theme files to `/wp-content/themes/eightyfourem/`
2. Activate the theme through the WordPress admin panel
3. Navigate to Appearance > Site Editor to customize your site

## Theme Structure

### Templates
- **Page Templates**: Standard page, page with sidebar, page without title, wide image page
- **Post Templates**: Single post, single with sidebar
- **Archive Templates**: Blog and portfolio archives
- **Special Templates**: 404 error, search results, home variations

### Block Patterns
The theme includes over 40 block patterns organized by category:
- **Banners**: Hero sections and project descriptions
- **Call-to-Actions**: Pricing, RSVP, subscription forms
- **Galleries**: Full-screen, grid, and offset image layouts
- **Content**: FAQ, testimonials, feature grids
- **Footer**: Various footer layouts

### Style Variations
Choose from 7 built-in color schemes:
- Ember (warm oranges)
- Fossil (earth tones)
- Ice (cool blues)
- Maelstrom (dark theme)
- Mint (green accents)
- Onyx (monochrome)
- Rust (rich browns)

## Customization

### Using the Site Editor
1. Go to Appearance > Site Editor
2. Select templates or template parts to edit
3. Use block patterns for quick content creation
4. Apply style variations from the Global Styles panel

### Custom Fonts
The theme includes optimized web fonts stored in `/assets/fonts/`:
- **Cardo**: Serif font for elegant typography
- **Instrument Sans**: Modern sans-serif
- **Inter**: Versatile system font
- **Jost**: Geometric sans-serif
- **Outfit**: Display font for headers

## Development

### Build Process

The theme uses Gulp for asset optimization. Before working with the theme, install dependencies:

```bash
npm install
```

#### Available Commands

- `npm start` - Build assets and watch for changes (development mode)
- `npm run build` - Build and minify all assets (production mode)
- `npm run watch` - Watch files for changes without initial build
- `npm run clean` - Remove all generated minified files

#### What Gets Built

The build process handles:
- **CSS files**:
  - Theme: `customizer.css`, `sticky-header.css`, `breadcrumbs.css`, `highlight.css`
  - Google Reviews Block: `editor.css`, `style.css` (in `assets/google-reviews-block/`)
  - Autoprefixer (targets last 2 browser versions)
  - Minification
  - Sourcemaps
- **JavaScript files**:
  - Theme: `sticky-header.js`, `highlight.js`
  - Google Reviews Block: `block.js` (in `assets/google-reviews-block/`)
  - Minification with terser
  - Sourcemaps

All minified files are output with `.min.css` or `.min.js` extensions.

### File Structure
```
eightyfourem/
├── assets/
│   ├── css/                  # Custom stylesheets
│   ├── fonts/                # Web font files
│   ├── js/                   # JavaScript files
│   └── google-reviews-block/ # Google Reviews block assets
├── includes/                 # Theme functionality modules
│   ├── acf.php              # ACF customizations
│   ├── block-styles.php     # Custom block styles
│   ├── block-stylesheets.php # Block-specific stylesheets
│   ├── breadcrumbs.php      # Breadcrumb functionality
│   ├── document-title.php   # Document title filters
│   ├── enqueue.php          # Script/style enqueuing
│   ├── google-reviews.php   # Google Reviews block
│   ├── gravity-forms.php    # Gravity Forms integration
│   ├── meta-tags.php        # SEO meta tags
│   ├── pattern-categories.php # Pattern categories
│   ├── performance.php      # Performance optimizations
│   ├── permalinks.php       # Permalink customizations
│   ├── schema.php           # Schema.org structured data
│   ├── sitemap.php          # XML sitemap generation
│   └── ... (additional includes)
├── parts/            # Template parts
├── patterns/         # Block patterns
├── styles/           # Style variations
├── templates/        # Page templates
├── functions.php     # Theme loader (includes files from includes/)
├── gulpfile.js       # Build configuration
├── package.json      # Node dependencies
├── style.css         # Main stylesheet
└── theme.json        # Theme configuration
```

### Theme Configuration
The `theme.json` file controls:
- Color palettes and gradients
- Typography scales
- Spacing options
- Layout settings
- Custom templates and patterns

## License

This theme is licensed under the MIT License. See [LICENSE](LICENSE) file for details.

Copyright (c) 2025 84EM

Based on Twenty Twenty-Four by the WordPress team.

### Image Credits
All images are licensed under CC0 (Creative Commons Zero) from Rawpixel, except for icon-message.webp which uses Unicode License V3.

## Support

For theme support and customization services, visit [84EM.com](https://www.84em.com/).

## Version History

See [CHANGELOG.md](CHANGELOG.md) for a detailed version history.
