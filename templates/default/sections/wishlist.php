<?php
/**
 * Wishlist Section Template - Updated Design
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if EDD Wish Lists is active
if (!class_exists('EDD_Wish_Lists')) {
    ?>
    <div class="empty-state">
        <div class="empty-icon">❤️</div>
        <h3><?php esc_html_e('Wish Lists Not Available', 'edd-customer-dashboard-pro'); ?></h3>
        <p><?php esc_html_e('Wish Lists extension is not active.', 'edd-customer-dashboard-pro'); ?></p>
        <button onclick="window.location.href='<?php echo esc_url(home_url('/downloads/')); ?>'" 
                class="btn">
            🛒 <?php esc_html_e('Browse Products', 'edd-customer-dashboard-pro'); ?>
        </button>
    </div>
    <?php
    return;
}

// Get current user
$current_user = wp_get_current_user();

if (!is_user_logged_in()) {
    ?>
    <div class="empty-state">
        <div class="empty-icon">⚠️</div>
        <h3><?php esc_html_e('Please Log In', 'edd-customer-dashboard-pro'); ?></h3>
        <p><?php esc_html_e('Please log in to view your wishlist.', 'edd-customer-dashboard-pro'); ?></p>
    </div>
    <?php
    return;
}

// Get wishlist data using our handler class
$wishlist_handler = EDDCDP_Wishlist_Handler::instance();
$wishlist_items = $wishlist_handler->get_user_wishlist_items();
$public_wishlist_urls = $wishlist_handler->get_public_wishlist_urls();
?>

<h2 class="section-title"><?php esc_html_e('Your Wishlist', 'edd-customer-dashboard-pro'); ?></h2>

<?php if (!empty($wishlist_items)) : ?>
<div class="wishlist-grid">
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
    
    <div class="wishlist-item">
        <?php if ($thumbnail) : ?>
        <div class="product-image" style="background-image: url('<?php echo esc_url($thumbnail); ?>'); background-size: cover; background-position: center;">
        </div>
        <?php else : ?>
        <div class="product-image">🎨</div>
        <?php endif; ?>
        
        <div style="padding: 0 5px;">
            <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 8px; color: var(--dark);">
                <a href="<?php echo esc_url($download_url); ?>" 
                   style="color: inherit; text-decoration: none;"
                   onmouseover="this.style.color='var(--primary)'" 
                   onmouseout="this.style.color='inherit'">
                    <?php echo esc_html($download->post_title); ?>
                </a>
            </h3>
            
            <?php if ($price_name) : ?>
            <p style="color: var(--gray); font-size: 0.9rem; margin-bottom: 8px;">
                <?php echo esc_html($price_name); ?>
            </p>
            <?php endif; ?>
            
            <p style="font-size: 1.2rem; font-weight: 700; color: var(--primary); margin-bottom: 15px;">
                <?php echo esc_html($formatted_price); ?>
            </p>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 8px;">
            <!-- Product Details Link -->
            <a href="<?php echo esc_url($download_url); ?>" 
               class="btn btn-secondary">
                👁️ <?php esc_html_e('View Details', 'edd-customer-dashboard-pro'); ?>
            </a>
            
            <!-- Add to Cart or View Cart -->
            <?php if ($in_cart) : ?>
            <a href="<?php echo esc_url(edd_get_checkout_uri()); ?>" 
               class="btn btn-success">
                ✅ <?php esc_html_e('View Cart', 'edd-customer-dashboard-pro'); ?>
            </a>
            <?php else : ?>
            <button onclick="addToCart(<?php echo esc_js($download_id); ?><?php echo $price_id !== null ? ', ' . esc_js($price_id) : ''; ?>)" 
                    class="btn">
                🛒 <?php esc_html_e('Add to Cart', 'edd-customer-dashboard-pro'); ?>
            </button>
            <?php endif; ?>
            
            <!-- Remove from Wishlist Button -->
            <form method="post" action="" style="margin: 0;">
                <?php wp_nonce_field('eddcdp_remove_wishlist', 'eddcdp_nonce'); ?>
                <input type="hidden" name="eddcdp_action" value="remove_from_wishlist">
                <input type="hidden" name="download_id" value="<?php echo esc_attr($download_id); ?>">
                <?php if ($price_id !== null) : ?>
                <input type="hidden" name="price_id" value="<?php echo esc_attr($price_id); ?>">
                <?php endif; ?>
                <button type="submit" 
                        onclick="return confirm('<?php esc_attr_e('Are you sure you want to remove this item from your wishlist?', 'edd-customer-dashboard-pro'); ?>')"
                        class="btn btn-secondary"
                        style="width: 100%; background: rgba(245, 87, 108, 0.1); color: #d32f2f; border-color: rgba(245, 87, 108, 0.3);">
                    ❌ <?php esc_html_e('Remove', 'edd-customer-dashboard-pro'); ?>
                </button>
            </form>
        </div>
    </div>
    
    <?php endforeach; ?>
</div>

<?php else : ?>
<!-- Empty State -->
<div class="empty-state">
    <div class="empty-icon">❤️</div>
    <h3><?php esc_html_e('Your Wishlist is Empty', 'edd-customer-dashboard-pro'); ?></h3>
    <p><?php esc_html_e('Start adding products to your wishlist to keep track of items you want to purchase later!', 'edd-customer-dashboard-pro'); ?></p>
    <button onclick="window.location.href='<?php echo esc_url(home_url('/downloads/')); ?>'" 
            class="btn">
        🛒 <?php esc_html_e('Browse Products', 'edd-customer-dashboard-pro'); ?>
    </button>
</div>
<?php endif; ?>

<?php if (!empty($public_wishlist_urls)) : ?>
<!-- Public Wishlist Sharing Section -->
<div style="background: rgba(33, 150, 243, 0.05); border-radius: 12px; padding: 20px; margin-top: 30px; border: 1px solid rgba(33, 150, 243, 0.1);">
    <h3 style="color: #1976d2; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
        🔗 <?php esc_html_e('Share Your Public Wishlists', 'edd-customer-dashboard-pro'); ?>
    </h3>
    <div style="display: grid; gap: 12px;">
        <?php foreach ($public_wishlist_urls as $wishlist) : ?>
        <div style="background: rgba(255, 255, 255, 0.8); border-radius: 8px; padding: 15px; border: 1px solid rgba(33, 150, 243, 0.2);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h4 style="margin: 0 0 5px 0; color: var(--dark);"><?php echo esc_html($wishlist['title']); ?></h4>
                    <p style="margin: 0; color: var(--gray); font-size: 0.9rem;">
                        <?php 
                        /* translators: %d: Number of items in wishlist */
                        printf(esc_html(_n('%d item', '%d items', $wishlist['item_count'], 'edd-customer-dashboard-pro')), esc_html(number_format_i18n($wishlist['item_count']))); 
                        ?>
                    </p>
                </div>
                <div style="display: flex; gap: 8px;">
                    <a href="<?php echo esc_url($wishlist['url']); ?>" 
                       class="btn btn-secondary"
                       style="padding: 8px 12px; font-size: 0.85rem;">
                        👁️ <?php esc_html_e('View', 'edd-customer-dashboard-pro'); ?>
                    </a>
                    <button onclick="copyWishlistUrl('<?php echo esc_js($wishlist['url']); ?>')" 
                            class="btn btn-secondary"
                            style="padding: 8px 12px; font-size: 0.85rem;">
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
// Add to cart functionality
function addToCart(downloadId, priceId = null) {
    const params = new URLSearchParams({
        action: 'edd_add_to_cart',
        download_id: downloadId
    });
    
    if (priceId !== null) {
        params.append('edd_options[price_id]', priceId);
    }
    
    fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: params
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('<?php esc_html_e('Added to cart successfully!', 'edd-customer-dashboard-pro'); ?>', 'success');
            
            // Reload page to update cart status
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification(data.data || '<?php esc_html_e('Error adding to cart. Please try again.', 'edd-customer-dashboard-pro'); ?>', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('<?php esc_html_e('Error adding to cart. Please try again.', 'edd-customer-dashboard-pro'); ?>', 'error');
    });
}

// Copy wishlist URL
function copyWishlistUrl(url) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(() => {
            showNotification('<?php esc_html_e('Wishlist link copied to clipboard!', 'edd-customer-dashboard-pro'); ?>', 'success');
        }).catch(() => {
            fallbackCopyUrl(url);
        });
    } else {
        fallbackCopyUrl(url);
    }
}

function fallbackCopyUrl(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.opacity = '0';
    document.body.appendChild(textArea);
    textArea.select();
    
    try {
        document.execCommand('copy');
        showNotification('<?php esc_html_e('Wishlist link copied to clipboard!', 'edd-customer-dashboard-pro'); ?>', 'success');
    } catch (err) {
        showNotification('<?php esc_html_e('Failed to copy link.', 'edd-customer-dashboard-pro'); ?>', 'error');
    }
    
    document.body.removeChild(textArea);
}

// Simple notification function
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        max-width: 400px;
        font-family: inherit;
        font-size: 0.9rem;
        animation: slideInRight 0.3s ease-out;
    `;
    
    switch (type) {
        case 'success':
            notification.style.background = 'linear-gradient(135deg, #43e97b, #38f9d7)';
            notification.style.color = 'white';
            break;
        case 'error':
            notification.style.background = 'linear-gradient(135deg, #f5576c, #d32f2f)';
            notification.style.color = 'white';
            break;
        default:
            notification.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
            notification.style.color = 'white';
    }
    
    notification.innerHTML = `
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" 
                    style="background: none; border: none; color: inherit; font-size: 18px; cursor: pointer; padding: 0; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">×</button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 4000);
}

// Add slideInRight animation
if (!document.querySelector('#wishlist-animations')) {
    const style = document.createElement('style');
    style.id = 'wishlist-animations';
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(style);
}
</script>