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
            <span>${isError ? '❌' : '✅'}</span>
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
    }, 2000);  // Thay đổi từ 4000ms (4s) thành 2000ms (2s)
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
            <span>🔒</span>
            <div>
                <div>Yêu cầu đăng nhập</div>
                <div style="font-size: 0.9rem; opacity: 0.9; margin-top: 0.25rem;">Vui lòng đăng nhập để sử dụng giỏ hàng.</div>
                <div style="font-size: 0.8rem; opacity: 0.7; margin-top: 0.25rem;">Nhấn để đến trang đăng nhập</div>
            </div>
        </div>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);

    // Tự động ẩn sau 2 giây
    setTimeout(() => {
        notification.style.transform = 'translateX(120%)';
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 400);
    }, 2000);

    notification.addEventListener('click', () => {
        // Determine correct path based on current location
        const isInHtmlFolder = window.location.pathname.includes('/html/');
        const loginPath = isInHtmlFolder ? 'login.html' : 'html/login.html';
        window.location.href = loginPath;
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
        const response = await fetch('../api/add_to_cart.php', {
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
            showNotification(`Đã thêm "${productName}" vào giỏ hàng.`);
            updateAllCartCounters(result.data.total_items);
        } else {
            throw new Error(result.message || 'Có lỗi xảy ra.');
        }
    } catch (error) {
        console.error('Add to cart error:', error);
        showNotification(error.message || 'Không thể thêm vào giỏ hàng.', true);
    }
}

async function buyNow(productId, productName) {
    const user = window.authUtils.getUser();
    if (!user || !user.id) {
        showLoginRequiredNotification();
        return;
    }

    try {
        // First, add to cart to get a cart_item_id
        const addResult = await fetch('../api/add_to_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                product_id: productId,
                quantity: 1
            })
        });

        const addData = await addResult.json();
        
        if (!addResult.ok || !addData.success) {
            throw new Error(addData.message || 'Không thể thêm vào giỏ hàng.');
        }

        // Get updated cart to fetch the newly added item with cart_item_id
        const cartResult = await fetch('../api/get_cart.php', {
            credentials: 'include'
        });

        const cartData = await cartResult.json();
        
        if (!cartResult.ok || !cartData.success || !cartData.data.cart) {
            throw new Error('Không thể tải giỏ hàng.');
        }

        // Find the item we just added
        const cartItem = cartData.data.cart.find(item => String(item.product_id) === String(productId));
        
        if (!cartItem) {
            throw new Error('Không tìm thấy sản phẩm trong giỏ hàng.');
        }

        // Create checkout item with cart_item_id
        const checkoutItem = {
            id: cartItem.id, // This is the cart_item_id
            cart_item_id: cartItem.id,
            product_id: cartItem.product_id,
            name: cartItem.name,
            price: Number(cartItem.price),
            quantity: Number(cartItem.quantity),
            image_url: cartItem.image_url || '',
            specifications: cartItem.specifications || ''
        };

        // Save to localStorage for checkout page
        localStorage.setItem('checkoutItems', JSON.stringify([checkoutItem]));

        // Show notification and redirect
        showNotification(`Chuyển đến trang đặt hàng: "${productName}"`);
        
        setTimeout(() => {
            window.location.href = 'dat-hang.html';
        }, 300);

    } catch (error) {
        console.error('Buy now error:', error);
        showNotification(error.message || 'Không thể mua hàng.', true);
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
    // Check if user is logged in first
    const currentUser = window.authUtils?.getUser();
    if (!currentUser) {
        updateAllCartCounters(0);
        return;
    }
    
    try {
        const response = await fetch('../api/get_cart.php', { credentials: 'include' });

        if (!response.ok) {
            updateAllCartCounters(0);
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
        
        // Try to find the product container (could be tr, div.product-card, etc.)
        const productContainer = cell.closest('tr') || cell.closest('.product-card') || cell.closest('div');
        if (!productContainer) return;

        const productId = addButton.dataset.productId;
        const nameEl = productContainer.querySelector('.product-title') || productContainer.querySelector('.product-specs h4') || productContainer.querySelector('h3');
        const productName = nameEl ? nameEl.textContent.trim() : 'Sản phẩm';

        if (addButton && productId && !addButton.dataset.cartBound) {
            addButton.dataset.cartBound = 'true';
            addButton.addEventListener('click', (event) => {
                event.preventDefault();
                addToCart(productId, productName);
            });
        }

        // "Buy Now" button - Add to cart and redirect to checkout
        if (buyButton && !buyButton.dataset.buyBound) {
             buyButton.dataset.buyBound = 'true';
             buyButton.addEventListener('click', (event) => {
                event.preventDefault();
                buyNow(productId, productName);
             });
        }
    });
}

// Expose updateAllCartCounters globally for use in cart-page.js
window.updateAllCartCounters = updateAllCartCounters;

// --- SCRIPT EXECUTION ---

document.addEventListener('DOMContentLoaded', () => {
    // Clear cart if no user is logged in (prevent data leak between sessions)
    const currentUser = window.authUtils?.getUser();
    if (!currentUser) {
        localStorage.removeItem('cartItems');
        updateAllCartCounters(0); // Reset counter immediately
    }
    
    initializeProductActionButtons();
    fetchCartCount(); // Fetch initial cart count when page loads
});

