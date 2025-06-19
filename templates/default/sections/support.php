<?php
/**
 * Support Section Template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<h2 class="eddcdp-section-title"><?php _e('Support Center', EDDCDP_TEXT_DOMAIN); ?></h2>

<div class="eddcdp-support-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="eddcdp-support-card" style="background: rgba(248, 250, 252, 0.8); border-radius: 12px; padding: 25px; text-align: center;">
        <div class="eddcdp-support-icon" style="width: 60px; height: 60px; margin: 0 auto 15px; border-radius: 12px; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white;">
            📝
        </div>
        <h3><?php _e('Submit a Ticket', EDDCDP_TEXT_DOMAIN); ?></h3>
        <p><?php _e('Get help with your purchases, downloads, or technical issues.', EDDCDP_TEXT_DOMAIN); ?></p>
        <a href="<?php echo defined('EDD_SUPPORT_URL') ? esc_url(EDD_SUPPORT_URL) : esc_url(home_url('/support')); ?>" class="eddcdp-btn" style="margin-top: 15px;">
            <?php _e('Create Ticket', EDDCDP_TEXT_DOMAIN); ?>
        </a>
    </div>
    
    <div class="eddcdp-support-card" style="background: rgba(248, 250, 252, 0.8); border-radius: 12px; padding: 25px; text-align: center;">
        <div class="eddcdp-support-icon" style="width: 60px; height: 60px; margin: 0 auto 15px; border-radius: 12px; background: linear-gradient(135deg, #f093fb, #f5576c); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white;">
            📚
        </div>
        <h3><?php _e('Documentation', EDDCDP_TEXT_DOMAIN); ?></h3>
        <p><?php _e('Browse our comprehensive documentation and guides.', EDDCDP_TEXT_DOMAIN); ?></p>
        <a href="<?php echo defined('EDD_DOCS_URL') ? esc_url(EDD_DOCS_URL) : esc_url(home_url('/docs')); ?>" class="eddcdp-btn" style="margin-top: 15px;">
            <?php _e('View Docs', EDDCDP_TEXT_DOMAIN); ?>
        </a>
    </div>
    
    <div class="eddcdp-support-card" style="background: rgba(248, 250, 252, 0.8); border-radius: 12px; padding: 25px; text-align: center;">
        <div class="eddcdp-support-icon" style="width: 60px; height: 60px; margin: 0 auto 15px; border-radius: 12px; background: linear-gradient(135deg, #43e97b, #38f9d7); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white;">
            💬
        </div>
        <h3><?php _e('Live Chat', EDDCDP_TEXT_DOMAIN); ?></h3>
        <p><?php _e('Chat with our support team in real-time.', EDDCDP_TEXT_DOMAIN); ?></p>
        <button class="eddcdp-btn" style="margin-top: 15px;" onclick="alert('<?php _e('Live chat integration coming soon!', EDDCDP_TEXT_DOMAIN); ?>');">
            <?php _e('Start Chat', EDDCDP_TEXT_DOMAIN); ?>
        </button>
    </div>
</div>

<div class="eddcdp-faq-section">
    <h3><?php _e('Frequently Asked Questions', EDDCDP_TEXT_DOMAIN); ?></h3>
    
    <div class="eddcdp-faq-list" style="margin-top: 20px;">
        <div class="eddcdp-faq-item" style="background: rgba(248, 250, 252, 0.8); border-radius: 8px; margin-bottom: 15px; overflow: hidden;">
            <div class="eddcdp-faq-question" style="padding: 15px; cursor: pointer; font-weight: 600; color: #333; border-bottom: 1px solid rgba(0,0,0,0.1);" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none';">
                <?php _e('How do I update my account information?', EDDCDP_TEXT_DOMAIN); ?>
                <span style="float: right;">▼</span>
            </div>
            <div class="eddcdp-faq-answer" style="padding: 15px; display: none; color: #666;">
                <?php _e('You can update your account information by visiting your WordPress profile page or contacting our support team.', EDDCDP_TEXT_DOMAIN); ?>
            </div>
        </div>
    </div>
</div>

<div class="eddcdp-contact-info" style="margin-top: 40px; padding: 30px; background: rgba(248, 250, 252, 0.8); border-radius: 12px; text-align: center;">
    <h3><?php _e('Still Need Help?', EDDCDP_TEXT_DOMAIN); ?></h3>
    <p><?php _e('Our support team is here to help you with any questions or issues.', EDDCDP_TEXT_DOMAIN); ?></p>
    <div style="margin-top: 20px;">
        <a href="mailto:<?php echo get_option('admin_email'); ?>" class="eddcdp-btn eddcdp-btn-secondary">
            ✉️ <?php _e('Email Support', EDDCDP_TEXT_DOMAIN); ?>
        </a>
    </div>
</div>
        
        <div class="eddcdp-faq-item" style="background: rgba(248, 250, 252, 0.8); border-radius: 8px; margin-bottom: 15px; overflow: hidden;">
            <div class="eddcdp-faq-question" style="padding: 15px; cursor: pointer; font-weight: 600; color: #333; border-bottom: 1px solid rgba(0,0,0,0.1);" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none';">
                <?php _e('How do I manage my software licenses?', EDDCDP_TEXT_DOMAIN); ?>
                <span style="float: right;">▼</span>
            </div>
            <div class="eddcdp-faq-answer" style="padding: 15px; display: none; color: #666;">
                <?php _e('Visit the Licenses section to view, activate, and deactivate your software licenses. You can also renew expired licenses from there.', EDDCDP_TEXT_DOMAIN); ?>
            </div>
        </div>
        
        <div class="eddcdp-faq-item" style="background: rgba(248, 250, 252, 0.8); border-radius: 8px; margin-bottom: 15px; overflow: hidden;">
            <div class="eddcdp-faq-question" style="padding: 15px; cursor: pointer; font-weight: 600; color: #333; border-bottom: 1px solid rgba(0,0,0,0.1);" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none';">
                <?php _e('Can I re-download my files?', EDDCDP_TEXT_DOMAIN); ?>
                <span style="float: right;">▼</span>
            </div>
            <div class="eddcdp-faq-answer" style="padding: 15px; display: none; color: #666;">
                <?php _e('Yes, most products allow unlimited re-downloads. Check the Downloads section to see your remaining download count for each item.', EDDCDP_TEXT_DOMAIN); ?>
            </div>
        </div>
        
        <div class="eddcdp-faq-item" style="background: rgba(248, 250, 252, 0.8); border-radius: 8px; margin-bottom: 15px; overflow: hidden;">
            <div class="eddcdp-faq-question" style="padding: 15px; cursor: pointer; font-weight: 600; color: #333; border-bottom: 1px solid rgba(0,0,0,0.1);" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none';">
                <?php _e('How do I download my purchased files?', EDDCDP_TEXT_DOMAIN); ?>
                <span style="float: right;">▼</span>
            </div>
            <div class="eddcdp-faq-answer" style="padding: 15px; display: none; color: #666;">
                <?php _e('You can download your files from the Purchases or Downloads section of this dashboard. Click the download button next to any purchased item.', EDDCDP_TEXT_DOMAIN); ?>
            </div>
        </div>