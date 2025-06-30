<?php
/**
 * Aurora Support Section Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user
$current_user = wp_get_current_user();
$customer = edd_get_customer_by('email', $current_user->user_email);

// Get support contact information (can be customized via filters)
$support_email = apply_filters('eddcdp_support_email', get_option('admin_email'));
$support_hours = apply_filters('eddcdp_support_hours', esc_html__('Mon-Fri, 9AM-6PM EST', 'edd-customer-dashboard-pro'));
$response_time = apply_filters('eddcdp_support_response_time', esc_html__('Usually within 24 hours', 'edd-customer-dashboard-pro'));

// Get recent orders for support context
$recent_orders = array();
if ($customer) {
    $recent_orders = edd_get_orders(array(
        'customer' => $customer->id,
        'number' => 5,
        'orderby' => 'date_created',
        'order' => 'DESC'
    ));
}
?>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <h1 class="dashboard-title"><?php esc_html_e('Support Center', 'edd-customer-dashboard-pro'); ?></h1>
    <div style="display: flex; align-items: center; gap: 10px; color: var(--aurora-secondary);">
        <i class="fas fa-clock"></i>
        <span style="font-size: 0.9rem; font-weight: 500;"><?php echo esc_html($support_hours); ?></span>
    </div>
</div>

<!-- Support Options -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; margin-bottom: 30px;">
    
    <!-- Create Support Ticket -->
    <div style="background: linear-gradient(135deg, var(--aurora-primary), var(--aurora-primary-light)); border-radius: 12px; padding: 25px; color: white; position: relative; overflow: hidden;">
        <div style="position: absolute; top: -20px; right: -20px; font-size: 4rem; opacity: 0.1;">
            <i class="fas fa-ticket-alt"></i>
        </div>
        <div style="position: relative; z-index: 1;">
            <h3 style="margin: 0 0 10px 0; font-size: 1.3rem; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-ticket-alt"></i>
                <?php esc_html_e('Create Support Ticket', 'edd-customer-dashboard-pro'); ?>
            </h3>
            <p style="margin: 0 0 20px 0; opacity: 0.9; line-height: 1.5;">
                <?php esc_html_e('Get personalized help with your purchases, downloads, or technical issues.', 'edd-customer-dashboard-pro'); ?>
            </p>
            <button onclick="showSupportForm()" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); font-weight: 600;">
                <i class="fas fa-plus"></i> <?php esc_html_e('New Ticket', 'edd-customer-dashboard-pro'); ?>
            </button>
        </div>
    </div>
    
    <!-- Live Chat -->
    <div style="background: linear-gradient(135deg, var(--aurora-secondary), #00a383); border-radius: 12px; padding: 25px; color: white; position: relative; overflow: hidden;">
        <div style="position: absolute; top: -20px; right: -20px; font-size: 4rem; opacity: 0.1;">
            <i class="fas fa-comments"></i>
        </div>
        <div style="position: relative; z-index: 1;">
            <h3 style="margin: 0 0 10px 0; font-size: 1.3rem; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-comments"></i>
                <?php esc_html_e('Live Chat', 'edd-customer-dashboard-pro'); ?>
                <span style="background: rgba(255,255,255,0.3); padding: 2px 6px; border-radius: 10px; font-size: 0.7rem;">ONLINE</span>
            </h3>
            <p style="margin: 0 0 20px 0; opacity: 0.9; line-height: 1.5;">
                <?php esc_html_e('Chat with our support team in real-time for immediate assistance.', 'edd-customer-dashboard-pro'); ?>
            </p>
            <button onclick="startLiveChat()" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); font-weight: 600;">
                <i class="fas fa-comment"></i> <?php esc_html_e('Start Chat', 'edd-customer-dashboard-pro'); ?>
            </button>
        </div>
    </div>
    
    <!-- Documentation -->
    <div style="background: linear-gradient(135deg, #e67e22, #d35400); border-radius: 12px; padding: 25px; color: white; position: relative; overflow: hidden;">
        <div style="position: absolute; top: -20px; right: -20px; font-size: 4rem; opacity: 0.1;">
            <i class="fas fa-book"></i>
        </div>
        <div style="position: relative; z-index: 1;">
            <h3 style="margin: 0 0 10px 0; font-size: 1.3rem; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-book"></i>
                <?php esc_html_e('Documentation', 'edd-customer-dashboard-pro'); ?>
            </h3>
            <p style="margin: 0 0 20px 0; opacity: 0.9; line-height: 1.5;">
                <?php esc_html_e('Browse our comprehensive guides, tutorials, and frequently asked questions.', 'edd-customer-dashboard-pro'); ?>
            </p>
            <a href="<?php echo esc_url(apply_filters('eddcdp_documentation_url', '#')); ?>" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); font-weight: 600; text-decoration: none;">
                <i class="fas fa-external-link-alt"></i> <?php esc_html_e('View Docs', 'edd-customer-dashboard-pro'); ?>
            </a>
        </div>
    </div>
</div>

<!-- Contact Information -->
<div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); border: 1px solid var(--aurora-gray-light); margin-bottom: 30px;">
    <h3 style="margin: 0 0 20px 0; color: var(--aurora-dark); display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-info-circle" style="color: var(--aurora-primary);"></i>
        <?php esc_html_e('Contact Information', 'edd-customer-dashboard-pro'); ?>
    </h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="width: 50px; height: 50px; background: rgba(108, 92, 231, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--aurora-primary); font-size: 1.2rem;">
                <i class="fas fa-envelope"></i>
            </div>
            <div>
                <div style="font-weight: 600; color: var(--aurora-dark); margin-bottom: 3px;"><?php esc_html_e('Email Support', 'edd-customer-dashboard-pro'); ?></div>
                <div style="color: var(--aurora-gray); font-size: 0.9rem;">
                    <a href="mailto:<?php echo esc_attr($support_email); ?>" style="color: var(--aurora-primary); text-decoration: none;">
                        <?php echo esc_html($support_email); ?>
                    </a>
                </div>
            </div>
        </div>
        
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="width: 50px; height: 50px; background: rgba(0, 184, 148, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--aurora-secondary); font-size: 1.2rem;">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <div style="font-weight: 600; color: var(--aurora-dark); margin-bottom: 3px;"><?php esc_html_e('Business Hours', 'edd-customer-dashboard-pro'); ?></div>
                <div style="color: var(--aurora-gray); font-size: 0.9rem;"><?php echo esc_html($support_hours); ?></div>
            </div>
        </div>
        
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="width: 50px; height: 50px; background: rgba(230, 126, 34, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #e67e22; font-size: 1.2rem;">
                <i class="fas fa-reply"></i>
            </div>
            <div>
                <div style="font-weight: 600; color: var(--aurora-dark); margin-bottom: 3px;"><?php esc_html_e('Response Time', 'edd-customer-dashboard-pro'); ?></div>
                <div style="color: var(--aurora-gray); font-size: 0.9rem;"><?php echo esc_html($response_time); ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Help & FAQs -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 30px;">
    
    <!-- Common Issues -->
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); border: 1px solid var(--aurora-gray-light);">
        <h3 style="margin: 0 0 20px 0; color: var(--aurora-dark); display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-question-circle" style="color: var(--aurora-warning);"></i>
            <?php esc_html_e('Common Issues', 'edd-customer-dashboard-pro'); ?>
        </h3>
        
        <div style="space-y: 15px;">
            <div style="border-left: 3px solid var(--aurora-primary); padding-left: 15px; margin-bottom: 15px;">
                <h4 style="margin: 0 0 5px 0; font-size: 1rem; color: var(--aurora-dark);"><?php esc_html_e('Download Issues', 'edd-customer-dashboard-pro'); ?></h4>
                <p style="margin: 0; color: var(--aurora-gray); font-size: 0.9rem; line-height: 1.4;">
                    <?php esc_html_e('Problems downloading your purchased files or accessing download links.', 'edd-customer-dashboard-pro'); ?>
                </p>
            </div>
            
            <div style="border-left: 3px solid var(--aurora-secondary); padding-left: 15px; margin-bottom: 15px;">
                <h4 style="margin: 0 0 5px 0; font-size: 1rem; color: var(--aurora-dark);"><?php esc_html_e('License Activation', 'edd-customer-dashboard-pro'); ?></h4>
                <p style="margin: 0; color: var(--aurora-gray); font-size: 0.9rem; line-height: 1.4;">
                    <?php esc_html_e('Help with activating, deactivating, or managing your software licenses.', 'edd-customer-dashboard-pro'); ?>
                </p>
            </div>
            
            <div style="border-left: 3px solid var(--aurora-warning); padding-left: 15px; margin-bottom: 15px;">
                <h4 style="margin: 0 0 5px 0; font-size: 1rem; color: var(--aurora-dark);"><?php esc_html_e('Account Access', 'edd-customer-dashboard-pro'); ?></h4>
                <p style="margin: 0; color: var(--aurora-gray); font-size: 0.9rem; line-height: 1.4;">
                    <?php esc_html_e('Issues logging into your account or accessing your customer dashboard.', 'edd-customer-dashboard-pro'); ?>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Self-Service Tools -->
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); border: 1px solid var(--aurora-gray-light);">
        <h3 style="margin: 0 0 20px 0; color: var(--aurora-dark); display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-tools" style="color: var(--aurora-secondary);"></i>
            <?php esc_html_e('Self-Service Tools', 'edd-customer-dashboard-pro'); ?>
        </h3>
        
        <div style="space-y: 12px;">
            <button onclick="window.AuroraDashboard?.switchSection('licenses')" class="btn btn-outline" style="width: 100%; justify-content: flex-start;">
                <i class="fas fa-key"></i> <?php esc_html_e('Manage Licenses', 'edd-customer-dashboard-pro'); ?>
            </button>
            
            <button onclick="window.AuroraDashboard?.switchSection('downloads')" class="btn btn-outline" style="width: 100%; justify-content: flex-start;">
                <i class="fas fa-download"></i> <?php esc_html_e('Re-download Files', 'edd-customer-dashboard-pro'); ?>
            </button>
            
            <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="btn btn-outline" style="width: 100%; justify-content: flex-start; text-decoration: none;">
                <i class="fas fa-lock"></i> <?php esc_html_e('Reset Password', 'edd-customer-dashboard-pro'); ?>
            </a>
            
            <a href="<?php echo esc_url(get_edit_user_link()); ?>" class="btn btn-outline" style="width: 100%; justify-content: flex-start; text-decoration: none;">
                <i class="fas fa-user-edit"></i> <?php esc_html_e('Update Profile', 'edd-customer-dashboard-pro'); ?>
            </a>
        </div>
    </div>
</div>

<?php if (!empty($recent_orders)) : ?>
<!-- Recent Orders for Support Context -->
<div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); border: 1px solid var(--aurora-gray-light);">
    <h3 style="margin: 0 0 20px 0; color: var(--aurora-dark); display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-history" style="color: var(--aurora-primary);"></i>
        <?php esc_html_e('Recent Orders', 'edd-customer-dashboard-pro'); ?>
        <span style="font-size: 0.8rem; color: var(--aurora-gray); font-weight: normal;"><?php esc_html_e('(for support reference)', 'edd-customer-dashboard-pro'); ?></span>
    </h3>
    
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid var(--aurora-gray-light);">
                    <th style="text-align: left; padding: 10px 15px; color: var(--aurora-gray); font-weight: 600; font-size: 0.85rem;"><?php esc_html_e('Order #', 'edd-customer-dashboard-pro'); ?></th>
                    <th style="text-align: left; padding: 10px 15px; color: var(--aurora-gray); font-weight: 600; font-size: 0.85rem;"><?php esc_html_e('Date', 'edd-customer-dashboard-pro'); ?></th>
                    <th style="text-align: left; padding: 10px 15px; color: var(--aurora-gray); font-weight: 600; font-size: 0.85rem;"><?php esc_html_e('Total', 'edd-customer-dashboard-pro'); ?></th>
                    <th style="text-align: left; padding: 10px 15px; color: var(--aurora-gray); font-weight: 600; font-size: 0.85rem;"><?php esc_html_e('Status', 'edd-customer-dashboard-pro'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_orders as $order) : 
                    $status_info = EDDCDP_Order_Details::instance()->get_formatted_order_status($order);
                ?>
                <tr style="border-bottom: 1px solid var(--aurora-gray-light);">
                    <td style="padding: 12px 15px; font-weight: 500; color: var(--aurora-primary);">#<?php echo esc_html($order->get_number()); ?></td>
                    <td style="padding: 12px 15px; color: var(--aurora-dark);"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($order->date_created))); ?></td>
                    <td style="padding: 12px 15px; font-weight: 600; color: var(--aurora-dark);"><?php echo esc_html(edd_currency_filter(edd_format_amount($order->total))); ?></td>
                    <td style="padding: 12px 15px;">
                        <span class="status-badge <?php echo $order->status === 'complete' ? 'status-active' : 'status-pending'; ?>">
                            <?php echo esc_html($status_info['icon'] . ' ' . $status_info['label']); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Support Form Modal -->
<div id="supportModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 12px; padding: 30px; max-width: 600px; width: 100%; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h3 style="margin: 0; color: var(--aurora-dark); font-size: 1.4rem;"><?php esc_html_e('Create Support Ticket', 'edd-customer-dashboard-pro'); ?></h3>
            <button onclick="closeSupportForm()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--aurora-gray); padding: 5px;">&times;</button>
        </div>
        
        <form id="supportForm" style="space-y: 20px;">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--aurora-dark);"><?php esc_html_e('Subject', 'edd-customer-dashboard-pro'); ?></label>
                <input type="text" required style="width: 100%; padding: 12px; border: 1px solid var(--aurora-gray-light); border-radius: 8px; font-size: 1rem;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--aurora-dark);"><?php esc_html_e('Category', 'edd-customer-dashboard-pro'); ?></label>
                <select style="width: 100%; padding: 12px; border: 1px solid var(--aurora-gray-light); border-radius: 8px; font-size: 1rem; background: white;">
                    <option value=""><?php esc_html_e('Select a category...', 'edd-customer-dashboard-pro'); ?></option>
                    <option value="download"><?php esc_html_e('Download Issues', 'edd-customer-dashboard-pro'); ?></option>
                    <option value="license"><?php esc_html_e('License Problems', 'edd-customer-dashboard-pro'); ?></option>
                    <option value="account"><?php esc_html_e('Account Access', 'edd-customer-dashboard-pro'); ?></option>
                    <option value="billing"><?php esc_html_e('Billing Questions', 'edd-customer-dashboard-pro'); ?></option>
                    <option value="technical"><?php esc_html_e('Technical Support', 'edd-customer-dashboard-pro'); ?></option>
                    <option value="other"><?php esc_html_e('Other', 'edd-customer-dashboard-pro'); ?></option>
                </select>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--aurora-dark);"><?php esc_html_e('Message', 'edd-customer-dashboard-pro'); ?></label>
                <textarea required rows="6" style="width: 100%; padding: 12px; border: 1px solid var(--aurora-gray-light); border-radius: 8px; font-size: 1rem; resize: vertical;" placeholder="<?php esc_attr_e('Please describe your issue in detail...', 'edd-customer-dashboard-pro'); ?>"></textarea>
            </div>
            
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="closeSupportForm()" class="btn btn-outline">
                    <?php esc_html_e('Cancel', 'edd-customer-dashboard-pro'); ?>
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> <?php esc_html_e('Send Ticket', 'edd-customer-dashboard-pro'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showSupportForm() {
    document.getElementById('supportModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeSupportForm() {
    document.getElementById('supportModal').style.display = 'none';
    document.body.style.overflow = '';
}

function startLiveChat() {
    // Placeholder for live chat integration
    window.AuroraDashboard?.showNotification('<?php esc_js_e('Live chat will be available soon!', 'edd-customer-dashboard-pro'); ?>', 'info');
}

// Close modal when clicking outside
document.getElementById('supportModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeSupportForm();
    }
});

// Handle support form submission
document.getElementById('supportForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    // Placeholder for form submission
    window.AuroraDashboard?.showNotification('<?php esc_js_e('Support ticket submitted successfully!', 'edd-customer-dashboard-pro'); ?>', 'success');
    closeSupportForm();
});
</script>