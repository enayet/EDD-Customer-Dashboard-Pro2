jQuery(document).ready(function($) {
    // Tab Navigation
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        // Remove active class from all tabs and sections
        $('.nav-tab').removeClass('active');
        $('.content-section').removeClass('active');
        
        // Add active class to clicked tab
        $(this).addClass('active');
        
        // Show corresponding content section
        const targetSection = $(this).data('section');
        $('#' + targetSection).addClass('active');
        
        // Smooth scroll on mobile
        if ($(window).width() <= 768) {
            $('html, body').animate({
                scrollTop: $('.dashboard-content').offset().top - 20
            }, 300);
        }
    });
    
    // Copy license key functionality
    $('.license-key').on('click', function() {
        const $this = $(this);
        const licenseKey = $this.text();
        
        // Create temporary input element
        const $temp = $('<input>');
        $('body').append($temp);
        $temp.val(licenseKey).select();
        
        try {
            // Copy to clipboard
            document.execCommand('copy');
            
            // Visual feedback
            const originalBg = $this.css('background');
            $this.css('background', 'rgba(67, 233, 123, 0.2)');
            
            // Show tooltip
            const $tooltip = $('<span class="copy-tooltip">Copied!</span>');
            $this.append($tooltip);
            
            setTimeout(function() {
                $this.css('background', originalBg);
                $tooltip.remove();
            }, 1500);
            
        } catch(err) {
            console.error('Failed to copy license key');
        }
        
        $temp.remove();
    });
    
    // Download button click handlers
    $('.btn-download').on('click', function(e) {
        const $btn = $(this);
        const originalHtml = $btn.html();
        
        // Show loading state
        $btn.html('⏳ Preparing...').prop('disabled', true);
        
        // Simulate download preparation
        setTimeout(function() {
            $btn.html('✅ Downloaded');
            
            // Reset button after delay
            setTimeout(function() {
                $btn.html(originalHtml).prop('disabled', false);
            }, 2000);
        }, 1500);
    });
    
    // Add hover animations to stat cards
    $('.stat-card').hover(
        function() {
            $(this).css('transform', 'translateY(-5px) scale(1.02)');
        },
        function() {
            $(this).css('transform', 'translateY(0) scale(1)');
        }
    );
    
    // Handle license site management
    $('.site-input-group button').on('click', function(e) {
        e.preventDefault();
        const $input = $(this).siblings('input');
        const siteUrl = $input.val();
        
        if (!siteUrl) {
            alert('Please enter a valid site URL');
            return;
        }
        
        // Here you would make an AJAX call to activate/deactivate the license
        // For now, we'll just show a success message
        alert('License activation feature coming soon!');
        $input.val('');
    });
    
    // Initialize tooltips
    $('.license-key').attr('title', 'Click to copy');
    
    // Handle responsive navigation
    if ($(window).width() <= 768) {
        $('.nav-tab').on('click', function() {
            // Collapse nav on mobile after selection
            setTimeout(function() {
                $('.dashboard-nav').animate({
                    height: '60px',
                    overflow: 'hidden'
                }, 300);
            }, 100);
        });
    }
});

// Add CSS for copy tooltip
const style = document.createElement('style');
style.textContent = `
    .license-key {
        position: relative;
    }
    
    .copy-tooltip {
        position: absolute;
        top: -30px;
        left: 50%;
        transform: translateX(-50%);
        background: #333;
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 0.8rem;
        white-space: nowrap;
        animation: fadeInOut 1.5s ease;
    }
    
    .copy-tooltip:after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-top-color: #333;
    }
    
    @keyframes fadeInOut {
        0% { opacity: 0; transform: translateX(-50%) translateY(-5px); }
        50% { opacity: 1; transform: translateX(-50%) translateY(0); }
        100% { opacity: 0; transform: translateX(-50%) translateY(-5px); }
    }
`;
document.head.appendChild(style);