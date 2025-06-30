<?php
/**
 * Support Section Template - Updated Design
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user and customer data
$current_user = wp_get_current_user();
$customer = edd_get_customer_by('email', $current_user->user_email);

// Get recent orders for support context
$recent_orders = array();
if ($customer) {
    $recent_orders = edd_get_orders(array(
        'customer' => $customer->id,
        'number' => 10,
        'status' => array('complete', 'pending', 'processing'),
        'orderby' => 'date_created',
        'order' => 'DESC'
    ));
}

// Get user's active licenses for reference
$active_licenses = array();
if (class_exists('EDD_Software_Licensing') && function_exists('edd_software_licensing')) {
    $licenses = edd_software_licensing()->licenses_db->get_licenses(array(
        'user_id' => $current_user->ID,
        'status' => 'active',
        'number' => 999999
    ));
    
    if ($licenses) {
        $active_licenses = array_slice($licenses, 0, 5); // Show only first 5
    }
}
?>

<h2 class="section-title"><?php esc_html_e('Support Center', 'edd-customer-dashboard-pro'); ?></h2>

<!-- Support Quick Stats -->
<div class="stats-grid" style="margin-bottom: 30px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">📞</div>
        <div class="stat-number">24/7</div>
        <div class="stat-label"><?php esc_html_e('Support Available', 'edd-customer-dashboard-pro'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">⚡</div>
        <div class="stat-number">&lt; 2h</div>
        <div class="stat-label"><?php esc_html_e('Avg Response Time', 'edd-customer-dashboard-pro'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a, #fee140);">💬</div>
        <div class="stat-number"><?php echo esc_html(number_format_i18n(count($recent_orders))); ?></div>
        <div class="stat-label"><?php esc_html_e('Your Orders', 'edd-customer-dashboard-pro'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">🔑</div>
        <div class="stat-number"><?php echo esc_html(number_format_i18n(count($active_licenses))); ?></div>
        <div class="stat-label"><?php esc_html_e('Active Licenses', 'edd-customer-dashboard-pro'); ?></div>
    </div>
</div>

<!-- Contact Methods -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <!-- Email Support -->
    <div class="purchase-item">
        <div style="text-align: center; padding: 10px;">
            <div style="width: 80px; height: 80px; margin: 0 auto 20px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                📧
            </div>
            <h3 style="color: var(--dark); margin-bottom: 10px;"><?php esc_html_e('Email Support', 'edd-customer-dashboard-pro'); ?></h3>
            <p style="color: var(--gray); margin-bottom: 20px; font-size: 0.9rem;">
                <?php esc_html_e('Get help with your orders, downloads, and technical issues. We typically respond within 2 hours.', 'edd-customer-dashboard-pro'); ?>
            </p>
            <button onclick="openEmailSupport()" class="btn" style="width: 100%;">
                📧 <?php esc_html_e('Send Email', 'edd-customer-dashboard-pro'); ?>
            </button>
        </div>
    </div>
    
    <!-- Knowledge Base -->
    <div class="purchase-item">
        <div style="text-align: center; padding: 10px;">
            <div style="width: 80px; height: 80px; margin: 0 auto 20px; background: linear-gradient(135deg, #43e97b, #38f9d7); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                📚
            </div>
            <h3 style="color: var(--dark); margin-bottom: 10px;"><?php esc_html_e('Knowledge Base', 'edd-customer-dashboard-pro'); ?></h3>
            <p style="color: var(--gray); margin-bottom: 20px; font-size: 0.9rem;">
                <?php esc_html_e('Find answers to common questions, tutorials, and documentation for our products.', 'edd-customer-dashboard-pro'); ?>
            </p>
            <button onclick="window.open('<?php echo esc_url(home_url('/docs/')); ?>', '_blank')" class="btn btn-success" style="width: 100%;">
                📖 <?php esc_html_e('Browse Docs', 'edd-customer-dashboard-pro'); ?>
            </button>
        </div>
    </div>
    
    <!-- Live Chat -->
    <div class="purchase-item">
        <div style="text-align: center; padding: 10px;">
            <div style="width: 80px; height: 80px; margin: 0 auto 20px; background: linear-gradient(135deg, #fa709a, #fee140); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                💬
            </div>
            <h3 style="color: var(--dark); margin-bottom: 10px;"><?php esc_html_e('Live Chat', 'edd-customer-dashboard-pro'); ?></h3>
            <p style="color: var(--gray); margin-bottom: 20px; font-size: 0.9rem;">
                <?php esc_html_e('Chat with our support team in real-time. Available Monday-Friday, 9AM-6PM EST.', 'edd-customer-dashboard-pro'); ?>
            </p>
            <button onclick="openLiveChat()" class="btn btn-warning" style="width: 100%;">
                💬 <?php esc_html_e('Start Chat', 'edd-customer-dashboard-pro'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Quick Support Form -->
<div class="purchase-item" style="margin-bottom: 30px;">
    <h3 style="color: var(--dark); margin-bottom: 20px; font-size: 1.3rem;">📝 <?php esc_html_e('Quick Support Request', 'edd-customer-dashboard-pro'); ?></h3>
    
    <form id="supportForm" style="display: grid; gap: 20px;">
        <!-- Support Type -->
        <div>
            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--dark);">
                <?php esc_html_e('What do you need help with?', 'edd-customer-dashboard-pro'); ?>
            </label>
            <select name="support_type" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; background: white;">
                <option value=""><?php esc_html_e('Select a topic...', 'edd-customer-dashboard-pro'); ?></option>
                <option value="download_issue"><?php esc_html_e('Download Issues', 'edd-customer-dashboard-pro'); ?></option>
                <option value="license_problem"><?php esc_html_e('License Problems', 'edd-customer-dashboard-pro'); ?></option>
                <option value="billing_question"><?php esc_html_e('Billing Questions', 'edd-customer-dashboard-pro'); ?></option>
                <option value="technical_support"><?php esc_html_e('Technical Support', 'edd-customer-dashboard-pro'); ?></option>
                <option value="product_question"><?php esc_html_e('Product Questions', 'edd-customer-dashboard-pro'); ?></option>
                <option value="refund_request"><?php esc_html_e('Refund Request', 'edd-customer-dashboard-pro'); ?></option>
                <option value="other"><?php esc_html_e('Other', 'edd-customer-dashboard-pro'); ?></option>
            </select>
        </div>
        
        <!-- Order Reference -->
        <?php if (!empty($recent_orders)) : ?>
        <div>
            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--dark);">
                <?php esc_html_e('Related Order (Optional)', 'edd-customer-dashboard-pro'); ?>
            </label>
            <select name="order_id" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; background: white;">
                <option value=""><?php esc_html_e('Select an order...', 'edd-customer-dashboard-pro'); ?></option>
                <?php foreach ($recent_orders as $order) : ?>
                <option value="<?php echo esc_attr($order->id); ?>">
                    <?php 
                    /* translators: %1$s: Order number, %2$s: Order date, %3$s: Order total */
                    printf(esc_html__('Order #%1$s - %2$s (%3$s)', 'edd-customer-dashboard-pro'), 
                        $order->get_number(), 
                        date_i18n(get_option('date_format'), strtotime($order->date_created)),
                        edd_currency_filter(edd_format_amount($order->total))
                    ); 
                    ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        
        <!-- Priority Level -->
        <div>
            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--dark);">
                <?php esc_html_e('Priority Level', 'edd-customer-dashboard-pro'); ?>
            </label>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px;">
                <label style="display: flex; align-items: center; gap: 8px; padding: 10px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; transition: all 0.3s ease;" onclick="selectPriority(this, 'low')">
                    <input type="radio" name="priority" value="low" required style="margin: 0;">
                    <span style="color: var(--success);">🟢</span>
                    <span><?php esc_html_e('Low', 'edd-customer-dashboard-pro'); ?></span>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; padding: 10px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; transition: all 0.3s ease;" onclick="selectPriority(this, 'medium')">
                    <input type="radio" name="priority" value="medium" required style="margin: 0;">
                    <span style="color: #f57c00;">🟡</span>
                    <span><?php esc_html_e('Medium', 'edd-customer-dashboard-pro'); ?></span>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; padding: 10px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; transition: all 0.3s ease;" onclick="selectPriority(this, 'high')">
                    <input type="radio" name="priority" value="high" required style="margin: 0;">
                    <span style="color: var(--danger);">🔴</span>
                    <span><?php esc_html_e('High', 'edd-customer-dashboard-pro'); ?></span>
                </label>
            </div>
        </div>
        
        <!-- Message -->
        <div>
            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--dark);">
                <?php esc_html_e('Describe your issue', 'edd-customer-dashboard-pro'); ?>
            </label>
            <textarea name="message" required 
                      placeholder="<?php esc_attr_e('Please provide as much detail as possible about your issue...', 'edd-customer-dashboard-pro'); ?>"
                      style="width: 100%; min-height: 120px; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; font-family: inherit; resize: vertical;"></textarea>
        </div>
        
        <!-- Submit Button -->
        <div style="text-align: center;">
            <button type="submit" class="btn" style="padding: 15px 40px; font-size: 1.1rem;">
                📤 <?php esc_html_e('Send Support Request', 'edd-customer-dashboard-pro'); ?>
            </button>
        </div>
    </form>
</div>

<!-- Account Information for Support -->
<?php if ($customer) : ?>
<div class="purchase-item">
    <h3 style="color: var(--dark); margin-bottom: 20px; font-size: 1.3rem;">👤 <?php esc_html_e('Your Account Information', 'edd-customer-dashboard-pro'); ?></h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        <div style="background: rgba(102, 126, 234, 0.05); border-radius: 8px; padding: 15px;">
            <h4 style="color: var(--primary); margin-bottom: 10px; font-size: 1rem;"><?php esc_html_e('Customer Details', 'edd-customer-dashboard-pro'); ?></h4>
            <div style="font-size: 0.9rem; line-height: 1.6;">
                <strong><?php esc_html_e('Name:', 'edd-customer-dashboard-pro'); ?></strong> <?php echo esc_html($current_user->display_name); ?><br>
                <strong><?php esc_html_e('Email:', 'edd-customer-dashboard-pro'); ?></strong> <?php echo esc_html($current_user->user_email); ?><br>
                <strong><?php esc_html_e('Customer ID:', 'edd-customer-dashboard-pro'); ?></strong> #<?php echo esc_html($customer->id); ?><br>
                <strong><?php esc_html_e('Member Since:', 'edd-customer-dashboard-pro'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($customer->date_created))); ?>
            </div>
        </div>
        
        <?php if (!empty($recent_orders)) : ?>
        <div style="background: rgba(67, 233, 123, 0.05); border-radius: 8px; padding: 15px;">
            <h4 style="color: var(--success); margin-bottom: 10px; font-size: 1rem;"><?php esc_html_e('Recent Orders', 'edd-customer-dashboard-pro'); ?></h4>
            <div style="font-size: 0.9rem; line-height: 1.6;">
                <?php 
                $display_orders = array_slice($recent_orders, 0, 3);
                foreach ($display_orders as $order) :
                ?>
                <div style="margin-bottom: 8px;">
                    <strong>#<?php echo esc_html($order->get_number()); ?></strong> - 
                    <?php echo esc_html(edd_currency_filter(edd_format_amount($order->total))); ?>
                    <br><small style="color: var(--gray);"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($order->date_created))); ?></small>
                </div>
                <?php endforeach; ?>
                <?php if (count($recent_orders) > 3) : ?>
                <small style="color: var(--gray);">
                    <?php 
                    /* translators: %d: Number of additional orders */
                    printf(esc_html__('+ %d more orders', 'edd-customer-dashboard-pro'), count($recent_orders) - 3); 
                    ?>
                </small>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($active_licenses)) : ?>
        <div style="background: rgba(79, 172, 254, 0.05); border-radius: 8px; padding: 15px;">
            <h4 style="color: #4facfe; margin-bottom: 10px; font-size: 1rem;"><?php esc_html_e('Active Licenses', 'edd-customer-dashboard-pro'); ?></h4>
            <div style="font-size: 0.9rem; line-height: 1.6;">
                <?php foreach ($active_licenses as $license) : ?>
                <div style="margin-bottom: 8px;">
                    <strong><?php echo esc_html(get_the_title($license->download_id)); ?></strong>
                    <br><small style="color: var(--gray); font-family: monospace;"><?php echo esc_html($license->license_key); ?></small>
                </div>
                <?php endforeach; ?>
                <?php if (count($active_licenses) >= 5) : ?>
                <small style="color: var(--gray);">
                    <?php esc_html_e('+ more licenses available', 'edd-customer-dashboard-pro'); ?>
                </small>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- FAQ Section -->
<div class="purchase-item">
    <h3 style="color: var(--dark); margin-bottom: 20px; font-size: 1.3rem;">❓ <?php esc_html_e('Frequently Asked Questions', 'edd-customer-dashboard-pro'); ?></h3>
    
    <div style="display: grid; gap: 12px;">
        <details style="background: rgba(248, 250, 252, 0.8); border-radius: 8px; padding: 15px; border: 1px solid var(--border);">
            <summary style="cursor: pointer; font-weight: 600; color: var(--dark); padding: 5px 0;">
                <?php esc_html_e('How do I download my purchased products?', 'edd-customer-dashboard-pro'); ?>
            </summary>
            <p style="color: var(--gray); margin: 10px 0 0 0; font-size: 0.9rem;">
                <?php esc_html_e('After purchase, go to the "Purchases" tab in your dashboard. Click the download button next to any completed order to access your files.', 'edd-customer-dashboard-pro'); ?>
            </p>
        </details>
        
        <details style="background: rgba(248, 250, 252, 0.8); border-radius: 8px; padding: 15px; border: 1px solid var(--border);">
            <summary style="cursor: pointer; font-weight: 600; color: var(--dark); padding: 5px 0;">
                <?php esc_html_e('How do I activate my software license?', 'edd-customer-dashboard-pro'); ?>
            </summary>
            <p style="color: var(--gray); margin: 10px 0 0 0; font-size: 0.9rem;">
                <?php esc_html_e('Visit the "Licenses" tab, find your product, and enter your website URL in the activation form. Click "Activate" to register your site.', 'edd-customer-dashboard-pro'); ?>
            </p>
        </details>
        
        <details style="background: rgba(248, 250, 252, 0.8); border-radius: 8px; padding: 15px; border: 1px solid var(--border);">
            <summary style="cursor: pointer; font-weight: 600; color: var(--dark); padding: 5px 0;">
                <?php esc_html_e('What if I reach my download limit?', 'edd-customer-dashboard-pro'); ?>
            </summary>
            <p style="color: var(--gray); margin: 10px 0 0 0; font-size: 0.9rem;">
                <?php esc_html_e('Contact our support team using the form above. We can reset your download count or provide additional downloads as needed.', 'edd-customer-dashboard-pro'); ?>
            </p>
        </details>
        
        <details style="background: rgba(248, 250, 252, 0.8); border-radius: 8px; padding: 15px; border: 1px solid var(--border);">
            <summary style="cursor: pointer; font-weight: 600; color: var(--dark); padding: 5px 0;">
                <?php esc_html_e('How do I get a refund?', 'edd-customer-dashboard-pro'); ?>
            </summary>
            <p style="color: var(--gray); margin: 10px 0 0 0; font-size: 0.9rem;">
                <?php esc_html_e('We offer a 30-day money-back guarantee. Contact support with your order number and reason for the refund request.', 'edd-customer-dashboard-pro'); ?>
            </p>
        </details>
    </div>
</div>

<script>
// Support form functionality
document.getElementById('supportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const supportData = {
        type: formData.get('support_type'),
        order_id: formData.get('order_id'),
        priority: formData.get('priority'),
        message: formData.get('message')
    };
    
    // Simulate form submission
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '⏳ <?php esc_html_e('Sending...', 'edd-customer-dashboard-pro'); ?>';
    submitBtn.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        showSupportNotification('<?php esc_html_e('Support request sent successfully! We\'ll respond within 2 hours.', 'edd-customer-dashboard-pro'); ?>', 'success');
        
        // Reset form
        this.reset();
        
        // Reset button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        
        // Reset priority selection visual state
        document.querySelectorAll('[onclick*="selectPriority"]').forEach(label => {
            label.style.borderColor = '#ddd';
            label.style.background = 'white';
        });
        
    }, 2000);
});

function selectPriority(element, priority) {
    // Reset all priority options
    document.querySelectorAll('[onclick*="selectPriority"]').forEach(label => {
        label.style.borderColor = '#ddd';
        label.style.background = 'white';
    });
    
    // Highlight selected option
    element.style.borderColor = 'var(--primary)';
    element.style.background = 'rgba(102, 126, 234, 0.05)';
}

function openEmailSupport() {
    const subject = encodeURIComponent('<?php esc_html_e('Support Request', 'edd-customer-dashboard-pro'); ?>');
    const body = encodeURIComponent(`<?php esc_html_e('Customer Details:', 'edd-customer-dashboard-pro'); ?>
<?php esc_html_e('Name:', 'edd-customer-dashboard-pro'); ?> <?php echo esc_js($current_user->display_name); ?>
<?php esc_html_e('Email:', 'edd-customer-dashboard-pro'); ?> <?php echo esc_js($current_user->user_email); ?>
<?php if ($customer) : ?><?php esc_html_e('Customer ID:', 'edd-customer-dashboard-pro'); ?> #<?php echo esc_js($customer->id); ?><?php endif; ?>

<?php esc_html_e('Please describe your issue:', 'edd-customer-dashboard-pro'); ?>

`);
    
    window.location.href = `mailto:support@yoursite.com?subject=${subject}&body=${body}`;
}

function openLiveChat() {
    // This would integrate with your live chat service (Intercom, Zendesk Chat, etc.)
    if (typeof window.Intercom !== 'undefined') {
        window.Intercom('show');
    } else if (typeof window.$crisp !== 'undefined') {
        window.$crisp.push(['do', 'chat:open']);
    } else {
        // Fallback - show notification about chat availability
        showSupportNotification('<?php esc_html_e('Live chat is available Monday-Friday, 9AM-6PM EST. Please use email support for immediate assistance.', 'edd-customer-dashboard-pro'); ?>', 'info');
    }
}

function showSupportNotification(message, type = 'info') {
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
    }, 5000);
}

// Add animation styles
if (!document.querySelector('#support-animations')) {
    const style = document.createElement('style');
    style.id = 'support-animations';
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
        
        details[open] summary {
            color: var(--primary);
        }
        
        details summary:hover {
            color: var(--primary);
        }
    `;
    document.head.appendChild(style);
}
</script>