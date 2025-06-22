<?php
/**
 * Analytics Data Component
 * 
 * Handles customer analytics and reporting data
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Analytics_Data implements EDDCDP_Component_Interface {
    
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
        return array('EDDCDP_Customer_Data');
    }
    
    /**
     * Check if component should load
     */
    public function should_load() {
        return true;
    }
    
    /**
     * Get component priority
     */
    public function get_priority() {
        return 30;
    }
    
    /**
     * Get customer analytics overview
     */
    public function get_customer_analytics($customer) {
        if (!$customer || !$customer->id) {
            return $this->get_empty_analytics();
        }
        
        $total_spent = $customer->purchase_value;
        $purchase_count = $customer->purchase_count;
        $avg_per_order = $purchase_count > 0 ? $total_spent / $purchase_count : 0;
        
        return array(
            'total_spent' => $total_spent,
            'purchase_count' => $purchase_count,
            'avg_per_order' => $avg_per_order,
            'first_purchase' => $this->get_first_purchase_date($customer->id),
            'last_purchase' => $this->get_last_purchase_date($customer->id),
            'downloads_count' => $this->get_total_downloads_count($customer),
            'lifetime_days' => $this->get_customer_lifetime_days($customer->id),
            'monthly_spending' => $this->get_monthly_spending_data($customer->id),
            'top_categories' => $this->get_top_purchase_categories($customer->id),
            'purchase_frequency' => $this->calculate_purchase_frequency($customer->id)
        );
    }
    
    /**
     * Get monthly spending data for charts
     */
    public function get_monthly_spending_data($customer_id, $months = 12) {
        $payments = edd_get_payments(array(
            'customer' => $customer_id,
            'status' => 'complete',
            'number' => 9999,
            'meta_query' => array(
                array(
                    'key' => '_edd_payment_total',
                    'compare' => 'EXISTS'
                )
            )
        ));
        
        $monthly_data = array();
        $end_date = time();
        $start_date = strtotime("-{$months} months", $end_date);
        
        // Initialize months with zero
        for ($i = 0; $i < $months; $i++) {
            $month_timestamp = strtotime("-{$i} months", $end_date);
            $month_key = date('Y-m', $month_timestamp);
            $monthly_data[$month_key] = array(
                'month' => date('M Y', $month_timestamp),
                'amount' => 0,
                'count' => 0
            );
        }
        
        // Add actual data
        foreach ($payments as $payment) {
            $payment_date = strtotime($payment->date);
            
            if ($payment_date >= $start_date) {
                $month_key = date('Y-m', $payment_date);
                
                if (isset($monthly_data[$month_key])) {
                    $monthly_data[$month_key]['amount'] += $payment->total;
                    $monthly_data[$month_key]['count']++;
                }
            }
        }
        
        return array_reverse($monthly_data, true);
    }
    
    /**
     * Get top purchase categories
     */
    public function get_top_purchase_categories($customer_id, $limit = 5) {
        $payments = edd_get_payments(array(
            'customer' => $customer_id,
            'status' => 'complete',
            'number' => 9999
        ));
        
        $categories = array();
        
        foreach ($payments as $payment) {
            $cart_details = edd_get_payment_meta_cart_details($payment->ID);
            
            if (!empty($cart_details) && is_array($cart_details)) {
                foreach ($cart_details as $item) {
                    $download_categories = get_the_terms($item['id'], 'download_category');
                    
                    if ($download_categories && !is_wp_error($download_categories)) {
                        foreach ($download_categories as $category) {
                            if (!isset($categories[$category->term_id])) {
                                $categories[$category->term_id] = array(
                                    'name' => $category->name,
                                    'count' => 0,
                                    'amount' => 0
                                );
                            }
                            
                            $categories[$category->term_id]['count']++;
                            $categories[$category->term_id]['amount'] += isset($item['item_price']) ? $item['item_price'] : 0;
                        }
                    }
                }
            }
        }
        
        // Sort by purchase count
        uasort($categories, function($a, $b) {
            return $b['count'] - $a['count'];
        });
        
        return array_slice($categories, 0, $limit, true);
    }
    
    /**
     * Calculate purchase frequency (days between purchases)
     */
    public function calculate_purchase_frequency($customer_id) {
        $payments = edd_get_payments(array(
            'customer' => $customer_id,
            'status' => 'complete',
            'number' => 9999,
            'orderby' => 'date',
            'order' => 'ASC'
        ));
        
        if (count($payments) < 2) {
            return 0; // Need at least 2 purchases to calculate frequency
        }
        
        $dates = array();
        foreach ($payments as $payment) {
            $dates[] = strtotime($payment->date);
        }
        
        $total_days = 0;
        $intervals = 0;
        
        for ($i = 1; $i < count($dates); $i++) {
            $total_days += ($dates[$i] - $dates[$i-1]) / DAY_IN_SECONDS;
            $intervals++;
        }
        
        return $intervals > 0 ? round($total_days / $intervals) : 0;
    }
    
    /**
     * Get customer lifetime value projection
     */
    public function get_lifetime_value_projection($customer_id) {
        $analytics = $this->get_customer_analytics(edd_get_customer($customer_id));
        
        if ($analytics['purchase_frequency'] == 0 || $analytics['avg_per_order'] == 0) {
            return 0;
        }
        
        // Simple projection: average order value * estimated purchases per year
        $purchases_per_year = 365 / max($analytics['purchase_frequency'], 1);
        $projected_annual_value = $analytics['avg_per_order'] * $purchases_per_year;
        
        return $projected_annual_value;
    }
    
    /**
     * Get download statistics
     */
    public function get_download_statistics($customer_id) {
        $payments = edd_get_payments(array(
            'customer' => $customer_id,
            'status' => 'complete',
            'number' => 9999
        ));
        
        $total_downloads = 0;
        $download_types = array();
        
        foreach ($payments as $payment) {
            $downloads = edd_get_payment_meta_downloads($payment->ID);
            
            if ($downloads) {
                foreach ($downloads as $download) {
                    $total_downloads++;
                    
                    // Get download type/category
                    $categories = get_the_terms($download['id'], 'download_category');
                    $category_name = __('Uncategorized', 'edd-customer-dashboard-pro');
                    
                    if ($categories && !is_wp_error($categories)) {
                        $category_name = $categories[0]->name;
                    }
                    
                    if (!isset($download_types[$category_name])) {
                        $download_types[$category_name] = 0;
                    }
                    $download_types[$category_name]++;
                }
            }
        }
        
        return array(
            'total_downloads' => $total_downloads,
            'download_types' => $download_types,
            'avg_downloads_per_order' => count($payments) > 0 ? $total_downloads / count($payments) : 0
        );
    }
    
    /**
     * Get seasonal spending patterns
     */
    public function get_seasonal_patterns($customer_id) {
        $payments = edd_get_payments(array(
            'customer' => $customer_id,
            'status' => 'complete',
            'number' => 9999
        ));
        
        $seasonal_data = array(
            'spring' => array('amount' => 0, 'count' => 0, 'months' => array(3, 4, 5)),
            'summer' => array('amount' => 0, 'count' => 0, 'months' => array(6, 7, 8)),
            'fall' => array('amount' => 0, 'count' => 0, 'months' => array(9, 10, 11)),
            'winter' => array('amount' => 0, 'count' => 0, 'months' => array(12, 1, 2))
        );
        
        foreach ($payments as $payment) {
            $month = intval(date('n', strtotime($payment->date)));
            
            foreach ($seasonal_data as $season => $data) {
                if (in_array($month, $data['months'])) {
                    $seasonal_data[$season]['amount'] += $payment->total;
                    $seasonal_data[$season]['count']++;
                    break;
                }
            }
        }
        
        return $seasonal_data;
    }
    
    /**
     * Get first purchase date
     */
    private function get_first_purchase_date($customer_id) {
        $payments = edd_get_payments(array(
            'customer' => $customer_id,
            'status' => 'complete',
            'number' => 1,
            'orderby' => 'date',
            'order' => 'ASC'
        ));
        
        return $payments ? $payments[0]->date : null;
    }
    
    /**
     * Get last purchase date
     */
    private function get_last_purchase_date($customer_id) {
        $payments = edd_get_payments(array(
            'customer' => $customer_id,
            'status' => 'complete',
            'number' => 1,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        return $payments ? $payments[0]->date : null;
    }
    
    /**
     * Get total downloads count
     */
    private function get_total_downloads_count($customer) {
        $payments = edd_get_payments(array(
            'customer' => $customer->id,
            'status' => 'complete',
            'number' => 9999
        ));
        
        $count = 0;
        foreach ($payments as $payment) {
            $downloads = edd_get_payment_meta_downloads($payment->ID);
            if ($downloads) {
                $count += count($downloads);
            }
        }
        
        return $count;
    }
    
    /**
     * Get customer lifetime in days
     */
    private function get_customer_lifetime_days($customer_id) {
        $first_purchase = $this->get_first_purchase_date($customer_id);
        
        if (!$first_purchase) {
            return 0;
        }
        
        return round((time() - strtotime($first_purchase)) / DAY_IN_SECONDS);
    }
    
    /**
     * Get empty analytics array
     */
    private function get_empty_analytics() {
        return array(
            'total_spent' => 0,
            'purchase_count' => 0,
            'avg_per_order' => 0,
            'first_purchase' => null,
            'last_purchase' => null,
            'downloads_count' => 0,
            'lifetime_days' => 0,
            'monthly_spending' => array(),
            'top_categories' => array(),
            'purchase_frequency' => 0
        );
    }
}