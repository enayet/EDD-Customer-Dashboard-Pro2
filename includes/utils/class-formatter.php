<?php
/**
 * Formatter Utility Class
 * 
 * Handles currency, date, and other formatting
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Formatter {
    
    /**
     * Format currency using EDD settings
     */
    public static function currency($amount) {
        // Sanitize amount
        $amount = self::sanitize_amount($amount);
        
        // Use EDD's built-in currency formatting
        return edd_currency_filter(edd_format_amount($amount));
    }
    
    /**
     * Format date using WordPress settings
     */
    public static function date($date, $format = null) {
        if (empty($date)) {
            return '';
        }
        
        if ($format === null) {
            $format = get_option('date_format');
        }
        
        return date_i18n($format, strtotime($date));
    }
    
    /**
     * Format datetime using WordPress settings
     */
    public static function datetime($datetime, $date_format = null, $time_format = null) {
        if (empty($datetime)) {
            return '';
        }
        
        if ($date_format === null) {
            $date_format = get_option('date_format');
        }
        
        if ($time_format === null) {
            $time_format = get_option('time_format');
        }
        
        $format = $date_format . ' ' . $time_format;
        return date_i18n($format, strtotime($datetime));
    }
    
    /**
     * Format file size
     */
    public static function file_size($bytes, $decimals = 2) {
        $size = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
    }
    
    /**
     * Format number with thousands separator
     */
    public static function number($number, $decimals = 0) {
        return number_format_i18n($number, $decimals);
    }
    
    /**
     * Format percentage
     */
    public static function percentage($value, $total, $decimals = 2) {
        if ($total == 0) {
            return '0%';
        }
        
        $percentage = ($value / $total) * 100;
        return number_format($percentage, $decimals) . '%';
    }
    
    /**
     * Format time duration
     */
    public static function duration($seconds) {
        $units = array(
            'year'   => 31536000,
            'month'  => 2592000,
            'week'   => 604800,
            'day'    => 86400,
            'hour'   => 3600,
            'minute' => 60,
            'second' => 1
        );
        
        foreach ($units as $name => $divisor) {
            if ($seconds >= $divisor) {
                $value = intval($seconds / $divisor);
                $seconds %= $divisor;
                
                if ($value == 1) {
                    return $value . ' ' . $name;
                } else {
                    return $value . ' ' . $name . 's';
                }
            }
        }
        
        return '0 seconds';
    }
    
    /**
     * Format days remaining
     */
    public static function days_remaining($expiration_date) {
        if (empty($expiration_date) || $expiration_date === '0000-00-00 00:00:00') {
            return __('Never expires', 'edd-customer-dashboard-pro');
        }
        
        $expiration_timestamp = strtotime($expiration_date);
        $current_timestamp = time();
        $days_remaining = ceil(($expiration_timestamp - $current_timestamp) / DAY_IN_SECONDS);
        
        if ($days_remaining < 0) {
            return sprintf(__('Expired %d days ago', 'edd-customer-dashboard-pro'), abs($days_remaining));
        } elseif ($days_remaining == 0) {
            return __('Expires today', 'edd-customer-dashboard-pro');
        } elseif ($days_remaining == 1) {
            return __('Expires tomorrow', 'edd-customer-dashboard-pro');
        } else {
            return sprintf(__('Expires in %d days', 'edd-customer-dashboard-pro'), $days_remaining);
        }
    }
    
    /**
     * Sanitize amount value
     */
    private static function sanitize_amount($amount) {
        // Handle arrays
        if (is_array($amount)) {
            $amount_keys = array('amount', 'price', 'item_price', 'total');
            
            foreach ($amount_keys as $key) {
                if (isset($amount[$key]) && is_numeric($amount[$key])) {
                    return floatval($amount[$key]);
                }
            }
            
            return 0;
        }
        
        // Handle null or non-numeric values
        if (is_null($amount) || $amount === '' || $amount === false) {
            return 0;
        }
        
        return is_numeric($amount) ? floatval($amount) : 0;
    }
    
    /**
     * Truncate text with ellipsis
     */
    public static function truncate($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . $suffix;
    }
    
    /**
     * Format license key for display
     */
    public static function license_key($key, $show_full = false) {
        if ($show_full) {
            return $key;
        }
        
        // Show first 8 and last 4 characters
        $length = strlen($key);
        if ($length <= 12) {
            return $key; // Too short to hide
        }
        
        return substr($key, 0, 8) . str_repeat('*', $length - 12) . substr($key, -4);
    }
    
    /**
     * Format user initials for avatar
     */
    public static function user_initials($name) {
        $names = explode(' ', trim($name));
        $initials = '';
        
        foreach ($names as $n) {
            if (!empty($n)) {
                $initials .= strtoupper(substr($n, 0, 1));
            }
        }
        
        return substr($initials, 0, 2);
    }
}