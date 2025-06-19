//jQuery(document).ready(function($) {
//
//        
//    // Auto-hide success messages after 5 seconds
//    $('.notice.notice-success, .notice.notice-error').delay(5000).fadeOut(500);
//    
//    
//
//    
//    // Template hover effects
//    $('.eddcdp-template-option').hover(
//        function() {
//            if (!$(this).hasClass('selected')) {
//                $(this).css('transform', 'translateY(-2px)');
//            }
//        },
//        function() {
//            $(this).css('transform', 'translateY(0)');
//        }
//    );
//});


jQuery(document).ready(function($) {
    
    // Auto-hide success messages after 5 seconds
    $('.notice.notice-success, .notice.notice-error').delay(5000).fadeOut(500);
    
    // Template card interactions
    $('.eddcdp-template-card').hover(
        function() {
            if (!$(this).hasClass('active')) {
                $(this).css('transform', 'translateY(-2px)');
            }
        },
        function() {
            if (!$(this).hasClass('active')) {
                $(this).css('transform', 'translateY(0)');
            }
        }
    );
    
    // Enhanced template activation with loading state
    $('.eddcdp-activate-btn').on('click', function(e) {
        const $btn = $(this);
        const $card = $btn.closest('.eddcdp-template-card');
        
        // Add loading state
        $card.addClass('loading');
        $btn.prop('disabled', true);
        
        // Update button text
        const originalText = $btn.text();
        $btn.html('<span class="dashicons dashicons-update spin"></span> Activating...');
        
        // Don't prevent default - let the normal form submission happen
        // The loading state will be visible until page reload
    });
    
    // Toggle switches enhancement
    $('.eddcdp-toggle input').on('change', function() {
        const $toggle = $(this).closest('.eddcdp-toggle');
        
        if ($(this).is(':checked')) {
            $toggle.addClass('checked');
        } else {
            $toggle.removeClass('checked');
        }
    });
    
    // Initialize toggle states
    $('.eddcdp-toggle input:checked').each(function() {
        $(this).closest('.eddcdp-toggle').addClass('checked');
    });
    
    // Form validation
    $('form').on('submit', function() {
        const $form = $(this);
        const $submitBtn = $form.find('input[type="submit"], button[type="submit"]');
        
        if ($submitBtn.length) {
            const originalText = $submitBtn.val() || $submitBtn.text();
            $submitBtn.prop('disabled', true);
            
            if ($submitBtn.is('input')) {
                $submitBtn.val('Saving...');
            } else {
                $submitBtn.html('<span class="dashicons dashicons-update spin"></span> Saving...');
            }
            
            // Reset after 10 seconds as fallback
            setTimeout(function() {
                $submitBtn.prop('disabled', false);
                if ($submitBtn.is('input')) {
                    $submitBtn.val(originalText);
                } else {
                    $submitBtn.text(originalText);
                }
            }, 3000);
        }
    });
    
    // Add smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 30
            }, 500);
        }
    });
    
    // Template card keyboard accessibility
    $('.eddcdp-template-card').on('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            const $activateBtn = $(this).find('.eddcdp-activate-btn');
            if ($activateBtn.length) {
                e.preventDefault();
                $activateBtn[0].click();
            }
        }
    });
    
    // Add tabindex to template cards for keyboard navigation
    $('.eddcdp-template-card').attr('tabindex', '0');
    
    // Responsive table handling
    function handleResponsiveTables() {
        const $tables = $('.form-table');
        
        if ($(window).width() <= 768) {
            $tables.addClass('mobile-layout');
        } else {
            $tables.removeClass('mobile-layout');
        }
    }
    
    // Handle responsive changes
    $(window).on('resize', function() {
        handleResponsiveTables();
    });
    
    // Initialize responsive handling
    handleResponsiveTables();
    
    // Template grid masonry-like layout adjustment
    function adjustTemplateGrid() {
        const $grid = $('.eddcdp-template-grid');
        const $cards = $grid.find('.eddcdp-template-card');
        
        // Reset heights
        $cards.css('height', 'auto');
        
        if ($(window).width() > 768) {
            // Find max height in each row
            let maxHeight = 0;
            $cards.each(function() {
                const height = $(this).outerHeight();
                if (height > maxHeight) {
                    maxHeight = height;
                }
            });
            
            // Set all cards to max height
            $cards.css('height', maxHeight + 'px');
        }
    }
    
    // Adjust grid on load and resize
    $(window).on('load resize', function() {
        setTimeout(adjustTemplateGrid, 100);
    });
    
    // Add CSS animation class for spinning dashicons
    $('<style>').prop('type', 'text/css').html(`
        .spin {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .eddcdp-toggle.checked .eddcdp-toggle-slider {
            background-color: #00a32a !important;
        }
        
        .form-table.mobile-layout th,
        .form-table.mobile-layout td {
            display: block;
            width: 100%;
        }
        
        .form-table.mobile-layout th {
            padding-bottom: 5px;
            border-bottom: none;
        }
    `).appendTo('head');
    
    // Enhanced error handling
    $(document).ajaxError(function(event, xhr, settings, thrownError) {
        console.error('AJAX Error:', {
            url: settings.url,
            error: thrownError,
            status: xhr.status,
            statusText: xhr.statusText
        });
        
        // Show user-friendly error message
        if (xhr.status === 0) {
            alert('Network error. Please check your connection and try again.');
        } else if (xhr.status >= 500) {
            alert('Server error. Please try again later.');
        } else if (xhr.status === 403) {
            alert('Permission denied. Please refresh the page and try again.');
        }
    });
    
    // Initialize tooltips if available
    if (typeof $.fn.tooltip === 'function') {
        $('[title]').tooltip();
    }
    
    // Add confirmation for potentially destructive actions
    $('a[href*="delete"], a[href*="remove"], button[name*="delete"]').on('click', function(e) {
        const action = $(this).text().toLowerCase();
        if (action.includes('delete') || action.includes('remove')) {
            if (!confirm('Are you sure you want to ' + action + '? This action cannot be undone.')) {
                e.preventDefault();
                return false;
            }
        }
    });
    
    // Trigger custom event when admin page is fully loaded
    $(document).trigger('eddcdp:admin:ready');
    
});