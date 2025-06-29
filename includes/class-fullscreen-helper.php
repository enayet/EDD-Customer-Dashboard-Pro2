<?php
/**
 * Fullscreen Helper Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Fullscreen_Helper {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Add any initialization if needed
    }
    
    /**
     * Get exit fullscreen URL
     */
    public static function get_exit_url() {
        // Try to get referer first
        $referer = wp_get_referer();
        if ($referer && !self::is_dashboard_url($referer)) {
            return $referer;
        }
        
        // Get current URL and clean it
        $current_url = home_url($_SERVER['REQUEST_URI']);
        $clean_url = remove_query_arg(array(
            'eddcdp_order',
            'eddcdp_order_licenses', 
            'eddcdp_invoice_form',
            'payment_id',
            'invoice'
        ), $current_url);
        
        // If cleaned URL is different from current, use it
        if ($clean_url !== $current_url) {
            return $clean_url;
        }
        
        // Fallback to home
        return home_url('/');
    }
    
    /**
     * Check if URL is a dashboard URL
     */
    private static function is_dashboard_url($url) {
        $dashboard_params = array(
            'eddcdp_order',
            'eddcdp_order_licenses',
            'eddcdp_invoice_form'
        );
        
        foreach ($dashboard_params as $param) {
            if (strpos($url, $param) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Add fullscreen exit button HTML
     */
    public static function render_exit_button() {
        $exit_url = self::get_exit_url();
        ?>
        <a href="<?php echo esc_url($exit_url); ?>" 
           class="eddcdp-fullscreen-exit"
           title="<?php esc_attr_e('Exit Fullscreen Dashboard', 'edd-customer-dashboard-pro'); ?>">
            <?php _e('Exit Dashboard', 'edd-customer-dashboard-pro'); ?>
        </a>
        <?php
    }
}