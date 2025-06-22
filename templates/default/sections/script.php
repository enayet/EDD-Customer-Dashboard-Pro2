<?php
/**
 * Dashboard JavaScript Section
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<script>
function dashboard() {
    return {
        activeTab: 'purchases',
        downloading: null,
        newSiteUrl: '',
        
        tabs: [
            { id: 'purchases', label: '📦 <?php _e('Purchases', 'eddcdp'); ?>' },
            { id: 'downloads', label: '⬇️ <?php _e('Downloads', 'eddcdp'); ?>' },
            { id: 'licenses', label: '🔑 <?php _e('Licenses', 'eddcdp'); ?>' },
            { id: 'wishlist', label: '❤️ <?php _e('Wishlist', 'eddcdp'); ?>' },
            { id: 'analytics', label: '📊 <?php _e('Analytics', 'eddcdp'); ?>' },
            { id: 'support', label: '💬 <?php _e('Support', 'eddcdp'); ?>' }
        ],
        
        downloadFile(productId) {
            this.downloading = productId;
            setTimeout(() => {
                this.downloading = null;
            }, 2000);
        },
        
        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                console.log('License key copied to clipboard');
            });
        },
        
        activateSite() {
            if (this.newSiteUrl) {
                console.log('Activating site:', this.newSiteUrl);
                this.newSiteUrl = '';
            }
        }
    }
}
</script>