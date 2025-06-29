<?php
/**
 * Support Section Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get support settings
$support_email = get_option('eddcdp_support_email', get_option('admin_email'));
$support_hours = get_option('eddcdp_support_hours', __('Mon-Fri, 9AM-6PM EST', 'edd-customer-dashboard-pro'));
$response_time = get_option('eddcdp_response_time', __('Usually within 24 hours', 'edd-customer-dashboard-pro'));
?>

<h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
    💬 <?php _e('Support Center', 'edd-customer-dashboard-pro'); ?>
</h2>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Quick Actions -->
    <div class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4"><?php _e('Quick Actions', 'edd-customer-dashboard-pro'); ?></h3>
        
        <div class="bg-gray-50/80 rounded-2xl p-6 border border-gray-200/50 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white text-xl">
                    🎫
                </div>
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-800"><?php _e('Create Support Ticket', 'edd-customer-dashboard-pro'); ?></h4>
                    <p class="text-sm text-gray-600"><?php _e('Get help with your purchases or technical issues', 'edd-customer-dashboard-pro'); ?></p>
                </div>
                <button onclick="createSupportTicket()" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-4 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300">
                    <?php _e('Create', 'edd-customer-dashboard-pro'); ?>
                </button>
            </div>
        </div>
        
        <div class="bg-gray-50/80 rounded-2xl p-6 border border-gray-200/50 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center text-white text-xl">
                    📚
                </div>
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-800"><?php _e('Documentation', 'edd-customer-dashboard-pro'); ?></h4>
                    <p class="text-sm text-gray-600"><?php _e('Browse our comprehensive guides and tutorials', 'edd-customer-dashboard-pro'); ?></p>
                </div>
                <button onclick="window.open('#', '_blank')" class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors">
                    <?php _e('Browse', 'edd-customer-dashboard-pro'); ?>
                </button>
            </div>
        </div>
        
        <div class="bg-gray-50/80 rounded-2xl p-6 border border-gray-200/50 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center text-white text-xl">
                    💬
                </div>
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-800"><?php _e('Live Chat', 'edd-customer-dashboard-pro'); ?></h4>
                    <p class="text-sm text-gray-600"><?php _e('Chat with our support team in real-time', 'edd-customer-dashboard-pro'); ?></p>
                </div>
                <button onclick="startLiveChat()" class="bg-gradient-to-r from-purple-500 to-pink-600 text-white px-4 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300">
                    <?php _e('Start Chat', 'edd-customer-dashboard-pro'); ?>
                </button>
            </div>
        </div>

        <!-- Knowledge Base Search -->
        <div class="bg-gray-50/80 rounded-2xl p-6 border border-gray-200/50">
            <h4 class="font-semibold text-gray-800 mb-3"><?php _e('Search Knowledge Base', 'edd-customer-dashboard-pro'); ?></h4>
            <div class="flex gap-3">
                <input 
                    type="text" 
                    placeholder="<?php _e('Search for help articles...', 'edd-customer-dashboard-pro'); ?>"
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    id="knowledge-search">
                <button onclick="searchKnowledgeBase()" class="bg-indigo-500 text-white px-6 py-2 rounded-xl font-medium hover:bg-indigo-600 transition-colors">
                    🔍 <?php _e('Search', 'edd-customer-dashboard-pro'); ?>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Contact Information -->
    <div>
        <h3 class="text-lg font-semibold text-gray-800 mb-4"><?php _e('Contact Information', 'edd-customer-dashboard-pro'); ?></h3>
        <div class="bg-gray-50/80 rounded-2xl p-6 border border-gray-200/50 mb-6">
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                        📧
                    </div>
                    <div>
                        <p class="font-medium text-gray-800"><?php _e('Email Support', 'edd-customer-dashboard-pro'); ?></p>
                        <p class="text-sm text-gray-600"><?php echo $support_email; ?></p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center text-green-600">
                        ⏰
                    </div>
                    <div>
                        <p class="font-medium text-gray-800"><?php _e('Business Hours', 'edd-customer-dashboard-pro'); ?></p>
                        <p class="text-sm text-gray-600"><?php echo $support_hours; ?></p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600">
                        🎯
                    </div>
                    <div>
                        <p class="font-medium text-gray-800"><?php _e('Response Time', 'edd-customer-dashboard-pro'); ?></p>
                        <p class="text-sm text-gray-600"><?php echo $response_time; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support Status -->
        <div class="bg-gray-50/80 rounded-2xl p-6 border border-gray-200/50 mb-6">
            <h4 class="font-semibold text-gray-800 mb-3"><?php _e('Support Status', 'edd-customer-dashboard-pro'); ?></h4>
            <div class="flex items-center gap-3 mb-3">
                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                <span class="text-sm text-gray-700"><?php _e('All systems operational', 'edd-customer-dashboard-pro'); ?></span>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                <span class="text-sm text-gray-700"><?php _e('Average response time: 2.5 hours', 'edd-customer-dashboard-pro'); ?></span>
            </div>
        </div>

        <!-- Recent Tickets (if any) -->
        <div class="bg-gray-50/80 rounded-2xl p-6 border border-gray-200/50">
            <h4 class="font-semibold text-gray-800 mb-3"><?php _e('Recent Support Tickets', 'edd-customer-dashboard-pro'); ?></h4>
            <p class="text-sm text-gray-500 italic"><?php _e('No recent support tickets found.', 'edd-customer-dashboard-pro'); ?></p>
            <button onclick="viewAllTickets()" class="mt-3 text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                <?php _e('View All Tickets →', 'edd-customer-dashboard-pro'); ?>
            </button>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<div class="mt-8 bg-gray-50/80 rounded-2xl p-6 border border-gray-200/50">
    <h3 class="text-lg font-semibold text-gray-800 mb-4"><?php _e('Frequently Asked Questions', 'edd-customer-dashboard-pro'); ?></h3>
    
    <div class="space-y-4">
        <div class="bg-white rounded-xl p-4">
            <h5 class="font-medium text-gray-800 mb-2"><?php _e('How do I download my purchased files?', 'edd-customer-dashboard-pro'); ?></h5>
            <p class="text-sm text-gray-600"><?php _e('You can download your files from the Downloads section or directly from your purchase confirmation email.', 'edd-customer-dashboard-pro'); ?></p>
        </div>
        
        <div class="bg-white rounded-xl p-4">
            <h5 class="font-medium text-gray-800 mb-2"><?php _e('How do I activate my license?', 'edd-customer-dashboard-pro'); ?></h5>
            <p class="text-sm text-gray-600"><?php _e('Go to the Licenses section, enter your site URL, and click Activate. Your license key will be applied automatically.', 'edd-customer-dashboard-pro'); ?></p>
        </div>
        
        <div class="bg-white rounded-xl p-4">
            <h5 class="font-medium text-gray-800 mb-2"><?php _e('What if my download link has expired?', 'edd-customer-dashboard-pro'); ?></h5>
            <p class="text-sm text-gray-600"><?php _e('Contact our support team and we\'ll generate a new download link for you within 24 hours.', 'edd-customer-dashboard-pro'); ?></p>
        </div>
    </div>
</div>

<script>
function createSupportTicket() {
    // This would typically open a modal or redirect to a support form
    alert('<?php _e('Support ticket form would open here. This is a demo.', 'edd-customer-dashboard-pro'); ?>');
}

function startLiveChat() {
    // This would integrate with a live chat service
    alert('<?php _e('Live chat would start here. This is a demo.', 'edd-customer-dashboard-pro'); ?>');
}

function searchKnowledgeBase() {
    const query = document.getElementById('knowledge-search').value;
    if (query.trim()) {
        // This would search the knowledge base
        alert('<?php _e('Searching for: ', 'edd-customer-dashboard-pro'); ?>' + query + '. <?php _e('This is a demo.', 'edd-customer-dashboard-pro'); ?>');
    }
}

function viewAllTickets() {
    // This would show all support tickets
    alert('<?php _e('All tickets view would open here. This is a demo.', 'edd-customer-dashboard-pro'); ?>');
}
</script>