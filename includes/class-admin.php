<?php
/**
 * Admin Settings Class - Optimized
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Admin {
    
    private static $instance = null;
    private $settings = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_ajax_eddcdp_clear_cache', array($this, 'ajax_clear_cache'));
        add_action('admin_notices', array($this, 'show_admin_notices'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        $page_hook = add_submenu_page(
            'edit.php?post_type=download',
            __('Dashboard Pro', 'eddcdp'),
            __('Dashboard Pro', 'eddcdp'),
            'manage_shop_settings',
            'eddcdp-settings',
            array($this, 'admin_page')
        );
        
        add_action('load-' . $page_hook, array($this, 'admin_page_init'));
    }
    
    /**
     * Initialize admin page
     */
    public function admin_page_init() {
        // Handle form submission
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['eddcdp_nonce'], 'eddcdp_save_settings')) {
            $this->save_settings();
            wp_redirect(admin_url('edit.php?post_type=download&page=eddcdp-settings&updated=1'));
            exit;
        }
        
        // Handle cache clear
        if (isset($_GET['action']) && $_GET['action'] === 'clear_cache' && wp_verify_nonce($_GET['nonce'], 'eddcdp_clear_cache')) {
            $this->clear_all_cache();
            wp_redirect(admin_url('edit.php?post_type=download&page=eddcdp-settings&cache_cleared=1'));
            exit;
        }
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'eddcdp_settings_group',
            'eddcdp_settings',
            array($this, 'sanitize_settings')
        );
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // Sanitize basic settings
        $sanitized['replace_edd_pages'] = !empty($input['replace_edd_pages']);
        $sanitized['fullscreen_mode'] = !empty($input['fullscreen_mode']);
        $sanitized['active_template'] = sanitize_text_field($input['active_template']);
        
        // Sanitize enabled sections
        $sanitized['enabled_sections'] = array();
        if (!empty($input['enabled_sections']) && is_array($input['enabled_sections'])) {
            $valid_sections = array('purchases', 'downloads', 'licenses', 'wishlist', 'analytics', 'support');
            foreach ($input['enabled_sections'] as $section => $enabled) {
                if (in_array($section, $valid_sections)) {
                    $sanitized['enabled_sections'][$section] = !empty($enabled);
                }
            }
        }
        
        // Clear cache when settings change
        EDDCDP_Templates::instance()->clear_cache();
        
        return $sanitized;
    }
    
    /**
     * Admin scripts and styles
     */
    public function admin_scripts($hook) {
        if ($hook !== 'download_page_eddcdp-settings') {
            return;
        }
        
        wp_enqueue_style(
            'eddcdp-admin',
            EDDCDP_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            EDDCDP_VERSION
        );
        
        wp_enqueue_script(
            'eddcdp-admin',
            EDDCDP_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            EDDCDP_VERSION,
            true
        );
        
        wp_localize_script('eddcdp-admin', 'eddcdpAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('eddcdp_admin_nonce'),
            'strings' => array(
                'cache_clearing' => __('Clearing cache...', 'eddcdp'),
                'cache_cleared' => __('Cache cleared successfully!', 'eddcdp'),
                'error_occurred' => __('An error occurred. Please try again.', 'eddcdp')
            )
        ));
    }
    
    /**
     * Get settings with caching
     */
    private function get_settings() {
        if (is_null($this->settings)) {
            $defaults = $this->get_default_settings();
            $this->settings = wp_parse_args(get_option('eddcdp_settings', array()), $defaults);
        }
        return $this->settings;
    }
    
    /**
     * Get default settings
     */
    private function get_default_settings() {
        return array(
            'replace_edd_pages' => false,
            'fullscreen_mode' => false,
            'active_template' => 'default',
            'enabled_sections' => array(
                'purchases' => true,
                'downloads' => true,
                'licenses' => true,
                'wishlist' => true,
                'analytics' => true,
                'support' => true
            )
        );
    }
    
    /**
     * Get system status
     */
    private function get_system_status() {
        $status = array();
        
        // EDD Status
        $status['edd'] = array(
            'label' => __('EDD Active', 'eddcdp'),
            'status' => class_exists('Easy_Digital_Downloads') ? 'active' : 'inactive',
            'message' => class_exists('Easy_Digital_Downloads') ? __('Easy Digital Downloads is active', 'eddcdp') : __('Easy Digital Downloads is not active', 'eddcdp')
        );
        
        // Software Licensing
        $status['licensing'] = array(
            'label' => __('Software Licensing', 'eddcdp'),
            'status' => (class_exists('EDD_Software_Licensing') && function_exists('edd_software_licensing')) ? 'active' : 'optional',
            'message' => (class_exists('EDD_Software_Licensing') && function_exists('edd_software_licensing')) ? __('Software Licensing extension is active', 'eddcdp') : __('Software Licensing extension is not active (optional)', 'eddcdp')
        );
        
        // Wish Lists
        $status['wishlist'] = array(
            'label' => __('Wish Lists', 'eddcdp'),
            'status' => function_exists('edd_wl_get_wish_list') ? 'active' : 'optional',
            'message' => function_exists('edd_wl_get_wish_list') ? __('Wish Lists extension is active', 'eddcdp') : __('Wish Lists extension is not active (optional)', 'eddcdp')
        );
        
        // Template validation
        $settings = $this->get_settings();
        $template_valid = EDDCDP_Templates::instance()->validate_template($settings['active_template']);
        $status['template'] = array(
            'label' => __('Active Template', 'eddcdp'),
            'status' => $template_valid ? 'active' : 'warning',
            'message' => $template_valid ? __('Template is compatible', 'eddcdp') : __('Template may have compatibility issues', 'eddcdp')
        );
        
        return $status;
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        $settings = $this->get_settings();
        $templates = EDDCDP_Templates::instance()->get_available_templates();
        $system_status = $this->get_system_status();
        ?>
        
        <div class="wrap eddcdp-admin">
            <h1><?php _e('EDD Customer Dashboard Pro', 'eddcdp'); ?></h1>
            
            <div class="eddcdp-admin-container">
                <div class="eddcdp-admin-main">
                    <form method="post" action="">
                        <?php wp_nonce_field('eddcdp_save_settings', 'eddcdp_nonce'); ?>
                        
                        
                        <div class="eddcdp-section">
                            <h2><?php _e('General Settings', 'eddcdp'); ?></h2>
                            
                            <div class="eddcdp-setting-row">
                                <div class="eddcdp-setting-label">
                                    <label><?php _e('Replace EDD Pages', 'eddcdp'); ?></label>
                                </div>
                                <div class="eddcdp-setting-control">
                                    <label class="eddcdp-toggle">
                                        <input type="checkbox" name="eddcdp_settings[replace_edd_pages]" value="1" <?php checked($settings['replace_edd_pages']); ?>>
                                        <span class="eddcdp-toggle-slider"></span>
                                    </label>
                                    <p class="description"><?php _e('Replace default EDD customer pages with Dashboard Pro', 'eddcdp'); ?></p>
                                </div>
                            </div>
                            
                            <div class="eddcdp-setting-row">
                                <div class="eddcdp-setting-label">
                                    <label><?php _e('Fullscreen Mode', 'eddcdp'); ?></label>
                                </div>
                                <div class="eddcdp-setting-control">
                                    <label class="eddcdp-toggle">
                                        <input type="checkbox" name="eddcdp_settings[fullscreen_mode]" value="1" <?php checked($settings['fullscreen_mode']); ?>>
                                        <span class="eddcdp-toggle-slider"></span>
                                    </label>
                                    <p class="description"><?php _e('Enable automatic fullscreen dashboard mode', 'eddcdp'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Dashboard Sections -->
                        <div class="eddcdp-section">
                            <h2><?php _e('Dashboard Sections', 'eddcdp'); ?></h2>
                            <p class="description"><?php _e('Enable or disable specific dashboard sections', 'eddcdp'); ?></p>
                            
                            <?php
                            $sections = array(
                                'purchases' => __('Purchases', 'eddcdp'),
                                'downloads' => __('Downloads', 'eddcdp'),
                                'licenses' => __('Licenses', 'eddcdp'),
                                'wishlist' => __('Wishlist', 'eddcdp'),
                                'analytics' => __('Analytics', 'eddcdp'),
                                'support' => __('Support', 'eddcdp')
                            );
                            
                            foreach ($sections as $key => $label) :
                                $checked = !empty($settings['enabled_sections'][$key]);
                            ?>
                            <div class="eddcdp-setting-row">
                                <div class="eddcdp-setting-label">
                                    <label><?php echo esc_html($label); ?></label>
                                </div>
                                <div class="eddcdp-setting-control">
                                    <label class="eddcdp-toggle">
                                        <input type="checkbox" name="eddcdp_settings[enabled_sections][<?php echo esc_attr($key); ?>]" value="1" <?php checked($checked); ?>>
                                        <span class="eddcdp-toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Template Selection -->
                        <div class="eddcdp-section">
                            <h2><?php _e('Template Selection', 'eddcdp'); ?></h2>
                            <p class="description"><?php _e('Choose and configure your dashboard template.', 'eddcdp'); ?></p>
                            
                            <div class="eddcdp-templates-grid">
                                <?php foreach ($templates as $template_key => $template) : 
                                    $is_active = ($settings['active_template'] === $template_key);
                                    $is_valid = EDDCDP_Templates::instance()->validate_template($template_key);
                                ?>
                                <div class="eddcdp-template-card <?php echo $is_active ? 'active' : ''; ?> <?php echo !$is_valid ? 'invalid' : ''; ?>">
                                    <div class="eddcdp-template-preview">
                                        <div class="eddcdp-template-icon">
                                            <?php echo isset($template['source']) && $template['source'] === 'theme' ? '🎨' : '📱'; ?>
                                        </div>
                                    </div>
                                    <div class="eddcdp-template-info">
                                        <h3><?php echo esc_html($template['name']); ?></h3>
                                        <p><?php echo esc_html($template['description']); ?></p>
                                        <div class="eddcdp-template-meta">
                                            <span class="version"><?php printf(__('Version: %s', 'eddcdp'), esc_html($template['version'])); ?></span>
                                            <span class="author"><?php printf(__('by %s', 'eddcdp'), esc_html($template['author'])); ?></span>
                                        </div>
                                        
                                        <?php if (!$is_valid) : ?>
                                        <div class="eddcdp-template-warning">
                                            <small><?php _e('This template may not be compatible with your current setup.', 'eddcdp'); ?></small>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="eddcdp-template-actions">
                                        <?php if ($is_active) : ?>
                                            <span class="eddcdp-template-status active">✓ <?php _e('Active', 'eddcdp'); ?></span>
                                        <?php else : ?>
                                            <label class="eddcdp-template-radio">
                                                <input type="radio" name="eddcdp_settings[active_template]" value="<?php echo esc_attr($template_key); ?>" <?php echo !$is_valid ? 'disabled' : ''; ?>>
                                                <?php _e('Select', 'eddcdp'); ?>
                                            </label>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>                        
                        
                        
                        
                        <!-- General Settings -->
                        <div class="eddcdp-submit-section">
                            <?php submit_button(__('Save Settings', 'eddcdp')); ?>
                        </div>
                    </form>
                </div>
                
                <!-- Sidebar -->
                <div class="eddcdp-admin-sidebar">
                    <div class="eddcdp-sidebar-section">
                        <h3><?php _e('How to Use', 'eddcdp'); ?></h3>
                        <p><?php _e('Use this shortcode to display the dashboard:', 'eddcdp'); ?></p>
                        <code>[edd_customer_dashboard_pro]</code>
                        
                        <p style="margin-top: 15px;"><?php _e('Or create a page and add the shortcode in the content area.', 'eddcdp'); ?></p>
                    </div>
                    
                    <div class="eddcdp-sidebar-section">
                        <h3><?php _e('Plugin Info', 'eddcdp'); ?></h3>
                        <p><strong><?php _e('Version:', 'eddcdp'); ?></strong> <?php echo EDDCDP_VERSION; ?></p>
                        <p><strong><?php _e('Active Template:', 'eddcdp'); ?></strong> <?php echo esc_html(ucfirst($settings['active_template'])); ?></p>
                        <p><strong><?php _e('Available Templates:', 'eddcdp'); ?></strong> <?php echo count($templates); ?></p>
                    </div>
                    
                    <div class="eddcdp-sidebar-section">
                        <h3><?php _e('Quick Actions', 'eddcdp'); ?></h3>
                        <p>
                            <a href="<?php echo wp_nonce_url(admin_url('edit.php?post_type=download&page=eddcdp-settings&action=clear_cache'), 'eddcdp_clear_cache', 'nonce'); ?>" 
                               class="button button-secondary">
                                <?php _e('Clear Template Cache', 'eddcdp'); ?>
                            </a>
                        </p>
                        
                        <p>
                            <a href="<?php echo admin_url('post-new.php?post_type=page'); ?>" class="button button-secondary">
                                <?php _e('Create Dashboard Page', 'eddcdp'); ?>
                            </a>
                        </p>
                    </div>
                    
                    <div class="eddcdp-sidebar-section">
                        <h3><?php _e('System Status', 'eddcdp'); ?></h3>
                        <?php foreach ($system_status as $item) : ?>
                        <div class="eddcdp-status-item">
                            <span class="status-indicator <?php echo esc_attr($item['status']); ?>"></span>
                            <span title="<?php echo esc_attr($item['message']); ?>"><?php echo esc_html($item['label']); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="eddcdp-sidebar-section">
                        <h3><?php _e('Support', 'eddcdp'); ?></h3>
                        <p><?php _e('Need help? Check our documentation or contact support.', 'eddcdp'); ?></p>
                        <p>
                            <a href="#" class="button button-secondary" target="_blank">
                                <?php _e('Documentation', 'eddcdp'); ?>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <?php
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        if (!current_user_can('manage_shop_settings')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'eddcdp'));
        }
        
        // Settings are automatically sanitized via register_setting callback
        $this->settings = null; // Clear cache
        
        add_settings_error(
            'eddcdp_settings',
            'settings_updated',
            __('Settings saved successfully!', 'eddcdp'),
            'updated'
        );
    }
    
    /**
     * Clear all cache
     */
    private function clear_all_cache() {
        EDDCDP_Templates::instance()->clear_cache();
        
        // Clear any other plugin caches here
        do_action('eddcdp_clear_cache');
    }
    
    /**
     * AJAX clear cache
     */
    public function ajax_clear_cache() {
        check_ajax_referer('eddcdp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_shop_settings')) {
            wp_send_json_error(__('Permission denied.', 'eddcdp'));
        }
        
        $this->clear_all_cache();
        
        wp_send_json_success(__('Cache cleared successfully!', 'eddcdp'));
    }
    
    /**
     * Show admin notices
     */
    public function show_admin_notices() {
        $screen = get_current_screen();
        if ($screen->id !== 'download_page_eddcdp-settings') {
            return;
        }
        
        // Show update notice
        if (isset($_GET['updated']) && $_GET['updated'] == '1') {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . __('Settings saved successfully!', 'eddcdp') . '</p>';
            echo '</div>';
        }
        
        // Show cache cleared notice
        if (isset($_GET['cache_cleared']) && $_GET['cache_cleared'] == '1') {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . __('Template cache cleared successfully!', 'eddcdp') . '</p>';
            echo '</div>';
        }
        
        // Show compatibility warnings
        $this->show_compatibility_notices();
    }
    
    /**
     * Show compatibility notices
     */
    private function show_compatibility_notices() {
        // Check EDD version
        if (defined('EDD_VERSION') && version_compare(EDD_VERSION, '3.0.0', '<')) {
            echo '<div class="notice notice-warning">';
            echo '<p>' . sprintf(__('EDD Customer Dashboard Pro works best with Easy Digital Downloads 3.0 or higher. You are currently running version %s.', 'eddcdp'), EDD_VERSION) . '</p>';
            echo '</div>';
        }
        
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            echo '<div class="notice notice-error">';
            echo '<p>' . sprintf(__('EDD Customer Dashboard Pro requires PHP 7.4 or higher. You are currently running version %s.', 'eddcdp'), PHP_VERSION) . '</p>';
            echo '</div>';
        }
        
        // Check if current template is valid
        $settings = $this->get_settings();
        if (!EDDCDP_Templates::instance()->validate_template($settings['active_template'])) {
            echo '<div class="notice notice-warning">';
            echo '<p>' . sprintf(__('The currently active template "%s" may not be compatible with your setup.', 'eddcdp'), $settings['active_template']) . '</p>';
            echo '</div>';
        }
    }
}               