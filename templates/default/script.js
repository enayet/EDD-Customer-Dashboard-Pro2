/**
 * EDD Customer Dashboard Pro - Default Template JavaScript
 * Modern Alpine.js implementation with enhanced functionality
 */

// Initialize Alpine.js dashboard component
function eddcdpDashboard() {
    return {
        // State
        activeTab: 'purchases',
        downloading: null,
        loading: false,
        notifications: [],
        
        // Methods
        init() {
            this.loadFromHash();
            this.setupKeyboardNavigation();
            this.preloadTabs();
        },
        
        // Tab management
        switchTab(tabName) {
            if (this.loading) return;
            
            this.activeTab = tabName;
            this.updateHash(tabName);
            this.trackTabView(tabName);
            
            // Smooth scroll to content on mobile
            if (window.innerWidth <= 768) {
                this.scrollToContent();
            }
        },
        
        loadFromHash() {
            const hash = window.location.hash.replace('#', '');
            const validTabs = ['purchases', 'downloads', 'licenses', 'wishlist', 'analytics', 'support'];
            
            if (validTabs.includes(hash)) {
                this.activeTab = hash;
            }
        },
        
        updateHash(tabName) {
            if (history.replaceState) {
                history.replaceState(null, null, '#' + tabName);
            }
        },
        
        scrollToContent() {
            const content = document.querySelector('.eddcdp-content');
            if (content) {
                content.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },
        
        // Download functionality
        downloadFile(downloadUrl, productName = '') {
            if (!downloadUrl) {
                this.showNotification('Download URL not available', 'error');
                return;
            }
            
            this.downloading = productName || 'file';
            
            // Create hidden link for download
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = '';
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Reset downloading state
            setTimeout(() => {
                this.downloading = null;
                this.showNotification(`${productName || 'File'} download started`, 'success');
            }, 1000);
        },
        
        // Clipboard functionality
        copyToClipboard(text, label = 'Text') {
            if (!navigator.clipboard) {
                this.fallbackCopyToClipboard(text);
                return;
            }
            
            navigator.clipboard.writeText(text).then(() => {
                this.showNotification(`${label} copied to clipboard`, 'success');
            }).catch(() => {
                this.fallbackCopyToClipboard(text);
            });
        },
        
        fallbackCopyToClipboard(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                this.showNotification('Copied to clipboard', 'success');
            } catch (err) {
                this.showNotification('Copy failed', 'error');
            }
            
            document.body.removeChild(textArea);
        },
        
        // Notifications
        showNotification(message, type = 'info', duration = 3000) {
            const notification = {
                id: Date.now(),
                message,
                type,
                visible: true
            };
            
            this.notifications.push(notification);
            
            setTimeout(() => {
                this.hideNotification(notification.id);
            }, duration);
        },
        
        hideNotification(id) {
            const index = this.notifications.findIndex(n => n.id === id);
            if (index > -1) {
                this.notifications[index].visible = false;
                setTimeout(() => {
                    this.notifications.splice(index, 1);
                }, 300);
            }
        },
        
        // License management
        activateLicense(licenseKey, siteUrl) {
            if (!licenseKey || !siteUrl) {
                this.showNotification('License key and site URL are required', 'error');
                return;
            }
            
            this.loading = true;
            
            // AJAX call to activate license
            this.makeAjaxCall('activate_license', {
                license_key: licenseKey,
                site_url: siteUrl
            }).then(response => {
                if (response.success) {
                    this.showNotification('License activated successfully', 'success');
                    this.refreshLicenseData();
                } else {
                    this.showNotification(response.data || 'Activation failed', 'error');
                }
            }).finally(() => {
                this.loading = false;
            });
        },
        
        deactivateLicense(licenseKey, siteUrl) {
            if (!confirm('Are you sure you want to deactivate this license?')) {
                return;
            }
            
            this.loading = true;
            
            this.makeAjaxCall('deactivate_license', {
                license_key: licenseKey,
                site_url: siteUrl
            }).then(response => {
                if (response.success) {
                    this.showNotification('License deactivated successfully', 'success');
                    this.refreshLicenseData();
                } else {
                    this.showNotification(response.data || 'Deactivation failed', 'error');
                }
            }).finally(() => {
                this.loading = false;
            });
        },
        
        // AJAX helper
        makeAjaxCall(action, data = {}) {
            const formData = new FormData();
            formData.append('action', 'eddcdp_' + action);
            formData.append('nonce', this.getNonce());
            
            Object.keys(data).forEach(key => {
                formData.append(key, data[key]);
            });
            
            return fetch(this.getAjaxUrl(), {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            }).then(response => response.json());
        },
        
        getNonce() {
            return window.eddcdp_ajax?.nonce || '';
        },
        
        getAjaxUrl() {
            return window.eddcdp_ajax?.ajax_url || '/wp-admin/admin-ajax.php';
        },
        
        // Data refresh
        refreshLicenseData() {
            // Reload license section data
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        },
        
        // Keyboard navigation
        setupKeyboardNavigation() {
            document.addEventListener('keydown', (e) => {
                if (e.target.matches('input, textarea, select')) return;
                
                const tabs = ['purchases', 'downloads', 'licenses', 'wishlist', 'analytics', 'support'];
                const currentIndex = tabs.indexOf(this.activeTab);
                
                switch (e.key) {
                    case 'ArrowLeft':
                        e.preventDefault();
                        const prevIndex = currentIndex > 0 ? currentIndex - 1 : tabs.length - 1;
                        this.switchTab(tabs[prevIndex]);
                        break;
                    case 'ArrowRight':
                        e.preventDefault();
                        const nextIndex = currentIndex < tabs.length - 1 ? currentIndex + 1 : 0;
                        this.switchTab(tabs[nextIndex]);
                        break;
                    case 'Escape':
                        // Close any open modals or notifications
                        this.notifications = [];
                        break;
                }
            });
        },
        
        // Analytics tracking
        trackTabView(tabName) {
            if (typeof gtag === 'function') {
                gtag('event', 'tab_view', {
                    event_category: 'Dashboard',
                    event_label: tabName
                });
            }
        },
        
        trackDownload(fileName) {
            if (typeof gtag === 'function') {
                gtag('event', 'download', {
                    event_category: 'File',
                    event_label: fileName
                });
            }
        },
        
        // Preload tab content
        preloadTabs() {
            // Preload critical tab content for better UX
            setTimeout(() => {
                const criticalTabs = ['downloads', 'licenses'];
                criticalTabs.forEach(tab => {
                    if (tab !== this.activeTab) {
                        this.preloadTabContent(tab);
                    }
                });
            }, 2000);
        },
        
        preloadTabContent(tabName) {
            // Implementation would depend on how content is loaded
            console.log(`Preloading ${tabName} content`);
        },
        
        // Form validation
        validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },
        
        validateUrl(url) {
            try {
                new URL(url);
                return true;
            } catch {
                return false;
            }
        },
        
        // Utility methods
        formatCurrency(amount, currency = 'USD') {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: currency
            }).format(amount);
        },
        
        formatDate(date, options = {}) {
            const defaultOptions = {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            };
            
            return new Intl.DateTimeFormat('en-US', {
                ...defaultOptions,
                ...options
            }).format(new Date(date));
        },
        
        truncateText(text, length = 50) {
            if (text.length <= length) return text;
            return text.substring(0, length) + '...';
        }
    };
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Alpine.js if not already loaded
    if (typeof Alpine === 'undefined') {
        // Load Alpine.js dynamically
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js';
        script.defer = true;
        document.head.appendChild(script);
    }
    
    // Setup global click handlers
    setupGlobalHandlers();
    
    // Setup performance monitoring
    setupPerformanceMonitoring();
    
    // Setup accessibility enhancements
    setupAccessibility();
});

function setupGlobalHandlers() {
    // Handle external links
    document.addEventListener('click', function(e) {
        if (e.target.matches('a[href^="http"]')) {
            e.target.setAttribute('rel', 'noopener noreferrer');
        }
    });
    
    // Handle form submissions
    document.addEventListener('submit', function(e) {
        if (e.target.matches('.eddcdp-form')) {
            handleFormSubmission(e);
        }
    });
    
    // Handle window resize
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(handleWindowResize, 250);
    });
}

function handleFormSubmission(e) {
    const form = e.target;
    const submitBtn = form.querySelector('[type="submit"]');
    
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing...';
        
        // Re-enable after 5 seconds as failsafe
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = submitBtn.dataset.originalText || 'Submit';
        }, 5000);
    }
}

function handleWindowResize() {
    // Update layout calculations if needed
    const dashboardWrapper = document.querySelector('.eddcdp-dashboard-wrapper');
    if (dashboardWrapper) {
        dashboardWrapper.style.setProperty('--viewport-height', window.innerHeight + 'px');
    }
}

function setupPerformanceMonitoring() {
    // Monitor Core Web Vitals
    if ('web-vital' in window) {
        const vitals = ['FCP', 'LCP', 'FID', 'CLS'];
        vitals.forEach(vital => {
            window.webVitals[`get${vital}`](console.log);
        });
    }
    
    // Monitor long tasks
    if ('PerformanceObserver' in window) {
        try {
            const observer = new PerformanceObserver((list) => {
                for (const entry of list.getEntries()) {
                    if (entry.duration > 50) {
                        console.warn('Long task detected:', entry);
                    }
                }
            });
            observer.observe({ entryTypes: ['longtask'] });
        } catch (e) {
            // PerformanceObserver not supported
        }
    }
}

function setupAccessibility() {
    // Announce dynamic content changes to screen readers
    const announcer = document.createElement('div');
    announcer.setAttribute('aria-live', 'polite');
    announcer.setAttribute('aria-atomic', 'true');
    announcer.style.position = 'absolute';
    announcer.style.left = '-10000px';
    announcer.style.width = '1px';
    announcer.style.height = '1px';
    announcer.style.overflow = 'hidden';
    document.body.appendChild(announcer);
    
    // Make announcements
    window.announceToScreenReader = function(message) {
        announcer.textContent = message;
        setTimeout(() => {
            announcer.textContent = '';
        }, 1000);
    };
    
    // Enhanced focus management
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            document.body.classList.add('keyboard-navigation');
        }
    });
    
    document.addEventListener('mousedown', function() {
        document.body.classList.remove('keyboard-navigation');
    });
    
    // Skip link functionality
    const skipLink = document.createElement('a');
    skipLink.href = '#main-content';
    skipLink.textContent = 'Skip to main content';
    skipLink.className = 'sr-only skip-link';
    skipLink.addEventListener('focus', function() {
        this.classList.remove('sr-only');
    });
    skipLink.addEventListener('blur', function() {
        this.classList.add('sr-only');
    });
    document.body.insertBefore(skipLink, document.body.firstChild);
}

// Export for use in other scripts
window.eddcdpDashboard = eddcdpDashboard;