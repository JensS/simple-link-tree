<?php
/**
 * Plugin Name: Simple Linktree
 * Plugin URI: https://github.com/JensS/simple-link-tree
 * Description: A minimalist Linktree-style page with dark/light mode support
 * Version: 1.2.3
 * Author: Jens Sage
 * Author URI: https://www.jenssage.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: simple-linktree
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SIMPLE_LINKTREE_VERSION', '1.2.3');
define('SIMPLE_LINKTREE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SIMPLE_LINKTREE_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load Composer autoloader and GitHub update checker (only for GitHub installs)
// When installed from WordPress.org or ClassicPress directory, vendor/ won't exist
// and updates will come from the respective directory instead
if (file_exists(SIMPLE_LINKTREE_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once SIMPLE_LINKTREE_PLUGIN_DIR . 'vendor/autoload.php';

    if (class_exists('YahnisElsts\PluginUpdateChecker\v5\PucFactory')) {
        $updateChecker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
            'https://github.com/JensS/simple-link-tree/',
            __FILE__,
            'simple-linktree'
        );
        // Use GitHub releases instead of branch commits for version detection
        $updateChecker->getVcsApi()->enableReleaseAssets();
    }
}

class Simple_Linktree {
    
    private static $instance = null;
    private $slug = 'links';
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Initialize plugin
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('template_redirect', array($this, 'template_redirect'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_bar_menu', array($this, 'add_admin_bar_menu'), 999);

        // AJAX handlers
        add_action('wp_ajax_slt_save_links', array($this, 'ajax_save_links'));
        add_action('wp_ajax_slt_delete_link', array($this, 'ajax_delete_link'));

        // Statistics - non-authenticated AJAX for click tracking
        add_action('wp_ajax_nopriv_slt_track_click', array($this, 'ajax_track_click'));
        add_action('wp_ajax_slt_track_click', array($this, 'ajax_track_click'));

        // Activation/Deactivation
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Get custom slug from settings
        $this->slug = get_option('slt_page_slug', 'links');
        
        // Add rewrite rule
        add_rewrite_rule(
            '^' . $this->slug . '/?$',
            'index.php?simple_linktree=1',
            'top'
        );
        
        // Add query var
        add_filter('query_vars', function($vars) {
            $vars[] = 'simple_linktree';
            return $vars;
        });
    }
    
    public function activate() {
        global $wpdb;

        // Set default options
        if (!get_option('slt_page_slug')) {
            add_option('slt_page_slug', 'links');
        }
        if (!get_option('slt_links')) {
            add_option('slt_links', json_encode(array()));
        }
        if (!get_option('slt_profile_name')) {
            add_option('slt_profile_name', get_bloginfo('name'));
        }
        if (!get_option('slt_profile_bio')) {
            add_option('slt_profile_bio', '');
        }

        // SEO/GEO default options
        if (!get_option('slt_seo_indexable')) {
            add_option('slt_seo_indexable', '1'); // Default to indexable
        }
        if (!get_option('slt_meta_description')) {
            add_option('slt_meta_description', '');
        }
        if (!get_option('slt_og_image')) {
            add_option('slt_og_image', '');
        }
        if (!get_option('slt_language')) {
            add_option('slt_language', get_bloginfo('language') ?: 'en');
        }
        if (!get_option('slt_geo_region')) {
            add_option('slt_geo_region', '');
        }
        if (!get_option('slt_geo_placename')) {
            add_option('slt_geo_placename', '');
        }
        if (!get_option('slt_schema_type')) {
            add_option('slt_schema_type', 'Person'); // Person or Organization
        }
        if (!get_option('slt_schema_location')) {
            add_option('slt_schema_location', '');
        }
        if (!get_option('slt_schema_country')) {
            add_option('slt_schema_country', '');
        }

        // Create statistics table
        $table_name = $wpdb->prefix . 'slt_stats';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            link_id varchar(50) DEFAULT NULL,
            event_type varchar(20) NOT NULL,
            event_date date NOT NULL,
            device_type varchar(10) NOT NULL DEFAULT 'desktop',
            count int(11) NOT NULL DEFAULT 1,
            PRIMARY KEY (id),
            UNIQUE KEY unique_stat (link_id, event_type, event_date, device_type)
        ) $charset_collate;";

        // Check if we need to add device_type column to existing table
        $row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table_name' AND column_name = 'device_type'");
        if (empty($row)) {
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN device_type varchar(10) NOT NULL DEFAULT 'desktop' AFTER event_date");
            $wpdb->query("ALTER TABLE $table_name DROP INDEX unique_stat");
            $wpdb->query("ALTER TABLE $table_name ADD UNIQUE KEY unique_stat (link_id, event_type, event_date, device_type)");
        }

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Simple Linktree',
            'Linktree',
            'manage_options',
            'simple-linktree',
            array($this, 'admin_page'),
            'dashicons-admin-links',
            30
        );
    }
    
    public function register_settings() {
        register_setting('slt_settings', 'slt_page_slug', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_title',
            'default' => 'links',
        ));
        register_setting('slt_settings', 'slt_links', array(
            'type' => 'string',
            'sanitize_callback' => array($this, 'sanitize_links_json'),
            'default' => '[]',
        ));
        register_setting('slt_settings', 'slt_profile_name', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ));
        register_setting('slt_settings', 'slt_profile_bio', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
            'default' => '',
        ));

        // SEO/GEO settings
        register_setting('slt_settings', 'slt_seo_indexable', array(
            'type' => 'string',
            'sanitize_callback' => array($this, 'sanitize_checkbox'),
            'default' => '0',
        ));
        register_setting('slt_settings', 'slt_meta_description', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
            'default' => '',
        ));
        register_setting('slt_settings', 'slt_og_image', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => '',
        ));
        register_setting('slt_settings', 'slt_language', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'en',
        ));
        register_setting('slt_settings', 'slt_geo_region', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ));
        register_setting('slt_settings', 'slt_geo_placename', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ));
        register_setting('slt_settings', 'slt_schema_type', array(
            'type' => 'string',
            'sanitize_callback' => array($this, 'sanitize_schema_type'),
            'default' => 'Person',
        ));
        register_setting('slt_settings', 'slt_schema_location', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ));
        register_setting('slt_settings', 'slt_schema_country', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ));
    }

    /**
     * Sanitize checkbox value
     */
    public function sanitize_checkbox($value) {
        return ($value === '1' || $value === 1 || $value === true) ? '1' : '0';
    }

    /**
     * Sanitize schema type (must be Person or Organization)
     */
    public function sanitize_schema_type($value) {
        $allowed = array('Person', 'Organization');
        return in_array($value, $allowed, true) ? $value : 'Person';
    }

    /**
     * Sanitize links JSON
     */
    public function sanitize_links_json($value) {
        if (empty($value)) {
            return '[]';
        }

        $links = json_decode($value, true);
        if (!is_array($links)) {
            return '[]';
        }

        $sanitized = array();
        foreach ($links as $link) {
            if (!is_array($link)) {
                continue;
            }
            $sanitized[] = array(
                'id' => sanitize_text_field($link['id'] ?? ''),
                'title' => sanitize_text_field($link['title'] ?? ''),
                'url' => esc_url_raw($link['url'] ?? ''),
                'icon' => sanitize_text_field($link['icon'] ?? ''),
            );
        }

        return wp_json_encode($sanitized);
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_simple-linktree') {
            return;
        }

        wp_enqueue_style('slt-admin-css', SIMPLE_LINKTREE_PLUGIN_URL . 'admin/css/admin.css', array(), SIMPLE_LINKTREE_VERSION);

        // Register and enqueue SortableJS from CDN.
        // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- External CDN with version in URL.
        wp_register_script('sortable', 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js', array(), '1.15.0', true);
        wp_enqueue_script('sortable');

        wp_enqueue_script('slt-admin-js', SIMPLE_LINKTREE_PLUGIN_URL . 'admin/js/admin.js', array('jquery', 'sortable'), SIMPLE_LINKTREE_VERSION, true);

        wp_localize_script('slt-admin-js', 'sltAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('slt_admin_nonce')
        ));
    }

    public function add_admin_bar_menu($wp_admin_bar) {
        $slug = get_option('slt_page_slug', 'links');
        $page_url = home_url('/' . $slug);

        $args = array(
            'id'    => 'view-linktree-page',
            'title' => 'View Linktree Page',
            'href'  => $page_url,
            'meta'  => array(
                'target' => '_blank',
                'class' => 'view-linktree-page-button'
            )
        );
        $wp_admin_bar->add_node($args);
    }
    
    public function admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Handle slug update
        if (isset($_POST['slt_update_slug']) && check_admin_referer('slt_settings_nonce')) {
            $new_slug = sanitize_title($_POST['slt_page_slug']);
            update_option('slt_page_slug', $new_slug);
            update_option('slt_profile_name', sanitize_text_field($_POST['slt_profile_name']));
            update_option('slt_profile_bio', sanitize_textarea_field($_POST['slt_profile_bio']));

            // Save SEO/GEO settings
            update_option('slt_seo_indexable', isset($_POST['slt_seo_indexable']) ? '1' : '0');
            update_option('slt_meta_description', sanitize_textarea_field($_POST['slt_meta_description'] ?? ''));
            update_option('slt_og_image', esc_url_raw($_POST['slt_og_image'] ?? ''));
            update_option('slt_language', sanitize_text_field($_POST['slt_language'] ?? 'en'));
            update_option('slt_geo_region', sanitize_text_field($_POST['slt_geo_region'] ?? ''));
            update_option('slt_geo_placename', sanitize_text_field($_POST['slt_geo_placename'] ?? ''));
            update_option('slt_schema_type', sanitize_text_field($_POST['slt_schema_type'] ?? 'Person'));
            update_option('slt_schema_location', sanitize_text_field($_POST['slt_schema_location'] ?? ''));
            update_option('slt_schema_country', sanitize_text_field($_POST['slt_schema_country'] ?? ''));

            $this->slug = $new_slug;
            flush_rewrite_rules();
            echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
        }

        $slug = get_option('slt_page_slug', 'links');
        $profile_name = get_option('slt_profile_name', get_bloginfo('name'));
        $profile_bio = get_option('slt_profile_bio', '');
        $links = json_decode(get_option('slt_links', '[]'), true);
        $page_url = home_url('/' . $slug);
        $statistics = $this->get_statistics();

        // SEO/GEO settings for admin template
        $seo_settings = array(
            'indexable' => get_option('slt_seo_indexable', '1'),
            'meta_description' => get_option('slt_meta_description', ''),
            'og_image' => get_option('slt_og_image', ''),
            'language' => get_option('slt_language', get_bloginfo('language') ?: 'en'),
            'geo_region' => get_option('slt_geo_region', ''),
            'geo_placename' => get_option('slt_geo_placename', ''),
            'schema_type' => get_option('slt_schema_type', 'Person'),
            'schema_location' => get_option('slt_schema_location', ''),
            'schema_country' => get_option('slt_schema_country', ''),
        );

        include SIMPLE_LINKTREE_PLUGIN_DIR . 'admin/views/admin-page.php';
    }
    
    public function ajax_save_links() {
        check_ajax_referer('slt_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }
        
        $links = isset($_POST['links']) ? $_POST['links'] : array();
        $sanitized_links = array();
        
        foreach ($links as $link) {
            $sanitized_links[] = array(
                'id' => sanitize_text_field($link['id']),
                'title' => sanitize_text_field($link['title']),
                'url' => esc_url_raw($link['url']),
                'icon' => sanitize_text_field($link['icon'])
            );
        }
        
        update_option('slt_links', json_encode($sanitized_links));
        wp_send_json_success('Links saved successfully');
    }
    
    public function ajax_delete_link() {
        check_ajax_referer('slt_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $link_id = sanitize_text_field($_POST['link_id']);
        $links = json_decode(get_option('slt_links', '[]'), true);

        $links = array_filter($links, function($link) use ($link_id) {
            return $link['id'] !== $link_id;
        });

        update_option('slt_links', json_encode(array_values($links)));
        wp_send_json_success('Link deleted successfully');
    }

    /**
     * Track click event via AJAX
     * GDPR-compliant: No cookies, no IP storage
     */
    public function ajax_track_click() {
        if (!isset($_POST['link_id'])) {
            wp_send_json_error('Missing link_id');
            return;
        }

        $link_id = sanitize_text_field($_POST['link_id']);

        // Validate that the link_id exists in our links to prevent arbitrary data insertion
        $links = json_decode(get_option('slt_links', '[]'), true);
        $link_exists = false;

        foreach ($links as $link) {
            if ($link['id'] === $link_id) {
                $link_exists = true;
                break;
            }
        }

        if (!$link_exists) {
            wp_send_json_error('Invalid link_id');
            return;
        }

        $this->track_event('click', $link_id);

        wp_send_json_success();
    }

    /**
     * Track an event (page view or link click)
     * Uses IP hash with daily salt to prevent duplicate counting within same day
     * Hash is regenerated daily so no long-term tracking is possible
     *
     * @param string $event_type 'view' or 'click'
     * @param string|null $link_id Link ID for click events, null for page views
     */
    private function track_event($event_type, $link_id = null) {
        global $wpdb;

        // Check if table exists first
        $table_name = $wpdb->prefix . 'slt_stats';
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) != $table_name) {
            return; // Table doesn't exist yet, skip tracking
        }

        // Detect device type
        $device_type = $this->detect_device_type();

        // Check if this is a duplicate view/click today (basic bot/refresh protection)
        // Use IP hash with daily salt - no PII stored, hash changes daily
        $ip_hash = $this->get_daily_ip_hash();
        $cache_key = 'slt_tracked_' . $event_type . '_' . ($link_id ?: 'page') . '_' . $device_type . '_' . $ip_hash;

        if (get_transient($cache_key)) {
            return; // Already tracked this view/click today
        }

        // Set transient to expire at end of day
        $seconds_until_midnight = strtotime('tomorrow') - time();
        set_transient($cache_key, '1', $seconds_until_midnight);

        // Record or increment the stat
        $event_date = current_time('Y-m-d');

        $wpdb->query($wpdb->prepare(
            "INSERT INTO $table_name (link_id, event_type, event_date, device_type, count)
            VALUES (%s, %s, %s, %s, 1)
            ON DUPLICATE KEY UPDATE count = count + 1",
            $link_id,
            $event_type,
            $event_date,
            $device_type
        ));
    }

    /**
     * Generate daily IP hash for duplicate detection
     * No IP addresses are stored - hash changes daily
     *
     * @return string
     */
    private function get_daily_ip_hash() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $daily_salt = gmdate('Y-m-d') . AUTH_KEY; // Daily changing salt
        return hash('sha256', $ip . $daily_salt);
    }

    /**
     * Detect device type from User-Agent
     * Returns 'mobile' or 'desktop'
     *
     * @return string
     */
    private function detect_device_type() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Common mobile device indicators
        $mobile_patterns = array(
            '/Mobile/i',
            '/Android.*Mobile/i',
            '/iPhone/i',
            '/iPod/i',
            '/BlackBerry/i',
            '/Windows Phone/i',
            '/webOS/i',
            '/Opera Mini/i',
            '/Opera Mobi/i',
            '/IEMobile/i',
            '/Mobile Safari/i',
        );

        foreach ($mobile_patterns as $pattern) {
            if (preg_match($pattern, $user_agent)) {
                return 'mobile';
            }
        }

        // Check for tablets - these are often considered mobile for analytics
        // but we'll count iPad/Android tablets as mobile too
        if (preg_match('/iPad|Android(?!.*Mobile)/i', $user_agent)) {
            return 'mobile';
        }

        return 'desktop';
    }

    /**
     * Get statistics data for admin display
     *
     * @return array
     */
    private function get_statistics() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slt_stats';

        // Check if table exists first
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) != $table_name) {
            return array(
                'total_views' => 0,
                'total_clicks' => 0,
                'link_stats' => array(),
                'daily_views' => array(),
                'device_stats' => array(
                    'mobile_views' => 0,
                    'desktop_views' => 0,
                    'mobile_clicks' => 0,
                    'desktop_clicks' => 0,
                )
            );
        }

        // Get total page views
        $total_views = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(count) FROM $table_name WHERE event_type = %s",
            'view'
        ));

        // Get total clicks
        $total_clicks = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(count) FROM $table_name WHERE event_type = %s",
            'click'
        ));

        // Get device-specific stats
        $mobile_views = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(count) FROM $table_name WHERE event_type = %s AND device_type = %s",
            'view',
            'mobile'
        ));

        $desktop_views = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(count) FROM $table_name WHERE event_type = %s AND device_type = %s",
            'view',
            'desktop'
        ));

        $mobile_clicks = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(count) FROM $table_name WHERE event_type = %s AND device_type = %s",
            'click',
            'mobile'
        ));

        $desktop_clicks = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(count) FROM $table_name WHERE event_type = %s AND device_type = %s",
            'click',
            'desktop'
        ));

        // Get clicks per link
        $links = json_decode(get_option('slt_links', '[]'), true);
        $link_stats = array();

        foreach ($links as $link) {
            $clicks = $wpdb->get_var($wpdb->prepare(
                "SELECT SUM(count) FROM $table_name WHERE event_type = %s AND link_id = %s",
                'click',
                $link['id']
            ));

            $link_stats[] = array(
                'id' => $link['id'],
                'title' => $link['title'],
                'url' => $link['url'],
                'clicks' => intval($clicks)
            );
        }

        // Sort by clicks descending
        usort($link_stats, function($a, $b) {
            return $b['clicks'] - $a['clicks'];
        });

        // Get recent activity (last 30 days)
        $daily_views = $wpdb->get_results($wpdb->prepare(
            "SELECT event_date, SUM(count) as views
            FROM $table_name
            WHERE event_type = %s AND event_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY event_date
            ORDER BY event_date DESC
            LIMIT 30",
            'view'
        ), ARRAY_A);

        return array(
            'total_views' => intval($total_views),
            'total_clicks' => intval($total_clicks),
            'link_stats' => $link_stats,
            'daily_views' => $daily_views,
            'device_stats' => array(
                'mobile_views' => intval($mobile_views),
                'desktop_views' => intval($desktop_views),
                'mobile_clicks' => intval($mobile_clicks),
                'desktop_clicks' => intval($desktop_clicks),
            )
        );
    }
    
    public function template_redirect() {
        if (get_query_var('simple_linktree') == 1) {
            $this->render_linktree_page();
            exit;
        }
    }
    
    private function render_linktree_page() {
        // Track page view (GDPR-compliant, no cookies)
        $this->track_event('view');

        $profile_name = get_option('slt_profile_name', get_bloginfo('name'));
        $profile_bio = get_option('slt_profile_bio', '');
        $links = json_decode(get_option('slt_links', '[]'), true);

        // SEO/GEO data for template
        $slug = get_option('slt_page_slug', 'links');
        $seo = array(
            'indexable' => get_option('slt_seo_indexable', '1') === '1',
            'meta_description' => get_option('slt_meta_description', ''),
            'og_image' => get_option('slt_og_image', ''),
            'language' => get_option('slt_language', get_bloginfo('language') ?: 'en'),
            'geo_region' => get_option('slt_geo_region', ''),
            'geo_placename' => get_option('slt_geo_placename', ''),
            'schema_type' => get_option('slt_schema_type', 'Person'),
            'schema_location' => get_option('slt_schema_location', ''),
            'schema_country' => get_option('slt_schema_country', ''),
            'canonical_url' => home_url('/' . $slug),
            'site_name' => get_bloginfo('name'),
        );

        include SIMPLE_LINKTREE_PLUGIN_DIR . 'public/views/linktree-page.php';
    }
}

// Initialize plugin
Simple_Linktree::get_instance();
