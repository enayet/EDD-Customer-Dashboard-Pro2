jQuery(document).ready(function($) {
    
    // ENHANCED: Detect if we're in full screen mode
    const isFullScreen = $('body').hasClass('eddcdp-fullscreen-mode') || 
                         window.location.search.includes('eddcdp_fullscreen=1') || 
                         window.location.search.includes('eddcdp_view=fullscreen');
    
    // Tab Navigation
    $('.eddcdp-nav-tab').on('click', function(e) {
        e.preventDefault();
        
        // Remove active class from all tabs and sections
        $('.eddcdp-nav-tab').removeClass('active');
        $('.eddcdp-content-section').removeClass('active');
        
        // Add active class to clicked tab
        $(this).addClass('active');
        
        // Show corresponding content section
        const targetSection = $(this).data('section');
        $('#eddcdp-' + targetSection).addClass('active');
        
        // Update URL without refreshing page (if not in receipt mode and not full screen)
        if (!window.location.search.includes('payment_key=') && !isFullScreen) {
            const newUrl = window.location.pathname + '?section=' + targetSection;
            window.history.pushState({}, '', newUrl);
        }
        
        // Smooth scroll on mobile
        if ($(window).width() <= 768) {
            $('html, body').animate({
                scrollTop: $('.eddcdp-dashboard-content').offset().top - 20
            }, 300);
        }
    });
    
    // Handle URL section parameter on page load
    const urlParams = new URLSearchParams(window.location.search);
    const sectionParam = urlParams.get('section');
    if (sectionParam && !urlParams.get('payment_key')) {
        // Only handle section switching if not in receipt mode
        const targetTab = $('[data-section="' + sectionParam + '"]');
        if (targetTab.length) {
            targetTab.click();
        }
    }
    
    // ENHANCED: Full screen toggle functionality
    $('.eddcdp-fullscreen-toggle a').on('click', function(e) {
        // Add loading animation to button
        const $btn = $(this);
        const originalHtml = $btn.html();
        
        $btn.html('🔄 ' + (typeof eddcdp_ajax !== 'undefined' && eddcdp_ajax.loading_text ? 
                  eddcdp_ajax.loading_text : 'Loading...'));
        
        // Let the navigation proceed naturally
        setTimeout(function() {
            // This will only run if navigation was prevented
            $btn.html(originalHtml);
        }, 3000);
    });
    
    // Enhanced Back to Dashboard functionality
    $('.eddcdp-btn').filter(function() {
        return $(this).text().indexOf('Back to Dashboard') !== -1 || 
               $(this).text().indexOf('Back to Site') !== -1;
    }).on('click', function(e) {
        e.preventDefault();
        
        // Get the URL without full screen parameters
        let targetUrl = $(this).attr('href');
        
        // If it's a "Back to Site" button in full screen mode
        if ($(this).text().indexOf('Back to Site') !== -1) {
            // Remove full screen parameters from URL
            targetUrl = removeFullScreenParams(targetUrl);
        }
        
        // Smooth transition
        $('body').addClass('eddcdp-transitioning');
        
        setTimeout(function() {
            window.location.href = targetUrl;
        }, 200);
    });
    
    // ENHANCED: Function to remove full screen parameters
    function removeFullScreenParams(url) {
        const urlObj = new URL(url, window.location.origin);
        urlObj.searchParams.delete('eddcdp_fullscreen');
        urlObj.searchParams.delete('eddcdp_view');
        return urlObj.toString();
    }
    
    // ENHANCED: Handle ESC key in full screen mode
    if (isFullScreen) {
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                const backBtn = $('.eddcdp-close-fullscreen, .eddcdp-btn').filter(function() {
                    return $(this).text().indexOf('Back to Site') !== -1 || 
                           $(this).text().indexOf('Back to Dashboard') !== -1;
                }).first();
                
                if (backBtn.length) {
                    backBtn[0].click();
                }
            }
        });
        
        // Add visual indicator for ESC functionality
        if ($('.eddcdp-fullscreen-header').length) {
            const escHint = $('<div class="eddcdp-esc-hint" style="position: absolute; top: 10px; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,0.7); color: white; padding: 5px 10px; border-radius: 15px; font-size: 11px; z-index: 1001; opacity: 0.8;">Press ESC to exit</div>');
            $('.eddcdp-fullscreen-header').append(escHint);
            
            // Auto-hide the hint after 3 seconds
            setTimeout(function() {
                escHint.fadeOut(500);
            }, 3000);
        }
    }
    
    // Copy license key functionality
    $('.eddcdp-license-key').on('click', function() {
        const $this = $(this);
        const licenseKey = $this.text().trim();
        
        // Modern clipboard API
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(licenseKey).then(function() {
                showCopyTooltip($this);
            }).catch(function() {
                fallbackCopyToClipboard(licenseKey, $this);
            });
        } else {
            fallbackCopyToClipboard(licenseKey, $this);
        }
    });
    
    // Copy payment key functionality for receipts
    $('.eddcdp-payment-key').on('click', function() {
        const $this = $(this);
        const paymentKey = $this.text().trim();
        
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(paymentKey).then(function() {
                showCopyTooltip($this);
            }).catch(function() {
                fallbackCopyToClipboard(paymentKey, $this);
            });
        } else {
            fallbackCopyToClipboard(paymentKey, $this);
        }
    });
    
    // Fallback copy function for older browsers
    function fallbackCopyToClipboard(text, $element) {
        const $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(text).select();
        
        try {
            document.execCommand('copy');
            showCopyTooltip($element);
        } catch(err) {
            console.error('Failed to copy:', err);
            alert('Copied: ' + text);
        }
        
        $temp.remove();
    }
    
    // Show copy tooltip
    function showCopyTooltip($element) {
        const $tooltip = $('<span class="eddcdp-copy-tooltip">Copied!</span>');
        $element.append($tooltip);
        
        // Remove tooltip after animation
        setTimeout(function() {
            $tooltip.remove();
        }, 1500);
        
        // Visual feedback
        const originalBg = $element.css('background');
        $element.css('background', 'rgba(67, 233, 123, 0.2)');
        
        setTimeout(function() {
            $element.css('background', originalBg);
        }, 1000);
    }
    
    // Enhanced download button click handlers with receipt support
    $('.eddcdp-btn-download').on('click', function(e) {
        const $btn = $(this);
        const originalHtml = $btn.html();
        const originalDisabled = $btn.prop('disabled');
        
        // Show loading state
        $btn.html('⏳ Preparing...').prop('disabled', true);
        
        // Don't prevent default for actual download links
        // Just show the loading state
        setTimeout(function() {
            $btn.html('✅ Downloaded');
            
            // Reset button after delay
            setTimeout(function() {
                $btn.html(originalHtml).prop('disabled', originalDisabled);
            }, 2000);
        }, 1500);
    });
    
    // Add hover animations to stat cards
    $('.eddcdp-stat-card').hover(
        function() {
            $(this).css('transform', 'translateY(-5px) scale(1.02)');
        },
        function() {
            $(this).css('transform', 'translateY(0) scale(1)');
        }
    );
    
    // ENHANCED: Full screen aware link handling
    $('a[href*="payment_key="], a[href*="view=invoice"]').on('click', function(e) {
        // Only handle if it's a same-page link and we're in full screen mode
        if (isFullScreen && ($(this).attr('href').indexOf(window.location.pathname) === 0 || 
            $(this).attr('href').indexOf('?payment_key=') === 0)) {
            
            // Add full screen parameters to maintain full screen mode
            const currentHref = $(this).attr('href');
            if (!currentHref.includes('eddcdp_fullscreen') && !currentHref.includes('eddcdp_view=fullscreen')) {
                const separator = currentHref.includes('?') ? '&' : '?';
                $(this).attr('href', currentHref + separator + 'eddcdp_fullscreen=1');
            }
            
            $('body').addClass('eddcdp-transitioning');
            
            // Small delay for smooth transition
            setTimeout(function() {
                window.location.href = $(e.target).attr('href');
            }, 200);
        }
    });
    
    // Print receipt functionality
    $('button[onclick*="window.print"]').on('click', function(e) {
        e.preventDefault();
        
        // Add print-specific class to body
        $('body').addClass('eddcdp-printing');
        
        // Delay to allow CSS to apply
        setTimeout(function() {
            window.print();
            $('body').removeClass('eddcdp-printing');
        }, 100);
    });
    
    // License activation functionality
    $('.eddcdp-activate-license').on('click', function(e) {
        e.preventDefault();
        
        const $btn = $(this);
        const $input = $btn.siblings('.eddcdp-site-url');
        const siteUrl = $input.val().trim();
        const licenseKey = $input.data('license');
        
        if (!siteUrl) {
            alert('Please enter a valid site URL');
            $input.focus();
            return;
        }
        
        // Basic URL validation
        const urlPattern = /^https?:\/\/.+/i;
        if (!urlPattern.test(siteUrl)) {
            alert('Please enter a valid URL starting with http:// or https://');
            $input.focus();
            return;
        }
        
        // Show loading state
        const originalHtml = $btn.html();
        $btn.html('⏳ Activating...').prop('disabled', true);
        
        // AJAX call for license activation
        $.ajax({
            url: eddcdp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'eddcdp_activate_license',
                license_key: licenseKey,
                site_url: siteUrl,
                nonce: eddcdp_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('License activated successfully!');
                    $input.val('');
                    // Reload the section or update the UI
                    location.reload();
                } else {
                    alert('Activation failed: ' + (response.data || 'Unknown error'));
                }
            },
            error: function() {
                alert('Network error. Please try again.');
            },
            complete: function() {
                $btn.html(originalHtml).prop('disabled', false);
            }
        });
    });
    
    // License deactivation functionality
    $(document).on('click', '[data-license][data-site]', function(e) {
        e.preventDefault();
        
        const $btn = $(this);
        const licenseKey = $btn.data('license');
        const siteUrl = $btn.data('site');
        
        if (!confirm('Are you sure you want to deactivate this license from ' + siteUrl + '?')) {
            return;
        }
        
        // Show loading state
        const originalHtml = $btn.html();
        $btn.html('⏳ Deactivating...').prop('disabled', true);
        
        // AJAX call for license deactivation
        $.ajax({
            url: eddcdp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'eddcdp_deactivate_license',
                license_key: licenseKey,
                site_url: siteUrl,
                nonce: eddcdp_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('License deactivated successfully!');
                    // Reload the section or update the UI
                    location.reload();
                } else {
                    alert('Deactivation failed: ' + (response.data || 'Unknown error'));
                }
            },
            error: function() {
                alert('Network error. Please try again.');
            },
            complete: function() {
                $btn.html(originalHtml).prop('disabled', false);
            }
        });
    });
    
    // Wishlist removal functionality
    $('.eddcdp-remove-wishlist').on('click', function(e) {
        e.preventDefault();
        
        const $btn = $(this);
        const downloadId = $btn.data('download-id');
        
        if (!confirm('Are you sure you want to remove this item from your wishlist?')) {
            return;
        }
        
        // Show loading state
        const originalHtml = $btn.html();
        $btn.html('⏳ Removing...').prop('disabled', true);
        
        // AJAX call for wishlist removal
        $.ajax({
            url: eddcdp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'eddcdp_remove_wishlist',
                download_id: downloadId,
                nonce: eddcdp_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Remove the wishlist item with animation
                    $btn.closest('.eddcdp-wishlist-item').fadeOut(300, function() {
                        $(this).remove();
                        
                        // Check if wishlist is empty
                        if ($('.eddcdp-wishlist-item').length === 0) {
                            location.reload();
                        }
                    });
                } else {
                    alert('Failed to remove item: ' + (response.data || 'Unknown error'));
                    $btn.html(originalHtml).prop('disabled', false);
                }
            },
            error: function() {
                alert('Network error. Please try again.');
                $btn.html(originalHtml).prop('disabled', false);
            }
        });
    });
    
    // FAQ toggle functionality
    $('.eddcdp-faq-question').on('click', function() {
        const $answer = $(this).next('.eddcdp-faq-answer');
        const $icon = $(this).find('span');
        
        if ($answer.is(':visible')) {
            $answer.slideUp(300);
            $icon.text('▼');
        } else {
            $answer.slideDown(300);
            $icon.text('▲');
        }
    });
    
    // ENHANCED: Responsive navigation for full screen mode
    if ($(window).width() <= 768) {
        $('.eddcdp-nav-tab').on('click', function() {
            // Collapse nav on mobile after selection
            setTimeout(function() {
                $('.eddcdp-dashboard-nav').animate({
                    height: '80px'
                }, 300);
            }, 100);
        });
        
        // ENHANCED: In full screen mode, ensure header is always visible
        if (isFullScreen) {
            $(window).on('scroll', function() {
                const scrollTop = $(window).scrollTop();
                const $header = $('.eddcdp-fullscreen-header');
                
                if (scrollTop > 50) {
                    $header.addClass('eddcdp-header-compact');
                } else {
                    $header.removeClass('eddcdp-header-compact');
                }
            });
        }
    }
    
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.getAttribute('href'));
        if (target.length) {
            const offset = isFullScreen ? 100 : 20; // Account for full screen header
            $('html, body').animate({
                scrollTop: target.offset().top - offset
            }, 500);
        }
    });
    
    // Auto-hide success messages
    $('.notice-success').delay(5000).fadeOut(500);
    
    // Initialize tooltips for license keys and payment keys
    $('.eddcdp-license-key, .eddcdp-payment-key').attr('title', 'Click to copy');
    
    // Keyboard accessibility for tabs
    $('.eddcdp-nav-tab').on('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).click();
        }
    });
    
    // Handle window resize for responsive features
    $(window).on('resize', function() {
        // Reset navigation height on desktop
        if ($(window).width() > 768) {
            $('.eddcdp-dashboard-nav').css('height', 'auto');
        }
        
        // ENHANCED: Adjust full screen layout on resize
        if (isFullScreen) {
            adjustFullScreenLayout();
        }
    });
    
    // ENHANCED: Full screen layout adjustments
    function adjustFullScreenLayout() {
        const windowHeight = $(window).height();
        const headerHeight = $('.eddcdp-fullscreen-header').outerHeight();
        const availableHeight = windowHeight - headerHeight;
        
        $('.eddcdp-fullscreen-content').css('min-height', availableHeight + 'px');
    }
    
    // Lazy load images in wishlist and receipts
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Handle form submissions with loading states
    $('form').on('submit', function() {
        const $form = $(this);
        const $submitBtn = $form.find('input[type="submit"], button[type="submit"]');
        
        if ($submitBtn.length) {
            const originalText = $submitBtn.val() || $submitBtn.text();
            $submitBtn.prop('disabled', true);
            
            if ($submitBtn.is('input')) {
                $submitBtn.val('Processing...');
            } else {
                $submitBtn.text('Processing...');
            }
            
            // Reset after 10 seconds as fallback
            setTimeout(function() {
                $submitBtn.prop('disabled', false);
                if ($submitBtn.is('input')) {
                    $submitBtn.val(originalText);
                } else {
                    $submitBtn.text(originalText);
                }
            }, 10000);
        }
    });
    
    // Enhanced error handling for AJAX requests
    $(document).ajaxError(function(event, xhr, settings, thrownError) {
        console.error('AJAX Error:', {
            url: settings.url,
            error: thrownError,
            status: xhr.status,
            statusText: xhr.statusText
        });
    });
    
    // Receipt-specific functionality
    if ($('.eddcdp-receipt-section').length) {
        // Auto-focus back to dashboard button for better UX
        $('.eddcdp-receipt-actions a:first-child').focus();
        
        // Add smooth scrolling for receipt sections
        $('.eddcdp-receipt-card-title').on('click', function() {
            const $card = $(this).closest('.eddcdp-receipt-card');
            const offset = isFullScreen ? 100 : 20;
            $('html, body').animate({
                scrollTop: $card.offset().top - offset
            }, 300);
        });
    }
    
    // Add transition classes for smooth navigation
    $('body').addClass('eddcdp-transitions-enabled');
    
    // ENHANCED: Initialize the dashboard with full screen awareness
    initializeDashboard();
    
    function initializeDashboard() {
        // Set the first enabled section as active if none is active (and not in receipt mode)
        if ($('.eddcdp-content-section.active').length === 0 && !$('.eddcdp-receipt-section').length) {
            const $firstTab = $('.eddcdp-nav-tab').first();
            if ($firstTab.length) {
                $firstTab.addClass('active');
                const firstSection = $firstTab.data('section');
                $('#eddcdp-' + firstSection).addClass('active');
            }
        }
        
        // ENHANCED: Apply full screen layout adjustments
        if (isFullScreen) {
            adjustFullScreenLayout();
            
            // Set focus to close button for accessibility
            setTimeout(function() {
                $('.eddcdp-close-fullscreen').focus();
            }, 500);
        }
        
        // Add loading class to body to prevent FOUC
        $('body').removeClass('eddcdp-loading');
        
        // Trigger custom event for other scripts
        $(document).trigger('eddcdp:dashboard:ready', { fullScreen: isFullScreen });
    }
    
    // ENHANCED: Full screen mode specific enhancements
    if (isFullScreen) {
        // Prevent page zoom/scale gestures that might interfere with full screen
        $(document).on('gesturestart', function(e) {
            e.preventDefault();
        });
        
        // Add focus trap for accessibility in full screen mode
        const focusableElements = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
        const $modal = $('.eddcdp-fullscreen-wrapper');
        const $firstFocusable = $modal.find(focusableElements).first();
        const $lastFocusable = $modal.find(focusableElements).last();
        
        $(document).on('keydown', function(e) {
            if (e.key === 'Tab') {
                if (e.shiftKey) {
                    if (document.activeElement === $firstFocusable[0]) {
                        $lastFocusable.focus();
                        e.preventDefault();
                    }
                } else {
                    if (document.activeElement === $lastFocusable[0]) {
                        $firstFocusable.focus();
                        e.preventDefault();
                    }
                }
            }
        });
    }
});

// ENHANCED: Add CSS for transitions and full screen mode
$('<style>').prop('type', 'text/css').html(`
    .eddcdp-transitions-enabled * {
        transition: all 0.2s ease;
    }
    
    .eddcdp-transitioning {
        opacity: 0.8;
        pointer-events: none;
    }
    
    .eddcdp-printing .eddcdp-dashboard-header,
    .eddcdp-printing .eddcdp-stats-grid,
    .eddcdp-printing .eddcdp-dashboard-nav,
    .eddcdp-printing .eddcdp-receipt-actions,
    .eddcdp-printing .eddcdp-fullscreen-header {
        display: none !important;
    }
    
    /* Full screen mode enhancements */
    .eddcdp-fullscreen-mode .eddcdp-fullscreen-toggle {
        display: none !important;
    }
    
    .eddcdp-header-compact {
        transform: translateY(-10px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2) !important;
    }
    
    .eddcdp-esc-hint {
        animation: eddcdpPulse 2s infinite;
    }
    
    @keyframes eddcdpPulse {
        0% { opacity: 0.8; }
        50% { opacity: 1; }
        100% { opacity: 0.8; }
    }
    
    @media print {
        .eddcdp-receipt-actions,
        .eddcdp-dashboard-header,
        .eddcdp-stats-grid,
        .eddcdp-dashboard-nav,
        .eddcdp-fullscreen-header,
        .eddcdp-esc-hint {
            display: none !important;
        }
        
        .eddcdp-receipt-card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
            margin-bottom: 20px !important;
            page-break-inside: avoid;
        }
        
        body {
            background: white !important;
        }
        
        .eddcdp-dashboard-container,
        .eddcdp-fullscreen-content {
            background: white !important;
            padding: 0 !important;
            max-width: none !important;
            min-height: auto !important;
        }
    }
    
    /* Mobile full screen optimizations */
    @media (max-width: 768px) {
        .eddcdp-fullscreen-mode .eddcdp-dashboard-container {
            padding: 10px !important;
        }
        
        .eddcdp-fullscreen-header {
            padding: 10px 15px !important;
        }
        
        .eddcdp-esc-hint {
            display: none; /* Hide ESC hint on mobile */
        }
    }
`).appendTo('head');