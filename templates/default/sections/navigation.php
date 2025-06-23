<?php
/**
 * Navigation Tabs Section
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 mb-8 shadow-lg border border-white/20">
    <div class="flex flex-wrap gap-3">
        <template x-for="tab in tabs" :key="tab.id">
            <button 
                @click="activeTab = tab.id"
                :class="activeTab === tab.id ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                class="px-6 py-3 rounded-xl font-medium transition-all duration-300 flex items-center gap-2 hover:-translate-y-0.5"
                x-text="tab.label">
            </button>
        </template>
    </div>
</div>




