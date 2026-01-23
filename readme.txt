=== Simple Linktree ===
Contributors: jenssage
Tags: linktree, link in bio, links page, social links, bio link
Requires at least: 4.9
Tested up to: 6.7
Requires PHP: 7.0
Stable tag: 1.2.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A minimalist Linktree-style page with automatic dark/light mode support. Create a clean link-in-bio page that works independently of your theme.

== Description ==

Simple Linktree creates a professional link-in-bio page that automatically adapts to your visitors' browser dark/light mode preferences. Perfect for sharing multiple links in one place.

**Key Features:**

* **Auto Dark/Light Mode** - Automatically switches based on browser's color scheme preference
* **Custom URL Slug** - Define your own page URL (e.g., /links, /bio, /social)
* **Drag & Drop Ordering** - Easily reorder links with an intuitive interface
* **Icon Support** - Add emojis or text icons to your links
* **Theme Independent** - Renders its own clean template, independent of your theme
* **Mobile Responsive** - Looks great on all devices
* **Privacy-First Statistics** - Cookie-free, GDPR-compliant page view and click tracking
* **SEO Controls** - Configurable meta tags, Open Graph, and Schema.org structured data
* **Lightweight** - Minimal overhead with no external dependencies on the public page

**ClassicPress Compatible**

This plugin is fully compatible with ClassicPress and uses modern, non-deprecated libraries.

== Installation ==

1. Upload the `simple-linktree` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **Linktree** in your admin menu to configure

**Quick Setup:**

After activation, the plugin will:

* Create a default page accessible at `yoursite.com/links`
* Set up the basic configuration
* Be ready for you to add your links!

== Frequently Asked Questions ==

= Can I customize the URL? =

Yes! Go to Linktree in your admin menu and change the "Page Slug" setting. Your linktree will be available at `yoursite.com/your-custom-slug`.

= Does this work with any theme? =

Yes! The plugin completely bypasses your theme and renders its own minimal template, ensuring consistent appearance regardless of your theme.

= Can I use custom icons? =

Yes! You can use:
* Emojis (e.g., link symbol, envelope, briefcase)
* Unicode symbols (arrows, stars, bullets)
* Simple text ("NEW", "BLOG", etc.)

= How do I change the order of links? =

Simply drag and drop the links in the admin panel using the handle icon on the left side of each link.

= Is it GDPR compliant? =

Yes! The built-in statistics feature uses no cookies and stores no personal data. It uses anonymous daily hashes for duplicate prevention that cannot be used to identify visitors.

= Can I disable statistics tracking? =

The statistics are minimal and privacy-respecting. If you prefer not to track any data, you can remove the tracking code from the public template file.

= Is it SEO-friendly? =

Yes! The plugin includes configurable SEO settings including:
* Robots meta tag (index/noindex)
* Meta description
* Open Graph tags for social sharing
* Twitter Card support
* Schema.org structured data
* Language and geographic targeting

== Screenshots ==

1. Public linktree page in light mode
2. Public linktree page in dark mode
3. Admin panel - link management
4. Admin panel - settings and statistics

== Changelog ==

= 1.2.3 =
* Added: Mobile vs Desktop device tracking in statistics
* Added: Device breakdown section showing views and clicks by device type
* Added: Percentage display for device distribution
* Added: Automatic database migration to add device_type column

= 1.2.2 =
* Fixed: Update checker now uses GitHub releases instead of branch commits
* Fixed: Added sanitize_callback to all register_setting() calls (WordPress.org requirement)
* Added: Custom sanitization functions for links JSON, checkbox, and schema type fields

= 1.2.1 =
* Prepared for WordPress.org and ClassicPress directory submission
* Documentation updates

= 1.2.0 =
* Added SEO & GEO optimization with admin-configurable settings
* Added Open Graph and Twitter Card meta tags
* Added Schema.org structured data (Person/Organization)
* Added language and geographic targeting options

= 1.1.0 =
* Added cookie-free, GDPR-compliant statistics tracking
* Page view and click tracking with daily aggregation
* Statistics dashboard in admin panel
* Privacy-first design with no personal data storage

= 1.0.1 =
* ClassicPress compatibility improvements
* Documentation updates
* Minor bug fixes

= 1.0.0 =
* Initial release
* Dark/light mode auto-detection
* Custom slug support
* Drag & drop link management
* Icon support
* Mobile responsive design

== Upgrade Notice ==

= 1.2.3 =
New feature: Track mobile vs desktop visitors. See device breakdown in your statistics dashboard.

= 1.2.2 =
Important fix for automatic updates and WordPress.org compliance. All settings now have proper sanitization.

= 1.2.1 =
Maintenance release preparing for plugin directory submission.

= 1.2.0 =
Adds comprehensive SEO features including Open Graph, Twitter Cards, and Schema.org structured data.

= 1.1.0 =
Adds privacy-respecting statistics to track page views and link clicks.
