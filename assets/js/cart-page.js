// Cart Page - Load and display cart items from database
// Uses the new database schema (title as name, market_price as price, technical_description as specifications)

let cartData = [];

// Initialize cart page
document.addEventListener('DOMContentLoaded', async function() {
    await loadCart();
});

// Load cart from API
async function loadCart() {
    try {
        const response = await fetch('../api/get_cart.php', {
            credentials: 'include'
        });
        
        const result = await response.json();
        
        if (result.success && result.data.cart) {
            cartData = result.data.cart;
            renderCart();
        } else {
            showEmptyCart();
        }
    } catch (error) {
        console.error('Error loading cart:', error);
        showEmptyCart();
    }
}

// Render cart items
function renderCart() {
    const cartContainer = document.getElementById('cart-items');
    const emptyCart = document.getElementById('empty-cart');
    
    // Check if elements exist
    if (!cartContainer) {
        console.error('Element #cart-items not found');
        return;
    }
    
    if (!cartData || cartData.length === 0) {
        showEmptyCart();
        return;
    }
    
    // Hide empty state
    if (emptyCart) emptyCart.classList.add('hidden');
    cartContainer.classList.remove('hidden');
    
    // Render each cart item
    cartContainer.innerHTML = cartData.map(item => `
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-shadow" data-cart-id="${item.id}">
            <div class="flex gap-6">
                <!-- Product Image -->
                <div class="w-32 h-32 flex-shrink-0">
                    <img 
                        src="../${item.image_url}" 
                        alt="${item.name}"
                        class="w-full h-full object-cover rounded-xl"
                        onerror="this.src='../assets/img/logo.jpg'"
                    >
                </div>
                
                <!-- Product Info -->
                <div class="flex-grow">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">
                        ${item.name}
                    </h3>
                    
                    ${item.specifications ? `
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
                            ${item.specifications.replace(/\n/g, ' • ')}
                        </p>
                    ` : ''}
                    
                    <div class="flex items-center justify-between mt-4">
                        <div class="text-2xl font-bold text-green-600">
                            ${formatPrice(item.price)}
                        </div>
                        
                        <!-- Quantity Controls -->
                        <div class="flex items-center gap-3">
                            <button 
                                onclick="updateQuantity(${item.id}, ${item.quantity - 1})"
                                class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            </button>
                            
                            <span class="w-12 text-center font-bold text-lg text-gray-800 dark:text-white">
                                ${item.quantity}
                            </span>
                            
                            <button 
                                onclick="updateQuantity(${item.id}, ${item.quantity + 1})"
                                class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Remove Button -->
                        <button 
                            onclick="removeItem(${item.id})"
                            class="ml-4 px-4 py-2 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors font-semibold"
                        >
                            Xóa
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
    
    updateSummary();
    
    // Enable checkout button
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.disabled = false;
        checkoutBtn.onclick = proceedToCheckout;
    }
}

// Update quantity
async function updateQuantity(cartId, newQuantity) {
    if (newQuantity < 1) {
        removeItem(cartId);
        return;
    }
    
    try {
        const response = await fetch('../api/update_cart_item.php', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                cart_id: cartId,
                quantity: newQuantity
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Update local data
            const item = cartData.find(i => i.id === cartId);
            if (item) item.quantity = newQuantity;
            
            renderCart();
            
            // Update cart count in header
            if (window.fetchCartCount) {
                window.fetchCartCount();
            }
        } else {
            alert('Không thể cập nhật số lượng: ' + result.message);
        }
    } catch (error) {
        console.error('Error updating quantity:', error);
        alert('Có lỗi xảy ra khi cập nhật số lượng');
    }
}

// Remove item from cart
async function removeItem(cartId) {
    if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
        return;
    }
    
    try {
        const response = await fetch('../api/remove_from_cart.php', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cart_id: cartId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Remove from local data
            cartData = cartData.filter(i => i.id !== cartId);
            
            if (cartData.length === 0) {
                showEmptyCart();
            } else {
                renderCart();
            }
            
            // Update cart count in header
            if (window.fetchCartCount) {
                window.fetchCartCount();
            }
        } else {
            alert('Không thể xóa sản phẩm: ' + result.message);
        }
    } catch (error) {
        console.error('Error removing item:', error);
        alert('Có lỗi xảy ra khi xóa sản phẩm');
    }
}

// Update order summary
function updateSummary() {
    const subtotal = cartData.reduce((sum, item) => {
        return sum + (parseFloat(item.price) * item.quantity);
    }, 0);
    
    const subtotalEl = document.getElementById('subtotal');
    const totalEl = document.getElementById('total-amount');
    
    if (subtotalEl) subtotalEl.textContent = formatPrice(subtotal);
    if (totalEl) totalEl.textContent = formatPrice(subtotal);
}

// Show empty cart state
function showEmptyCart() {
    const cartContainer = document.getElementById('cart-items');
    const emptyCart = document.getElementById('empty-cart');
    const checkoutBtn = document.getElementById('checkout-btn');
    
    if (cartContainer) cartContainer.classList.add('hidden');
    if (emptyCart) emptyCart.classList.remove('hidden');
    if (checkoutBtn) checkoutBtn.disabled = true;
    
    // Update summary to zero
    const subtotalEl = document.getElementById('subtotal');
    const totalEl = document.getElementById('total-amount');
    
    if (subtotalEl) subtotalEl.textContent = '0₫';
    if (totalEl) totalEl.textContent = '0₫';
}

// Proceed to checkout
function proceedToCheckout() {
    if (!cartData || cartData.length === 0) {
        alert('Giỏ hàng trống');
        return;
    }
    
    // Store cart IDs in sessionStorage for order page
    const cartIds = cartData.map(item => item.id);
    sessionStorage.setItem('checkoutCartIds', JSON.stringify(cartIds));
    
    // Redirect to order page
    window.location.href = 'dat-hang.html';
}

// Format price
function formatPrice(price) {
    // Round to integer and format with dots as thousand separators
    const roundedPrice = Math.round(price);
    return roundedPrice.toLocaleString('vi-VN') + '₫';
}

