<?php
/**
 * Cache Helper Utility Class
 * 
 * Handles caching of expensive operations and data
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Cache_Helper {
    
    /**
     * Cache group for plugin data
     */
    const CACHE_GROUP = 'eddcdp';
    
    /**
     * Default cache expiration (1 hour)
     */
    const DEFAULT_EXPIRATION = 3600;
    
    /**
     * Get cached data
     */
    public static function get($key, $group = self::CACHE_GROUP) {
        return wp_cache_get(self::build_key($key), $group);
    }
    
    /**
     * Set cached data
     */
    public static function set($key, $data, $expiration = self::DEFAULT_EXPIRATION, $group = self::CACHE_GROUP) {
        return wp_cache_set(self::build_key($key), $data, $group, $expiration);
    }
    
    /**
     * Delete cached data
     */
    public static function delete($key, $group = self::CACHE_GROUP) {
        return wp_cache_delete(self::build_key($key), $group);
    }
    
    /**
     * Get or set cached data with callback
     */
    public static function remember($key, $callback, $expiration = self::DEFAULT_EXPIRATION, $group = self::CACHE_GROUP) {
        $cached = self::get($key, $group);
        
        if ($cached !== false) {
            return $cached;
        }
        
        $data = call_user_func($callback);
        self::set($key, $data, $expiration, $group);
        
        return $data;
    }
    
    /**
     * Cache customer analytics data
     */
    public static function cache_customer_analytics($customer_id, $analytics_data) {
        $key = "customer_analytics_{$customer_id}";
        return self::set($key, $analytics_data, 1800); // 30 minutes
    }
    
    /**
     * Get cached customer analytics
     */
    public static function get_customer_analytics($customer_id) {
        $key = "customer_analytics_{$customer_id}";
        return self::get($key);
    }
    
    /**
     * Cache customer purchases
     */
    public static function cache_customer_purchases($customer_id, $purchases) {
        $key = "customer_purchases_{$customer_id}";
        return self::set($key, $purchases, 900); // 15 minutes
    }
    
    /**
     * Get cached customer purchases
     */
    public static function get_customer_purchases($customer_id) {
        $key = "customer_purchases_{$customer_id}";
        return self::get($key);
    }
    
    /**
     * Cache license data
     */
    public static function cache_license_data($user_id, $licenses) {
        $key = "user_licenses_{$user_id}";
        return self::set($key, $licenses, 600); // 10 minutes
    }
    
    /**
     * Get cached license data
     */
    public static function get_license_data($user_id) {
        $key = "user_licenses_{$user_id}";
        return self::get($key);
    }
    
    /**
     * Cache template data
     */
    public static function cache_template_data($template_name, $data) {
        $key = "template_data_{$template_name}";
        return self::set($key, $data, 7200); // 2 hours
    }
    
    /**
     * Get cached template data
     */
    public static function get_template_data($template_name) {
        $key = "template_data_{$template_name}";
        return self::get($key);
    }
    
    /**
     * Cache download statistics
     */
    public static function cache_download_stats($customer_id, $stats) {
        $key = "download_stats_{$customer_id}";
        return self::set($key, $stats, 1800); // 30 minutes
    }
    
    /**
     * Get cached download statistics
     */
    public static function get_download_stats($customer_id) {
        $key = "download_stats_{$customer_id}";
        return self::get($key);
    }
    
    /**
     * Invalidate customer-related cache
     */
    public static function invalidate_customer_cache($customer_id) {
        $keys_to_delete = array(
            "customer_analytics_{$customer_id}",
            "customer_purchases_{$customer_id}",
            "download_stats_{$customer_id}"
        );
        
        foreach ($keys_to_delete as $key) {
            self::delete($key);
        }
    }
    
    /**
     * Invalidate user license cache
     */
    public static function invalidate_license_cache($user_id) {
        $key = "user_licenses_{$user_id}";
        self::delete($key);
    }
    
    /**
     * Invalidate template cache
     */
    public static function invalidate_template_cache($template_name = null) {
        if ($template_name) {
            $key = "template_data_{$template_name}";
            self::delete($key);
        } else {
            // Clear all template cache
            self::flush_group('templates');
        }
    }
    
    /**
     * Cache payment data
     */
    public static function cache_payment_data($payment_key, $payment_data) {
        $key = "payment_data_{$payment_key}";
        return self::set($key, $payment_data, 3600); // 1 hour
    }
    
    /**
     * Get cached payment data
     */
    public static function get_payment_data($payment_key) {
        $key = "payment_data_{$payment_key}";
        return self::get($key);
    }
    
    /**
     * Cache wishlist data
     */
    public static function cache_wishlist_data($user_id, $wishlist) {
        $key = "user_wishlist_{$user_id}";
        return self::set($key, $wishlist, 1800); // 30 minutes
    }
    
    /**
     * Get cached wishlist data
     */
    public static function get_wishlist_data($user_id) {
        $key = "user_wishlist_{$user_id}";
        return self::get($key);
    }
    
    /**
     * Cache monthly spending data
     */
    public static function cache_monthly_spending($customer_id, $spending_data) {
        $key = "monthly_spending_{$customer_id}";
        return self::set($key, $spending_data, 7200); // 2 hours
    }
    
    /**
     * Get cached monthly spending data
     */
    public static function get_monthly_spending($customer_id) {
        $key = "monthly_spending_{$customer_id}";
        return self::get($key);
    }
    
    /**
     * Cache seasonal patterns
     */
    public static function cache_seasonal_patterns($customer_id, $patterns) {
        $key = "seasonal_patterns_{$customer_id}";
        return self::set($key, $patterns, 86400); // 24 hours
    }
    
    /**
     * Get cached seasonal patterns
     */
    public static function get_seasonal_patterns($customer_id) {
        $key = "seasonal_patterns_{$customer_id}";
        return self::get($key);
    }
    
    /**
     * Flush all plugin cache
     */
    public static function flush_all() {
        wp_cache_flush_group(self::CACHE_GROUP);
    }
    
    /**
     * Flush cache group
     */
    public static function flush_group($group = self::CACHE_GROUP) {
        wp_cache_flush_group($group);
    }
    
    /**
     * Build cache key with prefix
     */
    private static function build_key($key) {
        return 'eddcdp_' . $key;
    }
    
    /**
     * Get cache key for user data
     */
    public static function get_user_cache_key($user_id, $type) {
        return "user_{$user_id}_{$type}";
    }
    
    /**
     * Get cache key for template data
     */
    public static function get_template_cache_key($template_name, $type = 'data') {
        return "template_{$template_name}_{$type}";
    }
    
    /**
     * Auto-invalidate cache based on EDD hooks
     */
    public static function setup_auto_invalidation() {
        // Invalidate customer cache on new purchase
        add_action('edd_complete_purchase', function($payment_id) {
            $payment = edd_get_payment($payment_id);
            if ($payment && $payment->customer_id) {
                self::invalidate_customer_cache($payment->customer_id);
            }
        });
        
        // Invalidate license cache on license changes
        add_action('edd_sl_license_activated', function($license_id) {
            $license = edd_software_licensing()->get_license($license_id);
            if ($license && $license->user_id) {
                self::invalidate_license_cache($license->user_id);
            }
        });
        
        add_action('edd_sl_license_deactivated', function($license_id) {
            $license = edd_software_licensing()->get_license($license_id);
            if ($license && $license->user_id) {
                self::invalidate_license_cache($license->user_id);
            }
        });
        
        // Invalidate wishlist cache on changes
        if (class_exists('EDD_Wish_Lists')) {
            add_action('edd_wl_add_to_list', function($download_id, $list_id) {
                $list = get_post($list_id);
                if ($list && $list->post_author) {
                    $key = "user_wishlist_{$list->post_author}";
                    self::delete($key);
                }
            }, 10, 2);
            
            add_action('edd_wl_remove_from_list', function($download_id, $list_id) {
                $list = get_post($list_id);
                if ($list && $list->post_author) {
                    $key = "user_wishlist_{$list->post_author}";
                    self::delete($key);
                }
            }, 10, 2);
        }
        
        // Invalidate template cache on settings change
        add_action('update_option_eddcdp_settings', function() {
            self::flush_group('templates');
        });
    }
    
    /**
     * Get cache statistics
     */
    public static function get_cache_stats() {
        global $wp_object_cache;
        
        $stats = array(
            'cache_enabled' => wp_using_ext_object_cache(),
            'cache_type' => wp_using_ext_object_cache() ? 'persistent' : 'non-persistent',
            'cache_hits' => 0,
            'cache_misses' => 0
        );
        
        // Get cache stats if available
        if (method_exists($wp_object_cache, 'stats')) {
            $cache_stats = $wp_object_cache->stats();
            if (isset($cache_stats['cache_hits'])) {
                $stats['cache_hits'] = $cache_stats['cache_hits'];
            }
            if (isset($cache_stats['cache_misses'])) {
                $stats['cache_misses'] = $cache_stats['cache_misses'];
            }
        }
        
        return $stats;
    }
    
    /**
     * Warm up cache for user
     */
    public static function warmup_user_cache($user_id) {
        $customer = edd_get_customer_by('user_id', $user_id);
        
        if (!$customer) {
            return false;
        }
        
        // Warm up customer analytics
        if (class_exists('EDDCDP_Analytics_Data')) {
            $analytics = new EDDCDP_Analytics_Data();
            $analytics_data = $analytics->get_customer_analytics($customer);
            self::cache_customer_analytics($customer->id, $analytics_data);
        }
        
        // Warm up customer purchases
        if (class_exists('EDDCDP_Customer_Data')) {
            $customer_data = new EDDCDP_Customer_Data();
            $purchases = $customer_data->get_customer_purchases($customer);
            self::cache_customer_purchases($customer->id, $purchases);
        }
        
        // Warm up license data
        if (class_exists('EDDCDP_License_Data')) {
            $license_data = new EDDCDP_License_Data();
            $licenses = $license_data->get_customer_licenses($user_id);
            self::cache_license_data($user_id, $licenses);
        }
        
        return true;
    }
    
    /**
     * Schedule cache cleanup
     */
    public static function schedule_cleanup() {
        if (!wp_next_scheduled('eddcdp_cache_cleanup')) {
            wp_schedule_event(time(), 'daily', 'eddcdp_cache_cleanup');
        }
        
        add_action('eddcdp_cache_cleanup', function() {
            // Flush expired cache data
            self::flush_all();
            
            // Log cleanup
            error_log('EDDCDP: Daily cache cleanup completed');
        });
    }
    
    /**
     * Unschedule cache cleanup
     */
    public static function unschedule_cleanup() {
        wp_clear_scheduled_hook('eddcdp_cache_cleanup');
    }
}