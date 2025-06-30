

// Regular JavaScript functions for non-Alpine.js interactions

// Deactivate license site
function deactivateSite(licenseId, siteUrl) {
    if (!confirm(eddcdp.strings.confirm_deactivate)) {
        return;
    }
    
    // Show loading state
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = '⏳ ' + (eddcdp.strings.deactivating || 'Deactivating...');
    button.disabled = true;
    
    // AJAX call to deactivate site
    fetch(eddcdp.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'eddcdp_deactivate_license_site',
            license_id: licenseId,
            site_url: siteUrl,
            nonce: eddcdp.nonce
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to show updated state
            location.reload();
        } else {
            alert(data.data || eddcdp.strings.deactivate_error);
            // Restore button
            button.textContent = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(eddcdp.strings.deactivate_error);
        // Restore button
        button.textContent = originalText;
        button.disabled = false;
    });
}

// Add to cart function
function addToCart(downloadId, priceId = null) {
    const params = {
        action: 'edd_add_to_cart',
        download_id: downloadId,
        nonce: eddcdp.nonce
    };
    
    if (priceId !== null) {
        params.edd_options = { price_id: priceId };
    }
    
    fetch(eddcdp.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(params)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showNotification(eddcdp.strings.cart_success || 'Added to cart successfully!', 'success');
            
            // Update cart count if element exists
            const cartCount = document.querySelector('.edd-cart-quantity');
            if (cartCount && data.data && data.data.cart_quantity) {
                cartCount.textContent = data.data.cart_quantity;
            }
        } else {
            showNotification(data.data || 'Error adding to cart. Please try again.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding to cart. Please try again.', 'error');
    });
}

// Remove from wishlist
function removeFromWishlist(downloadId) {
    if (!confirm(eddcdp.strings.confirm_remove_wishlist || 'Are you sure you want to remove this item from your wishlist?')) {
        return;
    }
    
    fetch(eddcdp.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'eddcdp_remove_from_wishlist',
            download_id: downloadId,
            nonce: eddcdp.nonce
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.data || 'Error removing from wishlist. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error removing from wishlist. Please try again.');
    });
}

// Utility function for standalone notifications
function showNotification(message, type = 'info') {
    // This mirrors the Alpine.js function for standalone use
    const notification = document.createElement('div');
    notification.className = `eddcdp-notification eddcdp-notification-${type}`;
    notification.innerHTML = `
        <div class="eddcdp-notification-content">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="eddcdp-notification-close">&times;</button>
        </div>
    `;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        max-width: 400px;
        animation: slideInRight 0.3s ease-out;
    `;
    
    switch (type) {
        case 'success':
            notification.style.backgroundColor = '#10b981';
            notification.style.color = 'white';
            break;
        case 'error':
            notification.style.backgroundColor = '#ef4444';
            notification.style.color = 'white';
            break;
        default:
            notification.style.backgroundColor = '#3b82f6';
            notification.style.color = 'white';
    }
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .eddcdp-notification-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    
    .eddcdp-notification-close {
        background: none;
        border: none;
        color: inherit;
        font-size: 18px;
        cursor: pointer;
        padding: 0;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .eddcdp-notification-close:hover {
        opacity: 0.7;
    }
`;
document.head.appendChild(style);