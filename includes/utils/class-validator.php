<?php
/**
 * Validator Utility Class
 * 
 * Handles validation of various data types and inputs
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Validator {
    
    /**
     * Validate email address
     */
    public static function email($email) {
        return is_email($email);
    }
    
    /**
     * Validate URL
     */
    public static function url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Validate payment key format
     */
    public static function payment_key($payment_key) {
        if (empty($payment_key) || !is_string($payment_key)) {
            return false;
        }
        
        // EDD payment keys are typically 32 character strings
        return preg_match('/^[a-f0-9]{32}$/', $payment_key) === 1;
    }
    
    /**
     * Validate license key format
     */
    public static function license_key($license_key) {
        if (empty($license_key) || !is_string($license_key)) {
            return false;
        }
        
        // License keys are typically alphanumeric with dashes
        return preg_match('/^[A-Z0-9\-]{10,50}$/', strtoupper($license_key)) === 1;
    }
    
    /**
     * Validate user ID
     */
    public static function user_id($user_id) {
        $user_id = absint($user_id);
        return $user_id > 0 && get_user_by('id', $user_id) !== false;
    }
    
    /**
     * Validate customer ID
     */
    public static function customer_id($customer_id) {
        $customer_id = absint($customer_id);
        return $customer_id > 0 && edd_get_customer($customer_id) !== false;
    }
    
    /**
     * Validate payment ID
     */
    public static function payment_id($payment_id) {
        $payment_id = absint($payment_id);
        return $payment_id > 0 && edd_get_payment($payment_id) !== false;
    }
    
    /**
     * Validate download ID
     */
    public static function download_id($download_id) {
        $download_id = absint($download_id);
        return $download_id > 0 && get_post_type($download_id) === 'download';
    }
    
    /**
     * Validate template name
     */
    public static function template_name($template_name) {
        if (empty($template_name) || !is_string($template_name)) {
            return false;
        }
        
        // Template names should be alphanumeric with dashes/underscores
        return preg_match('/^[a-z0-9_\-]+$/', $template_name) === 1;
    }
    
    /**
     * Validate section name
     */
    public static function section_name($section_name) {
        if (empty($section_name) || !is_string($section_name)) {
            return false;
        }
        
        $valid_sections = array(
            'purchases',
            'downloads', 
            'licenses',
            'wishlist',
            'analytics',
            'support',
            'profile',
            'billing'
        );
        
        return in_array($section_name, $valid_sections, true);
    }
    
    /**
     * Validate currency amount
     */
    public static function currency_amount($amount) {
        if (is_null($amount) || $amount === '') {
            return false;
        }
        
        // Convert to float and check if it's numeric
        $amount = floatval($amount);
        return is_numeric($amount) && $amount >= 0;
    }
    
    /**
     * Validate date string
     */
    public static function date($date) {
        if (empty($date) || !is_string($date)) {
            return false;
        }
        
        return strtotime($date) !== false;
    }
    
    /**
     * Validate WordPress nonce
     */
    public static function nonce($nonce, $action) {
        return wp_verify_nonce($nonce, $action) !== false;
    }
    
    /**
     * Validate user capability
     */
    public static function user_capability($capability, $user_id = null) {
        if ($user_id !== null) {
            return user_can($user_id, $capability);
        }
        
        return current_user_can($capability);
    }
    
    /**
     * Validate file type
     */
    public static function file_type($file_path, $allowed_types = array()) {
        if (!file_exists($file_path)) {
            return false;
        }
        
        if (empty($allowed_types)) {
            $allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'zip');
        }
        
        $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        return in_array($file_extension, $allowed_types, true);
    }
    
    /**
     * Validate file size
     */
    public static function file_size($file_path, $max_size_mb = 10) {
        if (!file_exists($file_path)) {
            return false;
        }
        
        $file_size = filesize($file_path);
        $max_size_bytes = $max_size_mb * 1024 * 1024;
        
        return $file_size <= $max_size_bytes;
    }
    
    /**
     * Validate array structure
     */
    public static function array_structure($array, $required_keys = array()) {
        if (!is_array($array)) {
            return false;
        }
        
        foreach ($required_keys as $key) {
            if (!array_key_exists($key, $array)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validate JSON string
     */
    public static function json($json_string) {
        if (!is_string($json_string)) {
            return false;
        }
        
        json_decode($json_string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    /**
     * Validate password strength
     */
    public static function password_strength($password, $min_length = 8) {
        if (strlen($password) < $min_length) {
            return false;
        }
        
        // Check for at least one uppercase, one lowercase, and one number
        $has_uppercase = preg_match('/[A-Z]/', $password);
        $has_lowercase = preg_match('/[a-z]/', $password);
        $has_number = preg_match('/[0-9]/', $password);
        
        return $has_uppercase && $has_lowercase && $has_number;
    }
    
    /**
     * Validate phone number (basic)
     */
    public static function phone_number($phone) {
        if (empty($phone) || !is_string($phone)) {
            return false;
        }
        
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if it's between 10-15 digits
        return strlen($cleaned) >= 10 && strlen($cleaned) <= 15;
    }
    
    /**
     * Validate IP address
     */
    public static function ip_address($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }
    
    /**
     * Validate color hex code
     */
    public static function hex_color($hex) {
        return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $hex) === 1;
    }
    
    /**
     * Sanitize and validate settings array
     */
    public static function settings_array($settings) {
        if (!is_array($settings)) {
            return array();
        }
        
        $sanitized = array();
        
        // Active template
        if (isset($settings['active_template'])) {
            $template = sanitize_text_field($settings['active_template']);
            if (self::template_name($template)) {
                $sanitized['active_template'] = $template;
            }
        }
        
        // Boolean settings
        $boolean_settings = array('replace_edd_pages', 'fullscreen_mode');
        foreach ($boolean_settings as $setting) {
            $sanitized[$setting] = isset($settings[$setting]) ? (bool) $settings[$setting] : false;
        }
        
        // Enabled sections
        if (isset($settings['enabled_sections']) && is_array($settings['enabled_sections'])) {
            $sanitized['enabled_sections'] = array();
            foreach ($settings['enabled_sections'] as $section => $enabled) {
                if (self::section_name($section)) {
                    $sanitized['enabled_sections'][$section] = (bool) $enabled;
                }
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Validate shortcode attributes
     */
    public static function shortcode_atts($atts) {
        if (!is_array($atts)) {
            return array();
        }
        
        $valid_atts = array();
        
        // Template name
        if (isset($atts['template']) && self::template_name($atts['template'])) {
            $valid_atts['template'] = $atts['template'];
        }
        
        // User ID
        if (isset($atts['user_id'])) {
            $user_id = absint($atts['user_id']);
            if ($user_id > 0) {
                $valid_atts['user_id'] = $user_id;
            }
        }
        
        // Boolean attributes
        $boolean_atts = array('show_stats', 'show_navigation');
        foreach ($boolean_atts as $att) {
            if (isset($atts[$att])) {
                $valid_atts[$att] = in_array(strtolower($atts[$att]), array('yes', 'true', '1'), true) ? 'yes' : 'no';
            }
        }
        
        // Sections (comma-separated list)
        if (isset($atts['sections'])) {
            $sections = array_map('trim', explode(',', $atts['sections']));
            $valid_sections = array();
            
            foreach ($sections as $section) {
                if (self::section_name($section)) {
                    $valid_sections[] = $section;
                }
            }
            
            if (!empty($valid_sections)) {
                $valid_atts['sections'] = implode(',', $valid_sections);
            }
        }
        
        return $valid_atts;
    }
}