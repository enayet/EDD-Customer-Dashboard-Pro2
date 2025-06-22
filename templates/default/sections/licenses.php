<?php
/**
 * Licenses Section Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if EDD Software Licensing is active
if (!class_exists('EDD_Software_Licensing')) {
    echo '<div class="bg-yellow-50/80 rounded-2xl p-6 border border-yellow-200/50">';
    echo '<p class="text-yellow-800">' . __('Software Licensing extension is not active.', 'eddcdp') . '</p>';
    echo '</div>';
    return;
}

// Get current user and licenses
$current_user = wp_get_current_user();
$licenses = edd_software_licensing()->licenses_db->get_licenses(array(
    'user_id' => $current_user->ID,
    'number' => 20
));
?>

<h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
    🔑 <?php _e('License Management', 'eddcdp'); ?>
</h2>

<?php if ($licenses) : ?>
<div class="space-y-6">
    <?php foreach ($licenses as $license) : 
        $license_obj = edd_software_licensing()->get_license($license->ID);
        $download_id = $license->download_id;
        $download_name = get_the_title($download_id);
        $is_active = $license_obj->is_active();
        $is_expired = $license_obj->is_expired();
        $sites = $license_obj->get_sites();
        $activation_limit = $license_obj->activation_limit;
        $activation_count = $license_obj->activation_count;
    ?>
    
    <!-- License Item -->
    <div class="<?php echo $is_active && !$is_expired ? 'bg-green-50/50 border-green-200/50' : 'bg-red-50/50 border-red-200/50'; ?> rounded-2xl p-6 border">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
            <h3 class="text-xl font-semibold text-gray-800"><?php echo $download_name; ?></h3>
            <?php if ($is_active && !$is_expired) : ?>
            <span class="bg-green-100 text-green-800 px-4 py-2 rounded-full text-sm font-medium">
                ✅ <?php _e('Active', 'eddcdp'); ?>
            </span>
            <?php else : ?>
            <span class="bg-red-100 text-red-800 px-4 py-2 rounded-full text-sm font-medium">
                ❌ <?php _e('Expired', 'eddcdp'); ?>
            </span>
            <?php endif; ?>
        </div>
        
        <div class="bg-white/80 rounded-xl p-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2"><?php _e('License Key:', 'eddcdp'); ?></label>
                <div 
                    onclick="copyToClipboard('<?php echo $license_obj->key; ?>')"
                    class="bg-gray-100 p-3 rounded-lg font-mono text-sm cursor-pointer hover:bg-gray-200 transition-colors border">
                    <?php echo $license_obj->key; ?>
                </div>
                <p class="text-xs text-gray-500 mt-1"><?php _e('Click to copy', 'eddcdp'); ?></p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-sm text-gray-600">
                        <strong><?php _e('Purchase Date:', 'eddcdp'); ?></strong> 
                        <?php echo date_i18n(get_option('date_format'), strtotime($license_obj->date_created)); ?>
                    </p>
                    <p class="text-sm text-gray-600">
                        <strong><?php _e('Expires:', 'eddcdp'); ?></strong> 
                        <?php 
                        if ($license_obj->expiration) {
                            echo date_i18n(get_option('date_format'), strtotime($license_obj->expiration));
                        } else {
                            _e('Never', 'eddcdp');
                        }
                        ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">
                        <strong><?php _e('Activations:', 'eddcdp'); ?></strong> 
                        <?php 
                        if ($activation_limit > 0) {
                            echo $activation_count . ' ' . __('of', 'eddcdp') . ' ' . $activation_limit . ' ' . __('sites', 'eddcdp');
                        } else {
                            echo $activation_count . ' ' . __('of Unlimited sites', 'eddcdp');
                        }
                        ?>
                    </p>
                    <p class="text-sm text-gray-600">
                        <strong><?php _e('License Type:', 'eddcdp'); ?></strong> 
                        <?php echo ucfirst($license_obj->license_type); ?>
                    </p>
                </div>
            </div>
            
            <!-- Site Management -->
            <div class="border-t pt-4">
                <h4 class="font-medium text-gray-800 mb-3"><?php _e('Manage Sites', 'eddcdp'); ?></h4>
                
                <?php if ($is_active && !$is_expired && ($activation_limit == 0 || $activation_count < $activation_limit)) : ?>
                <div class="flex gap-3 mb-4">
                    <input 
                        type="url" 
                        placeholder="<?php _e('Enter your site URL (e.g., https://example.com)', 'eddcdp'); ?>"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        x-model="newSiteUrl">
                    <button 
                        @click="activateSite()"
                        class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300">
                        ✅ <?php _e('Activate', 'eddcdp'); ?>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php if ($sites) : ?>
                <div class="space-y-2 mb-4">
                    <h5 class="font-medium text-gray-800"><?php _e('Active Sites', 'eddcdp'); ?></h5>
                    <?php foreach ($sites as $site) : ?>
                    <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg">
                        <span class="text-sm"><?php echo esc_url($site); ?></span>
                        <button onclick="deactivateSite('<?php echo $license_obj->ID; ?>', '<?php echo esc_url($site); ?>')" class="text-red-600 hover:text-red-800 text-sm font-medium">
                            🔓 <?php _e('Deactivate', 'eddcdp'); ?>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else : ?>
                <p class="text-gray-500 italic text-sm mb-4"><?php _e('No sites activated yet.', 'eddcdp'); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="flex flex-wrap gap-3 mt-6 pt-4 border-t">
                <?php if ($is_expired) : ?>
                <button class="bg-gradient-to-r from-orange-500 to-amber-500 text-white px-4 py-2 rounded-xl hover:shadow-lg transition-all duration-300">
                    🔄 <?php _e('Renew License', 'eddcdp'); ?>
                </button>
                <?php else : ?>
                <button class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors">
                    🔄 <?php _e('Renew', 'eddcdp'); ?>
                </button>
                <?php endif; ?>
                <button class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors">
                    ⬆️ <?php _e('Upgrade', 'eddcdp'); ?>
                </button>
                <button class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors">
                    📄 <?php _e('Invoice', 'eddcdp'); ?>
                </button>
            </div>
        </div>
    </div>
    
    <?php endforeach; ?>
</div>

<?php else : ?>
<div class="bg-gray-50/80 rounded-2xl p-12 text-center border border-gray-200/50">
    <div class="w-24 h-24 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center text-white text-4xl mx-auto mb-6">
        🔑
    </div>
    <h3 class="text-2xl font-bold text-gray-800 mb-3"><?php _e('No Licenses Found', 'eddcdp'); ?></h3>
    <p class="text-gray-600 mb-6"><?php _e('You don\'t have any software licenses yet. Purchase a licensed product to get started!', 'eddcdp'); ?></p>
    <button onclick="window.location.href='<?php echo home_url('/downloads/'); ?>'" class="bg-gradient-to-r from-purple-500 to-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:shadow-lg transition-all duration-300">
        🛒 <?php _e('Browse Licensed Products', 'eddcdp'); ?>
    </button>
</div>
<?php endif; ?>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // You could add a toast notification here
        console.log('License key copied to clipboard');
    });
}

function deactivateSite(licenseId, siteUrl) {
    if (confirm('<?php _e('Are you sure you want to deactivate this site?', 'eddcdp'); ?>')) {
        // AJAX call to deactivate site
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'edd_deactivate_license_site',
                license_id: licenseId,
                site_url: siteUrl,
                nonce: '<?php echo wp_create_nonce('edd_deactivate_license_site'); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('<?php _e('Error deactivating site. Please try again.', 'eddcdp'); ?>');
            }
        });
    }
}
</script>