<?php
/**
 * Aurora Wishlist Section Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if EDD Wish Lists is active
if (!class_exists('EDD_Wish_Lists') || !function_exists('edd_wl_get_wish_list')) {
    echo '<div class="empty-state">';
    echo '<div class="empty-icon"><i class="fas fa-heart"></i></div>';
    echo '<h3 class="empty-title">' . esc_html__('Wish Lists Not Available', 'edd-customer-dashboard-pro') . '</h3>';
    echo '<p class="empty-text">' . esc_html__('The Wish Lists extension is not active on this site.', 'edd-customer-dashboard-pro') . '</p>';
    echo '<a href="' . esc_url(home_url('/downloads/')) . '" class="btn btn-primary">';
    echo '<i class="fas fa-store"></i> ' . esc_html__('Browse Products', 'edd-customer-dashboard-pro');
    echo '</a>';
    echo '</div>';
    return;
}

// Get current user
$current_user = wp_get_current_user();

if (!is_user_logged_in()) {
    echo '<div class="empty-state">';
    echo '<div class="empty-icon"><i class="fas fa-sign-in-alt"></i></div>';
    echo '<h3 class="empty-title">' . esc_html__('Please log in to view your wishlist.', 'edd-customer-dashboard-pro') . '</h3>';
    echo '</div>';
    return;
}

// Get wishlist data using our handler class
$wishlist_handler = EDDCDP_Wishlist_Handler::instance();
$wishlist_items = $wishlist_handler->get_user_wishlist_items();
$public_wishlist_urls = $wishlist_handler->get_public_wishlist_urls();

// Get stats
$total_items = count($wishlist_items);
$estimated_value = 0;
$available_items = 0;

foreach ($wishlist_items as $item) {
    $download_id = $item['id'];
    $download = get_post($download_id);
    
    if ($download && $download->post_status === 'publish') {
        $available_items++;
        
        // Calculate price
        $has_variable_pricing = edd_has_variable_prices($download_id);
        $price_id = isset($item['options']['price_id']) ? $item['options']['price_id'] : null;
        
        if ($has_variable_pricing && $price_id !== null) {
            $price = edd_get_price_option_amount($download_id, $price_id);
        } else {
            $price = edd_get_download_price($download_id);
        }
        
        $estimated_value += $price ? floatval($price) : 0;
    }
}
?>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <h1 class="dashboard-title"><?php esc_html_e('Your Wishlist', 'edd-customer-dashboard-pro'); ?></h1>
    <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="<?php esc_attr_e('Search wishlist...', 'edd-customer-dashboard-pro'); ?>">
    </div>
</div>

<?php if (!empty($wishlist_items)) : ?>
<!-- Wishlist Stats -->
<div class="stats-grid">
    <div class="stat-card wishlist">
        <div class="stat-value"><?php echo esc_html(number_format_i18n($total_items)); ?></div>
        <div class="stat-label">
            <i class="fas fa-heart"></i>
            <?php esc_html_e('Wishlist Items', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
    <div class="stat-card" style="border-left-color: var(--aurora-secondary);">
        <div class="stat-value"><?php echo esc_html(number_format_i18n($available_items)); ?></div>
        <div class="stat-label">
            <i class="fas fa-check-circle"></i>
            <?php esc_html_e('Available', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
    <div class="stat-card" style="border-left-color: #e67e22;">
        <div class="stat-value"><?php echo esc_html(edd_currency_filter(edd_format_amount($estimated_value))); ?></div>
        <div class="stat-label">
            <i class="fas fa-calculator"></i>
            <?php esc_html_e('Estimated Value', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
    <div class="stat-card" style="border-left-color: #8e44ad;">
        <div class="stat-value"><?php echo esc_html(number_format_i18n(count($public_wishlist_urls))); ?></div>
        <div class="stat-label">
            <i class="fas fa-share-alt"></i>
            <?php esc_html_e('Public Lists', 'edd-customer-dashboard-pro'); ?>
        </div>
    </div>
</div>

<!-- Wishlist Items Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 30px;">
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
        
        // Get product icon based on name
        $product_icon = 'fas fa-box';
        if (stripos($download->post_title, 'plugin') !== false) {
            $product_icon = 'fas fa-plug';
        } elseif (stripos($download->post_title, 'theme') !== false) {
            $product_icon = 'fas fa-paint-brush';
        } elseif (stripos($download->post_title, 'template') !== false) {
            $product_icon = 'fas fa-file-alt';
        } elseif (stripos($download->post_title, 'tool') !== false) {
            $product_icon = 'fas fa-wrench';
        }
    ?>
    
    <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); border: 1px solid var(--aurora-gray-light); transition: all 0.3s ease;" 
         onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 25px rgba(0, 0, 0, 0.1)'" 
         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 15px rgba(0, 0, 0, 0.05)'">
        
        <!-- Product Image/Icon -->
        <div style="text-align: center; margin-bottom: 15px;">
            <?php if ($thumbnail) : ?>
            <div style="width: 80px; height: 80px; margin: 0 auto; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 10px rgba(0,0,0,0.1);">
                <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($download->post_title); ?>" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <?php else : ?>
            <div style="width: 80px; height: 80px; margin: 0 auto; border-radius: 12px; background: linear-gradient(135deg, var(--aurora-primary), var(--aurora-primary-light)); color: white; display: flex; align-items: center; justify-content: center; font-size: 2rem; box-shadow: 0 5px 10px rgba(108, 92, 231, 0.3);">
                <i class="<?php echo esc_attr($product_icon); ?>"></i>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Product Info -->
        <div style="text-align: center;">
            <h3 style="margin: 0 0 8px 0; font-size: 1.1rem; font-weight: 600; color: var(--aurora-dark);">
                <a href="<?php echo esc_url($download_url); ?>" style="color: inherit; text-decoration: none;" 
                   onmouseover="this.style.color='var(--aurora-primary)'" 
                   onmouseout="this.style.color='var(--aurora-dark)'">
                    <?php echo esc_html($download->post_title); ?>
                </a>
            </h3>
            
            <?php if ($price_name) : ?>
            <p style="margin: 0 0 8px 0; color: var(--aurora-gray); font-size: 0.9rem;"><?php echo esc_html($price_name); ?></p>
            <?php endif; ?>
            
            <p style="margin: 0 0 15px 0; font-size: 1.3rem; font-weight: 700; color: var(--aurora-primary);">
                <?php echo esc_html($formatted_price); ?>
            </p>
        </div>
        
        <!-- Action Buttons -->
        <div style="display: flex; flex-direction: column; gap: 8px;">
            <!-- Product Details Button -->
            <a href="<?php echo esc_url($download_url); ?>" class="btn btn-primary" style="width: 100%; text-align: center; justify-content: center;">
                <i class="fas fa-eye"></i> <?php esc_html_e('View Details', 'edd-customer-dashboard-pro'); ?>
            </a>
            
            <!-- Add to Cart / In Cart Button -->
            <?php if ($in_cart) : ?>
            <a href="<?php echo esc_url(edd_get_checkout_uri()); ?>" class="btn btn-success" style="width: 100%; text-align: center; justify-content: center;">
                <i class="fas fa-shopping-cart"></i> <?php esc_html_e('In Cart - Checkout', 'edd-customer-dashboard-pro'); ?>
            </a>
            <?php else : ?>
            <button onclick="addToCartFromWishlist(<?php echo esc_js($download_id); ?>, <?php echo esc_js($price_id); ?>)" class="btn btn-outline" style="width: 100%; justify-content: center;">
                <i class="fas fa-cart-plus"></i> <?php esc_html_e('Add to Cart', 'edd-customer-dashboard-pro'); ?>
            </button>
            <?php endif; ?>
            
            <!-- Remove from Wishlist Button -->
            <form method="post" action="" style="width: 100%;" onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to remove this item from your wishlist?', 'edd-customer-dashboard-pro'); ?>')">
                <?php wp_nonce_field('eddcdp_remove_wishlist', 'eddcdp_nonce'); ?>
                <input type="hidden" name="eddcdp_action" value="remove_from_wishlist">
                <input type="hidden" name="download_id" value="<?php echo esc_attr($download_id); ?>">
                <?php if ($price_id !== null) : ?>
                <input type="hidden" name="price_id" value="<?php echo esc_attr($price_id); ?>">
                <?php endif; ?>
                <button type="submit" class="btn" style="width: 100%; background: #e74c3c; color: white; justify-content: center;" 
                        onmouseover="this.style.background='#c0392b'" 
                        onmouseout="this.style.background='#e74c3c'">
                    <i class="fas fa-trash-alt"></i> <?php esc_html_e('Remove', 'edd-customer-dashboard-pro'); ?>
                </button>
            </form>
        </div>
    </div>
    
    <?php endforeach; ?>
</div>

<?php if (!empty($public_wishlist_urls)) : ?>
<!-- Public Wishlist Sharing Section -->
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 25px; margin-top: 30px; color: white;">
    <h3 style="margin: 0 0 15px 0; font-size: 1.4rem; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-share-alt"></i> <?php esc_html_e('Share Your Public Wishlists', 'edd-customer-dashboard-pro'); ?>
    </h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
        <?php foreach ($public_wishlist_urls as $wishlist) : ?>
        <div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 15px; backdrop-filter: blur(10px);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                <div>
                    <h4 style="margin: 0; font-size: 1.1rem;"><?php echo esc_html($wishlist['title']); ?></h4>
                    <p style="margin: 5px 0 0 0; opacity: 0.8; font-size: 0.9rem;"><?php echo esc_html($wishlist['item_count']); ?></p>
                </div>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="<?php echo esc_url($wishlist['url']); ?>" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); flex: 1; text-align: center; justify-content: center; text-decoration: none;">
                    <i class="fas fa-external-link-alt"></i> <?php esc_html_e('View', 'edd-customer-dashboard-pro'); ?>
                </a>
                <button onclick="copyWishlistUrl('<?php echo esc_js($wishlist['url']); ?>')" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3);">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php else : ?>
<!-- Empty State -->
<div class="empty-state">
    <div class="empty-icon">
        <i class="fas fa-heart"></i>
    </div>
    <h3 class="empty-title"><?php esc_html_e('Your Wishlist is Empty', 'edd-customer-dashboard-pro'); ?></h3>
    <p class="empty-text"><?php esc_html_e('Start adding products to your wishlist to keep track of items you want to purchase later!', 'edd-customer-dashboard-pro'); ?></p>
    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
        <a href="<?php echo esc_url(home_url('/downloads/')); ?>" class="btn btn-primary">
            <i class="fas fa-store"></i> <?php esc_html_e('Browse Products', 'edd-customer-dashboard-pro'); ?>
        </a>
        <button onclick="window.AuroraDashboard?.switchSection('products')" class="btn btn-outline">
            <i class="fas fa-box-open"></i> <?php esc_html_e('View My Products', 'edd-customer-dashboard-pro'); ?>
        </button>
    </div>
</div>
<?php endif; ?>

<script>
function addToCartFromWishlist(downloadId, priceId) {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?php esc_js_e('Adding...', 'edd-customer-dashboard-pro'); ?>';
    button.disabled = true;
    
    // Add to cart (simplified - in real implementation, use AJAX)
    const params = new URLSearchParams();
    params.append('edd_action', 'add_to_cart');
    params.append('download_id', downloadId);
    if (priceId !== null) {
        params.append('edd_options[price_id]', priceId);
    }
    
    fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
        method: 'POST',
        body: params
    })
    .then(response => response.json())
    .then(data => {
        if (data.success || data.cart_item) {
            button.innerHTML = '<i class="fas fa-check"></i> <?php esc_js_e('Added to Cart!', 'edd-customer-dashboard-pro'); ?>';
            button.className = button.className.replace('btn-outline', 'btn-success');
            
            setTimeout(() => {
                location.reload(); // Refresh to show "In Cart" button
            }, 1500);
        } else {
            button.innerHTML = originalText;
            button.disabled = false;
            window.AuroraDashboard?.showNotification('<?php esc_js_e('Error adding to cart. Please try again.', 'edd-customer-dashboard-pro'); ?>', 'error');
        }
    })
    .catch(error => {
        button.innerHTML = originalText;
        button.disabled = false;
        window.AuroraDashboard?.showNotification('<?php esc_js_e('Error adding to cart. Please try again.', 'edd-customer-dashboard-pro'); ?>', 'error');
    });
}

function copyWishlistUrl(url) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(() => {
            window.AuroraDashboard?.showNotification('<?php esc_js_e('Wishlist link copied to clipboard!', 'edd-customer-dashboard-pro'); ?>', 'success');
        });
    }
}
</script>