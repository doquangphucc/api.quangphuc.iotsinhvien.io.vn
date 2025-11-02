// Checkout Page - Load order items from cart or sessionStorage
// Uses the new database schema

let orderItems = [];

// Export to global scope so inline scripts can access it
window.orderItems = orderItems;

// Initialize checkout page
document.addEventListener('DOMContentLoaded', async function() {
    await loadOrderItems();
    loadCities();
});

// Load order items (from cart or survey package)
async function loadOrderItems() {
    // Check if coming from survey page first
    const surveyPackage = localStorage.getItem('surveyPackage');
    if (surveyPackage) {
        try {
            const packageData = JSON.parse(surveyPackage);
            loadSurveyPackage(packageData);
            return;
        } catch (e) {
            console.error('Error loading survey package:', e);
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
        window.orderItems = orderItems; // Update global reference
        showEmptyOrder();
    }
}

// Load survey package from localStorage
function loadSurveyPackage(packageData) {
    try {
        console.log('Loading survey package:', packageData);
        
        // Combine main items and accessories
        const allItems = [
            ...packageData.items,
            ...packageData.accessories
        ];
        
        // Convert to orderItems format
        orderItems = allItems.map(item => {
            // Debug: log image_url for each item
            console.log(`Item: ${item.title}, image_url:`, item.image_url);
            
            // Process image_url - if null, empty, or undefined, use fallback
            let imageUrl = item.image_url;
            if (!imageUrl || imageUrl === 'null' || imageUrl === 'undefined') {
                imageUrl = '../assets/img/logo.jpg';
            }
            
            return {
                id: item.id,
                title: item.title,
                price: parseFloat(item.price),
                image_url: imageUrl,
                quantity: item.quantity,
                isVirtual: item.isVirtual || false
            };
        });
        
        console.log('Processed orderItems:', orderItems);
        
        window.orderItems = orderItems;
        renderOrderItems();
        
        // Display survey banner with summary info
        if (packageData.summary) {
            displaySurveyBanner({
                note: packageData.note,
                inverter: packageData.summary.inverter,
                cabinet: packageData.summary.cabinet,
                totalEstimate: packageData.summary.totalEstimate
            });
        }
        
        // Add note to notes field
        const notesField = document.getElementById('notes');
        if (notesField && packageData.note) {
            notesField.value = packageData.note;
        }
        
        showToast('✅ Đã tải gói khảo sát điện mặt trời', 'success');
        
        // Clear localStorage after loading
        localStorage.removeItem('surveyPackage');
        
    } catch (error) {
        console.error('Error loading survey package:', error);
        orderItems = [];
        window.orderItems = orderItems;
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
            
            window.orderItems = orderItems; // Update global reference
            renderOrderItems();
        } else {
            orderItems = [];
            window.orderItems = orderItems; // Update global reference
            showEmptyOrder();
        }
    } catch (error) {
        console.error('Error loading cart items:', error);
        orderItems = [];
        window.orderItems = orderItems; // Update global reference
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
    
    container.innerHTML = orderItems.map(item => {
        // Check if image_url is an emoji or a path
        const isEmoji = item.image_url && item.image_url.length <= 3 && !item.image_url.includes('/');
        
        let imageHtml;
        if (isEmoji) {
            imageHtml = `<div class="w-full h-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg text-3xl">${item.image_url}</div>`;
        } else if (item.image_url) {
            // Handle different path formats
            let imgSrc;
            if (item.image_url.startsWith('http://') || item.image_url.startsWith('https://')) {
                imgSrc = item.image_url;
            } else if (item.image_url.startsWith('/')) {
                // Absolute path from domain root - convert to relative
                imgSrc = '..' + item.image_url;
            } else if (item.image_url.startsWith('../')) {
                imgSrc = item.image_url;
            } else {
                imgSrc = '../' + item.image_url;
            }
            imageHtml = `<img 
                src="${imgSrc}" 
                alt="${item.title}"
                class="w-full h-full object-cover rounded-lg"
                onerror="this.src='../assets/img/logo.jpg'"
            >`;
        } else {
            imageHtml = `<img 
                src="../assets/img/logo.jpg" 
                alt="${item.title}"
                class="w-full h-full object-cover rounded-lg"
            >`;
        }
        
        // Check if this is a transport/shipping item (price = 0 and title contains vận chuyển)
        const isTransport = item.price === 0 && item.title.toLowerCase().includes('vận chuyển');
        
        const priceHtml = isTransport 
            ? `<span class="text-orange-600 dark:text-orange-400 text-xs italic">Tính theo khoảng cách</span>`
            : `<span class="font-bold text-green-600">${formatPrice(item.price * item.quantity)}</span>`;
        
        return `
            <div class="flex gap-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                <div class="w-16 h-16 flex-shrink-0">
                    ${imageHtml}
                </div>
                <div class="flex-grow">
                    <h4 class="font-semibold text-gray-800 dark:text-white text-sm mb-1">
                        ${item.title}
                    </h4>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400 text-sm">
                            SL: ${item.quantity}
                        </span>
                        ${priceHtml}
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
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

// Load cities (provinces) from Vietnam Open API via proxy
async function loadCities() {
    try {
        // Sử dụng proxy API để tránh lỗi SSL
        const response = await fetch('../api/proxy_provinces.php?endpoint=api/p/');
        if (!response.ok) {
            throw new Error('Lỗi khi tải danh sách tỉnh/thành: ' + response.status);
        }
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
        // Sử dụng proxy API để tránh lỗi SSL
        const response = await fetch(`../api/proxy_provinces.php?endpoint=api/p/${provinceCode}&depth=2`);
        if (!response.ok) {
            throw new Error('Lỗi khi tải danh sách quận/huyện: ' + response.status);
        }
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
        // Sử dụng proxy API để tránh lỗi SSL
        const response = await fetch(`../api/proxy_provinces.php?endpoint=api/d/${districtCode}&depth=2`);
        if (!response.ok) {
            throw new Error('Lỗi khi tải danh sách phường/xã: ' + response.status);
        }
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
    // Round to integer and format with dots as thousand separators
    const roundedPrice = Math.round(price);
    return roundedPrice.toLocaleString('vi-VN') + '₫';
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
        success: {
            bg: 'bg-gradient-to-r from-green-500 to-green-600',
            icon: 'bg-green-700/30',
            border: 'border-green-700'
        },
        error: {
            bg: 'bg-gradient-to-r from-red-500 to-red-600',
            icon: 'bg-red-700/30',
            border: 'border-red-700'
        },
        warning: {
            bg: 'bg-gradient-to-r from-amber-500 to-amber-600',
            icon: 'bg-amber-700/30',
            border: 'border-amber-700'
        },
        info: {
            bg: 'bg-gradient-to-r from-blue-500 to-blue-600',
            icon: 'bg-blue-700/30',
            border: 'border-blue-700'
        }
    };
    const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    };

    const colorScheme = colors[type];
    
    toast.className = `${colorScheme.bg} text-white px-6 py-4 rounded-xl shadow-2xl border-2 ${colorScheme.border} flex items-center space-x-4 transform translate-x-full transition-all duration-300 ease-out backdrop-blur-sm`;
    toast.innerHTML = `
        <div class="w-10 h-10 ${colorScheme.icon} rounded-full flex items-center justify-center flex-shrink-0">
            <span class="text-white font-bold text-lg">${icons[type]}</span>
        </div>
        <span class="font-semibold text-base">${message}</span>
    `;

    container.appendChild(toast);

    // Animate in
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
        toast.style.opacity = '1';
    }, 100);

    // Animate out and remove
    setTimeout(() => {
        toast.style.transform = 'translateX(150%)';
        toast.style.opacity = '0';
        toast.addEventListener('transitionend', () => toast.remove());
    }, 3000);
}

// Display survey package banner
function displaySurveyBanner(note) {
    const container = document.querySelector('.lg\\:col-span-2'); // Find the left column
    if (!container) return;
    
    const banner = document.createElement('div');
    banner.className = 'bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-2 border-blue-200 dark:border-blue-800 rounded-2xl p-6 mb-6';
    banner.innerHTML = `
        <div class="flex gap-4">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="font-bold text-blue-900 dark:text-blue-100 text-lg mb-2">
                    📦 Gói Khảo Sát Điện Mặt Trời
                </h3>
                <p class="text-blue-800 dark:text-blue-200 text-sm mb-3">
                    ${note.note}
                </p>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="bg-white/50 dark:bg-gray-800/50 p-3 rounded-lg">
                        <div class="text-blue-600 dark:text-blue-400 font-semibold mb-1">Inverter</div>
                        <div class="text-gray-700 dark:text-gray-300">${note.inverter}</div>
                    </div>
                    <div class="bg-white/50 dark:bg-gray-800/50 p-3 rounded-lg">
                        <div class="text-blue-600 dark:text-blue-400 font-semibold mb-1">Tủ điện</div>
                        <div class="text-gray-700 dark:text-gray-300">${note.cabinet}</div>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-blue-200 dark:border-blue-700">
                    <div class="flex justify-between items-center">
                        <span class="text-blue-700 dark:text-blue-300 font-semibold">Tổng ước tính:</span>
                        <span class="text-blue-900 dark:text-blue-100 font-bold text-xl">${formatPrice(note.totalEstimate)}</span>
                    </div>
                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-2">
                        💡 Giá cuối cùng sẽ được xác nhận sau khi bộ phận kinh doanh liên hệ với bạn
                    </p>
                </div>
            </div>
        </div>
    `;
    
    // Insert banner before the first form section
    container.insertBefore(banner, container.firstChild);
}

