# Repository Guidelines

## Project Structure & Module Organization
- `assets/css|js|fonts/` hold front-end sources; compiled files inherit the same path with `.min.(css|js)` suffixes. Keep Google Reviews block assets inside `assets/google-reviews-block/`.
- `includes/` contains PHP modules loaded by `functions.php`. Add new features by creating a focused include (e.g., `includes/performance.php`) and requiring it in `functions.php`.
- `patterns/`, `parts/`, `templates/`, and `styles/` mirror core block theme conventions. Keep template-part slugs consistent to prevent Site Editor mismatches.
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

## Coding Style & Naming Conventions
- PHP follows the WordPress Coding Standards: tabs for indentation, snake_case functions (`eightyfourem_register_patterns`), and early returns. Align enqueue logic with existing helpers in `includes/enqueue.php`.
- Files in the /includes/ directory are named for their purpose, e.g., `includes/performance.php` and use anonymous functions and name identifiers for function arguments.
- SCSS/CSS files use 2-space indentation inside `assets/css/`; prefer block-specific class prefixes such as `.ef-hero__heading`.
- JavaScript in `assets/js/` is plain ES2015; keep modules IIFE-scoped and lint manually for now (no automated linter).
- Block pattern slugs and filenames stay lowercase with hyphens (`patterns/cta-grid.html`).

## Testing Guidelines
- No automated suite yet; smoke-test changes by activating the theme in a local WordPress install and exercising modified templates/patterns.
- Validate responsive behavior in Chrome DevTools' device modes and confirm the sticky header (`assets/js/sticky-header.js`) logs no console errors.
- When editing data-driven templates, compare rendered markup against `theme.json` tokens to avoid color/spacing regressions.
- For the sticky header TOC, scroll past 50px to confirm the anchor menu appears, caps long labels with ellipses, skips hero headings, and swaps back to the original promo copy when returning to the top.

## Release Process
When preparing a release with version bump:
1. Update version number in `style.css`
2. **ALWAYS update `CHANGELOG.md`** following [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) format
   - Add new version section with date
   - Categorize changes: Added, Changed, Deprecated, Removed, Fixed, Security
   - Be specific about files and functionality affected
3. Commit with format: `v{version} - Brief description of main changes`
4. Follow [Semantic Versioning](https://semver.org/spec/v2.0.0.html):
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
