jQuery(document).ready(function($) {
    
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
        
        // Smooth scroll on mobile
        if ($(window).width() <= 768) {
            $('html, body').animate({
                scrollTop: $('.eddcdp-dashboard-content').offset().top - 20
            }, 300);
        }
    });
    
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
    
    // Fallback copy function for older browsers
    function fallbackCopyToClipboard(text, $element) {
        const $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(text).select();
        
        try {
            document.execCommand('copy');
            showCopyTooltip($element);
        } catch(err) {
            console.error('Failed to copy license key:', err);
            alert('License key: ' + text);
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
    
    // Download button click handlers
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
    
    // Handle responsive navigation
    if ($(window).width() <= 768) {
        $('.eddcdp-nav-tab').on('click', function() {
            // Collapse nav on mobile after selection
            setTimeout(function() {
                $('.eddcdp-dashboard-nav').animate({
                    height: '80px'
                }, 300);
            }, 100);
        });
    }
    
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 20
            }, 500);
        }
    });
    
    // Auto-hide success messages
    $('.notice-success').delay(5000).fadeOut(500);
    
    // Initialize tooltips for license keys
    $('.eddcdp-license-key').attr('title', 'Click to copy license key');
    
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
    });
    
    // Lazy load images in wishlist
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
    
    // Initialize the dashboard
    initializeDashboard();
    
    function initializeDashboard() {
        // Set the first enabled section as active if none is active
        if ($('.eddcdp-content-section.active').length === 0) {
            const $firstTab = $('.eddcdp-nav-tab').first();
            if ($firstTab.length) {
                $firstTab.addClass('active');
                const firstSection = $firstTab.data('section');
                $('#eddcdp-' + firstSection).addClass('active');
            }
        }
        
        // Add loading class to body to prevent FOUC
        $('body').removeClass('eddcdp-loading');
        
        // Trigger custom event for other scripts
        $(document).trigger('eddcdp:dashboard:ready');
    }
});