<?php
/**
 * Component Manager
 * 
 * Handles loading, dependency injection, and lifecycle of all plugin components
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Component_Manager {
    
    private $components = array();
    private $loaded_components = array();
    private $component_instances = array();
    
    /**
     * Register a component
     */
    public function register($component_class) {
        if (!class_exists($component_class)) {
            return false;
        }
        
        // Create instance to check interface
        $reflection = new ReflectionClass($component_class);
        if (!$reflection->implementsInterface('EDDCDP_Component_Interface')) {
            return false;
        }
        
        $instance = new $component_class();
        
        // Only register if component should load
        if (!$instance->should_load()) {
            return false;
        }
        
        $this->components[$component_class] = array(
            'class' => $component_class,
            'instance' => $instance,
            'dependencies' => $instance->get_dependencies(),
            'priority' => $instance->get_priority(),
            'loaded' => false
        );
        
        return true;
    }
    
    /**
     * Load all registered components
     */
    public function load_components() {
        // Sort by priority
        uasort($this->components, function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
        
        foreach ($this->components as $component_class => $component_data) {
            $this->load_component($component_class);
        }
    }
    
    /**
     * Load a specific component and its dependencies
     */
    private function load_component($component_class) {
        // Skip if already loaded
        if (isset($this->loaded_components[$component_class])) {
            return $this->loaded_components[$component_class];
        }
        
        // Check if component is registered
        if (!isset($this->components[$component_class])) {
            return false;
        }
        
        $component_data = $this->components[$component_class];
        
        // Load dependencies first
        foreach ($component_data['dependencies'] as $dependency) {
            $this->load_component($dependency);
        }
        
        // Initialize the component
        $instance = $component_data['instance'];
        $instance->init();
        
        // Mark as loaded
        $this->loaded_components[$component_class] = $instance;
        $this->component_instances[$component_class] = $instance;
        
        return $instance;
    }
    
    /**
     * Get a loaded component instance
     */
    public function get_component($component_class) {
        return isset($this->component_instances[$component_class]) 
            ? $this->component_instances[$component_class] 
            : null;
    }
    
    /**
     * Check if component is loaded
     */
    public function is_loaded($component_class) {
        return isset($this->loaded_components[$component_class]);
    }
    
    /**
     * Get all loaded components
     */
    public function get_loaded_components() {
        return $this->loaded_components;
    }
    
    /**
     * Auto-register components from directory
     */
    public function auto_register_from_directory($directory) {
        if (!is_dir($directory)) {
            return;
        }
        
        $files = glob($directory . '/class-*.php');
        
        foreach ($files as $file) {
            $class_name = $this->get_class_name_from_file($file);
            if ($class_name) {
                require_once $file;
                $this->register($class_name);
            }
        }
    }
    
    /**
     * Extract class name from file path
     */
    private function get_class_name_from_file($file) {
        $filename = basename($file, '.php');
        
        // Convert class-name-format to Class_Name_Format
        $parts = explode('-', str_replace('class-', '', $filename));
        $parts = array_map('ucfirst', $parts);
        
        return 'EDDCDP_' . implode('_', $parts);
    }
}