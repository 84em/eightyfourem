# Repository Guidelines

## Project Overview
The 84EM Block Theme is a custom WordPress FSE theme optimized for business websites. Recently streamlined (v2.4.0, v2.5.3) to remove unused patterns, fonts, templates, and style variations inherited from Twenty Twenty-Four base theme.

**Key Features:**
- **Case Study Filters** - Interactive filtering system with shareable URLs (`includes/case-study-filters.php`)
- **Sticky Header TOC** - Dynamic table of contents navigation in header (`assets/js/sticky-header.js`)
- **Google Reviews Block** - Custom Gutenberg block for displaying reviews
- **SEO Suite** - Meta tags, schema.org structured data, XML sitemap with batch processing
- **Custom 404 Handling** - Automatic redirects for legacy URLs (`includes/404.php`)

## Project Structure & Module Organization
- `assets/css|js|fonts/` hold front-end sources; compiled files inherit the same path with `.min.(css|js)` suffixes. Keep Google Reviews block assets inside `assets/google-reviews-block/`.
- `includes/` contains PHP modules loaded by `functions.php`. Add new features by creating a focused include (e.g., `includes/case-study-filters.php`) and requiring it in `functions.php`.
- `patterns/` directory now contains **only 2 patterns** actively used: `posts-3-col.php` and `template-home-business.php`. All unused Twenty Twenty-Four patterns removed (v2.4.0, v2.5.3).
- `parts/`, `templates/` mirror core block theme conventions. Keep template-part slugs consistent to prevent Site Editor mismatches.
- `styles/` directory empty - all 7 style variations removed (v2.4.0) as site uses custom global styles.
- `theme.json` centralizes color, typography, and spacing tokens—extend global styles there before touching individual CSS files.

## Build, Test, and Development Commands
- `npm install` — install gulp-based tooling; rerun after updating `package.json`.
- `npm start` — default watcher; compiles CSS/JS with sourcemaps and reloads on change.
- `npm run build` — production build with minification and autoprefixing; **automated via GitHub Actions on push, do NOT run manually for releases**.
- `npm run clean` — remove generated `.min.*` artifacts to ensure a fresh pipeline.

## Documentation Guidelines
- **ALWAYS update this AGENTS.md file** after making code changes that affect:
  - Project structure (new directories, file organization)
  - Build processes or development workflows
  - Coding conventions or patterns
  - New features or architectural changes
  - Testing procedures
  - Deployment or release processes
- Keep documentation current so future AI agents and developers understand the codebase accurately

## Key Include Files
Files in `includes/` directory provide modular functionality:

**Core Features:**
- `case-study-filters.php` - Interactive filtering system with shortcode `[case_study_filters]`, keyword configuration, JS localization
- `google-reviews.php` - Custom Gutenberg block for displaying Google Business reviews
- `404.php` - Custom redirect handler (e.g., `/project/*` → `/case-studies/*`)

**SEO & Content:**
- `meta-tags.php` - SEO meta tags (title, description, Open Graph, Twitter Cards)
- `schema.php` - Schema.org structured data generation for pages, posts, projects
- `sitemap.php` - XML sitemap with batch processing via Action Scheduler
- `search.php` - Search result filtering, Challenge heading removal from excerpts
- `breadcrumbs.php` - Breadcrumb navigation functionality

**UI & Navigation:**
- `enqueue.php` - Script/style enqueuing for sticky header, case study filters, Google Reviews, breadcrumbs
- `block-styles.php` - Custom block style registration
- `block-stylesheets.php` - Block-specific stylesheet loading

**Integrations:**
- `gravity-forms.php` - Gravity Forms customizations
- `acf.php` - Advanced Custom Fields customizations
- `cli.php` - WP-CLI commands for schema regeneration

## Coding Style & Naming Conventions

### PHP Architecture
All files in `includes/` follow a **purpose-based approach** to function organization:

**Use Anonymous Functions (Default):**
- ✅ Single-purpose hook/filter callbacks
- ✅ Self-contained logic only called by WordPress
- ✅ Functions under 50 lines
- ✅ PHP 8.0+ named parameters (`hook_name:`, `callback:`, `priority:`)
- ✅ Short array syntax `[]` instead of `array()`
- ✅ No `function_exists()` checks needed

**Example:**
```php
\add_action(
	hook_name: 'wp_head',
	callback: function () {
		// Simple, focused logic
	},
	priority: 1
);
```

**Use Named Functions (When Needed):**
- ✅ Functions called from multiple places (helpers, utilities)
- ✅ Complex logic benefiting from descriptive names in stack traces
- ✅ Exposed via shortcodes, WP-CLI, or public API
- ✅ Functions you might unit test
- ✅ Functions over 50 lines

**Example:**
```php
namespace EightyFourEM\CaseStudyFilters;

function get_filters() {
	return [ /* ... */ ];
}

add_shortcode( 'case_study_filters', __NAMESPACE__ . '\\render_filters' );
```

### General Conventions
- PHP follows WordPress Coding Standards: tabs for indentation, early returns
- All custom code uses `namespace EightyFourEM\` or sub-namespaces
- SCSS/CSS files use 2-space indentation; prefer block-specific class prefixes (`.case-study-filter-btn`)
- JavaScript in `assets/js/` is plain ES2015; keep modules IIFE-scoped
- Block pattern slugs and filenames use lowercase with hyphens (`patterns/posts-3-col.php`)

## Testing Guidelines
- No automated suite yet; smoke-test changes by activating the theme in a local WordPress install and exercising modified templates/patterns.
- Validate responsive behavior in Chrome DevTools' device modes and confirm JavaScript features log no console errors.
- When editing data-driven templates, compare rendered markup against `theme.json` tokens to avoid color/spacing regressions.

### Feature-Specific Testing
- **Sticky Header TOC** (`assets/js/sticky-header.js`)
  - Scroll past 50px to confirm hamburger menu TOC appears
  - Verify TOC generates from H2 headings, excludes hero and first H2
  - Test dropdown menu opens/closes, smooth scroll works with header offset
  - Confirm menu auto-closes when clicking links or outside menu
  - Check hysteresis (40px-60px) prevents flickering at scroll threshold
  - Mobile: verify full-width menu (100vw), scrollbar styling, down arrow indicator

- **Case Study Filters** (`includes/case-study-filters.php`, `assets/js/case-study-filter.js`)
  - Test all filter buttons (All, Financial, API, AI, Affiliates, E-Commerce, Education, Security, Reporting, Automation)
  - Verify keyword matching works (check AI filter with multiple keywords)
  - Confirm result counter updates ("X of Y projects")
  - Test shareable URL hashes (e.g., `#filter=woocommerce`)
  - Check smooth fade/scale animations during filtering
  - Verify page loads with hash filter applied

- **Search/Excerpt Filtering** (`includes/search.php`)
  - Test search results exclude "Challenge" headings from excerpts
  - Verify case studies page (parent 4406) strips Challenge headings from excerpts
  - Confirm excerpts start with actual content, not section headings

## Release Process
When preparing a release with version bump:
1. Update version numbers in:
   - `style.css` (Version header comment)
   - `package.json` (version field)
2. **ALWAYS update `CHANGELOG.md`** following [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) format
   - Add new version section with date
   - Categorize changes: Added, Changed, Deprecated, Removed, Fixed, Security
   - Be specific about files and functionality affected
3. Commit version files with format: `v{version} - Brief description of main changes`
4. Create git tag: `git tag v{version}`
5. Push branch and tags to GitHub for PR review
6. Follow [Semantic Versioning](https://semver.org/spec/v2.0.0.html):
   - MAJOR: Breaking changes
   - MINOR: New features (backwards compatible)
   - PATCH: Bug fixes (backwards compatible)

## Commit & Pull Request Guidelines
- Use imperative, scope-first commit subjects under 72 chars (e.g., `Enqueue: add critical CSS preload`). Group related edits into a single commit.
- Reference Jira/GitHub issues in the body when available and describe testing performed (e.g., "Tested locally on WP 6.8.3").
- Pull requests should summarize the change, outline manual verification, and include before/after screenshots for visual updates. Request design review for pattern tweaks.
- GitHub Actions automatically runs `npm run build` on push to compile production assets.

## Security & Configuration Tips
- Never hardcode credentials or API keys; pull secrets from environment variables or WordPress options pages instead.
- Sanitize and escape all dynamic output using `esc_html`, `wp_kses`, or block supports, matching existing patterns in `includes/meta-tags.php`.
