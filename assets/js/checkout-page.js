// Checkout Page - Load order items from cart or sessionStorage
// Uses the new database schema

let orderItems = [];

// Initialize checkout page
document.addEventListener('DOMContentLoaded', async function() {
    await loadOrderItems();
    loadCities();
});

// Load order items (from cart or quick order)
async function loadOrderItems() {
    // Check if this is a quick order from pricing page
    const quickOrderProduct = sessionStorage.getItem('quickOrderProduct');
    
    if (quickOrderProduct) {
        // Load single product from sessionStorage
        try {
            const product = JSON.parse(quickOrderProduct);
            orderItems = [product];
            sessionStorage.removeItem('quickOrderProduct'); // Clean up
            renderOrderItems();
            return;
        } catch (e) {
            console.error('Error parsing quick order:', e);
        }
    }
    
    // Load from cart (database)
    const checkoutCartIds = sessionStorage.getItem('checkoutCartIds');
    if (checkoutCartIds) {
        try {
            const cartIds = JSON.parse(checkoutCartIds);
            await loadCartItems(cartIds);
            sessionStorage.removeItem('checkoutCartIds'); // Clean up
            return;
        } catch (e) {
            console.error('Error loading cart items:', e);
        }
    }
    
    // If no items, show empty state
    if (orderItems.length === 0) {
        showEmptyOrder();
    }
}

// Load cart items from database
async function loadCartItems(cartIds) {
    try {
        const response = await fetch('../api/get_cart.php', {
            credentials: 'include'
        });
        
        const result = await response.json();
        
        if (result.success && result.data.cart) {
            // Filter only selected cart items
            const allCartItems = result.data.cart;
            orderItems = allCartItems
                .filter(item => cartIds.includes(item.id))
                .map(item => ({
                    id: item.product_id,
                    title: item.name,
                    price: parseFloat(item.price),
                    image_url: item.image_url,
                    quantity: item.quantity,
                    cart_id: item.id
                }));
            
            renderOrderItems();
        } else {
            showEmptyOrder();
        }
    } catch (error) {
        console.error('Error loading cart items:', error);
        showEmptyOrder();
    }
}

// Render order items in summary
function renderOrderItems() {
    const container = document.getElementById('order-items');
    if (!container) return;
    
    if (orderItems.length === 0) {
        showEmptyOrder();
        return;
    }
    
    container.innerHTML = orderItems.map(item => `
        <div class="flex gap-4 pb-4 border-b border-gray-200 dark:border-gray-700">
            <div class="w-16 h-16 flex-shrink-0">
                <img 
                    src="../${item.image_url}" 
                    alt="${item.title}"
                    class="w-full h-full object-cover rounded-lg"
                    onerror="this.src='../assets/img/logo.jpg'"
                >
            </div>
            <div class="flex-grow">
                <h4 class="font-semibold text-gray-800 dark:text-white text-sm mb-1">
                    ${item.title}
                </h4>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400 text-sm">
                        SL: ${item.quantity}
                    </span>
                    <span class="font-bold text-green-600">
                        ${formatPrice(item.price * item.quantity)}
                    </span>
                </div>
            </div>
        </div>
    `).join('');
    
    updateOrderSummary();
}

// Update order summary (subtotal, total)
function updateOrderSummary() {
    const subtotal = orderItems.reduce((sum, item) => {
        return sum + (item.price * item.quantity);
    }, 0);
    
    const subtotalEl = document.getElementById('subtotal');
    const totalEl = document.getElementById('total');
    
    if (subtotalEl) subtotalEl.textContent = formatPrice(subtotal);
    if (totalEl) totalEl.textContent = formatPrice(subtotal);
}

// Show empty order state
function showEmptyOrder() {
    const container = document.getElementById('order-items');
    if (!container) return;
    
    container.innerHTML = `
        <div class="text-center py-8">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Chưa có sản phẩm nào</p>
            <a href="gio-hang.html" class="inline-block bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                Thêm sản phẩm
            </a>
        </div>
    `;
    
    // Disable checkout button
    const form = document.getElementById('checkout-form');
    if (form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;
    }
}

// Load cities (provinces) from Vietnam Open API
async function loadCities() {
    try {
        const response = await fetch('https://provinces.open-api.vn/api/p/');
        const provinces = await response.json();
        
        const citySelect = document.getElementById('city');
        if (!citySelect) return;
        
        citySelect.innerHTML = '<option value="">Chọn tỉnh/thành</option>';
        provinces.forEach(province => {
            citySelect.innerHTML += `<option value="${province.code}">${province.name}</option>`;
        });
        
        // When city changes, load districts
        citySelect.addEventListener('change', async function() {
            const districtSelect = document.getElementById('district');
            const wardSelect = document.getElementById('ward');
            
            if (this.value) {
                // Load districts for selected province
                await loadDistricts(this.value);
                districtSelect.disabled = false;
                
                // Reset ward select
                wardSelect.disabled = true;
                wardSelect.innerHTML = '<option value="">Chọn quận/huyện trước</option>';
            } else {
                districtSelect.disabled = true;
                districtSelect.innerHTML = '<option value="">Chọn tỉnh/thành trước</option>';
                wardSelect.disabled = true;
                wardSelect.innerHTML = '<option value="">Chọn quận/huyện trước</option>';
            }
        });
    } catch (error) {
        console.error('Error loading provinces:', error);
        showToast('Không thể tải danh sách tỉnh/thành phố', 'error');
    }
}

// Load districts for selected province
async function loadDistricts(provinceCode) {
    try {
        const response = await fetch(`https://provinces.open-api.vn/api/p/${provinceCode}?depth=2`);
        const province = await response.json();
        
        const districtSelect = document.getElementById('district');
        if (!districtSelect) return;
        
        districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
        province.districts.forEach(district => {
            districtSelect.innerHTML += `<option value="${district.code}">${district.name}</option>`;
        });
        
        // When district changes, load wards
        districtSelect.removeEventListener('change', handleDistrictChange); // Remove old listener
        districtSelect.addEventListener('change', handleDistrictChange);
        
    } catch (error) {
        console.error('Error loading districts:', error);
        showToast('Không thể tải danh sách quận/huyện', 'error');
    }
}

// Handle district change
async function handleDistrictChange(event) {
    const wardSelect = document.getElementById('ward');
    const districtCode = event.target.value;
    
    if (districtCode) {
        await loadWards(districtCode);
        wardSelect.disabled = false;
    } else {
        wardSelect.disabled = true;
        wardSelect.innerHTML = '<option value="">Chọn quận/huyện trước</option>';
    }
}

// Load wards for selected district
async function loadWards(districtCode) {
    try {
        const response = await fetch(`https://provinces.open-api.vn/api/d/${districtCode}?depth=2`);
        const district = await response.json();
        
        const wardSelect = document.getElementById('ward');
        if (!wardSelect) return;
        
        wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
        district.wards.forEach(ward => {
            wardSelect.innerHTML += `<option value="${ward.code}">${ward.name}</option>`;
        });
        
    } catch (error) {
        console.error('Error loading wards:', error);
        showToast('Không thể tải danh sách phường/xã', 'error');
    }
}

// Format price helper
function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price);
}

// Toast notification function
function showToast(message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed top-4 right-4 z-50 space-y-3';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    };
    const icons = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };

    toast.className = `${colors[type]} text-white px-6 py-3 rounded-lg shadow-xl flex items-center space-x-3 transform translate-x-full transition-transform duration-300 ease-out`;
    toast.innerHTML = `
        <span class="text-xl">${icons[type]}</span>
        <span class="font-semibold">${message}</span>
    `;

    container.appendChild(toast);

    // Animate in
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);

    // Animate out and remove
    setTimeout(() => {
        toast.style.transform = 'translateX(120%)';
        toast.addEventListener('transitionend', () => toast.remove());
    }, 3000);
}

