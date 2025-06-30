<?php
/**
 * Downloads Section Template - Updated Design
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user
$current_user = wp_get_current_user();

// Get customer first to ensure we have valid customer
$customer = edd_get_customer_by('email', $current_user->user_email);

if (!$customer) {
    ?>
    <div class="empty-state">
        <div class="empty-icon">⚠️</div>
        <h3><?php esc_html_e('Customer Data Not Found', 'edd-customer-dashboard-pro'); ?></h3>
        <p><?php esc_html_e('Unable to retrieve your customer information.', 'edd-customer-dashboard-pro'); ?></p>
    </div>
    <?php
    return;
}

// Get download logs using EDD 3.0+ method
$download_logs = edd_get_file_download_logs(array(
    'customer' => $customer->id,
    'number' => 50, // Show more downloads in history
    'orderby' => 'date_created',
    'order' => 'DESC'
));
?>

<h2 class="section-title"><?php esc_html_e('Download History', 'edd-customer-dashboard-pro'); ?></h2>

<?php if ($download_logs) : ?>

<!-- Download Statistics -->
<div class="stats-grid" style="margin-bottom: 30px;">
    <div class="stat-card">
        <div class="stat-icon downloads">⬇️</div>
        <div class="stat-number"><?php echo esc_html(number_format_i18n(count($download_logs))); ?></div>
        <div class="stat-label"><?php esc_html_e('Total Downloads', 'edd-customer-dashboard-pro'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">📅</div>
        <div class="stat-number">
            <?php 
            $recent_downloads = array_filter($download_logs, function($log) {
                return strtotime($log->date_created) > strtotime('-7 days');
            });
            echo esc_html(number_format_i18n(count($recent_downloads)));
            ?>
        </div>
        <div class="stat-label"><?php esc_html_e('This Week', 'edd-customer-dashboard-pro'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a, #fee140);">📦</div>
        <div class="stat-number">
            <?php 
            $unique_products = array();
            foreach ($download_logs as $log) {
                $unique_products[$log->product_id] = true;
            }
            echo esc_html(number_format_i18n(count($unique_products)));
            ?>
        </div>
        <div class="stat-label"><?php esc_html_e('Unique Products', 'edd-customer-dashboard-pro'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">🔄</div>
        <div class="stat-number">
            <?php 
            $avg_downloads = count($unique_products) > 0 ? round(count($download_logs) / count($unique_products), 1) : 0;
            echo esc_html(number_format_i18n($avg_downloads, 1));
            ?>
        </div>
        <div class="stat-label"><?php esc_html_e('Avg per Product', 'edd-customer-dashboard-pro'); ?></div>
    </div>
</div>

<!-- Download History List -->
<div class="purchase-list">
    <?php 
    $grouped_downloads = array();
    
    // Group downloads by date for better organization
    foreach ($download_logs as $log) {
        $date_key = date('Y-m-d', strtotime($log->date_created));
        if (!isset($grouped_downloads[$date_key])) {
            $grouped_downloads[$date_key] = array();
        }
        $grouped_downloads[$date_key][] = $log;
    }
    
    foreach ($grouped_downloads as $date => $logs) :
        $display_date = date_i18n(get_option('date_format'), strtotime($date));
        $is_today = date('Y-m-d') === $date;
        $is_yesterday = date('Y-m-d', strtotime('-1 day')) === $date;
        
        if ($is_today) {
            $display_date = esc_html__('Today', 'edd-customer-dashboard-pro') . ' - ' . $display_date;
        } elseif ($is_yesterday) {
            $display_date = esc_html__('Yesterday', 'edd-customer-dashboard-pro') . ' - ' . $display_date;
        }
    ?>
    
    <!-- Date Group Header -->
    <div style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1)); border-radius: 8px; padding: 12px 20px; margin: 20px 0 15px 0; border-left: 4px solid var(--primary);">
        <h3 style="color: var(--primary); margin: 0; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 1.2rem;">📅</span>
            <?php echo esc_html($display_date); ?>
            <span style="background: rgba(102, 126, 234, 0.2); color: var(--primary); padding: 2px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                <?php 
                /* translators: %d: Number of downloads */
                printf(esc_html(_n('%d download', '%d downloads', count($logs), 'edd-customer-dashboard-pro')), count($logs)); 
                ?>
            </span>
        </h3>
    </div>
    
    <?php foreach ($logs as $log) : 
        $download_id = $log->product_id;
        $download_name = get_the_title($download_id);
        $download_date = $log->date_created;
        $file_id = $log->file_id;
        $download_time = date_i18n(get_option('time_format'), strtotime($download_date));
        
        // Get order info if available
        $order_id = !empty($log->order_id) ? $log->order_id : 0;
        $order = $order_id ? edd_get_order($order_id) : null;
        $order_number = $order ? $order->get_number() : '';
        
        // Check if download is still valid
        $has_access = false;
        $download_url = '';
        
        if ($order && $order->status === 'complete') {
            $download_url = edd_get_download_file_url($order->payment_key, $customer->email, $file_id, $download_id);
            $has_access = !empty($download_url);
        }
        
        // Calculate download limits
        $limit = edd_get_file_download_limit($download_id);
        $downloads_used = 0;
        $remaining_text = esc_html__('Unlimited', 'edd-customer-dashboard-pro');
        
        if ($limit && $order_id) {
            // Count downloads for this specific order and product
            $used_logs = edd_get_file_download_logs(array(
                'product_id' => $download_id,
                'order_id' => $order_id,
                'customer' => $customer->id,
                'number' => 999999,
                'fields' => 'ids'
            ));
            $downloads_used = is_array($used_logs) ? count($used_logs) : 0;
            $remaining_count = max(0, $limit - $downloads_used);
            /* translators: %1$d: remaining downloads, %2$d: total limit */
            $remaining_text = sprintf(esc_html__('%1$d of %2$d remaining', 'edd-customer-dashboard-pro'), $remaining_count, $limit);
        }
        
        // Determine if download limit is reached
        $limit_reached = $limit && $downloads_used >= $limit;
        
        // Get file info
        $files = edd_get_download_files($download_id);
        $file_name = esc_html__('Unknown File', 'edd-customer-dashboard-pro');
        if ($files && isset($files[$file_id])) {
            $file_name = $files[$file_id]['name'];
        }
    ?>
    
    <div class="purchase-item" style="<?php echo $is_today ? 'border-left: 4px solid var(--success);' : ''; ?>">
        <div class="purchase-header">
            <div class="order-info">
                <div class="product-name">
                    <?php echo esc_html($download_name); ?>
                    <?php if ($is_today) : ?>
                        <span style="background: linear-gradient(135deg, var(--success), var(--success-light)); color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: 600; margin-left: 8px;">
                            <?php esc_html_e('TODAY', 'edd-customer-dashboard-pro'); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="order-meta">
                    <span class="download-time">
                        🕐 <?php 
                        /* translators: %s: Download time */
                        printf(esc_html__('Downloaded at %s', 'edd-customer-dashboard-pro'), esc_html($download_time)); 
                        ?>
                    </span>
                    
                    <?php if ($order_number) : ?>
                    <span class="order-info">
                        📋 <?php 
                        /* translators: %s: Order number */
                        printf(esc_html__('Order #%s', 'edd-customer-dashboard-pro'), esc_html($order_number)); 
                        ?>
                    </span>
                    <?php endif; ?>
                    
                    <span class="file-info">
                        📄 <?php echo esc_html($file_name); ?>
                    </span>
                </div>
                
                <div style="margin-top: 8px; display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    <span style="font-size: 0.85rem; color: var(--gray);">
                        <strong><?php esc_html_e('Downloads remaining:', 'edd-customer-dashboard-pro'); ?></strong> 
                        <span style="color: <?php echo $limit_reached ? '#d32f2f' : 'var(--success)'; ?>;">
                            <?php echo esc_html($remaining_text); ?>
                        </span>
                    </span>
                    
                    <?php if ($limit_reached) : ?>
                    <span style="background: rgba(245, 87, 108, 0.1); color: #d32f2f; padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                        ⚠️ <?php esc_html_e('Limit Reached', 'edd-customer-dashboard-pro'); ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <?php if ($has_access && !$limit_reached) : ?>
                <a href="<?php echo esc_url($download_url); ?>" 
                   class="btn btn-download"
                   onclick="trackDownload('<?php echo esc_js($download_name); ?>')">
                    🔽 <?php esc_html_e('Download Again', 'edd-customer-dashboard-pro'); ?>
                </a>
                <?php else : ?>
                <span class="btn btn-secondary" style="opacity: 0.6; cursor: not-allowed;">
                    <?php 
                    if ($limit_reached) {
                        echo '🚫 ' . esc_html__('Limit Reached', 'edd-customer-dashboard-pro');
                    } else {
                        echo '❌ ' . esc_html__('Not Available', 'edd-customer-dashboard-pro');
                    }
                    ?>
                </span>
                <?php endif; ?>
                
                <!-- Download status indicator -->
                <?php 
                $hours_ago = (time() - strtotime($download_date)) / 3600;
                if ($hours_ago < 1) {
                    $status_class = 'success';
                    $status_text = esc_html__('Just now', 'edd-customer-dashboard-pro');
                    $status_icon = '🆕';
                } elseif ($hours_ago < 24) {
                    $status_class = 'primary';
                    $status_text = esc_html__('Recent', 'edd-customer-dashboard-pro');
                    $status_icon = '⭐';
                } elseif ($hours_ago < 168) { // 1 week
                    $status_class = 'warning';
                    $status_text = esc_html__('This week', 'edd-customer-dashboard-pro');
                    $status_icon = '📅';
                } else {
                    $status_class = 'gray';
                    $status_text = esc_html__('Older', 'edd-customer-dashboard-pro');
                    $status_icon = '📁';
                }
                ?>
                <span class="status-badge status-<?php echo esc_attr($status_class); ?>"
                      style="background: rgba(<?php 
                      switch($status_class) {
                          case 'success': echo '67, 233, 123'; break;
                          case 'primary': echo '102, 126, 234'; break;
                          case 'warning': echo '255, 193, 7'; break;
                          default: echo '156, 163, 175';
                      } ?>, 0.2); color: <?php 
                      switch($status_class) {
                          case 'success': echo '#2d7d32'; break;
                          case 'primary': echo '#1976d2'; break;
                          case 'warning': echo '#f57c00'; break;
                          default: echo '#666';
                      } ?>;">
                    <?php echo esc_html($status_icon . ' ' . $status_text); ?>
                </span>
            </div>
        </div>
        
        <!-- Additional Actions -->
        <div class="order-actions">
            <?php if ($order) : ?>
            <a href="<?php echo esc_url(add_query_arg('eddcdp_order', $order->id, get_permalink())); ?>" 
               class="btn btn-secondary">
                📋 <?php esc_html_e('View Order Details', 'edd-customer-dashboard-pro'); ?>
            </a>
            <?php endif; ?>
            
            <?php if ($limit_reached) : ?>
            <button onclick="requestAdditionalDownloads(<?php echo esc_js($order_id); ?>, <?php echo esc_js($download_id); ?>)" 
                    class="btn btn-secondary"
                    style="background: rgba(255, 193, 7, 0.1); color: #f57c00; border-color: rgba(255, 193, 7, 0.3);">
                📞 <?php esc_html_e('Request More Downloads', 'edd-customer-dashboard-pro'); ?>
            </button>
            <?php endif; ?>
            
            <a href="<?php echo esc_url(get_permalink($download_id)); ?>" 
               class="btn btn-secondary">
                👁️ <?php esc_html_e('View Product', 'edd-customer-dashboard-pro'); ?>
            </a>
        </div>
    </div>
    
    <?php endforeach; ?>
    <?php endforeach; ?>
</div>

<!-- Load More Downloads -->
<?php if (count($download_logs) >= 50) : ?>
<div style="text-align: center; margin-top: 30px;">
    <button onclick="loadMoreDownloads()" 
            class="btn btn-secondary">
        📜 <?php esc_html_e('Load More Downloads', 'edd-customer-dashboard-pro'); ?>
    </button>
</div>
<?php endif; ?>

<?php else : ?>
<!-- Empty State -->
<div class="empty-state">
    <div class="empty-icon">⬇️</div>
    <h3><?php esc_html_e('No Downloads Yet', 'edd-customer-dashboard-pro'); ?></h3>
    <p><?php esc_html_e('You haven\'t downloaded any files yet. Make a purchase to get started!', 'edd-customer-dashboard-pro'); ?></p>
    <div style="display: flex; flex-direction: column; gap: 15px; align-items: center;">
        <button onclick="window.location.href='<?php echo esc_url(home_url('/downloads/')); ?>'" 
                class="btn">
            🛒 <?php esc_html_e('Browse Products', 'edd-customer-dashboard-pro'); ?>
        </button>
        
        <button onclick="showPurchasesTab()" 
                class="btn btn-secondary">
            📦 <?php esc_html_e('View My Purchases', 'edd-customer-dashboard-pro'); ?>
        </button>
    </div>
</div>
<?php endif; ?>

<script>
// Download tracking and interactions
function trackDownload(productName) {
    console.log('Download tracked:', productName);
    
    // Show a subtle notification
    showDownloadNotification('<?php esc_html_e('Download started!', 'edd-customer-dashboard-pro'); ?>');
}

function showDownloadNotification(message) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: linear-gradient(135deg, var(--success), var(--success-light));
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(67, 233, 123, 0.3);
        z-index: 10000;
        font-size: 0.9rem;
        animation: slideInUp 0.3s ease-out;
    `;
    
    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 1.1rem;">✅</span>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutDown 0.3s ease-in';
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 300);
    }, 3000);
}

function requestAdditionalDownloads(orderId, downloadId) {
    const message = '<?php esc_html_e('I need additional downloads for Order #', 'edd-customer-dashboard-pro'); ?>' + orderId + ' <?php esc_html_e('- Product ID:', 'edd-customer-dashboard-pro'); ?> ' + downloadId;
    
    // In a real implementation, this would open a support form or modal
    if (confirm('<?php esc_html_e('This will open your email client to request additional downloads. Continue?', 'edd-customer-dashboard-pro'); ?>')) {
        const emailSubject = encodeURIComponent('<?php esc_html_e('Request for Additional Downloads', 'edd-customer-dashboard-pro'); ?>');
        const emailBody = encodeURIComponent(message + '\n\n<?php esc_html_e('Please provide additional download allowance for the above order.', 'edd-customer-dashboard-pro'); ?>');
        window.location.href = `mailto:support@yoursite.com?subject=${emailSubject}&body=${emailBody}`;
    }
}

function loadMoreDownloads() {
    // This would implement AJAX loading of more downloads
    alert('<?php esc_html_e('Load more downloads functionality would be implemented here with AJAX.', 'edd-customer-dashboard-pro'); ?>');
}

function showPurchasesTab() {
    const dashboardElement = document.querySelector('[x-data]');
    if (dashboardElement && dashboardElement._x_dataStack) {
        dashboardElement._x_dataStack[0].activeTab = 'purchases';
        window.location.hash = 'purchases';
    }
}

// Add animations
if (!document.querySelector('#download-animations')) {
    const style = document.createElement('style');
    style.id = 'download-animations';
    style.textContent = `
        @keyframes slideInUp {
            from {
                transform: translateY(100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutDown {
            from {
                transform: translateY(0);
                opacity: 1;
            }
            to {
                transform: translateY(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}

// Smooth scroll to recent downloads on page load if coming from hash
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash === '#downloads') {
        const todaySection = document.querySelector('[style*="border-left: 4px solid var(--success)"]');
        if (todaySection) {
            setTimeout(() => {
                todaySection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 500);
        }
    }
});
</script>