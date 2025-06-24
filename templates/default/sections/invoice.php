<?php
/**
 * Invoice Form Section for Dashboard
 * Add this to your dashboard.php where we handle the invoice form
 */

// This goes in your dashboard.php after the order licenses check
if (isset($_GET['eddcdp_invoice_form']) && isset($_GET['payment_id'])) : 
    // Handle invoice form display
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
    <div class="p-8">
        <!-- Header with Back Button -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <a href="<?php echo remove_query_arg(array('eddcdp_invoice_form', 'payment_id', 'invoice')); ?>" 
               class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-medium">
                ← <?php _e('Back to Dashboard', 'eddcdp'); ?>
            </a>

            <div class="text-sm text-gray-600">
                <?php printf(__('Update Invoice for Order #%s', 'eddcdp'), $order->get_number()); ?>
            </div>
        </div>

        <!-- Invoice Form Header -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 mb-6 shadow-lg border border-white/20">
            <h2 class="text-3xl font-bold text-gray-800 mb-2 flex items-center gap-3">
                📄 <?php printf(__('Update Invoice Details - Order #%s', 'eddcdp'), $order->get_number()); ?>
            </h2>
            <p class="text-gray-600">
                <?php _e('Update your billing details and generate a new invoice', 'eddcdp'); ?>
            </p>
        </div>

        <!-- Invoice Form Section -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20">
            <h3 class="text-xl font-semibold text-gray-800 mb-6"><?php _e('Billing Information', 'eddcdp'); ?></h3>

            <?php

                echo do_shortcode('[edd_invoices]');

            ?>        


        </div>
    </div>



<?php else : ?>

<!-- Invalid Invoice Access -->
<div class="p-8">
    <div class="bg-red-50/80 rounded-2xl p-6 border border-red-200/50">
        <h3 class="text-lg font-semibold text-red-800 mb-2"><?php _e('Access Denied', 'eddcdp'); ?></h3>
        <p class="text-red-700"><?php _e('You do not have permission to view this invoice or the invoice is invalid.', 'eddcdp'); ?></p>
        <div class="mt-4">
            <a href="<?php echo remove_query_arg(array('eddcdp_invoice_form', 'payment_id')); ?>" 
               class="text-indigo-600 hover:text-indigo-800 font-medium">
                ← <?php _e('Back to Dashboard', 'eddcdp'); ?>
            </a>
        </div>
    </div>
</div>

    <?php endif; ?>

<?php endif; ?>


<style>

/* EDD Invoices Form Styling - Target existing generated form */

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
    margin-bottom: 0 !important;
    background: none !important;
    border: none !important;
    padding: 0 !important;
}

/* Labels */
.edd-invoices-field {
    display: block !important;
    font-size: 0.875rem !important;
    font-weight: 500 !important;
    color: #374151 !important;
    margin-bottom: 0.5rem !important;
    background: none !important;
    border: none !important;
    padding: 0 !important;
    line-height: 1.25 !important;
}

/* All input fields */
#edd-invoices input[type="text"],
#edd-invoices select,
#edd-invoices textarea {
    width: 100% !important;
    padding: 0.75rem 1rem !important;
    border: 1px solid #d1d5db !important;
    border-radius: 0.75rem !important;
    font-size: 1rem !important;
    line-height: 1.5 !important;
    background-color: #ffffff !important;
    transition: all 0.2s ease-in-out !important;
    box-shadow: none !important;
    outline: none !important;
    font-family: inherit !important;
    margin: 0 !important;
}

/* Focus states */
#edd-invoices input[type="text"]:focus,
#edd-invoices select:focus,
#edd-invoices textarea:focus {
    border-color: #6366f1 !important;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
    outline: none !important;
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
    background-position: right 0.75rem center !important;
    background-repeat: no-repeat !important;
    background-size: 1rem !important;
    background-color: #ffffff !important;
    padding-right: 2.5rem !important;
}

/* Submit button styling */
.edd-invoices-generate-invoice-button {
    background: linear-gradient(to right, #6366f1, #8b5cf6) !important;
    color: white !important;
    border: none !important;
    padding: 1rem 2rem !important;
    border-radius: 0.75rem !important;
    font-weight: 600 !important;
    font-size: 1rem !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    text-transform: none !important;
    width: auto !important;
    margin: 1.5rem 0 0 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
    text-decoration: none !important;
    line-height: 1.25 !important;
    font-family: inherit !important;
}

.edd-invoices-generate-invoice-button:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
    background: linear-gradient(to right, #5855eb, #7c3aed) !important;
}

.edd-invoices-generate-invoice-button:active {
    transform: translateY(0) !important;
}

/* Add icon to submit button */
.edd-invoices-generate-invoice-button:before {
    content: "💾";
    font-size: 1.1rem;
    margin-right: 0.25rem;
}

/* Grid layout for city and zip */
.edd-invoices-div__city,
.edd-invoices-div__zip {
    display: inline-block !important;
    width: calc(50% - 0.5rem) !important;
    vertical-align: top !important;
}

.edd-invoices-div__city {
    margin-right: 1rem !important;
}

/* Grid layout for country and state */
.edd-invoices-div__country,
.edd-invoices-div__state {
    display: inline-block !important;
    width: calc(50% - 0.5rem) !important;
    vertical-align: top !important;
}

.edd-invoices-div__country {
    margin-right: 1rem !important;
}

/* Hidden fields */
input[name="address-id"],
input[name="_wp_http_referer"],
input[name="edd-invoices-nonce"] {
    display: none !important;
}

/* Specific field styling */
#company {
    background-color: #f9fafb !important;
}

#name {
    border-color: #d1d5db !important;
}

#name:focus {
    border-color: #6366f1 !important;
}

/* Optional field labels */
.edd-invoices-div__company .edd-invoices-field:after,
.edd-invoices-div__address2 .edd-invoices-field:after,
.edd-invoices-div__vat .edd-invoices-field:after,
.edd-invoices-div__notes .edd-invoices-field:after {
    content: " (Optional)";
    color: #9ca3af;
    font-weight: 400;
}

/* Required field indicator */
.edd-invoices-div__name .edd-invoices-field:after {
    content: " *";
    color: #ef4444;
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
    background: #9ca3af !important;
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
    color: #9ca3af !important;
    font-style: italic !important;
}

/* Error and success states */
#edd-invoices input.error,
#edd-invoices select.error,
#edd-invoices textarea.error {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
}

#edd-invoices input.success,
#edd-invoices select.success,
#edd-invoices textarea.success {
    border-color: #10b981 !important;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important;
}

/* Form validation feedback */
.edd-invoices-field-error {
    color: #ef4444 !important;
    font-size: 0.75rem !important;
    margin-top: 0.25rem !important;
    display: block !important;
}


</style>