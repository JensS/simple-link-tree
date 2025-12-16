# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Simple Linktree is a minimalist **ClassicPress plugin** that creates a "link-in-bio" style page with automatic dark/light mode support. The plugin **completely bypasses the theme** to render its own standalone template, ensuring consistent appearance across all installations.

**IMPORTANT**: This plugin is designed primarily for **ClassicPress**. WordPress compatibility is maintained where possible, but ClassicPress compatibility is the priority. Never use WordPress-specific features (like Gutenberg blocks) or deprecated libraries (like jQuery UI Sortable).

**Current Version**: 1.2.0 (check `SIMPLE_LINKTREE_VERSION` constant in simple-linktree.php)

## Architecture

### Core Design Pattern

The plugin uses a **singleton class pattern** with the main logic in `simple-linktree.php`. The architecture is built around three core concepts:

1. **Custom Rewrite Rules**: WordPress URL routing intercepts the custom slug (default: `/links`) and triggers the plugin's template renderer
2. **Theme Bypass**: Uses `template_redirect` hook to output HTML before the theme loads, then exits
3. **Options-based Storage**: All data stored as JSON in WordPress `wp_options` table (no custom database tables)

### Data Flow

```
User visits /links → WordPress rewrite engine → Query var: simple_linktree=1
→ template_redirect hook → render_linktree_page() → Load public template → Exit
```

```
Admin saves links → AJAX request → Nonce validation → Sanitize data
→ JSON encode → Update wp_options → Return success response
```

### Key Components

| Component | File | Purpose |
|-----------|------|---------|
| Main Plugin Class | `simple-linktree.php` | Singleton, hooks, routing, AJAX handlers |
| Admin Interface | `admin/views/admin-page.php` | Two-panel layout: settings + link management |
| Admin JavaScript | `admin/js/admin.js` | SortableJS integration, AJAX, form handling |
| Admin CSS | `admin/css/admin.css` | Responsive admin panel styling |
| Public Template | `public/views/linktree-page.php` | Complete HTML document with embedded CSS |

## Important Technical Details

### SortableJS Dependency

The plugin uses **SortableJS** (not jQuery UI Sortable) for drag-and-drop functionality. This is critical because ClassicPress deprecated jQuery UI Sortable.

```php
// In simple-linktree.php enqueue_admin_scripts():
wp_register_script('sortable', 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js', array(), '1.15.0', true);
wp_enqueue_script('sortable');
wp_enqueue_script('slt-admin-js', SIMPLE_LINKTREE_PLUGIN_URL . 'admin/js/admin.js', array('jquery', 'sortable'), SIMPLE_LINKTREE_VERSION, true);
```

**Never replace this with jQuery UI Sortable** - it will break on ClassicPress.

### Rewrite Rules & Slug Management

When the custom slug is updated:
1. Save new slug to `slt_page_slug` option
2. **Must call `flush_rewrite_rules()`** to regenerate permalink structure
3. Old slug becomes inaccessible immediately

This happens in:
- `activate()` - Plugin activation
- `admin_page()` - When settings form submitted (calls `flush_rewrite_rules()` after updating slug option)

### Options Storage Structure

```php
// All stored in wp_options table:
'slt_page_slug'      => 'links'           // string
'slt_profile_name'   => 'Site Name'       // string
'slt_profile_bio'    => 'Bio text...'     // string (can be empty)
'slt_links'          => '[{"id":"link-123","title":"...","url":"...","icon":"..."},...]'  // JSON array
```

**Link Object Schema**:
```json
{
  "id": "link-1234567890",      // Unique ID (timestamp-based)
  "title": "My Website",         // Required
  "url": "https://example.com",  // Required, validated URL
  "icon": "🔗"                   // Optional emoji/text
}
```

## Development Workflows

### Composer Dependencies

This plugin uses Composer for dependency management.

**Dependencies**:
- `yahnis-elsts/plugin-update-checker` (v5.6+) - Automatic plugin updates from GitHub

**IMPORTANT**: Unlike typical Composer projects, the `vendor/` directory **IS committed to the repository**. This is necessary because:
- Users install the plugin directly from GitHub (not via Composer)
- End users don't run `composer install`
- The Plugin Update Checker library must be present for the plugin to function

**For developers**: Only run `composer update` if you need to update dependencies. The `vendor/` directory should be committed after updates.

### No Build Process Required

This plugin has **no compilation, bundling, or build step** for CSS/JavaScript. All CSS and JavaScript files are served directly.

**To make changes**:
1. Edit the relevant file directly
2. Reload the browser (hard refresh if needed: Cmd+Shift+R / Ctrl+Shift+R)
3. If changes don't appear, increment version number in `simple-linktree.php` (lines 6 and 20)

### Version Bump Process

When CSS/JS changes aren't appearing due to browser caching, or when releasing a new version:

```php
// In simple-linktree.php
define('SIMPLE_LINKTREE_VERSION', '1.0.2');  // Line 20 - increment this
```

Also update the plugin header comment:
```php
* Version: 1.0.2  // Line 6 - must match constant
```

This forces browsers to download fresh assets since the version is appended as `?ver=X.X.X` query string.

**IMPORTANT**: After committing version changes to the `master` branch on GitHub, the plugin will **automatically notify ClassicPress installations** of the update via the Plugin Update Checker.

### Testing Changes

**Admin Panel Changes**:
1. Navigate to WordPress admin → Linktree menu
2. Test drag-and-drop, add/delete links, save functionality
3. Check browser console for JavaScript errors
4. Verify AJAX requests complete successfully (Network tab)

**Public Page Changes**:
1. Click "View Page" in admin bar or visit `/{your-slug}`
2. Test both light and dark mode (toggle OS preference or use browser dev tools)
3. Verify responsive behavior at different screen widths
4. Check that theme assets are NOT loading (view source)

### Debugging

**Enable WordPress debugging** (should already be on for this site):
```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

**Check logs**: `/wp-content/debug.log`

**Common issues**:
- **404 on custom slug**: Deactivate and reactivate plugin to flush rewrite rules
- **jQuery/JavaScript errors**: Check that SortableJS is loading before admin.js
- **AJAX failing**: Verify nonce is being passed correctly in `sltAdmin` object
- **Changes not appearing**: Hard refresh browser or bump version number

## Security Implementation

All AJAX handlers follow this pattern:
```php
public function ajax_save_links() {
    check_ajax_referer('slt_admin_nonce', 'nonce');  // Verify nonce token

    if (!current_user_can('manage_options')) {       // Check admin capability
        wp_send_json_error('Unauthorized');
        return;
    }

    // Sanitize all inputs
    $sanitized_links[] = array(
        'id' => sanitize_text_field($link['id']),
        'title' => sanitize_text_field($link['title']),
        'url' => esc_url_raw($link['url']),
        'icon' => sanitize_text_field($link['icon'])
    );

    // Process and return
}
```

**Never remove or bypass**:
- Nonce verification (`check_ajax_referer`, `check_admin_referer`)
- Capability checks (`current_user_can('manage_options')`)
- Input sanitization functions
- Output escaping in templates (`esc_html`, `esc_url`, `esc_attr`)

## File Editing Guidelines

### Admin JavaScript (`admin/js/admin.js`)

**Key patterns to maintain**:
- jQuery document ready wrapper
- SortableJS instantiation with `linksContainer` element
- Event delegation for dynamically added elements (`.delete-link-btn`)
- AJAX calls include nonce from `sltAdmin.nonce`
- New links get unique IDs: `'link-' + Date.now()`

### Public Template (`public/views/linktree-page.php`)

**Critical considerations**:
- This is a **complete HTML document** (includes `<!DOCTYPE html>`, `<head>`, `<body>`)
- CSS is embedded in `<style>` tags (no external stylesheet)
- Contains **minimal JavaScript** only for GDPR-compliant click tracking (uses `navigator.sendBeacon()`)
- Must manually escape all PHP output: `<?php echo esc_html($profile_name); ?>`
- Dark mode uses `@media (prefers-color-scheme: dark)` - do not use JavaScript toggles

### Admin Template (`admin/views/admin-page.php`)

**Layout structure**:
```html
<div class="slt-container">
  <div class="slt-panel slt-settings-panel">   <!-- 30% width, left panel -->
    <!-- General Settings form -->
  </div>

  <div class="slt-panel slt-links-panel">      <!-- 70% width, right panel -->
    <div id="links-container">                 <!-- Sortable container -->
      <!-- Link items rendered here -->
    </div>
  </div>
</div>
```

Responsive behavior at `@media (max-width: 768px)`: Panels stack vertically.

## Common Modifications

### Adding New Link Fields

To add a new field (e.g., "description") to links:

1. **Update admin template** (`admin/views/admin-page.php`):
   ```php
   <div class="slt-link-field">
       <label>Description</label>
       <input type="text" class="link-description" value="<?php echo esc_attr($link['description'] ?? ''); ?>" />
   </div>
   ```

2. **Update admin JavaScript** (`admin/js/admin.js`):
   ```javascript
   // In "Add new link" handler:
   '<div class="slt-link-field"><label>Description</label><input type="text" class="link-description" value="" /></div>' +

   // In "Save all links" handler:
   const link = {
       id: $item.data('id'),
       title: $item.find('.link-title').val().trim(),
       url: $item.find('.link-url').val().trim(),
       icon: $item.find('.link-icon').val().trim(),
       description: $item.find('.link-description').val().trim()  // Add this
   };
   ```

3. **Update AJAX handler** (`simple-linktree.php` in `ajax_save_links()`):
   ```php
   $sanitized_links[] = array(
       'id' => sanitize_text_field($link['id']),
       'title' => sanitize_text_field($link['title']),
       'url' => esc_url_raw($link['url']),
       'icon' => sanitize_text_field($link['icon']),
       'description' => sanitize_text_field($link['description'] ?? '')  // Add this
   );
   ```

4. **Update public template** (`public/views/linktree-page.php`):
   ```php
   <?php if (!empty($link['description'])): ?>
       <p class="link-description"><?php echo esc_html($link['description']); ?></p>
   <?php endif; ?>
   ```

### Changing URL Slug Programmatically

```php
update_option('slt_page_slug', 'new-slug');
flush_rewrite_rules();  // Critical! Must flush after slug change
```

### Customizing Colors

Edit CSS custom properties in `public/views/linktree-page.php`:
```css
:root {
    --bg-color: #ffffff;      /* Light mode background */
    --text-color: #0a0a0a;    /* Light mode text */
    --link-bg: #f5f5f5;       /* Light mode link buttons */
    --link-hover: #e8e8e8;    /* Light mode hover */
}

@media (prefers-color-scheme: dark) {
    :root {
        --bg-color: #0a0a0a;      /* Dark mode background */
        --text-color: #ffffff;    /* Dark mode text */
        --link-bg: #1a1a1a;       /* Dark mode link buttons */
        --link-hover: #2a2a2a;    /* Dark mode hover */
    }
}
```

## ClassicPress Compatibility (PRIMARY FOCUS)

This plugin is designed **primarily for ClassicPress**. All development decisions prioritize ClassicPress compatibility:

### Critical Requirements:
- **NEVER use Gutenberg blocks or block editor features** - ClassicPress does not include Gutenberg
- **NEVER use jQuery UI Sortable** - Deprecated in ClassicPress, use SortableJS only
- **No REST API dependencies** - ClassicPress REST API differs from WordPress
- **Classic editor patterns only** - Admin interface is custom-built using traditional WordPress hooks
- **Use classic enqueue methods** - Standard `wp_enqueue_script()` and `wp_enqueue_style()`

### WordPress Compatibility:
The plugin also works on WordPress 5.0+ as a side effect of following ClassicPress-compatible patterns, but **ClassicPress is the primary target platform**.

### Why SortableJS Over jQuery UI Sortable:
ClassicPress deprecated jQuery UI Sortable, making it unavailable. This is why we use SortableJS from CDN. **Never attempt to use jQuery UI Sortable or other deprecated jQuery UI components**.

## Performance Notes

**Public page performance**:
- Single HTTP request for HTML
- ~5KB embedded CSS (no external stylesheet)
- Minimal inline JavaScript (~30 lines for click tracking only)
- No theme assets loaded (complete bypass)
- No database queries on page load (data loaded once in `render_linktree_page()`)

**Admin panel considerations**:
- SortableJS loaded from CDN (~25KB minified)
- Admin CSS and JS only loaded on plugin's admin page (not sitewide)
- AJAX requests are lightweight (JSON payload typically <5KB)

## Automatic Updates from GitHub

The plugin uses **Yahnis Elsts' Plugin Update Checker** to provide automatic updates directly from the GitHub repository.

### How It Works

1. Plugin checks GitHub `master` branch for updates
2. Compares version in plugin header with latest commit on master
3. If newer version exists, shows update notification in ClassicPress admin
4. Users can update directly from the Plugins page (just like WordPress.org plugins)

### Update Configuration

Located at the top of `simple-linktree.php` (after the constants):

```php
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$updateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/JensS/simple-link-tree/',
    __FILE__,
    'simple-linktree'
);

$updateChecker->setBranch('master');  // Branch to check for updates
```

### Releasing Updates

To release a new version:

1. **Update version number** in `simple-linktree.php` (plugin header Version: and SIMPLE_LINKTREE_VERSION constant)
2. **Update version** in CLAUDE.md Project Overview section
3. **Commit changes** with descriptive message
4. **Push to master branch** on GitHub
5. Plugin installations will automatically detect the update within 12 hours

**No need to create GitHub releases or tags** - the update checker works directly from branch commits.

### Testing Updates Locally

The update checker caches results. To force an immediate check:
1. Go to Plugins page in ClassicPress admin
2. Delete transient: `wp transient delete puc_result_simple-linktree`
3. Refresh the plugins page

Or use this PHP snippet in a temporary admin page:
```php
delete_site_transient('update_plugins');
wp_update_plugins();  // Force check
```

## Statistics Tracking (GDPR-Compliant)

The plugin includes **cookie-free, GDPR-compliant statistics** tracking for page views and link clicks.

### How It Works

**Privacy-First Design:**
- No cookies used
- No personal data stored
- No IP addresses stored (only daily-changing hashes)
- Uses WordPress transients for duplicate prevention (expires after 24 hours)
- All data is aggregated daily

**Database Schema:**
```sql
CREATE TABLE wp_slt_stats (
    id bigint(20) AUTO_INCREMENT PRIMARY KEY,
    link_id varchar(50),           -- NULL for page views, link ID for clicks
    event_type varchar(20),        -- 'view' or 'click'
    event_date date,               -- Aggregated by date
    count int(11) DEFAULT 1,       -- Incrementing counter
    UNIQUE KEY unique_stat (link_id, event_type, event_date)
);
```

### Architecture

**Page View Tracking:**
- Triggered in `render_linktree_page()` method
- Calls `track_event('view')` before rendering template
- One view per unique visitor per day (using IP hash with daily salt)

**Click Tracking:**
- JavaScript at the bottom of `public/views/linktree-page.php`
- Uses `navigator.sendBeacon()` API for reliable tracking
- Sends AJAX request to `wp_ajax_slt_track_click` handler
- Tracked on click, doesn't delay navigation

**Duplicate Prevention:**
```php
// In track_event() method:
$ip_hash = hash('sha256', $_SERVER['REMOTE_ADDR'] . date('Y-m-d') . AUTH_KEY);
$cache_key = 'slt_tracked_' . $event_type . '_' . $link_id . '_' . $ip_hash;

if (get_transient($cache_key)) {
    return; // Already tracked today
}

set_transient($cache_key, '1', $seconds_until_midnight);
```

The hash uses:
- IP address (never stored)
- Current date (hash changes daily)
- WordPress AUTH_KEY (site-specific salt)

This means the same visitor cannot be tracked across days, ensuring privacy.

### Statistics Display

Admin panel shows (in `admin/views/admin-page.php`):

**Overview Metrics:**
- Total page views
- Total link clicks
- Click-through rate (CTR)

**Link Performance Table:**
- Sorted by clicks (descending)
- Shows title, URL, and total clicks per link

**Recent Activity:**
- Last 30 days of page views
- Daily breakdown

### Defensive Coding

The code gracefully handles missing stats table:

```php
// Both track_event() and get_statistics() check table existence
if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    return; // Skip tracking or return empty stats
}
```

This prevents errors if plugin is updated but not reactivated (table creation happens in `activate()` method).

### AJAX Endpoints

**Click Tracking** (public-facing, no auth required):
```php
// Action: slt_track_click
// Parameters: link_id
// Handler: ajax_track_click() in Simple_Linktree class
```

No nonce required since:
1. No sensitive data exposed
2. Rate-limited by transient system
3. Only increments anonymous counters

### CSS Styling

Statistics section styles in `admin/css/admin.css`:
- Grid layout for stat boxes
- Responsive design (stacks on mobile)
- WordPress admin theme integration

## SEO & GEO Optimization

The plugin includes comprehensive SEO and geographic targeting features, all configurable via the admin panel.

### SEO Features

**Meta Tags (in public template head):**
- Conditional robots meta tag (index/noindex based on admin setting)
- Meta description (auto-generated from bio if not set)
- Canonical URL

**Open Graph Tags:**
- `og:type`, `og:url`, `og:title`, `og:description`, `og:site_name`
- `og:image` and `og:image:alt` (when image URL configured)
- `og:locale` (derived from language setting)

**Twitter Card Tags:**
- `twitter:card` (summary_large_image when image set, otherwise summary)
- `twitter:title`, `twitter:description`, `twitter:image`

### GEO Features

**Language Targeting:**
- `lang` attribute on `<html>` element
- `hreflang` attribute for language/region targeting
- `og:locale` for social sharing

**Geographic Meta Tags:**
- `geo.region` - ISO 3166 region code (e.g., US-CA)
- `geo.placename` - City/location name

### Schema.org Structured Data

JSON-LD structured data is dynamically generated with:

```json
{
  "@context": "https://schema.org",
  "@type": "Person|Organization",
  "name": "Profile Name",
  "url": "canonical URL",
  "description": "Profile bio",
  "image": "OG image URL",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "City",
    "addressCountry": "Country"
  },
  "sameAs": ["array of all link URLs"]
}
```

**Key features:**
- Entity type configurable (Person or Organization)
- Location data in PostalAddress format
- All linktree links added as `sameAs` property (establishes social profile connections)

### Admin Settings

SEO/GEO options stored in `wp_options`:

| Option | Description |
|--------|-------------|
| `slt_seo_indexable` | Allow search engine indexing (1/0) |
| `slt_meta_description` | Custom meta description |
| `slt_og_image` | Social share image URL |
| `slt_language` | ISO 639-1 language code |
| `slt_geo_region` | ISO 3166 region code |
| `slt_geo_placename` | City/location name |
| `slt_schema_type` | Person or Organization |
| `slt_schema_location` | City for Schema.org |
| `slt_schema_country` | Country for Schema.org |

## Repository Information

- **GitHub**: https://github.com/JensS/simple-link-tree
- **Update Branch**: master
- **License**: GPL v2 or later
- **Author**: Jens Sage (jens@jenssage.com)
