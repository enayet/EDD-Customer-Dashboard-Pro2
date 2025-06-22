<?php
/**
 * Component Interface
 * 
 * Defines the contract that all plugin components must follow
 */

if (!defined('ABSPATH')) {
    exit;
}

interface EDDCDP_Component_Interface {
    
    /**
     * Initialize the component
     * 
     * @return void
     */
    public function init();
    
    /**
     * Get component dependencies
     * 
     * @return array Array of required component class names
     */
    public function get_dependencies();
    
    /**
     * Check if component should load
     * 
     * @return bool
     */
    public function should_load();
    
    /**
     * Get component priority (for loading order)
     * 
     * @return int Lower numbers load first
     */
    public function get_priority();
}