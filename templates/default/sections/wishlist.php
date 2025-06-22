<?php
/**
 * Wishlist Section Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if EDD Wish Lists is active
if (!class_exists('EDD_Wish_Lists')) {
    echo '<div class="bg-yellow-50/80 rounded-2xl p-6 border border-yellow-200/50">';
    echo '<p class="text-yellow-800">' . __('Wish Lists extension is not active.', 'eddcdp') . '</p>';
    echo '</div>';
    return;
}

// Get current user and wishlist
$current_user = wp_get_current_user();
$wishlist = edd_wl_get_wish_list($current_user->ID);
$wishlist_items = array();

if ($wishlist) {
    $wishlist_items = edd_wl_get_wish_list_downloads($wishlist->ID);
}
?>

<h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
    ❤️ <?php _e('Your Wishlist', 'eddcdp'); ?>
</h2>

<?php if ($wishlist_items) : ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($wishlist_items as $download_id) : 
        $download = get_post($download_id);
        $price = edd_get_download_price($download_id);
        $formatted_price = $price ? edd_currency_filter(edd_format_amount($price)) : __('Free', 'eddcdp');
        $thumbnail = get_the_post_thumbnail_url($download_id, 'medium');
        $has_variable_pricing = edd_has_variable_prices($download_id);
    ?>
    
    <div class="bg-gray-50/80 rounded-2xl p-6 border border-gray-200/50 text-center hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
        <?php if ($thumbnail) : ?>
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl overflow-hidden">
            <img src="<?php echo $thumbnail; ?>" alt="<?php echo $download->post_title; ?>" class="w-full h-full object-cover">
        </div>
        <?php else : ?>
        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white text-2xl mx-auto mb-4">
            🎨
        </div>
        <?php endif; ?>
        
        <h3 class="text-lg font-semibold text-gray-800 mb-2"><?php echo $download->post_title; ?></h3>
        
        <?php if (!$has_variable_pricing) : ?>
        <p class="text-2xl font-bold text-indigo-600 mb-4"><?php echo $formatted_price; ?></p>
        <?php else : ?>
        <p class="text-lg font-bold text-indigo-600 mb-4"><?php _e('Variable Pricing', 'eddcdp'); ?></p>
        <?php endif; ?>
        
        <div class="space-y-2">
            <?php if (!$has_variable_pricing) : ?>
            <button onclick="addToCart(<?php echo $download_id; ?>)" class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-4 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300">
                🛒 <?php _e('Add to Cart', 'eddcdp'); ?>
            </button>
            <?php else : ?>
            <button onclick="window.location.href='<?php echo get_permalink($download_id); ?>'" class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-4 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300">
                👁️ <?php _e('View Options', 'eddcdp'); ?>
            </button>
            <?php endif; ?>
            
            <button onclick="removeFromWishlist(<?php echo $download_id; ?>)" class="w-full bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors">
                ❌ <?php _e('Remove', 'eddcdp'); ?>
            </button>
        </div>
    </div>
    
    <?php endforeach; ?>
</div>

<?php else : ?>
<div class="bg-gray-50/80 rounded-2xl p-12 text-center border border-gray-200/50">
    <div class="w-24 h-24 bg-gradient-to-br from-pink-500 to-rose-600 rounded-full flex items-center justify-center text-white text-4xl mx-auto mb-6">
        ❤️
    </div>
    <h3 class="text-2xl font-bold text-gray-800 mb-3"><?php _e('Your Wishlist is Empty', 'eddcdp'); ?></h3>
    <p class="text-gray-600 mb-6"><?php _e('Start adding products to your wishlist to keep track of items you want to purchase later!', 'eddcdp'); ?></p>
    <button onclick="window.location.href='<?php echo home_url('/downloads/'); ?>'" class="bg-gradient-to-r from-pink-500 to-rose-600 text-white px-6 py-3 rounded-xl font-medium hover:shadow-lg transition-all duration-300">
        🛒 <?php _e('Browse Products', 'eddcdp'); ?>
    </button>
</div>
<?php endif; ?>

<script>
function addToCart(downloadId) {
    // AJAX call to add to cart
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'edd_add_to_cart',
            download_id: downloadId,
            nonce: '<?php echo wp_create_nonce('edd_add_to_cart'); ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('<?php _e('Added to cart successfully!', 'eddcdp'); ?>');
        } else {
            alert('<?php _e('Error adding to cart. Please try again.', 'eddcdp'); ?>');
        }
    });
}

function removeFromWishlist(downloadId) {
    if (confirm('<?php _e('Are you sure you want to remove this item from your wishlist?', 'eddcdp'); ?>')) {
        // AJAX call to remove from wishlist
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'edd_wl_remove_from_wish_list',
                download_id: downloadId,
                nonce: '<?php echo wp_create_nonce('edd_wl_remove_from_wish_list'); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('<?php _e('Error removing from wishlist. Please try again.', 'eddcdp'); ?>');
            }
        });
    }
}
</script>