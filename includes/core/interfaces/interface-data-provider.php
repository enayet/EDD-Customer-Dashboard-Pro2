<?php
/**
 * Data Provider Interface
 * 
 * Defines the contract for data provider components
 */

if (!defined('ABSPATH')) {
    exit;
}

interface EDDCDP_Data_Provider_Interface {
    
    /**
     * Get data by key
     * 
     * @param string $key Data key
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public function get($key, $default = null);
    
    /**
     * Set data by key
     * 
     * @param string $key Data key
     * @param mixed $value Data value
     * @return bool Success
     */
    public function set($key, $value);
    
    /**
     * Check if data exists
     * 
     * @param string $key Data key
     * @return bool
     */
    public function has($key);
    
    /**
     * Remove data by key
     * 
     * @param string $key Data key
     * @return bool Success
     */
    public function remove($key);
    
    /**
     * Get all data
     * 
     * @return array
     */
    public function get_all();
    
    /**
     * Clear all data
     * 
     * @return bool Success
     */
    public function clear();
}