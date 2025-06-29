<?php
/**
 * Admin Settings Class - Enhanced
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Admin {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_init', array($this, 'handle_template_activation'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('admin_notices', array($this, 'show_admin_notices'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=download',
            __('Dashboard Pro', 'edd-customer-dashboard-pro'),
            __('Dashboard Pro', 'edd-customer-dashboard-pro'),
            'manage_shop_settings',
            'eddcdp-settings',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Handle template activation
     */
    public function handle_template_activation() {
        // Only handle on our admin page
        if (!isset($_GET['page']) || $_GET['page'] !== 'eddcdp-settings') {
            return;
        }
        
        // Handle template activation
        if (isset($_GET['action']) && $_GET['action'] === 'activate_template' && isset($_GET['template'])) {
            if (wp_verify_nonce($_GET['_wpnonce'], 'eddcdp_activate_template')) {
                $template = sanitize_text_field($_GET['template']);
                $settings = $this->get_settings();
                $settings['active_template'] = $template;
                update_option('eddcdp_settings', $settings);
                
                wp_redirect(admin_url('edit.php?post_type=download&page=eddcdp-settings&template_activated=1'));
                exit;
            } else {
                wp_die(__('Security check failed. Please try again.', 'edd-customer-dashboard-pro'));
            }
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
        
        // Add settings sections
        add_settings_section(
            'eddcdp_general_section',
            __('General Settings', 'edd-customer-dashboard-pro'),
            null,
            'eddcdp-settings'
        );
        
        add_settings_section(
            'eddcdp_sections_section',
            __('Dashboard Sections', 'edd-customer-dashboard-pro'),
            null,
            'eddcdp-settings'
        );
        
        add_settings_section(
            'eddcdp_template_section',
            __('Template Selection', 'edd-customer-dashboard-pro'),
            null,
            'eddcdp-settings'
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
        $sanitized['active_template'] = sanitize_text_field($input['active_template'] ?? 'default');
        
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
        
        // Set default enabled sections if none provided
        if (empty($sanitized['enabled_sections'])) {
            $sanitized['enabled_sections'] = array(
                'purchases' => true,
                'downloads' => true,
                'licenses' => true,
                'wishlist' => true,
                'analytics' => true,
                'support' => true
            );
        }
        
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
    }
    
    /**
     * Get settings with defaults
     */
    private function get_settings() {
        $defaults = array(
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
        
        return wp_parse_args(get_option('eddcdp_settings', array()), $defaults);
    }
    
    /**
     * Get available templates
     */
    private function get_available_templates() {
        $templates = array();
        
        // Scan plugin templates directory
        $plugin_templates_dir = EDDCDP_PLUGIN_DIR . 'templates/';
        if (is_dir($plugin_templates_dir)) {
            $dirs = scandir($plugin_templates_dir);
            foreach ($dirs as $dir) {
                if ($dir === '.' || $dir === '..' || !is_dir($plugin_templates_dir . $dir)) {
                    continue;
                }
                
                $template_config = $this->get_template_config($plugin_templates_dir . $dir);
                if ($template_config) {
                    $templates[$dir] = $template_config;
                }
            }
        }
        
        // Scan theme templates directory
        $theme_templates_dir = get_stylesheet_directory() . '/eddcdp/templates/';
        if (is_dir($theme_templates_dir)) {
            $dirs = scandir($theme_templates_dir);
            foreach ($dirs as $dir) {
                if ($dir === '.' || $dir === '..' || !is_dir($theme_templates_dir . $dir)) {
                    continue;
                }
                
                $template_config = $this->get_template_config($theme_templates_dir . $dir);
                if ($template_config) {
                    $template_config['source'] = 'theme';
                    $template_config['name'] .= ' (Theme)';
                    $templates[$dir] = $template_config;
                }
            }
        }
        
        // Fallback if no templates found
        if (empty($templates)) {
            $templates['default'] = array(
                'name' => 'Default Dashboard',
                'description' => 'Modern, clean dashboard interface',
                'version' => '1.0.0',
                'author' => 'EDD Customer Dashboard Pro'
            );
        }
        
        return $templates;
    }
    
    /**
     * Get template configuration
     */
    private function get_template_config($template_dir) {
        $config_file = $template_dir . '/template.json';
        
        if (file_exists($config_file)) {
            $config = json_decode(file_get_contents($config_file), true);
            if ($config && is_array($config)) {
                return wp_parse_args($config, $this->get_default_template_config());
            }
        }
        
        // Default config if no JSON file
        return $this->get_default_template_config(basename($template_dir));
    }
    
    /**
     * Get default template configuration
     */
    private function get_default_template_config($template_name = '') {
        return array(
            'name' => !empty($template_name) ? ucfirst($template_name) . ' Template' : 'Unknown Template',
            'description' => 'Custom dashboard template',
            'version' => '1.0.0',
            'author' => 'EDD Customer Dashboard Pro',
            'supports' => array('purchases', 'downloads', 'licenses', 'wishlist', 'analytics', 'support')
        );
    }
    
    /**
     * Check if EDD Pro is active
     */
    private function is_edd_pro_active() {
        // Only check for actual EDD Pro indicators, not extensions
        if (defined('EDD_PRO_VERSION') || 
            class_exists('EDD_Pro') || 
            function_exists('edd_pro_version')) {
            return true;
        }
        return false;
    }
    
    /**
     * Get system status
     */
    private function get_system_status() {
        $status = array();
        
        // EDD Pro Status
        $status['edd_pro'] = array(
            'label' => __('EDD Pro', 'edd-customer-dashboard-pro'),
            'status' => $this->is_edd_pro_active() ? 'active' : 'inactive',
            'message' => $this->is_edd_pro_active() ? __('Easy Digital Downloads Pro is active', 'edd-customer-dashboard-pro') : __('Easy Digital Downloads Pro is not active', 'edd-customer-dashboard-pro')
        );
        
        // Software Licensing
        $status['licensing'] = array(
            'label' => __('Software Licensing', 'edd-customer-dashboard-pro'),
            'status' => (class_exists('EDD_Software_Licensing') && function_exists('edd_software_licensing')) ? 'active' : 'optional',
            'message' => (class_exists('EDD_Software_Licensing') && function_exists('edd_software_licensing')) ? __('Software Licensing extension is active', 'edd-customer-dashboard-pro') : __('Software Licensing extension is not active (optional)', 'edd-customer-dashboard-pro')
        );
        
        // Wish Lists
        $status['wishlist'] = array(
            'label' => __('Wish Lists', 'edd-customer-dashboard-pro'),
            'status' => function_exists('edd_wl_get_wish_list') ? 'active' : 'optional',
            'message' => function_exists('edd_wl_get_wish_list') ? __('Wish Lists extension is active', 'edd-customer-dashboard-pro') : __('Wish Lists extension is not active (optional)', 'edd-customer-dashboard-pro')
        );
        
        // Invoices
        $status['invoices'] = array(
            'label' => __('Invoices', 'edd-customer-dashboard-pro'),
            'status' => function_exists('edd_invoices_get_invoice_url') ? 'active' : 'optional',
            'message' => function_exists('edd_invoices_get_invoice_url') ? __('Invoices extension is active', 'edd-customer-dashboard-pro') : __('Invoices extension is not active (optional)', 'edd-customer-dashboard-pro')
        );
        
        return $status;
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        $settings = $this->get_settings();
        $templates = $this->get_available_templates();
        ?>
        
        <div class="wrap eddcdp-admin">
            <h1><?php _e('EDD Customer Dashboard Pro', 'edd-customer-dashboard-pro'); ?></h1>
            
            <div class="eddcdp-admin-container">
                <div class="eddcdp-admin-main">
                    <form method="post" action="options.php">
                        <?php settings_fields('eddcdp_settings_group'); ?>
                        
                        <div class="eddcdp-section">
                            <h2><?php _e('General Settings', 'edd-customer-dashboard-pro'); ?></h2>
                            
                            <div class="eddcdp-setting-row">
                                <div class="eddcdp-setting-label">
                                    <label><?php _e('Replace EDD Pages', 'edd-customer-dashboard-pro'); ?></label>
                                </div>
                                <div class="eddcdp-setting-control">
                                    <label class="eddcdp-toggle">
                                        <input type="checkbox" name="eddcdp_settings[replace_edd_pages]" value="1" <?php checked($settings['replace_edd_pages']); ?>>
                                        <span class="eddcdp-toggle-slider"></span>
                                    </label>
                                    <p class="description"><?php _e('Replace default EDD customer pages with Dashboard Pro', 'edd-customer-dashboard-pro'); ?></p>
                                </div>
                            </div>
                            
                            <div class="eddcdp-setting-row">
                                <div class="eddcdp-setting-label">
                                    <label><?php _e('Fullscreen Mode', 'edd-customer-dashboard-pro'); ?></label>
                                </div>
                                <div class="eddcdp-setting-control">
                                    <label class="eddcdp-toggle">
                                        <input type="checkbox" name="eddcdp_settings[fullscreen_mode]" value="1" <?php checked($settings['fullscreen_mode']); ?>>
                                        <span class="eddcdp-toggle-slider"></span>
                                    </label>
                                    <p class="description"><?php _e('Enable automatic fullscreen dashboard mode', 'edd-customer-dashboard-pro'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Dashboard Sections -->
                        <div class="eddcdp-section">
                            <h2><?php _e('Dashboard Sections', 'edd-customer-dashboard-pro'); ?></h2>
                            <p class="description"><?php _e('Enable or disable specific dashboard sections', 'edd-customer-dashboard-pro'); ?></p>
                            
                            <?php
                            $sections = array(
                                'purchases' => __('Purchases', 'edd-customer-dashboard-pro'),
                                'downloads' => __('Downloads', 'edd-customer-dashboard-pro'),
                                'licenses' => __('Licenses', 'edd-customer-dashboard-pro'),
                                'wishlist' => __('Wishlist', 'edd-customer-dashboard-pro'),
                                'analytics' => __('Analytics', 'edd-customer-dashboard-pro'),
                                'support' => __('Support', 'edd-customer-dashboard-pro')
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
                        
                        <div class="eddcdp-submit-section">
                            <?php submit_button(__('Save Settings', 'edd-customer-dashboard-pro'), 'primary', 'submit', false); ?>
                        </div>
                    </form>
                </div>
                
                <!-- Sidebar -->
                <div class="eddcdp-admin-sidebar">
                    <div class="eddcdp-sidebar-section">
                        <h3><?php _e('How to Use', 'edd-customer-dashboard-pro'); ?></h3>
                        <p><?php _e('Use this shortcode to display the dashboard:', 'edd-customer-dashboard-pro'); ?></p>
                        <code>[edd_customer_dashboard_pro]</code>
                        
                        <p style="margin-top: 15px;"><?php _e('Or create a page and add the shortcode in the content area.', 'edd-customer-dashboard-pro'); ?></p>
                    </div>
                    
                    <div class="eddcdp-sidebar-section">
                        <h3><?php _e('Plugin Info', 'edd-customer-dashboard-pro'); ?></h3>
                        <p><strong><?php _e('Version:', 'edd-customer-dashboard-pro'); ?></strong> <?php echo EDDCDP_VERSION; ?></p>
                        <p><strong><?php _e('Active Template:', 'edd-customer-dashboard-pro'); ?></strong> <?php echo esc_html(ucfirst($settings['active_template'])); ?></p>
                        <p><strong><?php _e('Available Templates:', 'edd-customer-dashboard-pro'); ?></strong> <?php echo count($templates); ?></p>
                    </div>
                    
                    <div class="eddcdp-sidebar-section">
                        <h3><?php _e('System Status', 'edd-customer-dashboard-pro'); ?></h3>
                        <?php
                        $system_status = $this->get_system_status();
                        foreach ($system_status as $item) : ?>
                        <div class="eddcdp-status-item">
                            <span class="status-indicator <?php echo esc_attr($item['status']); ?>"></span>
                            <span title="<?php echo esc_attr($item['message']); ?>"><?php echo esc_html($item['label']); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Template Selection - Full Width Section -->
            <div class="eddcdp-section eddcdp-template-selection-section">
                <h2><?php _e('Template Selection', 'edd-customer-dashboard-pro'); ?></h2>
                <p class="description"><?php _e('Choose and configure your dashboard template.', 'edd-customer-dashboard-pro'); ?></p>
                
                <div class="eddcdp-templates-grid eddcdp-templates-three-column">
                    <?php foreach ($templates as $template_key => $template) : 
                        $is_active = ($settings['active_template'] === $template_key);
                    ?>
                    <div class="eddcdp-template-card <?php echo $is_active ? 'active' : ''; ?>">
                        <div class="eddcdp-template-preview">
                            <div class="eddcdp-template-icon">
                                📱
                            </div>
                        </div>
                        <div class="eddcdp-template-info">
                            <h3><?php echo esc_html($template['name']); ?></h3>
                            <p><?php echo esc_html($template['description']); ?></p>
                            <div class="eddcdp-template-meta">
                                <span class="version"><?php printf(__('Version: %s', 'edd-customer-dashboard-pro'), esc_html($template['version'])); ?></span>
                                <span class="author"><?php printf(__('by %s', 'edd-customer-dashboard-pro'), esc_html($template['author'])); ?></span>
                            </div>
                        </div>
                        <div class="eddcdp-template-actions">
                            <?php if ($is_active) : ?>
                                <span class="eddcdp-template-status active">✓ <?php _e('Active', 'edd-customer-dashboard-pro'); ?></span>
                            <?php else : ?>
                                <a href="<?php echo wp_nonce_url(admin_url('edit.php?post_type=download&page=eddcdp-settings&action=activate_template&template=' . $template_key), 'eddcdp_activate_template'); ?>" 
                                   class="button button-primary">
                                    <?php _e('Activate', 'edd-customer-dashboard-pro'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <?php
    }
    
    /**
     * Show admin notices
     */
    public function show_admin_notices() {
        $screen = get_current_screen();
        if ($screen->id !== 'download_page_eddcdp-settings') {
            return;
        }
        
        // Show settings saved success message
        if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . __('Settings saved successfully!', 'edd-customer-dashboard-pro') . '</p>';
            echo '</div>';
        }
        
        // Show template activation success
        if (isset($_GET['template_activated'])) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . __('Template activated successfully!', 'edd-customer-dashboard-pro') . '</p>';
            echo '</div>';
        }
        
        // Check EDD version
        if (defined('EDD_VERSION') && version_compare(EDD_VERSION, '3.0.0', '<')) {
            echo '<div class="notice notice-warning">';
            echo '<p>' . sprintf(__('EDD Customer Dashboard Pro works best with Easy Digital Downloads 3.0 or higher. You are currently running version %s.', 'edd-customer-dashboard-pro'), EDD_VERSION) . '</p>';
            echo '</div>';
        }
        
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            echo '<div class="notice notice-error">';
            echo '<p>' . sprintf(__('EDD Customer Dashboard Pro requires PHP 7.4 or higher. You are currently running version %s.', 'edd-customer-dashboard-pro'), PHP_VERSION) . '</p>';
            echo '</div>';
        }
    }
}