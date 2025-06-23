<?php
/**
 * Stats Grid Section - EDD 3.0+ Compatible with Enhanced Utility Functions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();

// Get customer data using EDD 3.0+ methods
$customer = edd_get_customer_by('email', $current_user->user_email);
$purchase_count = $customer ? $customer->purchase_count : 0;

// Get download count using utility function
$download_count = eddcdp_get_customer_download_count($current_user->user_email);

// Get active license count using utility function
$license_count = eddcdp_get_customer_active_license_count($current_user->ID);

// Get wishlist count using utility function
$wishlist_count = eddcdp_get_customer_wishlist_count($current_user->ID);

// Get additional stats for enhanced display
$total_spent = $customer ? $customer->purchase_value : 0;
$orders = edd_get_orders(array(
    'customer' => $current_user->user_email,
    'number' => 999999,
    'status' => array('complete'),
    'fields' => 'ids'
));
$total_orders = is_array($orders) ? count($orders) : 0;

// Calculate average order value
$avg_order_value = $total_orders > 0 ? $total_spent / $total_orders : 0;

// Get pending orders count
$pending_orders = edd_get_orders(array(
    'customer' => $current_user->user_email,
    'number' => 999999,
    'status' => array('pending', 'processing'),
    'fields' => 'ids'
));
$pending_count = is_array($pending_orders) ? count($pending_orders) : 0;
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <!-- Total Purchases Card -->
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300 group hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-3xl font-bold text-gray-800 mb-1"><?php echo number_format($purchase_count); ?></p>
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide"><?php _e('Total Purchases', 'eddcdp'); ?></p>
                <?php if ($pending_count > 0) : ?>
                <p class="text-xs text-orange-600 mt-1">
                    <?php printf(_n('+%d pending', '+%d pending', $pending_count, 'eddcdp'), $pending_count); ?>
                </p>
                <?php endif; ?>
            </div>
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center text-white text-xl group-hover:scale-110 transition-transform">
                📦
            </div>
        </div>
        
        <!-- Additional info on hover/click -->
        <div class="mt-4 pt-4 border-t border-gray-200 opacity-0 group-hover:opacity-100 transition-opacity">
            <div class="flex justify-between text-xs text-gray-500">
                <span><?php _e('Total Spent:', 'eddcdp'); ?></span>
                <span class="font-medium"><?php echo eddcdp_format_price($total_spent); ?></span>
            </div>
            <?php if ($avg_order_value > 0) : ?>
            <div class="flex justify-between text-xs text-gray-500 mt-1">
                <span><?php _e('Avg Order:', 'eddcdp'); ?></span>
                <span class="font-medium"><?php echo eddcdp_format_price($avg_order_value); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Downloads Card -->
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300 group hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-3xl font-bold text-gray-800 mb-1"><?php echo number_format($download_count); ?></p>
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide"><?php _e('Downloads', 'eddcdp'); ?></p>
                <?php if ($purchase_count > 0) : ?>
                <p class="text-xs text-blue-600 mt-1">
                    <?php printf(__('%.1f per order', 'eddcdp'), $download_count / $purchase_count); ?>
                </p>
                <?php endif; ?>
            </div>
            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center text-white text-xl group-hover:scale-110 transition-transform">
                ⬇️
            </div>
        </div>
        
        <!-- Download activity indicator -->
        <div class="mt-4 pt-4 border-t border-gray-200 opacity-0 group-hover:opacity-100 transition-opacity">
            <?php
            // Get recent downloads (last 30 days)
            $recent_downloads = edd_get_file_download_logs(array(
                'customer' => $current_user->user_email,
                'number' => 999999,
                'date_query' => array(
                    'after' => '30 days ago'
                ),
                'fields' => 'ids'
            ));
            $recent_count = is_array($recent_downloads) ? count($recent_downloads) : 0;
            ?>
            <div class="flex justify-between text-xs text-gray-500">
                <span><?php _e('Last 30 days:', 'eddcdp'); ?></span>
                <span class="font-medium <?php echo $recent_count > 0 ? 'text-green-600' : ''; ?>">
                    <?php echo number_format($recent_count); ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Active Licenses Card -->
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300 group hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-3xl font-bold text-gray-800 mb-1"><?php echo number_format($license_count); ?></p>
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide"><?php _e('Active Licenses', 'eddcdp'); ?></p>
                <?php if (class_exists('EDD_Software_Licensing')) : ?>
                    <?php
                    // Get total licenses (including expired)
                    $total_licenses = eddcdp_get_customer_license_count($current_user->ID);
                    $expired_licenses = $total_licenses - $license_count;
                    ?>
                    <?php if ($expired_licenses > 0) : ?>
                    <p class="text-xs text-red-600 mt-1">
                        <?php printf(_n('%d expired', '%d expired', $expired_licenses, 'eddcdp'), $expired_licenses); ?>
                    </p>
                    <?php elseif ($license_count > 0) : ?>
                    <p class="text-xs text-green-600 mt-1">
                        <?php _e('All current', 'eddcdp'); ?>
                    </p>
                    <?php endif; ?>
                <?php else : ?>
                <p class="text-xs text-gray-400 mt-1"><?php _e('Extension needed', 'eddcdp'); ?></p>
                <?php endif; ?>
            </div>
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center text-white text-xl group-hover:scale-110 transition-transform">
                🔑
            </div>
        </div>
        
        <!-- License details on hover -->
        <?php if ($license_count > 0 && class_exists('EDD_Software_Licensing')) : ?>
        <div class="mt-4 pt-4 border-t border-gray-200 opacity-0 group-hover:opacity-100 transition-opacity">
            <?php
            // Get licenses expiring soon (next 30 days)
            $licenses = edd_software_licensing()->licenses_db->get_licenses(array(
                'user_id' => $current_user->ID,
                'status' => 'active',
                'number' => 999999
            ));
            
            $expiring_soon = 0;
            foreach ($licenses as $license) {
                if (!empty($license->expiration) && $license->expiration !== '0000-00-00 00:00:00') {
                    $expiration_date = strtotime($license->expiration);
                    $days_until_expiry = ceil(($expiration_date - time()) / (60 * 60 * 24));
                    if ($days_until_expiry <= 30 && $days_until_expiry > 0) {
                        $expiring_soon++;
                    }
                }
            }
            ?>
            <div class="flex justify-between text-xs text-gray-500">
                <span><?php _e('Expiring soon:', 'eddcdp'); ?></span>
                <span class="font-medium <?php echo $expiring_soon > 0 ? 'text-orange-600' : 'text-green-600'; ?>">
                    <?php echo $expiring_soon > 0 ? $expiring_soon : __('None', 'eddcdp'); ?>
                </span>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Wishlist Items Card -->
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300 group hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-3xl font-bold text-gray-800 mb-1"><?php echo number_format($wishlist_count); ?></p>
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide"><?php _e('Wishlist Items', 'eddcdp'); ?></p>
                <?php if (function_exists('edd_wl_get_wish_list')) : ?>
                    <?php if ($wishlist_count > 0) : ?>
                    <p class="text-xs text-pink-600 mt-1"><?php _e('Ready to purchase', 'eddcdp'); ?></p>
                    <?php else : ?>
                    <p class="text-xs text-gray-400 mt-1"><?php _e('Start adding items', 'eddcdp'); ?></p>
                    <?php endif; ?>
                <?php else : ?>
                <p class="text-xs text-gray-400 mt-1"><?php _e('Extension needed', 'eddcdp'); ?></p>
                <?php endif; ?>
            </div>
            <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl flex items-center justify-center text-white text-xl group-hover:scale-110 transition-transform">
                ❤️
            </div>
        </div>
        
        <!-- Wishlist actions on hover -->
        <?php if ($wishlist_count > 0 && function_exists('edd_wl_get_wish_list')) : ?>
        <div class="mt-4 pt-4 border-t border-gray-200 opacity-0 group-hover:opacity-100 transition-opacity">
            <button onclick="addAllToCart()" class="w-full text-xs bg-pink-500 text-white py-2 rounded-lg hover:bg-pink-600 transition-colors">
                <?php _e('Add All to Cart', 'eddcdp'); ?>
            </button>
        </div>
        <?php elseif ($wishlist_count === 0 && function_exists('edd_wl_get_wish_list')) : ?>
        <div class="mt-4 pt-4 border-t border-gray-200 opacity-0 group-hover:opacity-100 transition-opacity">
            <button onclick="browseProducts()" class="w-full text-xs bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600 transition-colors">
                <?php _e('Browse Products', 'eddcdp'); ?>
            </button>
        </div>
        <?php endif; ?>
    </div>
    
</div>

<!-- Quick Stats Summary (collapsible) -->
<div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl p-4 mb-8 border border-indigo-100">
    <div class="flex items-center justify-between cursor-pointer" onclick="toggleQuickStats()">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center text-white text-sm">
                📊
            </div>
            <span class="font-medium text-gray-700"><?php _e('Quick Stats Summary', 'eddcdp'); ?></span>
        </div>
        <div class="text-gray-500" id="quick-stats-toggle">
            <svg class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </div>
    
    <div id="quick-stats-content" class="hidden mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div class="text-center">
            <div class="font-bold text-gray-800"><?php echo eddcdp_format_price($total_spent); ?></div>
            <div class="text-gray-600"><?php _e('Total Spent', 'eddcdp'); ?></div>
        </div>
        
        <div class="text-center">
            <div class="font-bold text-gray-800"><?php echo $total_orders; ?></div>
            <div class="text-gray-600"><?php _e('Completed Orders', 'eddcdp'); ?></div>
        </div>
        
        <div class="text-center">
            <div class="font-bold text-gray-800">
                <?php echo $total_orders > 0 ? eddcdp_format_price($avg_order_value) : eddcdp_format_price(0); ?>
            </div>
            <div class="text-gray-600"><?php _e('Avg Order Value', 'eddcdp'); ?></div>
        </div>
        
        <div class="text-center">
            <div class="font-bold text-gray-800">
                <?php 
                if ($customer && !empty($customer->date_created)) {
                    $days_active = ceil((time() - strtotime($customer->date_created)) / (60 * 60 * 24));
                    echo number_format($days_active);
                } else {
                    echo '0';
                }
                ?>
            </div>
            <div class="text-gray-600"><?php _e('Days Active', 'eddcdp'); ?></div>
        </div>
    </div>
</div>

<script>
// Toggle quick stats summary
function toggleQuickStats() {
    const content = document.getElementById('quick-stats-content');
    const toggle = document.getElementById('quick-stats-toggle');
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        toggle.querySelector('svg').style.transform = 'rotate(180deg)';
    } else {
        content.classList.add('hidden');
        toggle.querySelector('svg').style.transform = 'rotate(0deg)';
    }
}

// Add all wishlist items to cart
function addAllToCart() {
    if (confirm('<?php _e('Add all wishlist items to your cart?', 'eddcdp'); ?>')) {
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'eddcdp_add_wishlist_to_cart',
                nonce: '<?php echo wp_create_nonce('eddcdp_ajax_nonce'); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('<?php _e('Wishlist items added to cart!', 'eddcdp'); ?>');
                if (confirm('<?php _e('Go to checkout now?', 'eddcdp'); ?>')) {
                    window.location.href = '<?php echo edd_get_checkout_uri(); ?>';
                }
            } else {
                alert(data.data || '<?php _e('Error adding items to cart.', 'eddcdp'); ?>');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?php _e('Error processing request.', 'eddcdp'); ?>');
        });
    }
}

// Browse products
function browseProducts() {
    window.location.href = '<?php echo esc_url(home_url('/downloads/')); ?>';
}
</script>