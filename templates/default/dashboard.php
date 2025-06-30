<?php
/**
 * Default Dashboard Template - Fixed to match default.html design
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user and settings
$current_user = wp_get_current_user();
$settings = get_option('eddcdp_settings', array());
$enabled_sections = isset($settings['enabled_sections']) ? $settings['enabled_sections'] : array();

// Include header
include 'header.php';

// Get order details instance
$order_details = EDDCDP_Order_Details::instance();

// Check if we're in fullscreen mode
$is_fullscreen = defined('EDDCDP_IS_FULLSCREEN') && EDDCDP_IS_FULLSCREEN;

?>

<div class="dashboard-container">
    
    <!-- Check what we're viewing -->
    <?php if ($order_details->is_viewing_order_details()) : ?>
        
        <!-- Order Details View -->
        <?php include 'sections/order-details.php'; ?>
        
    <?php elseif ($order_details->is_viewing_order_licenses()) : ?>
        
        <!-- Order Licenses View -->
        <?php include 'sections/order-licenses.php'; ?>
        
    <?php elseif (isset($_GET['eddcdp_invoice_form']) && isset($_GET['payment_id'])) : ?>

        <!-- Invoice View -->
        <?php include 'sections/invoice.php'; ?>
        
    <?php else : ?>
        
        <!-- Normal Dashboard View -->
        
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="welcome-section">
                <div class="welcome-text">
                    <h1>
                        <?php 
                        /* translators: %s: User display name */
                        printf(esc_html__('Welcome back, %s!', 'edd-customer-dashboard-pro'), esc_html($current_user->display_name)); 
                        ?>
                    </h1>
                    <p><?php esc_html_e('Manage your purchases, downloads, and account settings', 'edd-customer-dashboard-pro'); ?></p>
                </div>
                <div class="user-avatar">
                    <?php echo esc_html(strtoupper(substr($current_user->display_name, 0, 2))); ?>
                </div>
            </div>
        </div>

        <!-- Stats Grid Section -->
        <?php include 'sections/stats.php'; ?>
        
        <!-- Navigation Tabs -->
        <div class="dashboard-nav">
            <div class="nav-tabs">
                <?php if (!empty($enabled_sections['purchases'])) : ?>
                <a href="#" class="nav-tab active" data-section="purchases">
                    📦 <?php esc_html_e('Purchases', 'edd-customer-dashboard-pro'); ?>
                </a>
                <?php endif; ?>
                
                <?php if (!empty($enabled_sections['downloads'])) : ?>
                <a href="#" class="nav-tab" data-section="downloads">
                    ⬇️ <?php esc_html_e('Downloads', 'edd-customer-dashboard-pro'); ?>
                </a>
                <?php endif; ?>
                
                <?php if (!empty($enabled_sections['licenses'])) : ?>
                <a href="#" class="nav-tab" data-section="licenses">
                    🔑 <?php esc_html_e('Licenses', 'edd-customer-dashboard-pro'); ?>
                </a>
                <?php endif; ?>
                
                <?php if (!empty($enabled_sections['wishlist'])) : ?>
                <a href="#" class="nav-tab" data-section="wishlist">
                    ❤️ <?php esc_html_e('Wishlist', 'edd-customer-dashboard-pro'); ?>
                </a>
                <?php endif; ?>
                
                <?php if (!empty($enabled_sections['analytics'])) : ?>
                <a href="#" class="nav-tab" data-section="analytics">
                    📊 <?php esc_html_e('Analytics', 'edd-customer-dashboard-pro'); ?>
                </a>
                <?php endif; ?>
                
                <?php if (!empty($enabled_sections['support'])) : ?>
                <a href="#" class="nav-tab" data-section="support">
                    💬 <?php esc_html_e('Support', 'edd-customer-dashboard-pro'); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <!-- Tab Content Sections -->
            <?php if (!empty($enabled_sections['purchases'])) : ?>
            <div class="content-section active" id="purchases">
                <?php include 'sections/purchases.php'; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($enabled_sections['downloads'])) : ?>
            <div class="content-section" id="downloads">
                <?php include 'sections/downloads.php'; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($enabled_sections['licenses'])) : ?>
            <div class="content-section" id="licenses">
                <?php include 'sections/licenses.php'; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($enabled_sections['wishlist'])) : ?>
            <div class="content-section" id="wishlist">
                <?php include 'sections/wishlist.php'; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($enabled_sections['analytics'])) : ?>
            <div class="content-section" id="analytics">
                <?php include 'sections/analytics.php'; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($enabled_sections['support'])) : ?>
            <div class="content-section" id="support">
                <?php include 'sections/support.php'; ?>
            </div>
            <?php endif; ?>
        </div>
        
    <?php endif; ?>
</div>

<script>
// Tab Navigation - Fixed JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const navTabs = document.querySelectorAll('.nav-tab');
    const contentSections = document.querySelectorAll('.content-section');

    // Debug: Check if elements are found
    console.log('Nav tabs found:', navTabs.length);
    console.log('Content sections found:', contentSections.length);

    navTabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            console.log('Tab clicked:', this.getAttribute('data-section'));
            
            // Remove active class from all tabs and sections
            navTabs.forEach(t => t.classList.remove('active'));
            contentSections.forEach(s => s.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Show corresponding content section
            const targetSection = this.getAttribute('data-section');
            const targetElement = document.getElementById(targetSection);
            if (targetElement) {
                targetElement.classList.add('active');
                console.log('Activated section:', targetSection);
            } else {
                console.error('Target element not found:', targetSection);
            }
        });
    });

    // Handle initial hash-based navigation
    function handleHashNavigation() {
        const hash = window.location.hash.substring(1);
        if (hash) {
            const targetTab = document.querySelector(`[data-section="${hash}"]`);
            if (targetTab) {
                targetTab.click();
            }
        }
    }

    // Set initial tab and handle hash changes
    handleHashNavigation();
    window.addEventListener('hashchange', handleHashNavigation);

    // Copy license key functionality
    document.querySelectorAll('.license-key').forEach(licenseKey => {
        licenseKey.style.cursor = 'pointer';
        licenseKey.title = 'Click to copy';
        
        licenseKey.addEventListener('click', function() {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(this.textContent).then(() => {
                    const originalBg = this.style.background;
                    this.style.background = 'rgba(67, 233, 123, 0.2)';
                    this.style.transition = 'background 0.3s ease';
                    
                    setTimeout(() => {
                        this.style.background = originalBg;
                    }, 1000);
                });
            }
        });
    });

    // Download button animations
    document.querySelectorAll('.btn').forEach(btn => {
        if (btn.textContent.includes('Download')) {
            btn.addEventListener('click', function(e) {
                // Don't prevent default for actual download links
                if (this.tagName === 'A' && this.href) {
                    return;
                }
                
                const originalText = this.innerHTML;
                this.innerHTML = '⏳ Preparing...';
                this.disabled = true;
                
                setTimeout(() => {
                    this.innerHTML = '✅ Downloaded';
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.disabled = false;
                    }, 2000);
                }, 1500);
            });
        }
    });

    // Stat card hover animations
    document.querySelectorAll('.stat-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
});

// Global utility functions
window.eddcdpUtils = {
    switchTab: function(tabId) {
        const targetTab = document.querySelector(`[data-section="${tabId}"]`);
        if (targetTab) {
            targetTab.click();
        }
    },
    
    showNotification: function(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = 'eddcdp-notification';
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            max-width: 400px;
            animation: slideInRight 0.3s ease-out;
            font-family: inherit;
        `;
        
        switch (type) {
            case 'success':
                notification.style.background = '#10b981';
                notification.style.color = 'white';
                break;
            case 'error':
                notification.style.background = '#ef4444';
                notification.style.color = 'white';
                break;
            default:
                notification.style.background = '#3b82f6';
                notification.style.color = 'white';
        }
        
        notification.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                <span>${message}</span>
                <button type="button" onclick="this.closest('.eddcdp-notification').remove()" 
                        style="background: none; border: none; color: inherit; font-size: 18px; cursor: pointer; padding: 0; margin: 0;">×</button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }
};

// Add slideInRight animation
if (!document.querySelector('#eddcdp-animations')) {
    const style = document.createElement('style');
    style.id = 'eddcdp-animations';
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

<?php include 'footer.php'; ?>