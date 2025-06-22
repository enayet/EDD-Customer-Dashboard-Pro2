<?php
/**
 * EDD Wish Lists Integration
 * 
 * Handles all EDD Wish Lists functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Wishlist_Integration {
    
    private $wishlist_api;
    
    public function __construct() {
        if (class_exists('EDD_Wish_Lists')) {
            $this->wishlist_api = edd_wish_lists();
        }
    }
    
    /**
     * Get wishlist count for user
     */
    public function get_wishlist_count($user_id) {
        if (!$this->wishlist_api || !function_exists('edd_wl_get_wish_lists')) {
            return 0;
        }
        
        try {
            $wish_lists = edd_wl_get_wish_lists(array('user_id' => $user_id));
            $count = 0;
            
            if ($wish_lists) {
                foreach ($wish_lists as $list) {
                    $items = $this->get_list_items($list->ID);
                    $count += count($items);
                }
            }
            
            return $count;
            
        } catch (Exception $e) {
            error_log('EDDCDP: Error getting wishlist count: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get customer wishlist items
     */
    public function get_customer_wishlist($user_id) {
        if (!$this->wishlist_api || !function_exists('edd_wl_get_wish_lists')) {
            return array();
        }
        
        try {
            $wish_lists = edd_wl_get_wish_lists(array('user_id' => $user_id));
            $all_items = array();
            
            if ($wish_lists) {
                foreach ($wish_lists as $list) {
                    $items = $this->get_list_items($list->ID);
                    
                    // Add list information to each item
                    foreach ($items as $item) {
                        $item->list_id = $list->ID;
                        $item->list_title = $list->post_title;
                        $all_items[] = $item;
                    }
                }
            }
            
            return $all_items;
            
        } catch (Exception $e) {
            error_log('EDDCDP: Error getting customer wishlist: ' . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Get wish list items
     */
    private function get_list_items($list_id) {
        if (function_exists('edd_wl_get_list_items')) {
            return edd_wl_get_list_items($list_id);
        }
        
        // Fallback: Get items directly from post meta
        $items = get_post_meta($list_id, 'edd_wish_list_item', false);
        $item_objects = array();
        
        foreach ($items as $item) {
            if (is_array($item) && isset($item['download_id'])) {
                $item_object = (object) array(
                    'download_id' => $item['download_id'],
                    'list_id' => $list_id,
                    'date_added' => isset($item['date_added']) ? $item['date_added'] : current_time('mysql')
                );
                $item_objects[] = $item_object;
            }
        }
        
        return $item_objects;
    }
    
    /**
     * Get user wish lists
     */
    public function get_user_wish_lists($user_id) {
        if (!function_exists('edd_wl_get_wish_lists')) {
            return array();
        }
        
        try {
            return edd_wl_get_wish_lists(array('user_id' => $user_id));
        } catch (Exception $e) {
            error_log('EDDCDP: Error getting user wish lists: ' . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Add item to wishlist
     */
    public function add_to_wishlist($user_id, $download_id, $list_id = null) {
        if (!function_exists('edd_wl_add_to_list')) {
            return false;
        }
        
        // If no list ID provided, get or create default list
        if (!$list_id) {
            $list_id = $this->get_or_create_default_list($user_id);
        }
        
        if (!$list_id) {
            return false;
        }
        
        try {
            return edd_wl_add_to_list($download_id, $list_id);
        } catch (Exception $e) {
            error_log('EDDCDP: Error adding to wishlist: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove item from wishlist
     */
    public function remove_from_wishlist($download_id, $list_id) {
        if (!function_exists('edd_wl_remove_from_list')) {
            return false;
        }
        
        try {
            return edd_wl_remove_from_list($download_id, $list_id);
        } catch (Exception $e) {
            error_log('EDDCDP: Error removing from wishlist: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get or create default wishlist for user
     */
    private function get_or_create_default_list($user_id) {
        $lists = $this->get_user_wish_lists($user_id);
        
        // If user has lists, return the first one
        if (!empty($lists)) {
            return $lists[0]->ID;
        }
        
        // Create default list
        if (function_exists('edd_wl_create_list')) {
            $list_id = edd_wl_create_list(array(
                'user_id' => $user_id,
                'title' => __('My Wishlist', 'edd-customer-dashboard-pro'),
                'status' => 'private'
            ));
            
            return $list_id;
        }
        
        return false;
    }
    
    /**
     * Check if download is in user's wishlist
     */
    public function is_in_wishlist($user_id, $download_id) {
        $wishlist_items = $this->get_customer_wishlist($user_id);
        
        foreach ($wishlist_items as $item) {
            if ($item->download_id == $download_id) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get wishlist settings
     */
    public function get_wishlist_settings() {
        if (!$this->wishlist_api) {
            return array();
        }
        
        return array(
            'enabled' => true,
            'allow_guest_lists' => edd_get_option('edd_wl_allow_guest_lists', false),
            'require_login' => edd_get_option('edd_wl_require_login', true),
            'show_on_checkout' => edd_get_option('edd_wl_show_on_checkout', false)
        );
    }
}