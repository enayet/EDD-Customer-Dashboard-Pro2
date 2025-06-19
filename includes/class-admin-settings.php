<?php
/**
 * Fixed Admin Settings Class - Improved layout and functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Admin_Settings {
    
    private $settings_slug = 'eddcdp-settings';
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_init', array($this, 'handle_form_submissions'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Register settings (for WordPress compatibility)
     */
    public function register_settings() {
        // Register for WordPress compatibility, but we handle saving manually
        register_setting('eddcdp_settings_group', 'eddcdp_settings');
    }
    
    /**
     * Handle all form submissions manually
     */
    public function handle_form_submissions() {
        // Only process on our settings page
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (!isset($_GET['page']) || $_GET['page'] !== $this->settings_slug) {
            return;
        }
        
        // Handle template activation
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['activate_template']) && isset($_GET['_wpnonce'])) {
            $this->handle_template_activation();
            return;
        }
        
        // Handle settings save
        if (isset($_POST['eddcdp_save_settings']) && isset($_POST['eddcdp_settings_nonce'])) {
            $this->handle_settings_save();
            return;
        }
    }
    
    /**
     * Handle template activation
     */
    private function handle_template_activation() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $template_name = sanitize_text_field(wp_unslash($_GET['activate_template']));
        
        // Verify nonce
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'activate_template_' . $template_name)) {
            wp_die(esc_html__('Security check failed', 'edd-customer-dashboard-pro'));
        }
        
        // Get current settings
        $settings = get_option('eddcdp_settings', array());
        
        // Update active template
        $settings['active_template'] = $template_name;
        
        // Save settings
        $result = update_option('eddcdp_settings', $settings);
        
        // Redirect with success message
        if ($result !== false) {
            $redirect_url = add_query_arg(array(
                'page' => $this->settings_slug,
                'template_activated' => '1',
                'activated_template' => urlencode($template_name)
            ), admin_url('edit.php?post_type=download'));
        } else {
            $redirect_url = add_query_arg(array(
                'page' => $this->settings_slug,
                'template_error' => '1'
            ), admin_url('edit.php?post_type=download'));
        }
        
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Handle settings save
     */
    private function handle_settings_save() {
        // Verify nonce
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['eddcdp_settings_nonce'])), 'eddcdp_save_settings')) {
            wp_die(esc_html__('Security check failed', 'edd-customer-dashboard-pro'));
        }
        
        // Get current settings to preserve active_template
        $current_settings = get_option('eddcdp_settings', array());
        $settings = array();
        
        // Preserve active template
        $settings['active_template'] = isset($current_settings['active_template']) ? $current_settings['active_template'] : 'default';
        
        // Replace EDD Pages
        $settings['replace_edd_pages'] = isset($_POST['replace_edd_pages']) ? true : false;
        
        // Enabled Sections
        $settings['enabled_sections'] = array();
        $available_sections = $this->get_available_sections();
        
        foreach ($available_sections as $section_key => $section_name) {
            $settings['enabled_sections'][$section_key] = isset($_POST['enabled_sections'][$section_key]) ? true : false;
        }
        
        // Save settings
        $result = update_option('eddcdp_settings', $settings);
        
        // Redirect with success message
        if ($result !== false) {
            $redirect_url = add_query_arg(array(
                'page' => $this->settings_slug,
                'settings_saved' => '1'
            ), admin_url('edit.php?post_type=download'));
        } else {
            $redirect_url = add_query_arg(array(
                'page' => $this->settings_slug,
                'settings_error' => '1'
            ), admin_url('edit.php?post_type=download'));
        }
        
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=download',
            esc_html__('Customer Dashboard Pro', 'edd-customer-dashboard-pro'),
            esc_html__('Dashboard Pro', 'edd-customer-dashboard-pro'),
            'manage_shop_settings',
            $this->settings_slug,
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, $this->settings_slug) === false) {
            return;
        }
        
        wp_enqueue_style('eddcdp-admin', EDDCDP_PLUGIN_URL . 'assets/admin.css', array(), EDDCDP_VERSION);
        wp_enqueue_script('eddcdp-admin', EDDCDP_PLUGIN_URL . 'assets/admin.js', array('jquery'), EDDCDP_VERSION, true);
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Get current settings
        $settings = get_option('eddcdp_settings', array());
        $active_template = isset($settings['active_template']) ? $settings['active_template'] : 'default';
        $replace_edd_pages = isset($settings['replace_edd_pages']) ? $settings['replace_edd_pages'] : false;
        $enabled_sections = isset($settings['enabled_sections']) ? $settings['enabled_sections'] : array();
        
        // Show messages
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['template_activated'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $activated_template = isset($_GET['activated_template']) ? sanitize_text_field(wp_unslash($_GET['activated_template'])) : '';
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                 sprintf(esc_html__('Template "%s" activated successfully!', 'edd-customer-dashboard-pro'), esc_html($activated_template)) . 
                 '</p></div>';
        }
        
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['template_error'])) {
            echo '<div class="notice notice-error is-dismissible"><p>' . 
                 esc_html__('Failed to activate template. Please try again.', 'edd-customer-dashboard-pro') . 
                 '</p></div>';
        }
        
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['settings_saved'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                 esc_html__('Settings saved successfully!', 'edd-customer-dashboard-pro') . 
                 '</p></div>';
        }
        
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['settings_error'])) {
            echo '<div class="notice notice-error is-dismissible"><p>' . 
                 esc_html__('Failed to save settings. Please try again.', 'edd-customer-dashboard-pro') . 
                 '</p></div>';
        }
        
        ?>
        <div class="wrap eddcdp-admin">
            <h1><?php esc_html_e('EDD Customer Dashboard Pro Settings', 'edd-customer-dashboard-pro'); ?></h1>
            
            <div class="eddcdp-admin-wrapper">
                <div class="eddcdp-admin-main">
                    <!-- Settings Form -->
                    <div class="eddcdp-settings-section">
                        <h2><?php esc_html_e('General Settings', 'edd-customer-dashboard-pro'); ?></h2>
                        <form method="post" action="">
                            <?php wp_nonce_field('eddcdp_save_settings', 'eddcdp_settings_nonce'); ?>
                            <input type="hidden" name="eddcdp_save_settings" value="1" />
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php esc_html_e('Replace EDD Pages', 'edd-customer-dashboard-pro'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="replace_edd_pages" value="1" <?php checked($replace_edd_pages, true); ?> />
                                            <?php esc_html_e('Replace default EDD customer pages with Dashboard Pro', 'edd-customer-dashboard-pro'); ?>
                                        </label>
                                    </td>
                                </tr>
                            </table>
                            
                            <h3><?php esc_html_e('Dashboard Sections', 'edd-customer-dashboard-pro'); ?></h3>
                            <p class="description"><?php esc_html_e('Enable or disable specific dashboard sections.', 'edd-customer-dashboard-pro'); ?></p>
                            
                            <table class="form-table">
                                <?php foreach ($this->get_available_sections() as $section_key => $section_name) : ?>
                                    <?php $is_enabled = isset($enabled_sections[$section_key]) ? $enabled_sections[$section_key] : true; ?>
                                    <tr>
                                        <th scope="row"><?php echo esc_html($section_name); ?></th>
                                        <td>
                                            <label class="eddcdp-toggle">
                                                <input type="checkbox" name="enabled_sections[<?php echo esc_attr($section_key); ?>]" value="1" <?php checked($is_enabled, true); ?> />
                                                <span class="eddcdp-toggle-slider"></span>
                                            </label>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                            
                            <?php submit_button(esc_html__('Save Settings', 'edd-customer-dashboard-pro')); ?>
                        </form>
                    
                    </div>
                </div>    
                    
                <div class="eddcdp-admin-sidebar">
                    <div class="eddcdp-info-box">
                        <h3><?php esc_html_e('How to Use', 'edd-customer-dashboard-pro'); ?></h3>
                        <ul>
                            <li><?php esc_html_e('Use the shortcode to display the dashboard:', 'edd-customer-dashboard-pro'); ?></li>
                        </ul>
                        <code>[edd_customer_dashboard_pro]</code>

                        <h4><?php esc_html_e('Plugin Info', 'edd-customer-dashboard-pro'); ?></h4>
                        <p><strong><?php esc_html_e('Version:', 'edd-customer-dashboard-pro'); ?></strong> <?php echo esc_html(EDDCDP_VERSION); ?></p>
                        <p><strong><?php esc_html_e('Active Template:', 'edd-customer-dashboard-pro'); ?></strong> <?php echo esc_html(ucfirst($active_template)); ?></p>
                    </div>
                </div>  
                
            </div>
                   
            <div class="eddcdp-admin-wrapper">
                <div class="eddcdp-admin-main">                                                                               
                    
                    
                    <!-- Template Selection -->
                    <div class="eddcdp-templates-section">
                        <h2><?php esc_html_e('Template Settings', 'edd-customer-dashboard-pro'); ?></h2>
                        <p class="description"><?php esc_html_e('Choose and configure your dashboard template.', 'edd-customer-dashboard-pro'); ?></p>
                        
                        <div class="eddcdp-template-grid">
                            <?php $this->render_template_options($active_template); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            
        </div>
        <?php
    }
    
    /**
     * Render template options - IMPROVED LAYOUT
     */
    private function render_template_options($active_template) {
        // Use template loader to get available templates
        $template_loader = eddcdp()->get_template_loader();
        $available_templates = $template_loader ? $template_loader->get_available_templates() : array();
        
        // Fallback if template loader not available
        if (empty($available_templates)) {
            $available_templates = $this->get_available_templates_fallback();
        }
        
        foreach ($available_templates as $template_key => $template_info) {
            $is_active = ($active_template === $template_key);
            ?>
            <div class="eddcdp-template-card <?php echo $is_active ? 'active' : ''; ?>">
                <div class="eddcdp-template-preview">
                    <?php
                    // Try to get screenshot from template loader
                    $screenshot = false;
                    if ($template_loader) {
                        $screenshot = $template_loader->get_template_screenshot($template_key);
                    }
                    
                    if ($screenshot) :
                    ?>
                        <img src="<?php echo esc_url($screenshot); ?>" alt="<?php echo esc_attr($template_info['name']); ?>" />
                    <?php else : ?>
                        <div class="eddcdp-no-screenshot">
                            <span class="dashicons dashicons-admin-appearance"></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($is_active) : ?>
                        <div class="eddcdp-active-badge">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php esc_html_e('Active', 'edd-customer-dashboard-pro'); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="eddcdp-template-info">
                    <h4 class="eddcdp-template-name"><?php echo esc_html($template_info['name']); ?></h4>
                    <p class="eddcdp-template-description"><?php echo esc_html($template_info['description']); ?></p>
                    
                    <div class="eddcdp-template-meta">
                        <span class="eddcdp-template-version">
                            <?php printf(esc_html__('Version: %s', 'edd-customer-dashboard-pro'), esc_html($template_info['version'])); ?>
                        </span>
                        <?php if (isset($template_info['author'])) : ?>
                            <span class="eddcdp-template-author">
                                <?php printf(esc_html__('by %s', 'edd-customer-dashboard-pro'), esc_html($template_info['author'])); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="eddcdp-template-actions">
                        <?php if ($is_active) : ?>
                            <button type="button" class="button button-secondary" disabled>
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php esc_html_e('Active', 'edd-customer-dashboard-pro'); ?>
                            </button>
                        <?php else : ?>
                            <a href="<?php echo esc_url(wp_nonce_url(
                                add_query_arg(array(
                                    'page' => $this->settings_slug,
                                    'activate_template' => $template_key
                                ), admin_url('edit.php?post_type=download')),
                                'activate_template_' . $template_key
                            )); ?>" class="button button-primary eddcdp-activate-btn">
                                <?php esc_html_e('Activate', 'edd-customer-dashboard-pro'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    
    /**
     * Fallback for available templates if template loader fails
     */
    private function get_available_templates_fallback() {
        return array(
            'default' => array(
                'name' => esc_html__('Default Dashboard', 'edd-customer-dashboard-pro'),
                'description' => esc_html__('Modern, clean dashboard interface with glassmorphism design', 'edd-customer-dashboard-pro'),
                'version' => '1.0.0',
                'author' => esc_html__('EDD Customer Dashboard Pro', 'edd-customer-dashboard-pro')
            )
        );
    }
    
    /**
     * Get available dashboard sections
     */
    private function get_available_sections() {
        return array(
            'purchases' => esc_html__('Purchases', 'edd-customer-dashboard-pro'),
            'downloads' => esc_html__('Downloads', 'edd-customer-dashboard-pro'),
            'licenses' => esc_html__('Licenses', 'edd-customer-dashboard-pro'),
            'wishlist' => esc_html__('Wishlist', 'edd-customer-dashboard-pro'),
            'analytics' => esc_html__('Analytics', 'edd-customer-dashboard-pro'),
            'support' => esc_html__('Support', 'edd-customer-dashboard-pro')
        );
    }
}