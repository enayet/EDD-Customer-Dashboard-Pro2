<?php
/**
 * Admin Settings Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=download',
            __('Dashboard Pro', 'eddcdp'),
            __('Dashboard Pro', 'eddcdp'),
            'manage_shop_settings',
            'eddcdp-settings',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('eddcdp_settings_group', 'eddcdp_settings');
    }
    
    /**
     * Admin scripts
     */
    public function admin_scripts($hook) {
        if ($hook !== 'download_page_eddcdp-settings') {
            return;
        }
        
        wp_enqueue_style('eddcdp-admin', EDDCDP_PLUGIN_URL . 'assets/admin.css', array(), EDDCDP_VERSION);
    }
    
    /**
     * Get default settings
     */
    private function get_default_settings() {
        return array(
            'replace_edd_pages' => true,
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
     * Get current settings
     */
    private function get_settings() {
        $defaults = $this->get_default_settings();
        $settings = get_option('eddcdp_settings', $defaults);
        return wp_parse_args($settings, $defaults);
    }
    
    /**
     * Get available templates
     */
    private function get_available_templates() {
        return array(
            'default' => array(
                'name' => 'Default Dashboard',
                'description' => 'Modern, clean dashboard interface',
                'version' => '1.0.0',
                'active' => true
            )
        );
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['eddcdp_nonce'], 'eddcdp_save_settings')) {
            $this->save_settings();
        }
        
        $settings = $this->get_settings();
        $templates = $this->get_available_templates();
        ?>
        
        <div class="wrap eddcdp-admin">
            <h1><?php _e('EDD Customer Dashboard Pro', 'eddcdp'); ?></h1>
            
            <div class="eddcdp-admin-container">
                <div class="eddcdp-admin-main">
                    <form method="post" action="">
                        <?php wp_nonce_field('eddcdp_save_settings', 'eddcdp_nonce'); ?>
                        
                        <!-- General Settings -->
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
                                    <label><?php echo $label; ?></label>
                                </div>
                                <div class="eddcdp-setting-control">
                                    <label class="eddcdp-toggle">
                                        <input type="checkbox" name="eddcdp_settings[enabled_sections][<?php echo $key; ?>]" value="1" <?php checked($checked); ?>>
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
                                <?php foreach ($templates as $template_key => $template) : ?>
                                <div class="eddcdp-template-card <?php echo $settings['active_template'] === $template_key ? 'active' : ''; ?>">
                                    <div class="eddcdp-template-preview">
                                        <div class="eddcdp-template-icon">🎨</div>
                                    </div>
                                    <div class="eddcdp-template-info">
                                        <h3><?php echo $template['name']; ?></h3>
                                        <p><?php echo $template['description']; ?></p>
                                        <div class="eddcdp-template-meta">
                                            <span class="version"><?php printf(__('Version: %s', 'eddcdp'), $template['version']); ?></span>
                                            <span class="author"><?php _e('by EDD Customer Dashboard Pro', 'eddcdp'); ?></span>
                                        </div>
                                    </div>
                                    <div class="eddcdp-template-actions">
                                        <?php if ($settings['active_template'] === $template_key) : ?>
                                            <span class="eddcdp-template-status active">✓ <?php _e('Active', 'eddcdp'); ?></span>
                                        <?php else : ?>
                                            <label class="eddcdp-template-radio">
                                                <input type="radio" name="eddcdp_settings[active_template]" value="<?php echo $template_key; ?>">
                                                <?php _e('Select', 'eddcdp'); ?>
                                            </label>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
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
                    </div>
                    
                    <div class="eddcdp-sidebar-section">
                        <h3><?php _e('Plugin Info', 'eddcdp'); ?></h3>
                        <p><strong><?php _e('Version:', 'eddcdp'); ?></strong> <?php echo EDDCDP_VERSION; ?></p>
                        <p><strong><?php _e('Active Template:', 'eddcdp'); ?></strong> <?php echo ucfirst($settings['active_template']); ?></p>
                    </div>
                    
                    <div class="eddcdp-sidebar-section">
                        <h3><?php _e('Quick Actions', 'eddcdp'); ?></h3>
                        <button type="button" class="button"><?php _e('Clear Cache', 'eddcdp'); ?></button>
                    </div>
                    
                    <div class="eddcdp-sidebar-section">
                        <h3><?php _e('System Status', 'eddcdp'); ?></h3>
                        <div class="eddcdp-status-item">
                            <span class="status-indicator active"></span>
                            <?php _e('EDD Active', 'eddcdp'); ?>
                        </div>
                        <div class="eddcdp-status-item">
                            <span class="status-indicator warning"></span>
                            <?php _e('Template Issues', 'eddcdp'); ?>
                        </div>
                        <div class="eddcdp-status-item">
                            <span class="status-indicator active"></span>
                            <?php _e('Licensing Support', 'eddcdp'); ?>
                        </div>
                        <?php if (!empty($settings['enabled_sections']['wishlist'])) : ?>
                        <div class="eddcdp-status-item">
                            <span class="status-indicator optional"></span>
                            <?php _e('Wishlist Optional', 'eddcdp'); ?>
                        </div>
                        <?php endif; ?>
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
            return;
        }
        
        $settings = array();
        
        if (isset($_POST['eddcdp_settings'])) {
            $posted_settings = $_POST['eddcdp_settings'];
            
            $settings['replace_edd_pages'] = !empty($posted_settings['replace_edd_pages']);
            $settings['fullscreen_mode'] = !empty($posted_settings['fullscreen_mode']);
            $settings['active_template'] = sanitize_text_field($posted_settings['active_template']);
            $settings['enabled_sections'] = !empty($posted_settings['enabled_sections']) ? $posted_settings['enabled_sections'] : array();
        }
        
        update_option('eddcdp_settings', $settings);
        
        echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'eddcdp') . '</p></div>';
    }
}