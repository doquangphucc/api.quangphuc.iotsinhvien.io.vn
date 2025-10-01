// Common shopping cart functions for all product pages
// File: assets/js/shopping-cart.js

function showNotification(message, isError = false) {
    const notification = document.createElement('div');
    const bgColor = isError 
        ? 'linear-gradient(135deg, #ef4444, #dc2626)' 
        : 'linear-gradient(135deg, #3FA34D, #1E5631)';
    const shadowColor = isError 
        ? 'rgba(239, 68, 68, 0.3)' 
        : 'rgba(63, 163, 77, 0.35)';

    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${bgColor};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 8px 25px ${shadowColor};
        z-index: 10000;
        font-weight: 600;
        max-width: 350px;
        transform: translateX(120%);
        transition: transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    `;

    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <span>${isError ? 'âŒ' : 'âœ…'}</span>
            <div>${message}</div>
        </div>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);

    setTimeout(() => {
        notification.style.transform = 'translateX(120%)';
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 400);
    }, 4000);
}


function showLoginRequiredNotification() {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
        z-index: 10000;
        font-weight: 600;
        max-width: 350px;
        transform: translateX(120%);
        transition: transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        cursor: pointer;
    `;

    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span>ðŸ”’</span>
            <div>
                <div>YÃªu cáº§u Ä‘Äƒng nháº­p</div>
                <div style="font-size: 0.9rem; opacity: 0.9; margin-top: 0.25rem;">Vui lÃ²ng Ä‘Äƒng nháº­p Ä‘á»ƒ sá»­ dá»¥ng giá» hÃ ng.</div>
                <div style="font-size: 0.8rem; opacity: 0.7; margin-top: 0.25rem;">Nháº¥n Ä‘á»ƒ Ä‘áº¿n trang Ä‘Äƒng nháº­p</div>
            </div>
        </div>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);

    notification.addEventListener('click', () => {
        window.location.href = 'login.html';
    });
}

// --- NEW DATABASE-DRIVEN CART FUNCTIONS ---

async function addToCart(productId, productName) {
    const user = window.authUtils.getUser();
    if (!user || !user.id) {
        showLoginRequiredNotification();
        return;
    }

    try {
        const response = await fetch('api/add_to_cart.php', {
            credentials: 'include',
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                product_id: productId,
                quantity: 1
            })
        });

        if (response.status === 401) {
            if (window.authUtils) {
                authUtils.clearUser();
            }
            showLoginRequiredNotification();
            return;
        }

        const result = await response.json();

        if (result.success) {
            showNotification(`ÄÃ£ thÃªm "${productName}" vÃ o giá» hÃ ng.`);
            updateAllCartCounters(result.data.total_items);
        } else {
            throw new Error(result.message || 'CÃ³ lá»—i xáº£y ra.');
        }
    } catch (error) {
        console.error('Add to cart error:', error);
        showNotification(error.message || 'KhÃ´ng thá»ƒ thÃªm vÃ o giá» hÃ ng.', true);
    }
}

function updateAllCartCounters(totalItems) {
    const fabBadge = document.getElementById('fab-cart-count');

    if (!fabBadge) {
        return;
    }

    const safeTotal = typeof totalItems === 'number' ? totalItems : 0;
    fabBadge.textContent = safeTotal;

    if (safeTotal > 0) {
        fabBadge.style.display = 'flex';
    } else {
        fabBadge.style.display = 'none';
    }
}

async function fetchCartCount() {
    try {
        const response = await fetch('api/get_cart.php', { credentials: 'include' });

        if (response.status === 401) {
            updateAllCartCounters(0);
            if (window.authUtils) {
                authUtils.clearUser();
            }
            return;
        }

        const result = await response.json();
        if (result.success && result.data.cart) {
            const totalItems = result.data.cart.reduce((sum, item) => sum + item.quantity, 0);
            updateAllCartCounters(totalItems);
        } else {
            updateAllCartCounters(0);
        }
    } catch (error) {
        console.error('Error fetching cart count:', error);
        updateAllCartCounters(0);
    }
}


// --- PRODUCT PAGE INITIALIZATION ---

function initializeProductActionButtons() {
    const actionCells = document.querySelectorAll('.product-actions');
    if (!actionCells.length) return;

    actionCells.forEach(cell => {
        const addButton = cell.querySelector('.btn.primary');
        const buyButton = cell.querySelector('.btn.secondary');
        
        const row = cell.closest('tr');
        if (!row) return;

        const productId = addButton.dataset.productId;
        const nameEl = row.querySelector('.product-specs h4');
        const productName = nameEl ? nameEl.textContent.trim() : 'Sáº£n pháº©m';

        if (addButton && productId && !addButton.dataset.cartBound) {
            addButton.dataset.cartBound = 'true';
            addButton.addEventListener('click', (event) => {
                event.preventDefault();
                addToCart(productId, productName);
            });
        }

        // Note: The "Buy Now" functionality is not yet defined for database integration.
        // It currently uses localStorage and redirects. This can be updated later.
        if (buyButton && !buyButton.dataset.buyBound) {
             buyButton.dataset.buyBound = 'true';
             buyButton.addEventListener('click', (event) => {
                event.preventDefault();
                const user = window.authUtils.getUser();
                if (!user || !user.id) {
                    showLoginRequiredNotification();
                } else {
                    // Placeholder for future Buy Now logic with database
                    alert('Chá»©c nÄƒng "Mua Ngay" sáº½ Ä‘Æ°á»£c phÃ¡t triá»ƒn sau.');
                }
             });
        }
    });
}

// --- SCRIPT EXECUTION ---

document.addEventListener('DOMContentLoaded', () => {
    initializeProductActionButtons();
    fetchCartCount(); // Fetch initial cart count when page loads
});

