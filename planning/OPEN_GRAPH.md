# Open Graph Images Feature Plan

## Overview
Create `includes/open-graph-images.php` to handle Open Graph (og:image) meta tags with global default and post-level overrides.

## Implementation Details

### File Structure
- New file: `includes/open-graph-images.php`
- Add to `functions.php`: `require_once get_template_directory() . '/includes/open-graph-images.php';`
- Update `includes/cli.php`: Add WP-CLI test commands

### Features

#### 1. WordPress Settings Screen
- Add submenu under Settings → "Open Graph Images"
- Media library button to select default image
- Store attachment ID in option: `eightyfourem_default_og_image`
- Use WordPress Settings API for security (nonces, sanitization)
- Display image preview with dimensions/file size

#### 2. Post Meta Box
- Add meta box to posts, pages, and projects
- Media library button to override default
- Store in post meta: `_eightyfourem_og_image`
- Show "Using default" when no override set
- Clear button to remove override

#### 3. Output Generation
- Hook into `wp_head` (priority 5, after meta-tags.php)
- Output og:image, og:image:width, og:image:height, og:image:alt
- Output Twitter Card equivalents (twitter:image, twitter:image:alt)
- Fallback order:
  1. Post-level override
  2. Featured image (if set)
  3. Global default
  4. Skip if none available

## WP-CLI Integration Testing (NO MOCKS)

### Command Structure
`wp 84em test-og-images [--all|--global-default|--post-override|--fallback-logic|--meta-output|--validation|--cleanup]`

### Test Suite

#### 1. Global Default Test (`--global-default`)
- Create real image attachment using GD library
- Set option via `update_option()`
- Verify with `get_option()`
- Check attachment metadata with `wp_get_attachment_metadata()`
- Validate image URL with `wp_get_attachment_url()`

#### 2. Post Override Test (`--post-override`)
- Create real post via `wp_insert_post()`
- Create real attachment
- Set post meta via `update_post_meta()`
- Verify with `get_post_meta()`
- Test clearing override with `delete_post_meta()`

#### 3. Fallback Logic Test (`--fallback-logic`)
- Create post with no images (should use default)
- Set featured image via `set_post_thumbnail()` (should override default)
- Set post override (should take priority over both)
- Verify fallback order at each step

#### 4. Meta Output Test (`--meta-output`)
- Capture `wp_head` output via `ob_start()`/`ob_get_clean()`
- Use `do_action('wp_head')` to trigger hooks
- Parse output for required meta tags
- Verify proper escaping with `esc_url()`, `esc_attr()`
- Check for og:image:secure_url presence

#### 5. Validation Test (`--validation`)
- Create images at different sizes (valid 1200x630, small 600x315)
- Check MIME types via `get_post_mime_type()`
- Verify attachment post type
- Check file exists on disk with `get_attached_file()` + `file_exists()`

#### 6. Cleanup (`--cleanup`)
- Remove test posts via `wp_delete_post($id, true)`
- Remove test attachments via `wp_delete_attachment($id, true)`
- Delete test option via `delete_option()`

### Helper Functions
- `create_test_image($filename, $width, $height)` - Uses GD to create real JPEG
- `get_og_image_for_test($post_id)` - Simulates fallback logic for verification

## SEO Best Practices

### Image Requirements
- Minimum 1200×630px (Facebook/LinkedIn recommended)
- Validate aspect ratio warning in admin
- Show file size warning if >8MB
- Support common formats: JPG, PNG, WebP

### Meta Tags Output
- Include `og:image:secure_url` (https)
- Include dimensions for faster rendering
- Include `og:image:type` (image/jpeg, etc.)
- Include descriptive alt text from attachment

### Performance
- Use transient cache for image metadata (1 hour)
- Validate attachment exists before output
- Use `wp_get_attachment_image_src()` for proper sizing

## Security Considerations

### Input Validation
- Verify attachment IDs are valid integers via `absint()`
- Confirm attachments are images (MIME type check)
- Use `wp_verify_nonce()` for all forms
- Check `current_user_can('manage_options')` for settings
- Check `current_user_can('edit_post')` for meta boxes

### Output Escaping
- `esc_url()` for all image URLs
- `esc_attr()` for alt text and dimensions
- `wp_kses_post()` for any HTML in admin

### Data Sanitization
- `absint()` for attachment IDs
- `sanitize_text_field()` for text inputs
- Use `update_option()` with proper sanitization callbacks

## Architecture Consistency

### Follow existing patterns
- Use `namespace EightyFourEM;`
- Anonymous functions with PHP 8.0+ named parameters for hooks
- Named functions for reusable helpers (get_og_image_data, validation)
- `defined( 'ABSPATH' ) || exit;` guard
- Tab indentation (WordPress standards)
- Descriptive comments for complex logic

### Named functions for
- `get_og_image_data($post_id)` - retrieves image with fallback logic
- `render_settings_page()` - admin settings HTML
- `render_meta_box($post)` - post meta box HTML
- `validate_og_image($attachment_id)` - validates image dimensions/size
- `save_settings()` - handles settings form submission
- `save_meta_box($post_id)` - handles meta box save

## Testing Philosophy

### Real WordPress Integration
- All tests run in actual WordPress environment (local container)
- Use real database operations (no mocks)
- Use real file system (create actual images)
- Use real WordPress APIs (posts, attachments, options, meta)
- Capture real `wp_head` output
- Test actual user workflows

### What to Test
- CRUD operations (create, read, update, delete)
- Fallback logic with real data
- Database persistence
- File handling
- Security checks (capabilities, nonces)
- Output generation and escaping

### What NOT to Test
- WordPress core functions themselves
- Third-party libraries
- Mock scenarios that don't reflect real usage

## Suggested Future Enhancements

1. **Bulk Actions** - WP-CLI command to set OG images for multiple posts
2. **Auto-generation** - Option to auto-crop featured images to 1200×630
3. **Preview Tool** - Show how card will appear on Facebook/Twitter/LinkedIn
4. **Validation Warnings** - Admin notices for images not meeting requirements
5. **Post Type Control** - Settings to enable/disable per post type