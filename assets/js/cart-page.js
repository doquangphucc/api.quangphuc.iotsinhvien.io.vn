// Cart Page JavaScript - Load cart items from database
document.addEventListener('DOMContentLoaded', async function() {
    await loadCartItems();
    setupEventListeners();
});

async function loadCartItems() {
    try {
        // Check if user is logged in
        const user = window.authUtils?.getUser();
        if (!user) {
            showEmptyCart();
            return;
        }

        // Fetch cart from database
        const response = await fetch('../api/get_cart.php', {
            credentials: 'include'
        });

        if (!response.ok) {
            throw new Error('Failed to fetch cart');
        }

        const result = await response.json();
        
        if (result.success && result.data.cart && result.data.cart.length > 0) {
            displayCartItems(result.data.cart);
            updateCartSummary(result.data.cart);
        } else {
            showEmptyCart();
        }

    } catch (error) {
        console.error('Error loading cart:', error);
        showEmptyCart();
    }
}

function displayCartItems(cartItems) {
    const cartContainer = document.getElementById('cart-items');
    const orderItemsContainer = document.getElementById('order-items');
    
    if (!cartContainer && !orderItemsContainer) return;

    const container = cartContainer || orderItemsContainer;
    container.innerHTML = '';

    cartItems.forEach(item => {
        const itemElement = createCartItemElement(item);
        container.appendChild(itemElement);
    });

    // Show/hide empty cart state
    const emptyCart = document.getElementById('empty-cart');
    if (emptyCart) {
        emptyCart.classList.add('hidden');
    }
}

function createCartItemElement(item) {
    const div = document.createElement('div');
    div.className = 'bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg';
    div.innerHTML = `
        <div class="flex items-center gap-4">
            <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-xl flex items-center justify-center overflow-hidden">
                <img src="../${item.image_url}" alt="${item.name}" 
                     class="w-full h-full object-cover" 
                     onerror="this.src='../assets/img/logo.jpg'">
            </div>
            <div class="flex-1">
                <h3 class="font-bold text-gray-800 dark:text-white mb-2">${item.name}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">${item.specifications || ''}</p>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button class="quantity-btn w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors" 
                                onclick="updateQuantity(${item.id}, ${item.quantity - 1})">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        </button>
                        <span class="quantity font-semibold text-gray-800 dark:text-white min-w-[2rem] text-center">${item.quantity}</span>
                        <button class="quantity-btn w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors" 
                                onclick="updateQuantity(${item.id}, ${item.quantity + 1})">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-green-600 text-lg">${formatPrice(item.price * item.quantity)}</div>
                        <div class="text-sm text-gray-500">${formatPrice(item.price)}/sản phẩm</div>
                    </div>
                    <button class="remove-btn ml-4 p-2 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" 
                            onclick="removeFromCart(${item.id})" title="Xóa sản phẩm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    `;
    return div;
}

function updateCartSummary(cartItems) {
    const subtotal = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    // Update subtotal
    const subtotalElement = document.getElementById('subtotal');
    if (subtotalElement) {
        subtotalElement.textContent = formatPrice(subtotal);
    }

    // Update total
    const totalElement = document.getElementById('total') || document.getElementById('total-amount');
    if (totalElement) {
        totalElement.textContent = formatPrice(subtotal);
    }

    // Enable/disable checkout button
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.disabled = cartItems.length === 0;
    }
}

function showEmptyCart() {
    const cartContainer = document.getElementById('cart-items');
    const orderItemsContainer = document.getElementById('order-items');
    const emptyCart = document.getElementById('empty-cart');
    
    if (cartContainer) cartContainer.innerHTML = '';
    if (orderItemsContainer) orderItemsContainer.innerHTML = '';
    if (emptyCart) emptyCart.classList.remove('hidden');

    // Update summary to zero
    updateCartSummary([]);
}

async function updateQuantity(cartItemId, newQuantity) {
    if (newQuantity < 0) return;

    try {
        const response = await fetch('../api/update_cart_item.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                cart_item_id: cartItemId,
                quantity: newQuantity
            })
        });

        const result = await response.json();
        
        if (result.success) {
            // Reload cart items
            await loadCartItems();
            // Update cart counter
            if (window.fetchCartCount) {
                await window.fetchCartCount();
            }
        } else {
            throw new Error(result.message || 'Failed to update quantity');
        }
    } catch (error) {
        console.error('Error updating quantity:', error);
        alert('Không thể cập nhật số lượng: ' + error.message);
    }
}

async function removeFromCart(cartItemId) {
    if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
        return;
    }

    try {
        const response = await fetch('../api/update_cart_item.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                cart_item_id: cartItemId,
                quantity: 0 // 0 means delete
            })
        });

        const result = await response.json();
        
        if (result.success) {
            // Reload cart items
            await loadCartItems();
            // Update cart counter
            if (window.fetchCartCount) {
                await window.fetchCartCount();
            }
        } else {
            throw new Error(result.message || 'Failed to remove item');
        }
    } catch (error) {
        console.error('Error removing item:', error);
        alert('Không thể xóa sản phẩm: ' + error.message);
    }
}

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price);
}

function setupEventListeners() {
    // Checkout button
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            window.location.href = 'dat-hang.html';
        });
    }

    // Form submission for checkout
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            await submitOrder();
        });
    }
}

async function submitOrder() {
    try {
        // Get form data
        const formData = new FormData(document.getElementById('checkout-form'));
        const orderData = {
            fullname: formData.get('fullname'),
            phone: formData.get('phone'),
            email: formData.get('email'),
            city_name: formData.get('city'),
            district_name: formData.get('district'),
            address: formData.get('address'),
            notes: formData.get('notes')
        };

        // Get cart items
        const response = await fetch('../api/get_cart.php', {
            credentials: 'include'
        });
        const cartResult = await response.json();
        
        if (!cartResult.success || !cartResult.data.cart || cartResult.data.cart.length === 0) {
            alert('Giỏ hàng trống!');
            return;
        }

        // Submit order
        const orderResponse = await fetch('../api/create_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                customer: orderData,
                cart_item_ids: cartResult.data.cart.map(item => item.id)
            })
        });

        const orderResult = await orderResponse.json();
        
        if (orderResult.success) {
            // Show success modal
            document.getElementById('success-modal').classList.remove('hidden');
        } else {
            throw new Error(orderResult.message || 'Failed to create order');
        }
    } catch (error) {
        console.error('Error submitting order:', error);
        alert('Không thể đặt hàng: ' + error.message);
    }
}

// Load provinces for address form
async function loadProvinces() {
    try {
        const response = await fetch('../api/get_provinces.php');
        const result = await response.json();
        
        if (result.success) {
            const citySelect = document.getElementById('city');
            if (citySelect) {
                citySelect.innerHTML = '<option value="">Chọn tỉnh/thành</option>';
                result.data.forEach(province => {
                    const option = document.createElement('option');
                    option.value = province.id;
                    option.textContent = province.ten_tinh;
                    citySelect.appendChild(option);
                });
            }
        }
    } catch (error) {
        console.error('Error loading provinces:', error);
    }
}

// Load districts when city changes
function setupAddressForm() {
    const citySelect = document.getElementById('city');
    const districtSelect = document.getElementById('district');
    
    if (citySelect && districtSelect) {
        citySelect.addEventListener('change', async function() {
            const cityId = this.value;
            districtSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
            
            if (cityId) {
                try {
                    const response = await fetch(`../api/get_districts.php?city_id=${cityId}`);
                    const result = await response.json();
                    
                    if (result.success) {
                        result.data.forEach(district => {
                            const option = document.createElement('option');
                            option.value = district.id;
                            option.textContent = district.ten_phuong;
                            districtSelect.appendChild(option);
                        });
                    }
                } catch (error) {
                    console.error('Error loading districts:', error);
                }
            }
        });
    }
}

// Initialize address form if on checkout page
if (document.getElementById('checkout-form')) {
    loadProvinces();
    setupAddressForm();
}
