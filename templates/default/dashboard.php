<?php
/**
 * Default Dashboard Template - Modern Design
 * 
 * Based on default 2.html with clean Alpine.js implementation
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ensure we have required data
$user = isset($user) ? $user : wp_get_current_user();
$customer = isset($customer) ? $customer : null;
$customer_stats = isset($customer_stats) ? $customer_stats : array();
$enabled_sections = isset($enabled_sections) ? $enabled_sections : array();
$view_mode = isset($view_mode) ? $view_mode : 'dashboard';

// Get customer data
if (!$customer && $user->ID) {
    $customer = edd_get_customer_by('user_id', $user->ID);
}

// Fallback stats
if (empty($customer_stats) && $customer) {
    $customer_stats = array(
        'total_purchases' => $customer->purchase_count,
        'total_spent' => $customer->purchase_value,
        'download_count' => 0,
        'active_licenses' => 0,
        'wishlist_count' => 0
    );
}

// Get customer purchases
$purchases = array();
if ($customer) {
    $purchases = edd_get_payments(array(
        'customer' => $customer->id,
        'status' => array('complete', 'revoked'),
        'number' => 20
    ));
}

// Currency formatting helper
function eddcdp_format_currency($amount) {
    return edd_currency_filter(edd_format_amount($amount));
}

// Date formatting helper
function eddcdp_format_date($date) {
    return date_i18n(get_option('date_format'), strtotime($date));
}
?>

<div class="eddcdp-dashboard-wrapper" x-data="eddcdpDashboard()">
    <style>
        .eddcdp-dashboard-wrapper {
            background: linear-gradient(135deg, #f0f4ff 0%, #ffffff 50%, #faf4ff 100%);
            min-height: 600px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        
        .eddcdp-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .eddcdp-header {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 0.6s ease-out;
        }
        
        .eddcdp-header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1.5rem;
        }
        
        .eddcdp-welcome h1 {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0 0 0.5rem 0;
        }
        
        .eddcdp-welcome p {
            color: #6b7280;
            font-size: 1.125rem;
            margin: 0;
        }
        
        .eddcdp-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
            flex-shrink: 0;
        }
        
        .eddcdp-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .eddcdp-stat-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            animation: fadeInUp 0.6s ease-out;
        }
        
        .eddcdp-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 48px rgba(0, 0, 0, 0.15);
        }
        
        .eddcdp-stat-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .eddcdp-stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }
        
        .eddcdp-stat-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .eddcdp-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            transition: transform 0.3s ease;
        }
        
        .eddcdp-stat-card:hover .eddcdp-stat-icon {
            transform: scale(1.1);
        }
        
        .eddcdp-nav-tabs {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .eddcdp-tabs-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        
        .eddcdp-tab {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            background: #f3f4f6;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .eddcdp-tab:hover {
            background: #e5e7eb;
            transform: translateY(-2px);
        }
        
        .eddcdp-tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
        }
        
        .eddcdp-content {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            min-height: 600px;
            overflow: hidden;
        }
        
        .eddcdp-tab-content {
            padding: 2rem;
            animation: fadeInUp 0.4s ease-out;
        }
        
        .eddcdp-section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .eddcdp-purchase-item {
            background: rgba(249, 250, 251, 0.8);
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid rgba(229, 231, 235, 0.5);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .eddcdp-purchase-item:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }
        
        .eddcdp-purchase-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .eddcdp-purchase-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        
        .eddcdp-purchase-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .eddcdp-status-badge {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .eddcdp-status-complete {
            background: #dcfce7;
            color: #15803d;
        }
        
        .eddcdp-btn {
            padding: 0.5rem 1rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .eddcdp-btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .eddcdp-btn-primary:hover {
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
            transform: translateY(-2px);
        }
        
        .eddcdp-btn-secondary {
            background: white;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }
        
        .eddcdp-btn-secondary:hover {
            background: #f9fafb;
        }
        
        .eddcdp-download-item {
            background: rgba(255, 255, 255, 0.6);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .eddcdp-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .eddcdp-coming-soon {
            text-align: center;
            padding: 3rem;
        }
        
        .eddcdp-coming-soon-icon {
            width: 96px;
            height: 96px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
        }
        
        .eddcdp-coming-soon h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.75rem;
        }
        
        .eddcdp-coming-soon p {
            color: #6b7280;
            margin-bottom: 1.5rem;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @media (max-width: 768px) {
            .eddcdp-container {
                padding: 1rem;
            }
            
            .eddcdp-header {
                padding: 1.5rem;
            }
            
            .eddcdp-welcome h1 {
                font-size: 2rem;
            }
            
            .eddcdp-header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .eddcdp-stats-grid {
                grid-template-columns: 1fr;
            }
            
            .eddcdp-purchase-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>

    <div class="eddcdp-container">
        <!-- Welcome Header -->
        <div class="eddcdp-header">
            <div class="eddcdp-header-content">
                <div class="eddcdp-welcome">
                    <h1><?php printf(__('Welcome back, %s! 👋', 'edd-customer-dashboard-pro'), esc_html($user->display_name)); ?></h1>
                    <p><?php esc_html_e('Manage your digital products, licenses, and downloads', 'edd-customer-dashboard-pro'); ?></p>
                </div>
                <div class="eddcdp-avatar">
                    <?php 
                    $initials = '';
                    $name_parts = explode(' ', trim($user->display_name));
                    foreach ($name_parts as $part) {
                        if (!empty($part)) {
                            $initials .= strtoupper(substr($part, 0, 1));
                        }
                    }
                    echo esc_html(substr($initials, 0, 2));
                    ?>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="eddcdp-stats-grid">
            <div class="eddcdp-stat-card">
                <div class="eddcdp-stat-content">
                    <div>
                        <div class="eddcdp-stat-value"><?php echo esc_html($customer_stats['total_purchases'] ?? 0); ?></div>
                        <div class="eddcdp-stat-label"><?php esc_html_e('Total Purchases', 'edd-customer-dashboard-pro'); ?></div>
                    </div>
                    <div class="eddcdp-stat-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                        📦
                    </div>
                </div>
            </div>
            
            <div class="eddcdp-stat-card">
                <div class="eddcdp-stat-content">
                    <div>
                        <div class="eddcdp-stat-value"><?php echo esc_html($customer_stats['download_count'] ?? 0); ?></div>
                        <div class="eddcdp-stat-label"><?php esc_html_e('Downloads', 'edd-customer-dashboard-pro'); ?></div>
                    </div>
                    <div class="eddcdp-stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        ⬇️
                    </div>
                </div>
            </div>
            
            <div class="eddcdp-stat-card">
                <div class="eddcdp-stat-content">
                    <div>
                        <div class="eddcdp-stat-value"><?php echo esc_html($customer_stats['active_licenses'] ?? 0); ?></div>
                        <div class="eddcdp-stat-label"><?php esc_html_e('Active Licenses', 'edd-customer-dashboard-pro'); ?></div>
                    </div>
                    <div class="eddcdp-stat-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                        🔑
                    </div>
                </div>
            </div>
            
            <div class="eddcdp-stat-card">
                <div class="eddcdp-stat-content">
                    <div>
                        <div class="eddcdp-stat-value"><?php echo esc_html($customer_stats['wishlist_count'] ?? 0); ?></div>
                        <div class="eddcdp-stat-label"><?php esc_html_e('Wishlist Items', 'edd-customer-dashboard-pro'); ?></div>
                    </div>
                    <div class="eddcdp-stat-icon" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);">
                        ❤️
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="eddcdp-nav-tabs">
            <div class="eddcdp-tabs-container">
                <?php if (empty($enabled_sections) || isset($enabled_sections['purchases']) && $enabled_sections['purchases']) : ?>
                    <button @click="activeTab = 'purchases'" :class="{'active': activeTab === 'purchases'}" class="eddcdp-tab">
                        📦 <?php esc_html_e('Purchases', 'edd-customer-dashboard-pro'); ?>
                    </button>
                <?php endif; ?>
                
                <?php if (empty($enabled_sections) || isset($enabled_sections['downloads']) && $enabled_sections['downloads']) : ?>
                    <button @click="activeTab = 'downloads'" :class="{'active': activeTab === 'downloads'}" class="eddcdp-tab">
                        ⬇️ <?php esc_html_e('Downloads', 'edd-customer-dashboard-pro'); ?>
                    </button>
                <?php endif; ?>
                
                <?php if ((empty($enabled_sections) || isset($enabled_sections['licenses']) && $enabled_sections['licenses']) && class_exists('EDD_Software_Licensing')) : ?>
                    <button @click="activeTab = 'licenses'" :class="{'active': activeTab === 'licenses'}" class="eddcdp-tab">
                        🔑 <?php esc_html_e('Licenses', 'edd-customer-dashboard-pro'); ?>
                    </button>
                <?php endif; ?>
                
                <?php if ((empty($enabled_sections) || isset($enabled_sections['wishlist']) && $enabled_sections['wishlist']) && class_exists('EDD_Wish_Lists')) : ?>
                    <button @click="activeTab = 'wishlist'" :class="{'active': activeTab === 'wishlist'}" class="eddcdp-tab">
                        ❤️ <?php esc_html_e('Wishlist', 'edd-customer-dashboard-pro'); ?>
                    </button>
                <?php endif; ?>
                
                <?php if (empty($enabled_sections) || isset($enabled_sections['analytics']) && $enabled_sections['analytics']) : ?>
                    <button @click="activeTab = 'analytics'" :class="{'active': activeTab === 'analytics'}" class="eddcdp-tab">
                        📊 <?php esc_html_e('Analytics', 'edd-customer-dashboard-pro'); ?>
                    </button>
                <?php endif; ?>
                
                <?php if (empty($enabled_sections) || isset($enabled_sections['support']) && $enabled_sections['support']) : ?>
                    <button @click="activeTab = 'support'" :class="{'active': activeTab === 'support'}" class="eddcdp-tab">
                        💬 <?php esc_html_e('Support', 'edd-customer-dashboard-pro'); ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Content Area -->
        <div class="eddcdp-content">
            <!-- Purchases Tab -->
            <?php if (empty($enabled_sections) || isset($enabled_sections['purchases']) && $enabled_sections['purchases']) : ?>
                <div x-show="activeTab === 'purchases'" class="eddcdp-tab-content">
                    <h2 class="eddcdp-section-title">
                        📦 <?php esc_html_e('Your Orders & Purchases', 'edd-customer-dashboard-pro'); ?>
                    </h2>
                    
                    <?php if (!empty($purchases)) : ?>
                        <div class="eddcdp-purchases-list">
                            <?php foreach ($purchases as $payment) : ?>
                                <div class="eddcdp-purchase-item">
                                    <div class="eddcdp-purchase-header">
                                        <div>
                                            <h3 class="eddcdp-purchase-title">
                                                <?php 
                                                $downloads = edd_get_payment_meta_downloads($payment->ID);
                                                if ($downloads && is_array($downloads)) {
                                                    $first_download = reset($downloads);
                                                    echo esc_html(get_the_title($first_download['id']));
                                                    if (count($downloads) > 1) {
                                                        printf(' +%d %s', count($downloads) - 1, __('more', 'edd-customer-dashboard-pro'));
                                                    }
                                                } else {
                                                    echo esc_html__('Order', 'edd-customer-dashboard-pro') . ' #' . esc_html($payment->number);
                                                }
                                                ?>
                                            </h3>
                                            <div class="eddcdp-purchase-meta">
                                                <span>📋 <?php printf(__('Order #%s', 'edd-customer-dashboard-pro'), esc_html($payment->number)); ?></span>
                                                <span>📅 <?php echo esc_html(eddcdp_format_date($payment->date)); ?></span>
                                                <span>💰 <?php echo esc_html(eddcdp_format_currency($payment->total)); ?></span>
                                            </div>
                                        </div>
                                        <span class="eddcdp-status-badge eddcdp-status-complete">
                                            ✅ <?php echo esc_html(edd_get_payment_status($payment, true)); ?>
                                        </span>
                                    </div>
                                    
                                    <?php if ($downloads && is_array($downloads)) : ?>
                                        <div class="eddcdp-downloads-list">
                                            <?php foreach ($downloads as $download) : ?>
                                                <div class="eddcdp-download-item">
                                                    <div>
                                                        <div class="eddcdp-download-name"><?php echo esc_html(get_the_title($download['id'])); ?></div>
                                                        <div class="eddcdp-download-meta">
                                                            <?php 
                                                            $version = get_post_meta($download['id'], '_edd_sl_version', true);
                                                            if ($version) {
                                                                printf(__('Version: %s', 'edd-customer-dashboard-pro'), esc_html($version));
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                    <?php if (edd_is_payment_complete($payment->ID) && edd_can_view_receipt($payment->key)) : ?>
                                                        <a href="<?php echo esc_url(edd_get_download_file_url($payment->key, $payment->email, 0, $download['id'])); ?>" 
                                                           class="eddcdp-btn eddcdp-btn-primary">
                                                            🔽 <?php esc_html_e('Download', 'edd-customer-dashboard-pro'); ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="eddcdp-actions">
                                        <a href="<?php echo esc_url(edd_get_success_page_uri('?payment_key=' . $payment->key)); ?>" 
                                           class="eddcdp-btn eddcdp-btn-secondary">
                                            📋 <?php esc_html_e('Details', 'edd-customer-dashboard-pro'); ?>
                                        </a>
                                        <a href="<?php echo esc_url(add_query_arg('view', 'invoice', edd_get_success_page_uri('?payment_key=' . $payment->key))); ?>" 
                                           class="eddcdp-btn eddcdp-btn-secondary">
                                            📄 <?php esc_html_e('Invoice', 'edd-customer-dashboard-pro'); ?>
                                        </a>
                                        <?php if (class_exists('EDD_Software_Licensing')) : ?>
                                            <button class="eddcdp-btn eddcdp-btn-secondary">
                                                🔑 <?php esc_html_e('Licenses', 'edd-customer-dashboard-pro'); ?>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="eddcdp-coming-soon">
                            <div class="eddcdp-coming-soon-icon">📦</div>
                            <h3><?php esc_html_e('No Purchases Yet', 'edd-customer-dashboard-pro'); ?></h3>
                            <p><?php esc_html_e('When you make your first purchase, it will appear here.', 'edd-customer-dashboard-pro'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Downloads Tab -->
            <?php if (empty($enabled_sections) || isset($enabled_sections['downloads']) && $enabled_sections['downloads']) : ?>
                <div x-show="activeTab === 'downloads'" class="eddcdp-tab-content">
                    <h2 class="eddcdp-section-title">
                        ⬇️ <?php esc_html_e('Download History', 'edd-customer-dashboard-pro'); ?>
                    </h2>
                    
                    <div class="eddcdp-coming-soon">
                        <div class="eddcdp-coming-soon-icon">⬇️</div>
                        <h3><?php esc_html_e('Download History Coming Soon', 'edd-customer-dashboard-pro'); ?></h3>
                        <p><?php esc_html_e('Track your download history and remaining download counts.', 'edd-customer-dashboard-pro'); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Licenses Tab -->
            <?php if ((empty($enabled_sections) || isset($enabled_sections['licenses']) && $enabled_sections['licenses']) && class_exists('EDD_Software_Licensing')) : ?>
                <div x-show="activeTab === 'licenses'" class="eddcdp-tab-content">
                    <h2 class="eddcdp-section-title">
                        🔑 <?php esc_html_e('License Management', 'edd-customer-dashboard-pro'); ?>
                    </h2>
                    
                    <div class="eddcdp-coming-soon">
                        <div class="eddcdp-coming-soon-icon">🔑</div>
                        <h3><?php esc_html_e('License Management Coming Soon', 'edd-customer-dashboard-pro'); ?></h3>
                        <p><?php esc_html_e('Manage your software licenses, activations, and renewals.', 'edd-customer-dashboard-pro'); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Wishlist Tab -->
            <?php if ((empty($enabled_sections) || isset($enabled_sections['wishlist']) && $enabled_sections['wishlist']) && class_exists('EDD_Wish_Lists')) : ?>
                <div x-show="activeTab === 'wishlist'" class="eddcdp-tab-content">
                    <h2 class="eddcdp-section-title">
                        ❤️ <?php esc_html_e('Your Wishlist', 'edd-customer-dashboard-pro'); ?>
                    </h2>
                    
                    <div class="eddcdp-coming-soon">
                        <div class="eddcdp-coming-soon-icon">❤️</div>
                        <h3><?php esc_html_e('Wishlist Coming Soon', 'edd-customer-dashboard-pro'); ?></h3>
                        <p><?php esc_html_e('Save products you want to purchase later and get notified of updates.', 'edd-customer-dashboard-pro'); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Analytics Tab -->
            <?php if (empty($enabled_sections) || isset($enabled_sections['analytics']) && $enabled_sections['analytics']) : ?>
                <div x-show="activeTab === 'analytics'" class="eddcdp-tab-content">
                    <h2 class="eddcdp-section-title">
                        📊 <?php esc_html_e('Purchase Analytics', 'edd-customer-dashboard-pro'); ?>
                    </h2>
                    
                    <?php if ($customer && $customer->purchase_value > 0) : ?>
                        <div class="eddcdp-stats-grid" style="margin-bottom: 2rem;">
                            <div class="eddcdp-stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                                <div class="eddcdp-stat-content">
                                    <div>
                                        <div class="eddcdp-stat-value" style="color: white;"><?php echo esc_html(eddcdp_format_currency($customer->purchase_value)); ?></div>
                                        <div class="eddcdp-stat-label" style="color: rgba(255, 255, 255, 0.8);"><?php esc_html_e('Total Spent', 'edd-customer-dashboard-pro'); ?></div>
                                    </div>
                                    <div class="eddcdp-stat-icon" style="background: rgba(255, 255, 255, 0.2);">
                                        💰
                                    </div>
                                </div>
                            </div>
                            
                            <div class="eddcdp-stat-card" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white;">
                                <div class="eddcdp-stat-content">
                                    <div>
                                        <div class="eddcdp-stat-value" style="color: white;">
                                            <?php 
                                            $avg_order = $customer->purchase_count > 0 ? $customer->purchase_value / $customer->purchase_count : 0;
                                            echo esc_html(eddcdp_format_currency($avg_order));
                                            ?>
                                        </div>
                                        <div class="eddcdp-stat-label" style="color: rgba(255, 255, 255, 0.8);"><?php esc_html_e('Avg Per Order', 'edd-customer-dashboard-pro'); ?></div>
                                    </div>
                                    <div class="eddcdp-stat-icon" style="background: rgba(255, 255, 255, 0.2);">
                                        📈
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="eddcdp-coming-soon">
                        <div class="eddcdp-coming-soon-icon">📊</div>
                        <h3><?php esc_html_e('Advanced Analytics Coming Soon', 'edd-customer-dashboard-pro'); ?></h3>
                        <p><?php esc_html_e('Detailed charts and insights about your purchase history, usage patterns, and recommendations.', 'edd-customer-dashboard-pro'); ?></p>
                        <button class="eddcdp-btn eddcdp-btn-primary">
                            📬 <?php esc_html_e('Notify Me When Available', 'edd-customer-dashboard-pro'); ?>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Support Tab -->
            <?php if (empty($enabled_sections) || isset($enabled_sections['support']) && $enabled_sections['support']) : ?>
                <div x-show="activeTab === 'support'" class="eddcdp-tab-content">
                    <h2 class="eddcdp-section-title">
                        💬 <?php esc_html_e('Support Center', 'edd-customer-dashboard-pro'); ?>
                    </h2>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                        <!-- Quick Actions -->
                        <div>
                            <h3 style="font-size: 1.125rem; font-weight: 600; color: #1f2937; margin-bottom: 1rem;">
                                <?php esc_html_e('Quick Actions', 'edd-customer-dashboard-pro'); ?>
                            </h3>
                            
                            <div style="space-y: 1rem;">
                                <div class="eddcdp-purchase-item" style="margin-bottom: 1rem;">
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <div class="eddcdp-stat-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white;">
                                            🎫
                                        </div>
                                        <div style="flex: 1;">
                                            <h4 style="font-weight: 600; color: #1f2937; margin: 0 0 0.25rem 0;"><?php esc_html_e('Create Support Ticket', 'edd-customer-dashboard-pro'); ?></h4>
                                            <p style="font-size: 0.875rem; color: #6b7280; margin: 0;"><?php esc_html_e('Get help with your purchases or technical issues', 'edd-customer-dashboard-pro'); ?></p>
                                        </div>
                                        <button class="eddcdp-btn eddcdp-btn-primary">
                                            <?php esc_html_e('Create', 'edd-customer-dashboard-pro'); ?>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="eddcdp-purchase-item" style="margin-bottom: 1rem;">
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <div class="eddcdp-stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                                            📚
                                        </div>
                                        <div style="flex: 1;">
                                            <h4 style="font-weight: 600; color: #1f2937; margin: 0 0 0.25rem 0;"><?php esc_html_e('Documentation', 'edd-customer-dashboard-pro'); ?></h4>
                                            <p style="font-size: 0.875rem; color: #6b7280; margin: 0;"><?php esc_html_e('Browse our comprehensive guides and tutorials', 'edd-customer-dashboard-pro'); ?></p>
                                        </div>
                                        <button class="eddcdp-btn eddcdp-btn-secondary">
                                            <?php esc_html_e('Browse', 'edd-customer-dashboard-pro'); ?>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="eddcdp-purchase-item">
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <div class="eddcdp-stat-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white;">
                                            💬
                                        </div>
                                        <div style="flex: 1;">
                                            <h4 style="font-weight: 600; color: #1f2937; margin: 0 0 0.25rem 0;"><?php esc_html_e('Live Chat', 'edd-customer-dashboard-pro'); ?></h4>
                                            <p style="font-size: 0.875rem; color: #6b7280; margin: 0;"><?php esc_html_e('Chat with our support team in real-time', 'edd-customer-dashboard-pro'); ?></p>
                                        </div>
                                        <button class="eddcdp-btn" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white;">
                                            <?php esc_html_e('Start Chat', 'edd-customer-dashboard-pro'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact Information -->
                        <div>
                            <h3 style="font-size: 1.125rem; font-weight: 600; color: #1f2937; margin-bottom: 1rem;">
                                <?php esc_html_e('Contact Information', 'edd-customer-dashboard-pro'); ?>
                            </h3>
                            <div class="eddcdp-purchase-item">
                                <div style="display: flex; flex-direction: column; gap: 1rem;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 32px; height: 32px; background: #dbeafe; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #3b82f6;">
                                            📧
                                        </div>
                                        <div>
                                            <p style="font-weight: 600; color: #1f2937; margin: 0;"><?php esc_html_e('Email Support', 'edd-customer-dashboard-pro'); ?></p>
                                            <p style="font-size: 0.875rem; color: #6b7280; margin: 0;">support@yourstore.com</p>
                                        </div>
                                    </div>
                                    
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 32px; height: 32px; background: #dcfce7; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #10b981;">
                                            ⏰
                                        </div>
                                        <div>
                                            <p style="font-weight: 600; color: #1f2937; margin: 0;"><?php esc_html_e('Business Hours', 'edd-customer-dashboard-pro'); ?></p>
                                            <p style="font-size: 0.875rem; color: #6b7280; margin: 0;"><?php esc_html_e('Mon-Fri, 9AM-6PM EST', 'edd-customer-dashboard-pro'); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 32px; height: 32px; background: #f3e8ff; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #8b5cf6;">
                                            🎯
                                        </div>
                                        <div>
                                            <p style="font-weight: 600; color: #1f2937; margin: 0;"><?php esc_html_e('Response Time', 'edd-customer-dashboard-pro'); ?></p>
                                            <p style="font-size: 0.875rem; color: #6b7280; margin: 0;"><?php esc_html_e('Usually within 24 hours', 'edd-customer-dashboard-pro'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function eddcdpDashboard() {
            return {
                activeTab: 'purchases',
                downloading: null,
                
                downloadFile(productId) {
                    this.downloading = productId;
                    
                    // Simulate download process
                    setTimeout(() => {
                        this.downloading = null;
                    }, 2000);
                },
                
                copyToClipboard(text) {
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(text).then(() => {
                            console.log('Text copied to clipboard');
                        });
                    }
                }
            }
        }
        
        // Initialize Alpine.js if not already loaded
        if (typeof Alpine === 'undefined') {
            document.addEventListener('DOMContentLoaded', function() {
                // Load Alpine.js dynamically if not present
                if (!window.Alpine) {
                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js';
                    script.defer = true;
                    document.head.appendChild(script);
                }
            });
        }
    </script>
</div>