<?php
/**
 * Frontend Fullscreen Manager Component
 * 
 * Handles all fullscreen mode detection and rendering
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Fullscreen_Manager implements EDDCDP_Component_Interface {
    
    private $is_fullscreen_mode = false;
    private $template_loader;
    private $dashboard_data;
    
    /**
     * Constructor for legacy compatibility
     */
    public function __construct($template_loader = null, $dashboard_data = null) {
        $this->template_loader = $template_loader;
        $this->dashboard_data = $dashboard_data;
    }
    
    /**
     * Initialize component
     */
    public function init() {
        // Component initialization
    }
    
    /**
     * Get component dependencies
     */
    public function get_dependencies() {
        return array('EDDCDP_Template_Manager', 'EDDCDP_Customer_Data');
    }
    
    /**
     * Check if component should load
     */
    public function should_load() {
        return !is_admin(); // Only load on frontend
    }
    
    /**
     * Get component priority
     */
    public function get_priority() {
        return 40; // Load after other components
    }
    
    /**
     * Check if current request should be fullscreen
     */
    public function should_load_fullscreen() {
        if (!is_user_logged_in()) {
            return false;
        }
        
        // Manual override parameters
        if ($this->has_exit_parameter()) {
            return false;
        }
        
        if ($this->has_force_parameter()) {
            return true;
        }
        
        // Check if fullscreen mode is enabled in settings
        $settings = get_option('eddcdp_settings', array());
        if (!isset($settings['fullscreen_mode']) || !$settings['fullscreen_mode']) {
            return false;
        }
        
        // Check various conditions for auto-fullscreen
        return $this->is_edd_dashboard_page() || 
               $this->is_edd_url_pattern() || 
               $this->has_payment_key();
    }
    
    /**
     * Load fullscreen template and exit
     */
    public function load_fullscreen() {
        $this->is_fullscreen_mode = true;
        
        // Prevent caching
        nocache_headers();
        
        // Prepare template data
        $template_data = $this->prepare_template_data();
        
        // Render fullscreen template
        $this->render_fullscreen_template($template_data);
        exit;
    }
    
    /**
     * Check for exit fullscreen parameter
     */
    private function has_exit_parameter() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return isset($_GET['eddcdp_exit_fullscreen']) && $_GET['eddcdp_exit_fullscreen'] === '1';
    }
    
    /**
     * Check for force fullscreen parameter
     */
    private function has_force_parameter() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return isset($_GET['eddcdp_fullscreen']) && $_GET['eddcdp_fullscreen'] === '1';
    }
    
    /**
     * Check if current page has EDD dashboard shortcodes
     */
    private function is_edd_dashboard_page() {
        global $post;
        
        if (!$post) {
            return false;
        }
        
        $edd_shortcodes = array(
            'edd_customer_dashboard_pro',
            'purchase_history',
            'download_history',
            'edd_profile_editor',
            'edd_receipt'
        );
        
        foreach ($edd_shortcodes as $shortcode) {
            if (has_shortcode($post->post_content, $shortcode)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check URL patterns for EDD pages
     */
    private function is_edd_url_pattern() {
        $current_url = $_SERVER['REQUEST_URI'] ?? '';
        
        $edd_patterns = array(
            'order-history',
            'purchase-history', 
            'checkout-2/order-history',
            'account/orders',
            'my-account/orders',
            'customer/dashboard',
            'member/dashboard'
        );
        
        foreach ($edd_patterns as $pattern) {
            if (strpos($current_url, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check for payment key parameter
     */
    private function has_payment_key() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return isset($_GET['payment_key']) || isset($_GET['purchase_key']);
    }
    
    /**
     * Prepare template data for rendering
     */
    private function prepare_template_data() {
        $user = wp_get_current_user();
        $customer = edd_get_customer_by('user_id', $user->ID);

        if (!$customer) {
            wp_die(esc_html__('No customer data found.', 'edd-customer-dashboard-pro'));
        }

        // Handle payment/receipt mode
        $payment_data = $this->get_payment_data();
        $settings = get_option('eddcdp_settings', array());
        $enabled_sections = isset($settings['enabled_sections']) && is_array($settings['enabled_sections']) 
            ? $settings['enabled_sections'] 
            : array();

        return array(
            'user' => $user,
            'customer' => $customer,
            'payment' => $payment_data['payment'],
            'payment_key' => $payment_data['payment_key'],
            'settings' => $settings,
            'enabled_sections' => $enabled_sections,
            'dashboard_data' => $this->dashboard_data,
            'view_mode' => $payment_data['view_mode'],
            'fullscreen_mode' => true
        );
    }
    
    /**
     * Get payment data if in receipt mode
     */
    private function get_payment_data() {
        $payment_key = '';
        $payment = null;
        $view_mode = 'dashboard';
        
        // Check URL parameters for payment key
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['payment_key'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $payment_key = sanitize_text_field(wp_unslash($_GET['payment_key']));
        } elseif (isset($_GET['purchase_key'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $payment_key = sanitize_text_field(wp_unslash($_GET['purchase_key']));
        }
        
        if (!empty($payment_key)) {
            $payment = edd_get_payment_by('key', $payment_key);
            
            if ($payment && edd_can_view_receipt($payment_key)) {
                $view_mode = 'receipt';
            }
        }
        
        return array(
            'payment_key' => $payment_key,
            'payment' => $payment,
            'view_mode' => $view_mode
        );
    }
    
    /**
     * Generate smart back URL
     */
    private function get_back_url() {
        $back_url = home_url(); // Default to homepage
        
        // Check for HTTP referrer
        if (isset($_SERVER['HTTP_REFERER'])) {
            $referrer = $_SERVER['HTTP_REFERER'];
            $site_url = home_url();
            
            // Only use referrer if it's from the same site and not a dashboard page
            if (strpos($referrer, $site_url) === 0) {
                $referrer_path = str_replace($site_url, '', $referrer);
                
                // Don't go back to another dashboard page
                $dashboard_patterns = array('order-history', 'purchase-history', 'payment_key=');
                $is_dashboard_referrer = false;
                
                foreach ($dashboard_patterns as $pattern) {
                    if (strpos($referrer_path, $pattern) !== false) {
                        $is_dashboard_referrer = true;
                        break;
                    }
                }
                
                if (!$is_dashboard_referrer) {
                    $back_url = $referrer;
                }
            }
        }
        
        // Add exit parameter to ensure we don't loop back to full screen
        return add_query_arg('eddcdp_exit_fullscreen', '1', $back_url);
    }
    
    /**
     * Render the fullscreen template
     */
    private function render_fullscreen_template($template_data) {
        // Extract data for template
        extract($template_data);
        
        // Get template URL for assets
        $template_url = $this->template_loader ? $this->template_loader->get_template_url() : '';
        $back_url = $this->get_back_url();
        
        // Load fullscreen template file
        $fullscreen_template = EDDCDP_PLUGIN_DIR . 'templates/fullscreen-layout.php';
        
        if (file_exists($fullscreen_template)) {
            include $fullscreen_template;
        } else {
            // Inline fallback template
            $this->render_inline_fullscreen_template($template_data, $template_url, $back_url);
        }
    }
    
    /**
     * Render inline fullscreen template (fallback)
     */
    private function render_inline_fullscreen_template($template_data, $template_url, $back_url) {
        extract($template_data);
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php 
            if ($view_mode === 'receipt' && isset($payment)) {
                printf(esc_html__('Order #%s - %s', 'edd-customer-dashboard-pro'), esc_html($payment->number), get_bloginfo('name'));
            } else {
                printf(esc_html__('Customer Dashboard - %s', 'edd-customer-dashboard-pro'), get_bloginfo('name'));
            }
            ?></title>
            
            <?php if ($template_url) : ?>
                <link rel="stylesheet" href="<?php echo esc_url($template_url . 'style.css?v=' . EDDCDP_VERSION); ?>">
            <?php endif; ?>
            
            <style>
                body { margin: 0; padding: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
                .eddcdp-fullscreen-wrapper { min-height: 100vh; display: flex; flex-direction: column; }
                .eddcdp-fullscreen-header { 
                    background: rgba(255, 255, 255, 0.95); 
                    backdrop-filter: blur(10px); 
                    padding: 15px 30px; 
                    border-bottom: 1px solid rgba(255, 255, 255, 0.2); 
                    display: flex; 
                    justify-content: space-between; 
                    align-items: center; 
                    position: sticky; 
                    top: 0; 
                    z-index: 1000; 
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
                }
                .eddcdp-fullscreen-title { 
                    font-size: 1.5rem; 
                    font-weight: 700; 
                    background: linear-gradient(135deg, #667eea, #764ba2); 
                    -webkit-background-clip: text; 
                    -webkit-text-fill-color: transparent; 
                    background-clip: text; 
                    margin: 0; 
                }
                .eddcdp-back-to-site { 
                    background: linear-gradient(135deg, #667eea, #764ba2); 
                    color: white; 
                    border: none; 
                    border-radius: 8px; 
                    padding: 10px 20px; 
                    cursor: pointer; 
                    font-size: 0.9rem; 
                    font-weight: 600; 
                    transition: all 0.3s ease; 
                    text-decoration: none; 
                    display: inline-flex; 
                    align-items: center; 
                    gap: 8px; 
                }
                .eddcdp-back-to-site:hover { 
                    transform: translateY(-2px); 
                    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); 
                    color: white; 
                    text-decoration: none; 
                }
                .eddcdp-fullscreen-content { flex: 1; padding: 0; }
                .eddcdp-dashboard-container { padding: 20px; background: transparent; min-height: calc(100vh - 80px); }
                @media (max-width: 768px) {
                    .eddcdp-fullscreen-header { padding: 10px 15px; flex-direction: column; gap: 10px; align-items: flex-start; }
                    .eddcdp-fullscreen-title { font-size: 1.2rem; }
                    .eddcdp-dashboard-container { padding: 15px; min-height: calc(100vh - 120px); }
                }
            </style>
            
            <?php wp_head(); ?>
        </head>
        <body <?php body_class('eddcdp-fullscreen-mode'); ?>>
            
            <div class="eddcdp-fullscreen-wrapper">
                <div class="eddcdp-fullscreen-header">
                    <h1 class="eddcdp-fullscreen-title">
                        <?php 
                        if ($view_mode === 'receipt' && isset($payment)) {
                            printf(esc_html__('Order #%s', 'edd-customer-dashboard-pro'), esc_html($payment->number));
                        } else {
                            esc_html_e('Customer Dashboard', 'edd-customer-dashboard-pro');
                        }
                        ?>
                    </h1>
                    
                    <div class="eddcdp-fullscreen-actions">
                        <a href="<?php echo esc_url($back_url); ?>" class="eddcdp-back-to-site">
                            ← <?php esc_html_e('Back to Site', 'edd-customer-dashboard-pro'); ?>
                        </a>
                    </div>
                </div>
                
                <div class="eddcdp-fullscreen-content">
                    <?php
                    // Load the dashboard template
                    if ($this->template_loader) {
                        do_action('eddcdp_fullscreen_dashboard_loaded', $template_data);
                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template loader output is already escaped
                        echo $this->template_loader->load_template(null, $template_data);
                    } else {
                        echo '<p>' . esc_html__('Dashboard template not available.', 'edd-customer-dashboard-pro') . '</p>';
                    }
                    ?>
                </div>
            </div>
            
            <?php if ($template_url) : ?>
                <script src="<?php echo esc_url($template_url . 'script.js?v=' . EDDCDP_VERSION); ?>"></script>
            <?php endif; ?>
            
            <script>
                // Handle Escape key to go back to site
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        window.location.href = '<?php echo esc_js($back_url); ?>';
                    }
                });
                
                // Update any dashboard links to stay in normal mode when going back
                document.addEventListener('DOMContentLoaded', function() {
                    const backLinks = document.querySelectorAll('a[href*="order-history"], a[href*="purchase-history"]');
                    backLinks.forEach(function(link) {
                        const linkUrl = new URL(link.href, window.location.origin);
                        linkUrl.searchParams.set('eddcdp_exit_fullscreen', '1');
                        link.href = linkUrl.toString();
                    });
                });
            </script>
            
            <?php wp_footer(); ?>
        </body>
        </html>
        <?php
    }
    
    /**
     * Check if we're in fullscreen mode
     */
    public function is_fullscreen_mode() {
        return $this->is_fullscreen_mode;
    }
}