jQuery(document).ready(function($) {

        
    // Auto-hide success messages after 5 seconds
    $('.notice.notice-success, .notice.notice-error').delay(5000).fadeOut(500);
    
    

    
    // Template hover effects
    $('.eddcdp-template-option').hover(
        function() {
            if (!$(this).hasClass('selected')) {
                $(this).css('transform', 'translateY(-2px)');
            }
        },
        function() {
            $(this).css('transform', 'translateY(0)');
        }
    );
});