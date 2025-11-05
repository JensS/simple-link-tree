# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Simple Linktree** is a minimalist WordPress/ClassicPress plugin that creates a Linktree-style link-in-bio page with automatic dark/light mode detection based on browser preferences.

**Key Features:**
- Custom URL slug for linktree page (e.g., `/links`, `/bio`)
- Drag-and-drop link reordering using SortableJS
- Auto dark/light mode via CSS `prefers-color-scheme`
- Theme-independent rendering (bypasses WordPress theme)
- AJAX-based link management
- Icon support (emojis/unicode)

## Architecture

### Plugin Structure

This is a single-class WordPress plugin with a singleton pattern:

```
simple-linktree/
├── simple-linktree.php          # Main plugin file (Simple_Linktree class)
├── admin/
│   ├── css/admin.css            # Admin UI styles
│   ├── js/admin.js              # SortableJS integration, AJAX handlers
│   └── views/admin-page.php     # Admin interface template
└── public/
    └── views/linktree-page.php  # Public-facing linktree template
```

### Core Architecture (`simple-linktree.php`)

**Singleton Pattern:**
- `Simple_Linktree::get_instance()` - Single instance throughout lifecycle

**WordPress Integration:**
- **Rewrite Rules**: Creates custom URL endpoint at `/{slug}` via `add_rewrite_rule()`
- **Query Vars**: Registers `simple_linktree` query var for routing
- **Template Redirect**: Intercepts requests when `simple_linktree=1` and renders custom template
- **Options API**: Stores data in WordPress options table:
  - `slt_page_slug` - Custom URL slug (default: 'links')
  - `slt_links` - JSON-encoded array of link objects
  - `slt_profile_name` - Profile name displayed at top
  - `slt_profile_bio` - Optional bio text

**AJAX Endpoints:**
- `wp_ajax_slt_save_links` - Saves all links (order preserved)
- `wp_ajax_slt_delete_link` - Deletes single link by ID

### Data Model

Links are stored as JSON in `slt_links` option:

```php
[
    {
        "id": "link-1234567890",  // Unique ID (timestamp-based)
        "title": "Link Title",
        "url": "https://example.com",
        "icon": "🔗"  // Optional emoji/unicode
    }
]
```

### Frontend Architecture

**Theme Bypass:**
- Uses `template_redirect` hook to completely bypass WordPress theme
- Renders standalone HTML page with inline CSS
- No theme dependencies - ensures consistent appearance

**Dark/Light Mode:**
- CSS-only implementation using `@media (prefers-color-scheme: dark)`
- No JavaScript toggle needed
- Automatic browser preference detection

### Admin Interface

**Drag & Drop:**
- SortableJS library (loaded from CDN: `https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js`)
- Handle-based dragging (`.slt-link-handle`)
- Order saved to database on "Save All Links" click

**JavaScript Architecture (`admin/js/admin.js`):**
- jQuery-based for WordPress compatibility
- AJAX communication via `sltAdmin.ajax_url` and nonce
- Real-time slug preview update
- Auto-save keyboard shortcut (Ctrl/Cmd+S)
- URL auto-prefixing with `https://`

## Development Notes

### Adding New Features

**Adding Link Properties:**
1. Update data model in `ajax_save_links()` sanitization loop (simple-linktree.php:186-192)
2. Add input field to admin template (admin/views/admin-page.php)
3. Update AJAX serialization in admin.js (admin/js/admin.js:68-80)
4. Display in public template (public/views/linktree-page.php)

**Adding New Settings:**
1. Register setting: `register_setting('slt_settings', 'slt_new_option')` in `register_settings()`
2. Add form field to admin-page.php settings section
3. Handle save in `admin_page()` POST handler
4. Add default value in `activate()` method

### Rewrite Rules

**IMPORTANT**: When changing the slug system:
- Rewrite rules are added in `init()` hook
- Must call `flush_rewrite_rules()` after slug changes
- Activation/deactivation hooks already handle this
- Query var `simple_linktree` triggers custom template rendering

### AJAX Security

All AJAX requests use:
- Nonce verification: `check_ajax_referer('slt_admin_nonce', 'nonce')`
- Capability check: `current_user_can('manage_options')`
- Input sanitization: `sanitize_text_field()`, `esc_url_raw()`, etc.

### SortableJS Integration

The drag-and-drop functionality depends on SortableJS being loaded **before** admin.js:

```php
wp_register_script('sortable', 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js', array(), '1.15.0', true);
wp_enqueue_script('sortable');
wp_enqueue_script('slt-admin-js', ..., array('jquery', 'sortable'), ...);
```

Order is preserved by DOM order when serializing links in `$('#save-links-btn').on('click')` handler.

## Important Implementation Details

### Theme Independence

The plugin bypasses WordPress themes entirely:
- `template_redirect` hook exits early with custom output
- No `get_header()`, `get_footer()`, or theme template tags
- Inline CSS ensures no theme stylesheet conflicts

### Link ID Generation

Links use timestamp-based IDs: `'link-' + Date.now()` (admin.js:24)
- Ensures unique IDs without database roundtrip
- IDs persist through reordering
- Used for deletion targeting

### URL Sanitization

URLs are automatically prefixed with `https://` on blur if missing protocol (admin.js:121-128)

### Admin Bar Integration

"View Linktree Page" button added to WordPress admin bar (`add_admin_bar_menu()`) opens linktree in new tab.

## ClassicPress Compatibility

This plugin is fully compatible with ClassicPress (WordPress fork):
- Uses WordPress core APIs only (no Gutenberg dependencies)
- Classic admin interfaces (no block editor)
- Standard options/settings API

## Common Development Tasks

### Testing Slug Changes

1. Change slug in admin settings
2. Save settings (triggers `flush_rewrite_rules()`)
3. Verify old slug returns 404
4. Verify new slug loads linktree page

### Testing Link Reordering

1. Add multiple links in admin
2. Drag links to new positions
3. Click "Save All Links"
4. Verify order persists on page reload
5. Check frontend displays in correct order

### Testing AJAX Functionality

1. Open browser dev tools → Network tab
2. Add/delete/reorder links
3. Click "Save All Links"
4. Verify AJAX requests to `admin-ajax.php`
5. Check response status and data

### Debugging

Enable WordPress debug mode (if not already enabled):

```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Check error logs at: `wp-content/debug.log`

## Browser Compatibility

Minimum browser requirements:
- Modern browsers with CSS custom properties support
- `prefers-color-scheme` media query support (2019+)
- ES5 JavaScript (for admin interface)
