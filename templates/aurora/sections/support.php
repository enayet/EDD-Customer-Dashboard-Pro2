<?php
/**
 * Aurora Template - Support Section
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<h2 class="eddcdp-section-title"><?php esc_html_e('Support Center', 'edd-customer-dashboard-pro'); ?></h2>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div style="background: white; border-radius: 12px; padding: 25px; text-align: center; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); border-left: 4px solid var(--primary);">
        <div style="width: 60px; height: 60px; margin: 0 auto 15px; border-radius: 12px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white;">
            <i class="fas fa-ticket-alt"></i>
        </div>
        <h3><?php esc_html_e('Submit a Ticket', 'edd-customer-dashboard-pro'); ?></h3>
        <p style="color: var(--gray); margin: 15px 0;"><?php esc_html_e('Get help with your purchases, downloads, or technical issues.', 'edd-customer-dashboard-pro'); ?></p>
        <a href="<?php echo defined('EDD_SUPPORT_URL') ? esc_url(EDD_SUPPORT_URL) : esc_url(home_url('/support')); ?>" class="eddcdp-btn eddcdp-btn-primary" style="margin-top: 15px;">
            <i class="fas fa-plus"></i> <?php esc_html_e('Create Ticket', 'edd-customer-dashboard-pro'); ?>
        </a>
    </div>
    
    <div style="background: white; border-radius: 12px; padding: 25px; text-align: center; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); border-left: 4px solid var(--secondary);">
        <div style="width: 60px; height: 60px; margin: 0 auto 15px; border-radius: 12px; background: linear-gradient(135deg, var(--secondary), #00a383); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white;">
            <i class="fas fa-book"></i>
        </div>
        <h3><?php esc_html_e('Documentation', 'edd-customer-dashboard-pro'); ?></h3>
        <p style="color: var(--gray); margin: 15px 0;"><?php esc_html_e('Browse our comprehensive documentation and guides.', 'edd-customer-dashboard-pro'); ?></p>
        <a href="<?php echo defined('EDD_DOCS_URL') ? esc_url(EDD_DOCS_URL) : esc_url(home_url('/docs')); ?>" class="eddcdp-btn eddcdp-btn-success" style="margin-top: 15px;">
            <i class="fas fa-external-link-alt"></i> <?php esc_html_e('View Docs', 'edd-customer-dashboard-pro'); ?>
        </a>
    </div>
    
    <div style="background: white; border-radius: 12px; padding: 25px; text-align: center; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); border-left: 4px solid #e84393;">
        <div style="width: 60px; height: 60px; margin: 0 auto 15px; border-radius: 12px; background: linear-gradient(135deg, #e84393, #fd79a8); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white;">
            <i class="fas fa-headset"></i>
        </div>
        <h3><?php esc_html_e('Live Chat', 'edd-customer-dashboard-pro'); ?></h3>
        <p style="color: var(--gray); margin: 15px 0;"><?php esc_html_e('Chat with our support team in real-time.', 'edd-customer-dashboard-pro'); ?></p>
        <button class="eddcdp-btn" style="margin-top: 15px; background: #e84393; color: white;" onclick="alert('<?php esc_attr_e('Live chat integration coming soon!', 'edd-customer-dashboard-pro'); ?>');">
            <i class="fas fa-comments"></i> <?php esc_html_e('Start Chat', 'edd-customer-dashboard-pro'); ?>
        </button>
    </div>
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);">
    <h3 style="margin-bottom: 20px; color: var(--dark); display: flex; align-items: center;">
        <i class="fas fa-question-circle" style="margin-right: 10px; color: var(--primary);"></i>
        <?php esc_html_e('Frequently Asked Questions', 'edd-customer-dashboard-pro'); ?>
    </h3>
    
    <div style="display: grid; gap: 15px;">
        <div style="background: var(--light); border-radius: 8px; overflow: hidden; border-left: 4px solid var(--primary);">
            <div class="eddcdp-faq-question" style="padding: 15px; cursor: pointer; font-weight: 600; color: var(--dark); display: flex; justify-content: space-between; align-items: center;" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none'; this.querySelector('.faq-toggle').textContent = this.nextElementSibling.style.display === 'none' ? '+' : '-';">
                <span><?php esc_html_e('How do I update my account information?', 'edd-customer-dashboard-pro'); ?></span>
                <span class="faq-toggle" style="font-size: 1.2rem; color: var(--primary);">+</span>
            </div>
            <div class="eddcdp-faq-answer" style="padding: 15px; display: none; color: var(--gray); border-top: 1px solid var(--gray-light);">
                <?php esc_html_e('You can update your account information by visiting your WordPress profile page or contacting our support team.', 'edd-customer-dashboard-pro'); ?>
            </div>
        </div>
        
        <div style="background: var(--light); border-radius: 8px; overflow: hidden; border-left: 4px solid var(--secondary);">
            <div class="eddcdp-faq-question" style="padding: 15px; cursor: pointer; font-weight: 600; color: var(--dark); display: flex; justify-content: space-between; align-items: center;" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none'; this.querySelector('.faq-toggle').textContent = this.nextElementSibling.style.display === 'none' ? '+' : '-';">
                <span><?php esc_html_e('How do I manage my software licenses?', 'edd-customer-dashboard-pro'); ?></span>
                <span class="faq-toggle" style="font-size: 1.2rem; color: var(--secondary);">+</span>
            </div>
            <div class="eddcdp-faq-answer" style="padding: 15px; display: none; color: var(--gray); border-top: 1px solid var(--gray-light);">
                <?php esc_html_e('Visit the Licenses section to view, activate, and deactivate your software licenses. You can also renew expired licenses from there.', 'edd-customer-dashboard-pro'); ?>
            </div>
        </div>
        
        <div style="background: var(--light); border-radius: 8px; overflow: hidden; border-left: 4px solid #0984e3;">
            <div class="eddcdp-faq-question" style="padding: 15px; cursor: pointer; font-weight: 600; color: var(--dark); display: flex; justify-content: space-between; align-items: center;" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none'; this.querySelector('.faq-toggle').textContent = this.nextElementSibling.style.display === 'none' ? '+' : '-';">
                <span><?php esc_html_e('Can I re-download my files?', 'edd-customer-dashboard-pro'); ?></span>
                <span class="faq-toggle" style="font-size: 1.2rem; color: #0984e3;">+</span>
            </div>
            <div class="eddcdp-faq-answer" style="padding: 15px; display: none; color: var(--gray); border-top: 1px solid var(--gray-light);">
                <?php esc_html_e('Yes, most products allow unlimited re-downloads. Check the Downloads section to see your remaining download count for each item.', 'edd-customer-dashboard-pro'); ?>
            </div>
        </div>
        
        <div style="background: var(--light); border-radius: 8px; overflow: hidden; border-left: 4px solid #e84393;">
            <div class="eddcdp-faq-question" style="padding: 15px; cursor: pointer; font-weight: 600; color: var(--dark); display: flex; justify-content: space-between; align-items: center;" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none'; this.querySelector('.faq-toggle').textContent = this.nextElementSibling.style.display === 'none' ? '+' : '-';">
                <span><?php esc_html_e('How do I download my purchased files?', 'edd-customer-dashboard-pro'); ?></span>
                <span class="faq-toggle" style="font-size: 1.2rem; color: #e84393;">+</span>
            </div>
            <div class="eddcdp-faq-answer" style="padding: 15px; display: none; color: var(--gray); border-top: 1px solid var(--gray-light);">
                <?php esc_html_e('You can download your files from the Purchases or Downloads section of this dashboard. Click the download button next to any purchased item.', 'edd-customer-dashboard-pro'); ?>
            </div>
        </div>
    </div>
</div>

<div style="margin-top: 30px; padding: 30px; background: white; border-radius: 12px; text-align: center; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);">
    <h3 style="margin-bottom: 15px; color: var(--dark); display: flex; align-items: center; justify-content: center;">
        <i class="fas fa-life-ring" style="margin-right: 10px; color: var(--primary);"></i>
        <?php esc_html_e('Still Need Help?', 'edd-customer-dashboard-pro'); ?>
    </h3>
    <p style="color: var(--gray); margin-bottom: 20px;"><?php esc_html_e('Our support team is here to help you with any questions or issues.', 'edd-customer-dashboard-pro'); ?></p>
    <div>
        <a href="mailto:<?php echo esc_attr(get_option('admin_email')); ?>" class="eddcdp-btn eddcdp-btn-primary">
            <i class="fas fa-envelope"></i> <?php esc_html_e('Email Support', 'edd-customer-dashboard-pro'); ?>
        </a>
    </div>
</div>