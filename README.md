# Simple Linktree - ClassicPress Plugin

A minimalist Linktree-style plugin designed **primarily for ClassicPress** that automatically adapts to browser dark/light mode preferences. Perfect for creating a clean, professional link-in-bio page.

**Note**: This plugin is built for ClassicPress compatibility first. It also works with WordPress 5.0+, but ClassicPress is the primary target platform.

## Features

- **Auto Dark/Light Mode**: Automatically switches based on browser's `prefers-color-scheme`
- **Custom URL Slug**: Define your own page slug (e.g., `/links`, `/bio`, `/social`)
- **Drag & Drop Ordering**: Easily reorder links with drag-and-drop interface
- **Icon Support**: Add emojis or text icons to your links
- **Theme Independent**: Completely bypasses your theme for a clean, minimal design
- **Mobile Responsive**: Looks great on all devices
- **ClassicPress First**: Built specifically for ClassicPress compatibility (also works with WordPress 5.0+)
- **Automatic Updates**: Self-updating directly from GitHub (no WordPress.org dependency)

## Installation

### Manual Installation

1. Download the plugin files
2. Upload the entire `simple-linktree` folder to `/wp-content/plugins/`
3. Run `composer install` in the plugin directory (required for auto-updates)
4. Activate the plugin through the 'Plugins' menu in ClassicPress
5. Go to **Linktree** in your admin menu to configure

**Important**: The plugin requires Composer dependencies for automatic updates. After uploading, SSH into your server and run:

```bash
cd /path/to/wp-content/plugins/simple-linktree
composer install
```

### Quick Setup

After activation, the plugin will:
- Create a default page accessible at `yoursite.com/links`
- Set up the basic configuration
- Check for updates automatically from GitHub
- Be ready for you to add your links!

## Usage

### Configuring Your Linktree

1. Go to **Linktree** in your ClassicPress admin menu
2. Set your **Page Slug** (the URL where your linktree will be accessible)
3. Add your **Profile Name** (displayed at the top)
4. Optionally add a **Profile Bio**
5. Click **Save Settings**

### Managing Links

1. Click **Add New Link** to create a new link
2. Fill in:
   - **Title**: The text displayed on the button
   - **URL**: The destination URL
   - **Icon**: Optional emoji or symbol (e.g., 🔗, 📧, 🎵)
3. Drag links up or down to reorder them
4. Click **Save All Links** when done
5. Click **View Page** to see your linktree in action

### Keyboard Shortcuts

- **Ctrl+S** (or **Cmd+S** on Mac): Quick save links

## Technical Details

### Dark/Light Mode

The plugin uses CSS custom properties and `@media (prefers-color-scheme: dark)` to automatically detect and adapt to the user's browser preference. No JavaScript or manual toggle required!

### URL Structure

- Default: `yoursite.com/links`
- Custom: `yoursite.com/[your-custom-slug]`

The plugin uses ClassicPress/WordPress rewrite rules to create clean URLs that work independently of your theme.

### Performance

- Minimal overhead: No external dependencies
- Lightweight CSS: < 5KB
- Fast loading: Single-page template
- No theme assets loaded: Completely independent rendering

## File Structure

```
simple-linktree/
├── simple-linktree.php          # Main plugin file
├── README.md                     # This file
├── admin/
│   ├── css/
│   │   └── admin.css            # Admin panel styles
│   ├── js/
│   │   └── admin.js             # Admin panel JavaScript
│   └── views/
│       └── admin-page.php       # Admin interface template
└── public/
    └── views/
        └── linktree-page.php    # Public-facing linktree page
```

## Requirements

- **ClassicPress** (any version) or WordPress 5.0+
- PHP 7.0 or higher
- **Composer** (for automatic updates feature)
- **Note**: This plugin does NOT use Gutenberg or jQuery UI Sortable, ensuring full ClassicPress compatibility

## Browser Support

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Opera (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Frequently Asked Questions

### Can I customize the colors?

Currently, the plugin uses automatic dark/light mode based on browser preferences. Colors are defined in CSS custom properties in the `linktree-page.php` file and can be modified if needed.

### Does this work with any theme?

Yes! The plugin completely bypasses your theme and renders its own minimal template, ensuring consistent appearance regardless of your theme.

### Can I use custom icons?

Yes! You can use:
- Emojis (🔗, 📧, 💼, etc.)
- Unicode symbols (→, ★, ●, etc.)
- Simple text ("NEW", "BLOG", etc.)

### How do I change the order of links?

Simply drag and drop the links in the admin panel using the handle icon (≡) on the left side of each link.

### Is it SEO-friendly?

The linktree page includes `noindex, nofollow` meta tags since it's typically used as a utility page. This is intentional to keep your main content prioritized in search results.

### How do updates work?

The plugin automatically checks the GitHub repository for updates. When a new version is available, you'll see an update notification in your ClassicPress admin (just like WordPress.org plugins). Click "Update Now" to install the latest version.

Updates are delivered directly from GitHub without requiring WordPress.org hosting.

## Changelog

### Version 1.0.1
- Added automatic plugin updates from GitHub using Plugin Update Checker
- Emphasis on ClassicPress as primary target platform
- Documentation updates
- Composer dependency management

### Version 1.0.0
- Initial release
- Dark/light mode auto-detection
- Custom slug support
- Drag & drop link management (using SortableJS for ClassicPress compatibility)
- Icon support
- Mobile responsive design
- ClassicPress-first design (no Gutenberg, no deprecated jQuery UI)

## Support

For issues, questions, or contributions, please visit the plugin repository at https://github.com/JensS/simple-linktree.

## License

GPL v2 or later

## Credits

Developed with ❤️ for the ClassicPress community
