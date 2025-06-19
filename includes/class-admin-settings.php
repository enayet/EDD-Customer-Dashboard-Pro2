<?php
/**
 * Simple Admin Settings Class - Direct template activation
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Admin_Settings {
    
    private $settings_slug = 'eddcdp-settings';
    private $activation_message = null;
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        // Remove the admin_init hook for template activation since we handle it in render_settings_page
    }
    
    /**
     * Handle template activation - Simplified
     */
    public function handle_template_activation() {
        // Only handle POST requests for template activation
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        
        // Check if we're on the right page
        if (!isset($_GET['page']) || $_GET['page'] !== $this->settings_slug) {
            return;
        }
        
        // Check if this is a template activation request
        if (!isset($_POST['activate_template']) || !isset($_POST['eddcdp_activate_template_nonce'])) {
            return;
        }
        
        // Verify nonce
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['eddcdp_activate_template_nonce'])), 'eddcdp_activate_template')) {
            $this->activation_message = array('type' => 'error', 'message' => __('Security check failed', 'edd-customer-dashboard-pro'));
            return;
        }
        
        // Get template name
        $template_name = sanitize_text_field(wp_unslash($_POST['activate_template']));
        
        // Get current settings
        $settings = get_option('eddcdp_settings', array());
        
        // Update active template
        $old_template = isset($settings['active_template']) ? $settings['active_template'] : '';
        $settings['active_template'] = $template_name;
        
        // Save settings
        $result = update_option('eddcdp_settings', $settings);
        
        // Verify the change was saved
        $updated_settings = get_option('eddcdp_settings', array());
        $current_template = isset($updated_settings['active_template']) ? $updated_settings['active_template'] : '';
        
        if ($current_template === $template_name) {
            $this->activation_message = array(
                'type' => 'success', 
                'message' => sprintf(__('Template "%s" activated successfully!', 'edd-customer-dashboard-pro'), $template_name)
            );
        } else {
            $this->activation_message = array(
                'type' => 'error', 
                'message' => __('Failed to activate template. Please try again.', 'edd-customer-dashboard-pro')
            );
        }
    }
    
    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=download',
            __('Customer Dashboard Pro', 'edd-customer-dashboard-pro'),
            __('Dashboard Pro', 'edd-customer-dashboard-pro'),
            'manage_shop_settings',
            $this->settings_slug,
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('eddcdp_settings_group', 'eddcdp_settings', array($this, 'sanitize_settings'));
        
        // General Settings Section
        add_settings_section(
            'eddcdp_general_section',
            __('General Settings', 'edd-customer-dashboard-pro'),
            array($this, 'general_section_callback'),
            $this->settings_slug
        );
        
        // Template Settings Section
        add_settings_section(
            'eddcdp_template_section',
            __('Template Settings', 'edd-customer-dashboard-pro'),
            array($this, 'template_section_callback'),
            $this->settings_slug
        );
        
        // Sections Settings Section
        add_settings_section(
            'eddcdp_sections_section',
            __('Dashboard Sections', 'edd-customer-dashboard-pro'),
            array($this, 'sections_section_callback'),
            $this->settings_slug
        );
        
        // Add settings fields
        $this->add_settings_fields();
    }
    
    /**
     * Add settings fields
     */
    private function add_settings_fields() {
        // Replace EDD Pages
        add_settings_field(
            'replace_edd_pages',
            __('Replace EDD Pages', 'edd-customer-dashboard-pro'),
            array($this, 'checkbox_field_callback'),
            $this->settings_slug,
            'eddcdp_general_section',
            array(
                'field' => 'replace_edd_pages',
                'description' => __('Replace default EDD customer pages with Dashboard Pro', 'edd-customer-dashboard-pro')
            )
        );
        
        // Active Template
        add_settings_field(
            'active_template',
            __('Active Template', 'edd-customer-dashboard-pro'),
            array($this, 'template_field_callback'),
            $this->settings_slug,
            'eddcdp_template_section'
        );
        
        // Section toggles
        $sections = $this->get_available_sections();
        foreach ($sections as $section_key => $section_name) {
            add_settings_field(
                'enabled_sections_' . $section_key,
                $section_name,
                array($this, 'section_toggle_callback'),
                $this->settings_slug,
                'eddcdp_sections_section',
                array('section' => $section_key)
            );
        }
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
        // Handle template activation first
        $this->handle_template_activation();
        
        // Show activation message if any
        if ($this->activation_message) {
            $class = $this->activation_message['type'] === 'success' ? 'notice-success' : 'notice-error';
            echo '<div class="notice ' . $class . ' is-dismissible"><p>' . esc_html($this->activation_message['message']) . '</p></div>';
        }
        
        ?>
        <div class="wrap eddcdp-admin">
            <h1><?php esc_html_e('EDD Customer Dashboard Pro Settings', 'edd-customer-dashboard-pro'); ?></h1>
            
            <div class="eddcdp-admin-content">
                <form method="post" action="options.php">
                    <?php
                    settings_fields('eddcdp_settings_group');
                    do_settings_sections($this->settings_slug);
                    submit_button();
                    ?>
                </form>
            </div>
        </div>
        <?php
    }
    
    /**
     * Section callbacks
     */
    public function general_section_callback() {
        echo '<p>' . esc_html__('Configure general dashboard settings.', 'edd-customer-dashboard-pro') . '</p>';
    }
    
    public function template_section_callback() {
        echo '<p>' . esc_html__('Choose and configure your dashboard template.', 'edd-customer-dashboard-pro') . '</p>';
    }
    
    public function sections_section_callback() {
        echo '<p>' . esc_html__('Enable or disable specific dashboard sections.', 'edd-customer-dashboard-pro') . '</p>';
    }
    
    /**
     * Field callbacks
     */
    public function checkbox_field_callback($args) {
        $settings = get_option('eddcdp_settings', array());
        $field = $args['field'];
        $value = isset($settings[$field]) ? $settings[$field] : false;
        $description = isset($args['description']) ? $args['description'] : '';
        
        ?>
        <label>
            <input type="checkbox" name="eddcdp_settings[<?php echo esc_attr($field); ?>]" value="1" <?php checked($value, true); ?> />
            <?php echo esc_html($description); ?>
        </label>
        <?php
    }
    
    /**
     * Simple template field - no radio buttons, just activate buttons
     */
    public function template_field_callback() {
        $template_loader = eddcdp()->get_template_loader();
        $active_template = $template_loader->get_active_template();
        $available_templates = $template_loader->get_available_templates();
        
        ?>
        <div class="eddcdp-template-selector">
            <?php foreach ($available_templates as $template_key => $template_info) : ?>
                <?php $is_active = ($active_template === $template_key); ?>
                
                <div class="eddcdp-template-option <?php echo $is_active ? 'selected' : ''; ?>">
                    <div class="template-preview">
                        <?php
                        $screenshot = $template_loader->get_template_screenshot($template_key);
                        if ($screenshot) :
                        ?>
                            <img src="<?php echo esc_url($screenshot); ?>" alt="<?php echo esc_attr($template_info['name']); ?>" />
                        <?php else : ?>
                            <div class="no-screenshot">
                                <span class="dashicons dashicons-admin-appearance"></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="template-info">
                            <h4><?php echo esc_html($template_info['name']); ?></h4>
                            <p><?php echo esc_html($template_info['description']); ?></p>
                            <div class="template-meta">
                                <span class="version"><?php printf(esc_html__('Version: %s', 'edd-customer-dashboard-pro'), esc_html($template_info['version'])); ?></span>
                                <?php if (isset($template_info['author'])) : ?>
                                    <span class="author"><?php printf(esc_html__('by %s', 'edd-customer-dashboard-pro'), esc_html($template_info['author'])); ?></span>
                                <?php endif; ?>
                            </div>
                            

                            
                            <div class="template-actions">
                                <?php if ($is_active) : ?>
                                    <span class="template-status active">
                                        <span class="dashicons dashicons-yes-alt"></span>
                                        <?php _e('Active', 'edd-customer-dashboard-pro'); ?>
                                    </span>
                                <?php else : ?>
                                    <?php 
                                    $activate_url = wp_nonce_url(
                                        add_query_arg(array(
                                            'page' => $this->settings_slug,
                                            'activate_template' => $template_key
                                        ), admin_url('edit.php?post_type=download')),
                                        'activate_template'
                                    );
                                    ?>
                                    <a href="<?php echo esc_url($activate_url); ?>" 
                                       class="button button-primary">
                                        <?php _e('Activate', 'edd-customer-dashboard-pro'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <p class="description">
            <?php _e('Click "Activate" to switch templates instantly.', 'edd-customer-dashboard-pro'); ?>
        </p>
        <?php
    }
    
    public function section_toggle_callback($args) {
        $settings = get_option('eddcdp_settings', array());
        $section = $args['section'];
        $enabled_sections = isset($settings['enabled_sections']) ? $settings['enabled_sections'] : array();
        $is_enabled = isset($enabled_sections[$section]) ? $enabled_sections[$section] : true;
        
        ?>
        <label class="eddcdp-toggle">
            <input type="checkbox" name="eddcdp_settings[enabled_sections][<?php echo esc_attr($section); ?>]" value="1" <?php checked($is_enabled, true); ?> />
            <span class="eddcdp-toggle-slider"></span>
        </label>
        <?php
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // Replace EDD Pages
        $sanitized['replace_edd_pages'] = isset($input['replace_edd_pages']) ? true : false;
        
        // Keep current active template (it's handled by direct activation)
        $current_settings = get_option('eddcdp_settings', array());
        if (isset($current_settings['active_template'])) {
            $sanitized['active_template'] = $current_settings['active_template'];
        } else {
            $sanitized['active_template'] = 'default';
        }
        
        // Enabled Sections
        if (isset($input['enabled_sections']) && is_array($input['enabled_sections'])) {
            $sanitized['enabled_sections'] = array();
            $available_sections = $this->get_available_sections();
            
            foreach ($available_sections as $section_key => $section_name) {
                $sanitized['enabled_sections'][$section_key] = isset($input['enabled_sections'][$section_key]) ? true : false;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Get available dashboard sections
     */
    private function get_available_sections() {
        return array(
            'purchases' => __('Purchases', 'edd-customer-dashboard-pro'),
            'downloads' => __('Downloads', 'edd-customer-dashboard-pro'),
            'licenses' => __('Licenses', 'edd-customer-dashboard-pro'),
            'wishlist' => __('Wishlist', 'edd-customer-dashboard-pro'),
            'analytics' => __('Analytics', 'edd-customer-dashboard-pro'),
            'support' => __('Support', 'edd-customer-dashboard-pro')
        );
    }
}