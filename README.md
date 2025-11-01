# 84EM Block Theme

A modern WordPress block theme for 84EM, based on Twenty Twenty-Four with custom enhancements and styling.

## Overview

The 84EM Block Theme is a full site editing (FSE) WordPress theme that provides a flexible foundation for building modern websites. Built on the WordPress block editor, it offers extensive customization options through block patterns and custom templates.

## Features

- **Full Site Editing (FSE)** - Complete control over your site's layout and design
- **Block Patterns** - Pre-designed content layouts for quick page building
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
- **Page Templates**: Standard page, page without title, no CTA page
- **Post Templates**: Single post
- **Archive Templates**: Archive and index templates
- **Custom Post Types**: Local pages template (single-local.html)
- **Special Templates**: 404 error, search results, home template

### Block Patterns
The theme includes a minimal set of block patterns in active use:
- **posts-3-col**: Three-column post layout
- **template-home-business**: Business homepage template

Note: Unused patterns inherited from Twenty Twenty-Four have been removed to keep the theme lean and focused on actual site needs.

## Customization

### Using the Site Editor
1. Go to Appearance > Site Editor
2. Select templates or template parts to edit
3. Use block patterns for quick content creation
4. Customize colors and typography from the Global Styles panel

### Custom Fonts
The theme includes optimized web fonts stored in `/assets/fonts/`:
- **Instrument Sans**: Modern sans-serif for body text
- **Jost**: Geometric sans-serif for headings

## Development

### WP-CLI Commands

The theme includes WP-CLI commands for schema regeneration:

```bash
# Regenerate schema for all content
wp 84em regenerate-schema --all

# Regenerate schema for specific content types
wp 84em regenerate-schema --pages
wp 84em regenerate-schema --posts
wp 84em regenerate-schema --projects
wp 84em regenerate-schema --service-pages

# Regenerate schema for specific content by slug
wp 84em regenerate-schema --slug=about
```

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
│   ├── author-pages.php     # Author page customizations
│   ├── block-styles.php     # Custom block styles
│   ├── block-stylesheets.php # Block-specific stylesheets
│   ├── breadcrumbs.php      # Breadcrumb functionality
│   ├── cli.php              # WP-CLI commands
│   ├── dequeue.php          # Script/style dequeuing
│   ├── disable-comments.php # Comments disabling functionality
│   ├── document-title.php   # Document title filters
│   ├── enqueue.php          # Script/style enqueuing
│   ├── footer.php           # Footer functionality and UAGB scripts
│   ├── google-reviews.php   # Google Reviews block
│   ├── gravity-forms.php    # Gravity Forms integration
│   ├── meta-tags.php        # SEO meta tags
│   ├── pattern-categories.php # Pattern categories
│   ├── permalinks.php       # Permalink customizations
│   ├── schema.php           # Schema.org structured data
│   ├── search.php           # Search customizations
│   ├── shortcode-last-updated.php # Last updated shortcode
│   ├── shortlinks.php       # Shortlink functionality
│   └── sitemap.php          # XML sitemap generation
├── parts/            # Template parts
├── patterns/         # Block patterns
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

This theme is licensed under the GNU GPL v2.0 or later. See [LICENSE](LICENSE) file for details.

Copyright (c) 2025 84EM

Based on Twenty Twenty-Four by the WordPress team.

### Image Credits
All images are licensed under CC0 (Creative Commons Zero) from Rawpixel, except for icon-message.webp which uses Unicode License V3.

## Support

For theme support and customization services, visit [84EM.com](https://www.84em.com/).

## Version History

See [CHANGELOG.md](CHANGELOG.md) for a detailed version history.
