<?php
/**
 * Invoice Section Template (Professional Invoice Layout)
 * File: templates/default/sections/invoice.php
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get payment downloads
$downloads = edd_get_payment_meta_downloads($payment->ID);
$user_info = edd_get_payment_meta_user_info($payment->ID);
$payment_meta = edd_get_payment_meta($payment->ID);
$payment_date = date_i18n(get_option('date_format'), strtotime($payment->date));

// Get company/site information for invoice header
$site_name = get_bloginfo('name');
$admin_email = get_option('admin_email');

// Check if EDD Invoices plugin is active
$has_invoices_plugin = class_exists('EDD_Invoices');
$has_pdf_invoices = class_exists('EDD_PDF_Invoices') || function_exists('eddpdfi_generate_pdf');
?>

<div class="eddcdp-invoice-section">
    <!-- Enhanced Invoice Header -->
    <div class="eddcdp-invoice-header">
        <div class="eddcdp-invoice-title-area">
            <h2 class="eddcdp-section-title">
                📄 <?php esc_html_e('Invoice', 'edd-customer-dashboard-pro'); ?> <?php echo esc_html($payment->number); ?>
            </h2>
            <div class="eddcdp-invoice-status">
                <span class="eddcdp-status-badge eddcdp-status-<?php echo esc_attr($payment->status); ?>">
                    <?php echo esc_html(strtoupper($dashboard_data->get_payment_status_label($payment))); ?>
                </span>
                <div class="eddcdp-invoice-date">
                    <?php esc_html_e('Purchase Date:', 'edd-customer-dashboard-pro'); ?> <?php echo esc_html($payment_date); ?>
                </div>
            </div>
        </div>
        <div class="eddcdp-invoice-actions">
            <a href="<?php echo esc_url(remove_query_arg('view')); ?>" class="eddcdp-btn eddcdp-btn-secondary">
                ← <?php esc_html_e('Back', 'edd-customer-dashboard-pro'); ?>
            </a>
            <button onclick="window.print()" class="eddcdp-btn eddcdp-btn-outline">
                🖨️ <?php esc_html_e('Print', 'edd-customer-dashboard-pro'); ?>
            </button>
            
            <?php if ($has_pdf_invoices) : ?>
                <a href="<?php echo esc_url($dashboard_data->get_pdf_invoice_url($payment)); ?>" 
                   class="eddcdp-btn eddcdp-btn-primary" target="_blank">
                    📄 <?php esc_html_e('Download PDF', 'edd-customer-dashboard-pro'); ?>
                </a>
            <?php else : ?>
                <button onclick="eddcdpGeneratePDF()" class="eddcdp-btn eddcdp-btn-primary">
                    📄 <?php esc_html_e('Download PDF', 'edd-customer-dashboard-pro'); ?>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Invoice Content -->
    <div class="eddcdp-invoice-content">
        
        <!-- Invoice Header with Company Info -->
        <div class="eddcdp-invoice-header-section">
            <div class="eddcdp-invoice-from-to">
                <div class="eddcdp-invoice-from">
                    <h3><?php esc_html_e('INVOICE FROM:', 'edd-customer-dashboard-pro'); ?></h3>
                    <div class="eddcdp-company-info">
                        <strong><?php echo esc_html($site_name); ?></strong><br>
                        <?php
                        // Get company address from EDD settings or options
                        $company_address = $dashboard_data->get_company_address();
                        if ($company_address) {
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped in get_company_address method
                            echo $company_address;
                        }
                        ?>
                        
                        <?php
                        // Registration/Tax ID if available
                        $registration_number = get_option('edd_company_registration', '');
                        if (!empty($registration_number)) {
                            echo '<br><br>' . esc_html__('Registration:', 'edd-customer-dashboard-pro') . ' ' . esc_html($registration_number);
                        }
                        ?>
                    </div>
                </div>
                
                <div class="eddcdp-invoice-to">
                    <h3><?php esc_html_e('INVOICE TO:', 'edd-customer-dashboard-pro'); ?></h3>
                    <div class="eddcdp-customer-info">
                        <?php if (!empty($user_info['first_name']) || !empty($user_info['last_name'])) : ?>
                            <strong><?php echo esc_html(trim($user_info['first_name'] . ' ' . $user_info['last_name'])); ?></strong><br>
                        <?php endif; ?>
                        
                        <?php echo esc_html($payment->email); ?><br>
                        
                        <?php if (!empty($user_info['address'])) : ?>
                            <?php 
                            $address = $user_info['address'];
                            $address_lines = array();
                            
                            if (!empty($address['line1'])) {
                                $address_lines[] = esc_html($address['line1']);
                            }
                            if (!empty($address['line2'])) {
                                $address_lines[] = esc_html($address['line2']);
                            }
                            
                            $city_state_zip = array();
                            if (!empty($address['city'])) {
                                $city_state_zip[] = esc_html($address['city']);
                            }
                            if (!empty($address['state'])) {
                                $city_state_zip[] = esc_html($address['state']);
                            }
                            if (!empty($address['zip'])) {
                                $city_state_zip[] = esc_html($address['zip']);
                            }
                            
                            if (!empty($city_state_zip)) {
                                $address_lines[] = implode(', ', $city_state_zip);
                            }
                            
                            if (!empty($address['country'])) {
                                $address_lines[] = esc_html($address['country']);
                            }
                            
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped above
                            echo implode('<br>', $address_lines);
                            ?>
                        <?php endif; ?>
                        
                        <div class="eddcdp-update-info">
                            <button class="eddcdp-btn-link" onclick="eddcdpShowUpdateForm()">
                                <?php esc_html_e('Update', 'edd-customer-dashboard-pro'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Items Table -->
        <div class="eddcdp-invoice-items-section">
            <h3><?php esc_html_e('INVOICE ITEMS:', 'edd-customer-dashboard-pro'); ?></h3>
            
            <div class="eddcdp-invoice-table">
                <?php if ($downloads) : ?>
                    <?php foreach ($downloads as $download) : ?>
                        <div class="eddcdp-invoice-item">
                            <div class="eddcdp-item-description">
                                <strong><?php echo esc_html(get_the_title($download['id'])); ?></strong>
                                
                                <?php if (!empty($download['options']['price_name'])) : ?>
                                    <div class="eddcdp-item-variant">
                                        <?php echo esc_html($download['options']['price_name']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (edd_use_skus() && edd_get_download_sku($download['id'])) : ?>
                                    <div class="eddcdp-item-sku">
                                        <?php esc_html_e('SKU:', 'edd-customer-dashboard-pro'); ?> <?php echo esc_html(edd_get_download_sku($download['id'])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="eddcdp-item-price">
                                <?php echo esc_html($dashboard_data->format_currency($dashboard_data->get_download_price_from_payment($download, $payment->ID))); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Invoice Totals -->
        <div class="eddcdp-invoice-totals">
            <div class="eddcdp-totals-table">
                <div class="eddcdp-total-row">
                    <span class="eddcdp-total-label"><?php esc_html_e('Subtotal:', 'edd-customer-dashboard-pro'); ?></span>
                    <span class="eddcdp-total-value"><?php echo esc_html($dashboard_data->format_currency(edd_get_payment_subtotal($payment->ID))); ?></span>
                </div>
                
                <?php 
                // Get discounts using the correct EDD method
                $cart_discounts = edd_get_payment_meta($payment->ID, '_edd_payment_discount', true);
                $discount_amount = 0;
                
                if (!empty($cart_discounts)) {
                    // Calculate discount amount
                    $subtotal = edd_get_payment_subtotal($payment->ID);
                    $total_before_tax = $payment->total - edd_get_payment_tax($payment->ID);
                    $discount_amount = $subtotal - $total_before_tax;
                    
                    if ($discount_amount > 0) :
                ?>
                    <div class="eddcdp-total-row eddcdp-discount-row">
                        <span class="eddcdp-total-label">
                            <?php esc_html_e('Discount —', 'edd-customer-dashboard-pro'); ?> 
                            <?php echo esc_html($cart_discounts); ?>
                            <?php if ($subtotal > 0) : ?>
                                (<?php echo esc_html(number_format(($discount_amount / $subtotal) * 100, 2)); ?>%)
                            <?php endif; ?>:
                        </span>
                        <span class="eddcdp-total-value eddcdp-discount-amount">
                            -<?php echo esc_html($dashboard_data->format_currency($discount_amount)); ?>
                        </span>
                    </div>
                <?php 
                    endif;
                }
                ?>
                
                <?php 
                // Show fees if any
                $fees = edd_get_payment_fees($payment->ID);
                if (!empty($fees)) : ?>
                    <?php foreach ($fees as $fee) : ?>
                        <div class="eddcdp-total-row">
                            <span class="eddcdp-total-label"><?php echo esc_html($fee['label']); ?>:</span>
                            <span class="eddcdp-total-value"><?php echo esc_html($dashboard_data->format_currency($fee['amount'])); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php 
                // Show tax if any
                $tax = edd_get_payment_tax($payment->ID);
                if ($tax > 0) : ?>
                    <div class="eddcdp-total-row">
                        <span class="eddcdp-total-label"><?php esc_html_e('Tax:', 'edd-customer-dashboard-pro'); ?></span>
                        <span class="eddcdp-total-value"><?php echo esc_html($dashboard_data->format_currency($tax)); ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="eddcdp-total-row eddcdp-grand-total">
                    <span class="eddcdp-total-label"><strong><?php esc_html_e('Total:', 'edd-customer-dashboard-pro'); ?></strong></span>
                    <span class="eddcdp-total-value"><strong><?php echo esc_html($dashboard_data->format_currency($payment->total)); ?></strong></span>
                </div>
                
                <div class="eddcdp-payment-status-row">
                    <span class="eddcdp-total-label"><strong><?php esc_html_e('Payment Status:', 'edd-customer-dashboard-pro'); ?></strong></span>
                    <span class="eddcdp-payment-status"><?php echo esc_html($dashboard_data->get_payment_status_label($payment)); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="eddcdp-invoice-footer-actions">
            <a href="<?php echo esc_url(remove_query_arg('view')); ?>" class="eddcdp-btn eddcdp-btn-secondary">
                <?php esc_html_e('Back', 'edd-customer-dashboard-pro'); ?>
            </a>
            <button onclick="window.print()" class="eddcdp-btn eddcdp-btn-outline">
                <?php esc_html_e('Print', 'edd-customer-dashboard-pro'); ?>
            </button>
            
            <?php if ($has_pdf_invoices) : ?>
                <a href="<?php echo esc_url($dashboard_data->get_pdf_invoice_url($payment)); ?>" 
                   class="eddcdp-btn eddcdp-btn-primary" target="_blank">
                    <?php esc_html_e('Download PDF', 'edd-customer-dashboard-pro'); ?>
                </a>
            <?php else : ?>
                <button onclick="eddcdpGeneratePDF()" class="eddcdp-btn eddcdp-btn-primary">
                    <?php esc_html_e('Download PDF', 'edd-customer-dashboard-pro'); ?>
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Update Customer Info Form (Hidden by default) -->
<div id="eddcdp-update-form" class="eddcdp-update-form" style="display: none;">
    <div class="eddcdp-update-overlay">
        <div class="eddcdp-update-modal">
            <h3><?php esc_html_e('Update Billing Information', 'edd-customer-dashboard-pro'); ?></h3>
            <form id="eddcdp-billing-form">
                <?php wp_nonce_field('eddcdp_update_billing', 'eddcdp_billing_nonce'); ?>
                
                <div class="eddcdp-form-row">
                    <div class="eddcdp-form-group">
                        <label><?php esc_html_e('First Name', 'edd-customer-dashboard-pro'); ?></label>
                        <input type="text" name="first_name" value="<?php echo esc_attr($user_info['first_name'] ?? ''); ?>">
                    </div>
                    <div class="eddcdp-form-group">
                        <label><?php esc_html_e('Last Name', 'edd-customer-dashboard-pro'); ?></label>
                        <input type="text" name="last_name" value="<?php echo esc_attr($user_info['last_name'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="eddcdp-form-group">
                    <label><?php esc_html_e('Email', 'edd-customer-dashboard-pro'); ?></label>
                    <input type="email" name="email" value="<?php echo esc_attr($payment->email); ?>">
                </div>
                
                <div class="eddcdp-form-group">
                    <label><?php esc_html_e('Address Line 1', 'edd-customer-dashboard-pro'); ?></label>
                    <input type="text" name="address[line1]" value="<?php echo esc_attr($user_info['address']['line1'] ?? ''); ?>">
                </div>
                
                <div class="eddcdp-form-group">
                    <label><?php esc_html_e('Address Line 2', 'edd-customer-dashboard-pro'); ?></label>
                    <input type="text" name="address[line2]" value="<?php echo esc_attr($user_info['address']['line2'] ?? ''); ?>">
                </div>
                
                <div class="eddcdp-form-row">
                    <div class="eddcdp-form-group">
                        <label><?php esc_html_e('City', 'edd-customer-dashboard-pro'); ?></label>
                        <input type="text" name="address[city]" value="<?php echo esc_attr($user_info['address']['city'] ?? ''); ?>">
                    </div>
                    <div class="eddcdp-form-group">
                        <label><?php esc_html_e('State/Province', 'edd-customer-dashboard-pro'); ?></label>
                        <input type="text" name="address[state]" value="<?php echo esc_attr($user_info['address']['state'] ?? ''); ?>">
                    </div>
                    <div class="eddcdp-form-group">
                        <label><?php esc_html_e('ZIP/Postal Code', 'edd-customer-dashboard-pro'); ?></label>
                        <input type="text" name="address[zip]" value="<?php echo esc_attr($user_info['address']['zip'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="eddcdp-form-group">
                    <label><?php esc_html_e('Country', 'edd-customer-dashboard-pro'); ?></label>
                    <select name="address[country]">
                        <?php
                        $countries = edd_get_country_list();
                        $selected_country = $user_info['address']['country'] ?? '';
                        foreach ($countries as $code => $name) {
                            echo '<option value="' . esc_attr($code) . '"' . selected($selected_country, $code, false) . '>' . esc_html($name) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div class="eddcdp-form-actions">
                    <button type="button" onclick="eddcdpHideUpdateForm()" class="eddcdp-btn eddcdp-btn-secondary">
                        <?php esc_html_e('Cancel', 'edd-customer-dashboard-pro'); ?>
                    </button>
                    <button type="submit" class="eddcdp-btn eddcdp-btn-primary">
                        <?php esc_html_e('Update', 'edd-customer-dashboard-pro'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// JavaScript functions for invoice functionality
function eddcdpShowUpdateForm() {
    document.getElementById('eddcdp-update-form').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function eddcdpHideUpdateForm() {
    document.getElementById('eddcdp-update-form').style.display = 'none';
    document.body.style.overflow = '';
}

function eddcdpGeneratePDF() {
    document.body.classList.add('eddcdp-printing');
    setTimeout(function() {
        window.print();
        document.body.classList.remove('eddcdp-printing');
    }, 100);
}

// Handle billing form submission
jQuery(document).ready(function($) {
    $('#eddcdp-billing-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const originalText = $submitBtn.text();
        
        $submitBtn.prop('disabled', true).text('<?php esc_html_e('Updating...', 'edd-customer-dashboard-pro'); ?>');
        
        const formData = new FormData(this);
        formData.append('action', 'eddcdp_update_billing');
        formData.append('payment_id', <?php echo esc_js($payment->ID); ?>);
        
        $.ajax({
            url: eddcdp_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('<?php esc_html_e('Information updated successfully!', 'edd-customer-dashboard-pro'); ?>');
                    location.reload();
                } else {
                    alert('<?php esc_html_e('Update failed:', 'edd-customer-dashboard-pro'); ?> ' + (response.data || '<?php esc_html_e('Unknown error', 'edd-customer-dashboard-pro'); ?>'));
                }
            },
            error: function() {
                alert('<?php esc_html_e('Network error. Please try again.', 'edd-customer-dashboard-pro'); ?>');
            },
            complete: function() {
                $submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Close modal when clicking outside
    $('#eddcdp-update-form').on('click', function(e) {
        if (e.target === this) {
            eddcdpHideUpdateForm();
        }
    });
    
    // Close modal on Escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#eddcdp-update-form').is(':visible')) {
            eddcdpHideUpdateForm();
        }
    });
});
</script>