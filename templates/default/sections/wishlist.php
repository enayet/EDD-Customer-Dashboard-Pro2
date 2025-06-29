<?php
/**
 * Wishlist Section Template - Minimal Version
 * Based on actual EDD Wish Lists functions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if EDD Wish Lists is active
if (!class_exists('EDD_Wish_Lists')) {
    echo '<div class="bg-yellow-50/80 rounded-2xl p-6 border border-yellow-200/50">';
    echo '<p class="text-yellow-800">' . esc_html__('Wish Lists extension is not active.', 'edd-customer-dashboard-pro') . '</p>';
    echo '</div>';
    return;
}

// Get current user
$current_user = wp_get_current_user();

if (!is_user_logged_in()) {
    echo '<div class="bg-yellow-50/80 rounded-2xl p-6 border border-yellow-200/50">';
    echo '<p class="text-yellow-800">' . esc_html__('Please log in to view your wishlist.', 'edd-customer-dashboard-pro') . '</p>';
    echo '</div>';
    return;
}

// Get wishlist data using our handler class
$wishlist_handler = EDDCDP_Wishlist_Handler::instance();
$wishlist_items = $wishlist_handler->get_user_wishlist_items();
$public_wishlist_urls = $wishlist_handler->get_public_wishlist_urls();
?>

<h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
    ❤️ <?php esc_html_e('Your Wishlist', 'edd-customer-dashboard-pro'); ?>
</h2>

<?php if (!empty($wishlist_items)) : ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($wishlist_items as $item) : 
        $download_id = $item['id'];
        $download = get_post($download_id);
        
        // Skip if download doesn't exist or isn't published
        if (!$download || $download->post_status !== 'publish') {
            continue;
        }
        
        // Get pricing info
        $has_variable_pricing = edd_has_variable_prices($download_id);
        $price_id = isset($item['options']['price_id']) ? $item['options']['price_id'] : null;
        
        if ($has_variable_pricing && $price_id !== null) {
            $price = edd_get_price_option_amount($download_id, $price_id);
            $price_name = edd_get_price_option_name($download_id, $price_id);
        } else {
            $price = edd_get_download_price($download_id);
            $price_name = '';
        }
        
        $formatted_price = $price ? edd_currency_filter(edd_format_amount($price)) : esc_html__('Free', 'edd-customer-dashboard-pro');
        $thumbnail = get_the_post_thumbnail_url($download_id, 'medium');
        $download_url = get_permalink($download_id);
        
        // Check if already in cart
        $in_cart = $has_variable_pricing ? 
            edd_item_in_cart($download_id, array('price_id' => $price_id)) : 
            edd_item_in_cart($download_id);
    ?>
    
    <div class="bg-gray-50/80 rounded-2xl p-6 border border-gray-200/50 text-center hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
        <?php if ($thumbnail) : ?>
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl overflow-hidden">
            <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($download->post_title); ?>" class="w-full h-full object-cover">
        </div>
        <?php else : ?>
        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white text-2xl mx-auto mb-4">
            🎨
        </div>
        <?php endif; ?>
        
        <h3 class="text-lg font-semibold text-gray-800 mb-2">
            <a href="<?php echo esc_url($download_url); ?>" class="hover:text-indigo-600 transition-colors">
                <?php echo esc_html($download->post_title); ?>
            </a>
        </h3>
        
        <?php if ($price_name) : ?>
        <p class="text-sm text-gray-600 mb-2"><?php echo esc_html($price_name); ?></p>
        <?php endif; ?>
        
        <p class="text-xl font-bold text-indigo-600 mb-4"><?php echo esc_html($formatted_price); ?></p>
        
        <div class="space-y-2">
            <!-- Product Details Link -->
            <a href="<?php echo esc_url($download_url); ?>" 
               class="block w-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-4 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300 text-decoration-none text-center">
                👁️ <?php esc_html_e('Product Details', 'edd-customer-dashboard-pro'); ?>
            </a>
            
            <!-- Remove from Wishlist Button -->
            <form method="post" action="" class="w-full">
                <?php wp_nonce_field('eddcdp_remove_wishlist', 'eddcdp_nonce'); ?>
                <input type="hidden" name="eddcdp_action" value="remove_from_wishlist">
                <input type="hidden" name="download_id" value="<?php echo esc_attr($download_id); ?>">
                <?php if ($price_id !== null) : ?>
                <input type="hidden" name="price_id" value="<?php echo esc_attr($price_id); ?>">
                <?php endif; ?>
                <button type="submit" 
                        onclick="return confirm('<?php esc_attr_e('Are you sure you want to remove this item from your wishlist?', 'edd-customer-dashboard-pro'); ?>')"
                        class="w-full bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors">
                    ❌ <?php esc_html_e('Remove', 'edd-customer-dashboard-pro'); ?>
                </button>
            </form>
        </div>
    </div>
    
    <?php endforeach; ?>
</div>

<?php else : ?>
<div class="bg-gray-50/80 rounded-2xl p-12 text-center border border-gray-200/50">
    <div class="w-24 h-24 bg-gradient-to-br from-pink-500 to-rose-600 rounded-full flex items-center justify-center text-white text-4xl mx-auto mb-6">
        ❤️
    </div>
    <h3 class="text-2xl font-bold text-gray-800 mb-3"><?php esc_html_e('Your Wishlist is Empty', 'edd-customer-dashboard-pro'); ?></h3>
    <p class="text-gray-600 mb-6"><?php esc_html_e('Start adding products to your wishlist to keep track of items you want to purchase later!', 'edd-customer-dashboard-pro'); ?></p>
    <a href="<?php echo esc_url(home_url('/downloads/')); ?>" 
       class="bg-gradient-to-r from-pink-500 to-rose-600 text-white px-6 py-3 rounded-xl font-medium hover:shadow-lg transition-all duration-300 text-decoration-none">
        🛒 <?php esc_html_e('Browse Products', 'edd-customer-dashboard-pro'); ?>
    </a>
</div>
<?php endif; ?>

<?php if (!empty($public_wishlist_urls)) : ?>
<!-- Public Wishlist Sharing Section -->
<div class="bg-blue-50/80 rounded-2xl p-6 border border-blue-200/50 mt-8">
    <h3 class="text-xl font-semibold text-blue-800 mb-4 flex items-center gap-2">
        🔗 <?php esc_html_e('Share Your Public Wishlists', 'edd-customer-dashboard-pro'); ?>
    </h3>
    <div class="space-y-3">
        <?php foreach ($public_wishlist_urls as $wishlist) : ?>
        <div class="bg-white/80 rounded-lg p-4 border border-blue-200">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h4 class="font-medium text-gray-800"><?php echo esc_html($wishlist['title']); ?></h4>
                    <p class="text-sm text-gray-600"><?php echo esc_html($wishlist['item_count']); ?></p>
                </div>
                <div class="flex gap-2">
                    <a href="<?php echo esc_url($wishlist['url']); ?>" 
                       class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors text-decoration-none text-sm">
                        👁️ <?php esc_html_e('View', 'edd-customer-dashboard-pro'); ?>
                    </a>
                    <button onclick="copyToClipboard('<?php echo esc_js($wishlist['url']); ?>')" 
                            class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors text-sm">
                        📋 <?php esc_html_e('Copy Link', 'edd-customer-dashboard-pro'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification('<?php esc_js_e('Link copied to clipboard!', 'edd-customer-dashboard-pro'); ?>', 'success');
        }).catch(() => {
            fallbackCopy(text);
        });
    } else {
        fallbackCopy(text);
    }
}

function fallbackCopy(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.opacity = '0';
    document.body.appendChild(textArea);
    textArea.select();
    
    try {
        document.execCommand('copy');
        showNotification('<?php esc_js_e('Link copied to clipboard!', 'edd-customer-dashboard-pro'); ?>', 'success');
    } catch (err) {
        showNotification('<?php esc_js_e('Failed to copy link.', 'edd-customer-dashboard-pro'); ?>', 'error');
    }
    
    document.body.removeChild(textArea);
}

// Simple notification function
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transition-all duration-300`;
    
    switch (type) {
        case 'success':
            notification.className += ' bg-green-500 text-white';
            break;
        case 'error':
            notification.className += ' bg-red-500 text-white';
            break;
        default:
            notification.className += ' bg-blue-500 text-white';
    }
    
    notification.innerHTML = `
        <div class="flex items-center justify-between">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200 text-xl">×</button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 3000);
}
</script>