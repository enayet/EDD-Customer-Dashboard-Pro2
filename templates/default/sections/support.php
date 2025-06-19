<?php
/**
 * Support Section Template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<h2 class="eddcdp-section-title"><?php _e('Support Center', 'edd-customer-dashboard-pro'); ?></h2>

<div class="eddcdp-support-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="eddcdp-support-card" style="background: rgba(248, 250, 252, 0.8); border-radius: 12px; padding: 25px; text-align: center;">
        <div class="eddcdp-support-icon" style="width: 60px; height: 60px; margin: 0 auto 15px; border-radius: 12px; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white;">
            📝
        </div>
        <h3><?php _e('Submit a Ticket', 'edd-customer-dashboard-pro'); ?></h3>
        <p><?php _e('Get help with your purchases, downloads, or technical issues.', 'edd-customer-dashboard-pro'); ?></p>
        <a href="<?php echo defined('EDD_SUPPORT_URL') ? esc_url(EDD_SUPPORT_URL) : esc_url(home_url('/support')); ?>" class="eddcdp-btn" style="margin-top: 15px;">
            <?php _e('Create Ticket', 'edd-customer-dashboard-pro'); ?>
        </a>
    </div>
    
    <div class="eddcdp-support-card" style="background: rgba(248, 250, 252, 0.8); border-radius: 12px; padding: 25px; text-align: center;">
        <div class="eddcdp-support-icon" style="width: 60px; height: 60px; margin: 0 auto 15px; border-radius: 12px; background: linear-gradient(135deg, #f093fb, #f5576c); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white;">
            📚
        </div>
        <h3><?php _e('Documentation', 'edd-customer-dashboard-pro'); ?></h3>
        <p><?php _e('Browse our comprehensive documentation and guides.', 'edd-customer-dashboard-pro'); ?></p>
        <a href="<?php echo defined('EDD_DOCS_URL') ? esc_url(EDD_DOCS_URL) : esc_url(home_url('/docs')); ?>" class="eddcdp-btn" style="margin-top: 15px;">
            <?php _e('View Docs', 'edd-customer-dashboard-pro'); ?>
        </a>
    </div>
    
    <div class="eddcdp-support-card" style="background: rgba(248, 250, 252, 0.8); border-radius: 12px; padding: 25px; text-align: center;">
        <div class="eddcdp-support-icon" style="width: 60px; height: 60px; margin: 0 auto 15px; border-radius: 12px; background: linear-gradient(135deg, #43e97b, #38f9d7); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white;">
            💬
        </div>
        <h3><?php _e('Live Chat', 'edd-customer-dashboard-pro'); ?></h3>
        <p><?php _e('Chat with our support team in real-time.', 'edd-customer-dashboard-pro'); ?></p>
        <button class="eddcdp-btn" style="margin-top: 15px;" onclick="alert('<?php _e('Live chat integration coming soon!', 'edd-customer-dashboard-pro'); ?>');">
            <?php _e('Start Chat', 'edd-customer-dashboard-pro'); ?>
        </button>
    </div>
</div>

<div class="eddcdp-faq-section">
    <h3><?php _e('Frequently Asked Questions', 'edd-customer-dashboard-pro'); ?></h3>
    
    <div class="eddcdp-faq-list" style="margin-top: 20px;">
        <div class="eddcdp-faq-item" style="background: rgba(248, 250, 252, 0.8); border-radius: 8px; margin-bottom: 15px; overflow: hidden;">
            <div class="eddcdp-faq-question" style="padding: 15px; cursor: pointer; font-weight: 600; color: #333; border-bottom: 1px solid rgba(0,0,0,0.1);" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none';">
                <?php _e('How do I update my account information?', 'edd-customer-dashboard-pro'); ?>
                <span style="float: right;">▼</span>
            </div>
            <div class="eddcdp-faq-answer" style="padding: 15px; display: none; color: #666;">
                <?php _e('You can update your account information by visiting your WordPress profile page or contacting our support team.', 'edd-customer-dashboard-pro'); ?>
            </div>
        </div>
    </div>
</div>

<div class="eddcdp-contact-info" style="margin-top: 40px; padding: 30px; background: rgba(248, 250, 252, 0.8); border-radius: 12px; text-align: center;">
    <h3><?php _e('Still Need Help?', 'edd-customer-dashboard-pro'); ?></h3>
    <p><?php _e('Our support team is here to help you with any questions or issues.', 'edd-customer-dashboard-pro'); ?></p>
    <div style="margin-top: 20px;">
        <a href="mailto:<?php echo get_option('admin_email'); ?>" class="eddcdp-btn eddcdp-btn-secondary">
            ✉️ <?php _e('Email Support', 'edd-customer-dashboard-pro'); ?>
        </a>
    </div>
</div>
        
        <div class="eddcdp-faq-item" style="background: rgba(248, 250, 252, 0.8); border-radius: 8px; margin-bottom: 15px; overflow: hidden;">
            <div class="eddcdp-faq-question" style="padding: 15px; cursor: pointer; font-weight: 600; color: #333; border-bottom: 1px solid rgba(0,0,0,0.1);" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none';">
                <?php _e('How do I manage my software licenses?', 'edd-customer-dashboard-pro'); ?>
                <span style="float: right;">▼</span>
            </div>
            <div class="eddcdp-faq-answer" style="padding: 15px; display: none; color: #666;">
                <?php _e('Visit the Licenses section to view, activate, and deactivate your software licenses. You can also renew expired licenses from there.', 'edd-customer-dashboard-pro'); ?>
            </div>
        </div>
        
        <div class="eddcdp-faq-item" style="background: rgba(248, 250, 252, 0.8); border-radius: 8px; margin-bottom: 15px; overflow: hidden;">
            <div class="eddcdp-faq-question" style="padding: 15px; cursor: pointer; font-weight: 600; color: #333; border-bottom: 1px solid rgba(0,0,0,0.1);" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none';">
                <?php _e('Can I re-download my files?', 'edd-customer-dashboard-pro'); ?>
                <span style="float: right;">▼</span>
            </div>
            <div class="eddcdp-faq-answer" style="padding: 15px; display: none; color: #666;">
                <?php _e('Yes, most products allow unlimited re-downloads. Check the Downloads section to see your remaining download count for each item.', 'edd-customer-dashboard-pro'); ?>
            </div>
        </div>
        
        <div class="eddcdp-faq-item" style="background: rgba(248, 250, 252, 0.8); border-radius: 8px; margin-bottom: 15px; overflow: hidden;">
            <div class="eddcdp-faq-question" style="padding: 15px; cursor: pointer; font-weight: 600; color: #333; border-bottom: 1px solid rgba(0,0,0,0.1);" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none';">
                <?php _e('How do I download my purchased files?', 'edd-customer-dashboard-pro'); ?>
                <span style="float: right;">▼</span>
            </div>
            <div class="eddcdp-faq-answer" style="padding: 15px; display: none; color: #666;">
                <?php _e('You can download your files from the Purchases or Downloads section of this dashboard. Click the download button next to any purchased item.', 'edd-customer-dashboard-pro'); ?>
            </div>
        </div>