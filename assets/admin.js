jQuery(document).ready(function($) {
    
    // Toggle switches only
    $('.eddcdp-toggle input').on('change', function() {
        const $toggle = $(this).closest('.eddcdp-toggle');
        if ($(this).is(':checked')) {
            $toggle.addClass('active');
        } else {
            $toggle.removeClass('active');
        }
    });
    
    // Form validation
    $('form').on('submit', function(e) {
        let isValid = true;
        
        // Check if at least one section is enabled
        const enabledSections = $('input[name^="eddcdp_settings[enabled_sections]"]:checked').length;
        if (enabledSections === 0) {
            alert('Please enable at least one dashboard section.');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Initialize active toggles
    $('.eddcdp-toggle input:checked').each(function() {
        $(this).closest('.eddcdp-toggle').addClass('active');
    });
    
    // Settings save feedback
    if (window.location.search.includes('settings-updated=true') || window.location.search.includes('template_activated=1')) {
        const message = window.location.search.includes('template_activated=1') ? 
            'Template activated successfully!' : 
            'Settings saved successfully!';
            
        const $notice = $('<div class="notice notice-success is-dismissible"><p>' + message + '</p></div>');
        $('.wrap h1').after($notice);
        
        setTimeout(function() {
            $notice.fadeOut();
        }, 3000);
    }
});