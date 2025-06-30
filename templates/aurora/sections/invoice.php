<?php
/**
 * Aurora Invoice Form Section for Dashboard
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle invoice form display
if (isset($_GET['eddcdp_invoice_form']) && isset($_GET['payment_id'])) : 
    $payment_id = intval($_GET['payment_id']);
    
    // Simple validation: check if user owns this order
    $order = edd_get_order($payment_id);
    $is_valid = false;
    
    if ($order) {
        $customer = edd_get_customer_by('email', wp_get_current_user()->user_email);
        if ($customer && $order->customer_id == $customer->id) {
            $is_valid = true;
        }
    }
    
    if ($is_valid) :
?>

<!-- Invoice Form View -->
<div style="padding: 0;">
    <!-- Header with Back Button -->
    <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 30px;">
        <a href="<?php echo esc_url(remove_query_arg(array('eddcdp_invoice_form', 'payment_id', 'invoice'))); ?>" 
           class="order-back-btn">
            <i class="fas fa-arrow-left"></i>
            <?php esc_html_e('Back to Dashboard', 'edd-customer-dashboard-pro'); ?>
        </a>

        <div style="text-align: right;">
            <div style="color: var(--aurora-gray); font-size: 0.9rem;">
                <?php 
                /* translators: %s: Order number */
                printf(esc_html__('Update Invoice for Order #%s', 'edd-customer-dashboard-pro'), esc_html($order->get_number())); 
                ?>
            </div>
        </div>
    </div>

    <!-- Invoice Form Header -->
    <div style="background: linear-gradient(135deg, var(--aurora-primary), var(--aurora-primary-light)); border-radius: 12px; padding: 25px; margin-bottom: 25px; color: white; position: relative; overflow: hidden;">
        <div style="position: absolute; top: -20px; right: -20px; font-size: 4rem; opacity: 0.1;">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div style="position: relative; z-index: 1;">
            <h2 style="margin: 0 0 8px 0; font-size: 1.8rem; font-weight: 700; display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-file-invoice"></i>
                <?php 
                /* translators: %s: Order number */
                printf(esc_html__('Update Invoice - Order #%s', 'edd-customer-dashboard-pro'), esc_html($order->get_number())); 
                ?>
            </h2>
            <p style="margin: 0; opacity: 0.9; font-size: 1rem;">
                <?php esc_html_e('Update your billing details and generate a new invoice', 'edd-customer-dashboard-pro'); ?>
            </p>
        </div>
    </div>

    <!-- Invoice Form Section -->
    <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); border: 1px solid var(--aurora-gray-light);">
        <h3 style="margin: 0 0 25px 0; color: var(--aurora-dark); font-size: 1.3rem; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-edit" style="color: var(--aurora-primary);"></i>
            <?php esc_html_e('Billing Information', 'edd-customer-dashboard-pro'); ?>
        </h3>

        <!-- Invoice Form Container -->
        <div id="aurora-invoice-form-container">
            <?php
            // Load the EDD Invoices shortcode
            echo do_shortcode('[edd_invoices]');
            ?>
        </div>
    </div>
</div>

<style>
/* Aurora Invoice Form Styling - Enhanced */

/* Reset form container */
#edd-invoices {
    background: none !important;
    border: none !important;
    padding: 0 !important;
    box-shadow: none !important;
    max-width: none !important;
}

/* Form field containers */
.edd-invoices-div {
    margin-bottom: 20px !important;
    background: none !important;
    border: none !important;
    padding: 0 !important;
}

/* Labels */
.edd-invoices-field {
    display: block !important;
    font-size: 0.9rem !important;
    font-weight: 600 !important;
    color: var(--aurora-dark) !important;
    margin-bottom: 8px !important;
    background: none !important;
    border: none !important;
    padding: 0 !important;
    line-height: 1.4 !important;
}

/* All input fields */
#edd-invoices input[type="text"],
#edd-invoices select,
#edd-invoices textarea {
    width: 100% !important;
    padding: 12px 16px !important;
    border: 1px solid var(--aurora-gray-light) !important;
    border-radius: 8px !important;
    font-size: 1rem !important;
    line-height: 1.5 !important;
    background-color: #ffffff !important;
    transition: all 0.2s ease !important;
    box-shadow: none !important;
    outline: none !important;
    font-family: inherit !important;
    margin: 0 !important;
    color: var(--aurora-dark) !important;
}

/* Focus states */
#edd-invoices input[type="text"]:focus,
#edd-invoices select:focus,
#edd-invoices textarea:focus {
    border-color: var(--aurora-primary) !important;
    box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1) !important;
    outline: none !important;
}

/* Hover states */
#edd-invoices input[type="text"]:hover,
#edd-invoices select:hover,
#edd-invoices textarea:hover {
    border-color: var(--aurora-primary-light) !important;
}

/* Textarea specific */
#edd-invoices textarea {
    resize: vertical !important;
    min-height: 100px !important;
    font-family: inherit !important;
}

/* Select dropdown styling */
#edd-invoices select {
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E") !important;
    background-position: right 12px center !important;
    background-repeat: no-repeat !important;
    background-size: 16px !important;
    background-color: #ffffff !important;
    padding-right: 40px !important;
}

/* Submit button styling */
.edd-invoices-generate-invoice-button {
    background: linear-gradient(135deg, var(--aurora-primary), var(--aurora-primary-light)) !important;
    color: white !important;
    border: none !important;
    padding: 14px 28px !important;
    border-radius: 8px !important;
    font-weight: 600 !important;
    font-size: 1rem !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    text-transform: none !important;
    width: auto !important;
    margin: 25px 0 0 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    box-shadow: 0 4px 12px rgba(108, 92, 231, 0.3) !important;
    text-decoration: none !important;
    line-height: 1.25 !important;
    font-family: inherit !important;
    min-width: 180px !important;
    justify-content: center !important;
}

.edd-invoices-generate-invoice-button:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 20px rgba(108, 92, 231, 0.4) !important;
    background: linear-gradient(135deg, #5a4bd1, #9b8dff) !important;
}

.edd-invoices-generate-invoice-button:active {
    transform: translateY(0) !important;
}

/* Add icon to submit button */
.edd-invoices-generate-invoice-button:before {
    content: "📄";
    font-size: 1.1rem;
    margin-right: 6px;
}

/* Grid layout for city and zip */
.edd-invoices-div__city,
.edd-invoices-div__zip {
    display: inline-block !important;
    width: calc(50% - 10px) !important;
    vertical-align: top !important;
}

.edd-invoices-div__city {
    margin-right: 20px !important;
}

/* Grid layout for country and state */
.edd-invoices-div__country,
.edd-invoices-div__state {
    display: inline-block !important;
    width: calc(50% - 10px) !important;
    vertical-align: top !important;
}

.edd-invoices-div__country {
    margin-right: 20px !important;
}

/* Hidden fields */
input[name="address-id"],
input[name="_wp_http_referer"],
input[name="edd-invoices-nonce"] {
    display: none !important;
}

/* Specific field styling */
#company {
    background-color: #f8f9fa !important;
}

#name {
    border-color: var(--aurora-gray-light) !important;
}

#name:focus {
    border-color: var(--aurora-primary) !important;
}

/* Optional field labels */
.edd-invoices-div__company .edd-invoices-field:after,
.edd-invoices-div__address2 .edd-invoices-field:after,
.edd-invoices-div__vat .edd-invoices-field:after,
.edd-invoices-div__notes .edd-invoices-field:after {
    content: " (Optional)";
    color: var(--aurora-gray);
    font-weight: 400;
}

/* Required field indicator */
.edd-invoices-div__name .edd-invoices-field:after {
    content: " *";
    color: var(--aurora-danger);
}

/* Form section headers */
.edd-invoices-div h3 {
    color: var(--aurora-dark) !important;
    font-size: 1.1rem !important;
    margin: 25px 0 15px 0 !important;
    padding-bottom: 8px !important;
    border-bottom: 2px solid var(--aurora-light) !important;
}

/* Responsive design */
@media (max-width: 768px) {
    .edd-invoices-div__city,
    .edd-invoices-div__zip,
    .edd-invoices-div__country,
    .edd-invoices-div__state {
        display: block !important;
        width: 100% !important;
        margin-right: 0 !important;
        margin-bottom: 15px !important;
    }
    
    .edd-invoices-generate-invoice-button {
        width: 100% !important;
        text-align: center !important;
        justify-content: center !important;
    }
}

/* Loading state */
#edd-invoices.loading {
    pointer-events: none !important;
    opacity: 0.7 !important;
}

#edd-invoices.loading .edd-invoices-generate-invoice-button {
    background: var(--aurora-gray) !important;
    cursor: not-allowed !important;
}

#edd-invoices.loading .edd-invoices-generate-invoice-button:before {
    content: "⏳";
    animation: pulse 1.5s ease-in-out infinite;
}

/* Animation keyframes */
@keyframes pulse {
    0%, 100% { 
        opacity: 1; 
    }
    50% { 
        opacity: 0.5; 
    }
}

/* Placeholder styling */
#edd-invoices input::placeholder,
#edd-invoices textarea::placeholder {
    color: var(--aurora-gray) !important;
    font-style: italic !important;
}

/* Error and success states */
#edd-invoices input.error,
#edd-invoices select.error,
#edd-invoices textarea.error {
    border-color: var(--aurora-danger) !important;
    box-shadow: 0 0 0 3px rgba(214, 48, 49, 0.1) !important;
}

#edd-invoices input.success,
#edd-invoices select.success,
#edd-invoices textarea.success {
    border-color: var(--aurora-secondary) !important;
    box-shadow: 0 0 0 3px rgba(0, 184, 148, 0.1) !important;
}

/* Form validation feedback */
.edd-invoices-field-error {
    color: var(--aurora-danger) !important;
    font-size: 0.8rem !important;
    margin-top: 5px !important;
    display: block !important;
    font-weight: 500 !important;
}

/* Success message styling */
.edd-invoices-success {
    background: rgba(0, 184, 148, 0.1) !important;
    color: var(--aurora-secondary) !important;
    padding: 15px 20px !important;
    border-radius: 8px !important;
    border: 1px solid rgba(0, 184, 148, 0.2) !important;
    margin: 20px 0 !important;
}
</style>

<?php else : ?>

<!-- Invalid Invoice Access -->
<div style="padding: 30px;">
    <div style="background: linear-gradient(135deg, #e74c3c, #c0392b); border-radius: 12px; padding: 25px; color: white; text-align: center;">
        <div style="font-size: 3rem; margin-bottom: 15px;">
            <i class="fas fa-ban"></i>
        </div>
        <h3 style="margin: 0 0 10px 0; font-size: 1.4rem;"><?php esc_html_e('Access Denied', 'edd-customer-dashboard-pro'); ?></h3>
        <p style="margin: 0 0 20px 0; opacity: 0.9;"><?php esc_html_e('You do not have permission to view this invoice or the invoice is invalid.', 'edd-customer-dashboard-pro'); ?></p>
        <a href="<?php echo esc_url(remove_query_arg(array('eddcdp_invoice_form', 'payment_id'))); ?>" 
           class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); text-decoration: none;">
            <i class="fas fa-arrow-left"></i> <?php esc_html_e('Back to Dashboard', 'edd-customer-dashboard-pro'); ?>
        </a>
    </div>
</div>

<?php endif; ?>

<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add loading state to invoice form
    const invoiceForm = document.querySelector('#edd-invoices form');
    if (invoiceForm) {
        invoiceForm.addEventListener('submit', function() {
            document.getElementById('edd-invoices').classList.add('loading');
        });
    }
    
    // Enhance form UX
    const invoiceInputs = document.querySelectorAll('#edd-invoices input, #edd-invoices select, #edd-invoices textarea');
    invoiceInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'translateY(-2px)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'translateY(0)';
        });
    });
});
</script>