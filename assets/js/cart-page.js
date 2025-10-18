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
            // Show success notification
            showSuccessNotification('Đã cập nhật số lượng sản phẩm');
            
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
        showErrorNotification('Không thể cập nhật số lượng: ' + error.message);
    }
}

async function removeFromCart(cartItemId) {
    // Show custom confirmation modal instead of browser confirm
    showDeleteConfirmation(cartItemId);
}

function showDeleteConfirmation(cartItemId) {
    // Create modal overlay
    const modalOverlay = document.createElement('div');
    modalOverlay.className = 'fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4';
    modalOverlay.id = 'delete-confirmation-modal';
    
    modalOverlay.innerHTML = `
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-md w-full text-center transform transition-all shadow-2xl">
            <div class="w-20 h-20 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-3">Xác nhận xóa sản phẩm</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-8">Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?</p>
            <div class="flex gap-4">
                <button onclick="closeDeleteConfirmation()" class="flex-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold py-3 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    Hủy bỏ
                </button>
                <button onclick="confirmDelete(${cartItemId})" class="flex-1 bg-gradient-to-r from-red-600 to-red-700 text-white font-semibold py-3 rounded-xl hover:from-red-700 hover:to-red-800 transition-all shadow-lg">
                    Xóa sản phẩm
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modalOverlay);
    
    // Add animation
    setTimeout(() => {
        modalOverlay.querySelector('div').style.transform = 'scale(1)';
    }, 10);
}

function closeDeleteConfirmation() {
    const modal = document.getElementById('delete-confirmation-modal');
    if (modal) {
        modal.remove();
    }
}

async function confirmDelete(cartItemId) {
    closeDeleteConfirmation();
    
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
            // Show success notification
            showSuccessNotification('Đã xóa sản phẩm khỏi giỏ hàng');
            
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
        showErrorNotification('Không thể xóa sản phẩm: ' + error.message);
    }
}

function showSuccessNotification(message) {
    showNotification(message, false);
}

function showErrorNotification(message) {
    showNotification(message, true);
}

function showNotification(message, isError = false) {
    // Remove existing notification if any
    const existingNotification = document.getElementById('cart-notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    const notification = document.createElement('div');
    notification.id = 'cart-notification';
    notification.className = `fixed top-24 right-4 z-50 max-w-sm w-full transform transition-all duration-300 translate-x-full`;
    
    const bgColor = isError ? 'bg-red-500' : 'bg-green-500';
    const iconColor = isError ? 'text-red-100' : 'text-green-100';
    const icon = isError ? 
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>' :
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';
    
    notification.innerHTML = `
        <div class="${bgColor} text-white px-6 py-4 rounded-xl shadow-lg flex items-center gap-3">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 ${iconColor}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    ${icon}
                </svg>
            </div>
            <div class="flex-1">
                <p class="font-semibold">${message}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="flex-shrink-0 ${iconColor} hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }
    }, 4000);
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

// Checkout page specific functions
async function setupAddressDropdowns() {
    const citySelect = document.getElementById('city');
    const districtSelect = document.getElementById('district');
    
    if (!citySelect || !districtSelect) return;
    
    try {
        // Load provinces
        const response = await fetch('../api/get_provinces.php', {
            credentials: 'include'
        });
        
        if (!response.ok) {
            throw new Error('Failed to fetch provinces');
        }
        
        const result = await response.json();
        
        if (result.success && result.data.provinces) {
            // Clear existing options except the first one
            citySelect.innerHTML = '<option value="">Chọn tỉnh/thành</option>';
            
            // Add provinces
            result.data.provinces.forEach(province => {
                const option = document.createElement('option');
                option.value = province.id;
                option.textContent = province.ten_tinh;
                citySelect.appendChild(option);
            });
        }
        
        // Handle city change
        citySelect.addEventListener('change', async function() {
            const provinceId = this.value;
            districtSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
            
            if (!provinceId) {
                districtSelect.innerHTML = '<option value="">Chọn tỉnh/thành trước</option>';
                return;
            }
            
            try {
                // Load districts for selected province
                const districtResponse = await fetch(`../api/get_districts.php?province_id=${provinceId}`, {
                    credentials: 'include'
                });
                
                if (!districtResponse.ok) {
                    throw new Error('Failed to fetch districts');
                }
                
                const districtResult = await districtResponse.json();
                
                if (districtResult.success && districtResult.data.districts) {
                    // Clear existing options except the first one
                    districtSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
                    
                    // Add districts
                    districtResult.data.districts.forEach(district => {
                        const option = document.createElement('option');
                        option.value = district.id;
                        option.textContent = district.ten_phuong;
                        districtSelect.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading districts:', error);
                districtSelect.innerHTML = '<option value="">Lỗi tải danh sách</option>';
            }
        });
        
    } catch (error) {
        console.error('Error loading provinces:', error);
        citySelect.innerHTML = '<option value="">Lỗi tải danh sách</option>';
    }
}

// Initialize checkout page
function initializeCheckoutPage() {
    const checkoutItems = JSON.parse(localStorage.getItem('checkoutItems') || '[]');
    renderCheckoutItems(checkoutItems);
    setupAddressDropdowns();
    setupEventListeners();
}

// Check if we're on checkout page and initialize
if (document.getElementById('checkout-form')) {
    initializeCheckoutPage();
}

async function submitOrder() {
    try {
        // Get form data
        const formData = new FormData(document.getElementById('checkout-form'));
        
        // Get selected city and district names
        const citySelect = document.getElementById('city');
        const districtSelect = document.getElementById('district');
        
        const orderData = {
            fullname: formData.get('fullname'),
            phone: formData.get('phone'),
            email: formData.get('email'),
            city_name: citySelect.options[citySelect.selectedIndex]?.textContent || '',
            district_name: districtSelect.options[districtSelect.selectedIndex]?.textContent || '',
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


