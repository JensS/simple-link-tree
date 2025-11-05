<?php
/**
 * Plugin Name: Simple Linktree
 * Plugin URI: https://github.com/JensS/simple-linktree
 * Description: A minimalist Linktree-style page with dark/light mode support
 * Version: 1.0.0
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
define('SIMPLE_LINKTREE_VERSION', '1.0.0');
define('SIMPLE_LINKTREE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SIMPLE_LINKTREE_PLUGIN_URL', plugin_dir_url(__FILE__));

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
        
        // AJAX handlers
        add_action('wp_ajax_slt_save_links', array($this, 'ajax_save_links'));
        add_action('wp_ajax_slt_delete_link', array($this, 'ajax_delete_link'));
        
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
        register_setting('slt_settings', 'slt_page_slug');
        register_setting('slt_settings', 'slt_links');
        register_setting('slt_settings', 'slt_profile_name');
        register_setting('slt_settings', 'slt_profile_bio');
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_simple-linktree') {
            return;
        }
        
        wp_enqueue_style('slt-admin-css', SIMPLE_LINKTREE_PLUGIN_URL . 'admin/css/admin.css', array(), SIMPLE_LINKTREE_VERSION);
        wp_enqueue_script('slt-admin-js', SIMPLE_LINKTREE_PLUGIN_URL . 'admin/js/admin.js', array('jquery'), SIMPLE_LINKTREE_VERSION, true);
        
        wp_localize_script('slt-admin-js', 'sltAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('slt_admin_nonce')
        ));
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
            $this->slug = $new_slug;
            flush_rewrite_rules();
            echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
        }
        
        $slug = get_option('slt_page_slug', 'links');
        $profile_name = get_option('slt_profile_name', get_bloginfo('name'));
        $profile_bio = get_option('slt_profile_bio', '');
        $links = json_decode(get_option('slt_links', '[]'), true);
        $page_url = home_url('/' . $slug);
        
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
    
    public function template_redirect() {
        if (get_query_var('simple_linktree') == 1) {
            $this->render_linktree_page();
            exit;
        }
    }
    
    private function render_linktree_page() {
        $profile_name = get_option('slt_profile_name', get_bloginfo('name'));
        $profile_bio = get_option('slt_profile_bio', '');
        $links = json_decode(get_option('slt_links', '[]'), true);
        
        include SIMPLE_LINKTREE_PLUGIN_DIR . 'public/views/linktree-page.php';
    }
}

// Initialize plugin
Simple_Linktree::get_instance();
