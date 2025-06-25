<?php
/**
 * Wishlist Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Wishlist_Handler {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Hook early to catch form submissions before any output
        add_action('wp', array($this, 'handle_wishlist_actions'), 1);
    }
    
    /**
     * Handle wishlist actions - called early on wp hook
     */
    public function handle_wishlist_actions() {
        // Only process if we're on the right page and user is logged in
        if (!is_user_logged_in()) {
            return;
        }
        
        // Handle wishlist item removal - EXACT copy from working template code
        if (isset($_POST['eddcdp_action']) && $_POST['eddcdp_action'] === 'remove_from_wishlist') {
            if (wp_verify_nonce($_POST['eddcdp_nonce'], 'eddcdp_remove_wishlist')) {
                $download_id = intval($_POST['download_id']);
                $price_id = isset($_POST['price_id']) ? intval($_POST['price_id']) : null;
                
                if ($download_id && function_exists('edd_remove_from_wish_list')) {
                    // Get all user's wish lists
                    $private_lists = edd_wl_get_query('private');
                    $public_lists = edd_wl_get_query('public');
                    $all_lists = array_merge((array)$private_lists, (array)$public_lists);
                    
                    $removed = false;
                    foreach ($all_lists as $list_id) {
                        $wish_list = get_post_meta($list_id, 'edd_wish_list', true);
                        if (!empty($wish_list) && is_array($wish_list)) {
                            foreach ($wish_list as $key => $item) {
                                // Match by download ID and price ID if applicable
                                $item_matches = ($item['id'] == $download_id);
                                if ($price_id !== null) {
                                    $item_matches = $item_matches && (
                                        isset($item['options']['price_id']) && 
                                        $item['options']['price_id'] == $price_id
                                    );
                                }
                                
                                if ($item_matches) {
                                    edd_remove_from_wish_list($key, $list_id);
                                    $removed = true;
                                    break 2; // Break out of both loops
                                }
                            }
                        }
                    }
                    
                    if ($removed) {
                        // Redirect to prevent form resubmission
                        wp_redirect(remove_query_arg(array('eddcdp_action', 'download_id', 'price_id', 'eddcdp_nonce')));
                        exit;
                    }
                }
            }
        }
    }
    
    /**
     * Get all wishlist items for current user
     */
    public function get_user_wishlist_items() {
        if (!is_user_logged_in() || !function_exists('edd_wl_get_query')) {
            return array();
        }
        
        // Get wishlist data using the actual EDD Wish Lists approach
        $private = edd_wl_get_query('private');
        $public = edd_wl_get_query('public');
        
        // Collect all wishlist items from all lists
        $all_wishlist_items = array();
        
        // Get items from private lists
        if ($private) {
            foreach ($private as $list_id) {
                $items = get_post_meta($list_id, 'edd_wish_list', true);
                if (!empty($items) && is_array($items)) {
                    $all_wishlist_items = array_merge($all_wishlist_items, $items);
                }
            }
        }
        
        // Get items from public lists
        if ($public) {
            foreach ($public as $list_id) {
                $items = get_post_meta($list_id, 'edd_wish_list', true);
                if (!empty($items) && is_array($items)) {
                    $all_wishlist_items = array_merge($all_wishlist_items, $items);
                }
            }
        }
        
        // Remove duplicates
        return $this->remove_duplicate_items($all_wishlist_items);
    }
    
    /**
     * Remove duplicate items from wishlist
     */
    private function remove_duplicate_items($items) {
        $unique_items = array();
        $seen_items = array();
        
        foreach ($items as $item) {
            $item_key = $item['id'];
            if (isset($item['options']['price_id'])) {
                $item_key .= '_' . $item['options']['price_id'];
            }
            
            if (!in_array($item_key, $seen_items)) {
                $unique_items[] = $item;
                $seen_items[] = $item_key;
            }
        }
        
        return $unique_items;
    }
    
    /**
     * Get public wishlist URLs for sharing
     */
    public function get_public_wishlist_urls() {
        if (!is_user_logged_in() || !function_exists('edd_wl_get_query')) {
            return array();
        }
        
        $public = edd_wl_get_query('public');
        $public_wishlist_urls = array();
        
        if ($public) {
            foreach ($public as $list_id) {
                $public_wishlist_urls[] = array(
                    'title' => get_the_title($list_id),
                    'url' => edd_wl_get_wish_list_view_uri($list_id),
                    'item_count' => edd_wl_get_item_count($list_id)
                );
            }
        }
        
        return $public_wishlist_urls;
    }
}