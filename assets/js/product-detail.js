// Product Detail Page Script
// Load and display product details from API

const PRODUCT_DETAIL_API_URL = (function() {
    const origin = window.location && window.location.origin;
    if (origin && origin !== 'null' && origin !== 'file://') {
        return `${origin.replace(/\/$/, '')}/api/get_product_detail_public.php`;
    }
    return 'https://api.quangphuc.iotsinhvien.io.vn/api/get_product_detail_public.php';
})();

// Format price
function formatPrice(price) {
    if (!price) return 'Liên hệ';
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price);
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load product detail from API
async function loadProductDetail() {
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');
    
    if (!productId || productId <= 0) {
        showError();
        return;
    }
    
    const loadingEl = document.getElementById('product-loading');
    const errorEl = document.getElementById('product-error');
    const contentEl = document.getElementById('product-detail-content');
    
    try {
        const response = await fetch(`${PRODUCT_DETAIL_API_URL}?id=${productId}&t=${Date.now()}`);
        
        if (!response.ok) {
            throw new Error(`Server responded ${response.status}`);
        }
        
        const data = await response.json();
        
        if (!data.success || !data.product) {
            showError();
            return;
        }
        
        // Hide loading, show content
        loadingEl.classList.add('hidden');
        errorEl.classList.add('hidden');
        contentEl.classList.remove('hidden');
        
        // Store product globally for cart functions
        window.currentProduct = data.product;
        
        // Render product detail
        renderProductDetail(data.product);
        
    } catch (error) {
        console.error('Error loading product detail:', error);
        showError();
    }
}

// Show error state
function showError() {
    document.getElementById('product-loading').classList.add('hidden');
    document.getElementById('product-error').classList.remove('hidden');
    document.getElementById('product-detail-content').classList.add('hidden');
}

// Render product detail
function renderProductDetail(product) {
    const contentEl = document.getElementById('product-detail-content');
    
    // Build technical specs section
    let specsHTML = '';
    if (product.panel_power_watt || product.inverter_power_watt || product.battery_capacity_kwh || product.cabinet_power_kw) {
        specsHTML = `
            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 mb-6">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-4">📋 Thông Số Kỹ Thuật</h3>
                <div class="grid md:grid-cols-2 gap-4">
                    ${product.panel_power_watt ? `
                        <div class="flex items-center gap-3">
                            <span class="text-green-600 dark:text-green-400 font-semibold">⚡ Công suất tấm pin:</span>
                            <span class="text-gray-700 dark:text-gray-300">${product.panel_power_watt}W</span>
                        </div>
                    ` : ''}
                    ${product.inverter_power_watt ? `
                        <div class="flex items-center gap-3">
                            <span class="text-green-600 dark:text-green-400 font-semibold">🔌 Công suất inverter:</span>
                            <span class="text-gray-700 dark:text-gray-300">${product.inverter_power_watt}W</span>
                        </div>
                    ` : ''}
                    ${product.battery_capacity_kwh ? `
                        <div class="flex items-center gap-3">
                            <span class="text-green-600 dark:text-green-400 font-semibold">🔋 Dung lượng pin:</span>
                            <span class="text-gray-700 dark:text-gray-300">${product.battery_capacity_kwh}kWh</span>
                        </div>
                    ` : ''}
                    ${product.cabinet_power_kw ? `
                        <div class="flex items-center gap-3">
                            <span class="text-green-600 dark:text-green-400 font-semibold">⚙️ Công suất tủ điện:</span>
                            <span class="text-gray-700 dark:text-gray-300">${product.cabinet_power_kw}kW</span>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }
    
    // Prepare images array (main image + gallery images)
    // Main image comes first, then gallery images
    const allImages = [];
    const mainImageUrl = product.image_url || '';
    
    // Add main image first if exists
    if (mainImageUrl) {
        allImages.push({
            id: 'main',
            image_url: mainImageUrl
        });
    }
    
    // Add gallery images (excluding main image if duplicated)
    if (product.images && product.images.length > 0) {
        product.images.forEach(img => {
            // Normalize URLs for comparison (remove ../ prefix if present)
            const mainUrlClean = mainImageUrl.replace(/^\.\.\//, '');
            const imgUrlClean = img.image_url.replace(/^\.\.\//, '');
            
            // Don't duplicate main image
            if (imgUrlClean !== mainUrlClean) {
                allImages.push(img);
            }
        });
    }
    
    // Gallery thumbnails show all images except we highlight the main one
    const galleryImages = allImages.length > 1 ? allImages : (allImages.length === 1 ? [] : []);
    
    const html = `
        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Product Image -->
            <div class="flex flex-col items-center space-y-4">
                <div class="relative w-full max-w-lg">
                    ${mainImageUrl ? `
                        <div id="main-image-container" class="relative overflow-hidden rounded-2xl shadow-2xl bg-white dark:bg-gray-800 p-4 cursor-zoom-in">
                            <img id="main-product-image" 
                                 src="${escapeHtml(mainImageUrl)}" 
                                 alt="${escapeHtml(product.title)}" 
                                 class="w-full h-auto object-contain transition-transform duration-300"
                                 onerror="this.src='../assets/img/logo.jpg'">
                        </div>
                    ` : `
                        <div class="w-full h-96 bg-gray-100 dark:bg-gray-700 rounded-2xl flex items-center justify-center text-6xl">
                            📦
                        </div>
                    `}
                    ${!product.is_active ? `
                        <div class="absolute top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-full text-sm font-bold shadow-lg">
                            Tạm hết hàng
                        </div>
                    ` : ''}
                </div>
                
                <!-- Gallery Images -->
                ${galleryImages.length > 0 ? `
                    <div class="w-full max-w-lg">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Thêm ảnh sản phẩm:</h3>
                        <div class="flex gap-2 overflow-x-auto pb-2" style="scrollbar-width: thin;">
                            ${galleryImages.map((img, index) => `
                                <div class="gallery-thumbnail flex-shrink-0 relative group cursor-pointer ${img.image_url === mainImageUrl ? 'active' : ''}" 
                                     data-image-url="${escapeHtml(img.image_url)}"
                                     onclick="changeMainImage('${escapeHtml(img.image_url)}')">
                                    <img src="${escapeHtml(img.image_url)}" 
                                         alt="Gallery ${index + 1}" 
                                         class="w-20 h-20 object-cover rounded-lg border-2 border-gray-300 dark:border-gray-600 group-hover:border-green-500 transition-all"
                                         onerror="this.src='../assets/img/logo.jpg'">
                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all rounded-lg"></div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
            </div>
            
            <!-- Product Info -->
            <div class="space-y-6">
                <!-- Category Badge -->
                ${product.category_name ? `
                    <div class="flex items-center gap-2">
                        ${product.category_logo ? `
                            <img src="${escapeHtml(product.category_logo)}" 
                                 alt="${escapeHtml(product.category_name)}" 
                                 class="h-6 w-6 object-contain" 
                                 onerror="this.style.display='none'">
                        ` : ''}
                        <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-full text-sm font-semibold">
                            ${escapeHtml(product.category_name)}
                        </span>
                    </div>
                ` : ''}
                
                <!-- Product Title -->
                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-800 dark:text-white leading-tight">
                    ${escapeHtml(product.title)}
                </h1>
                
                <!-- Pricing -->
                <div class="space-y-3 p-6 bg-gradient-to-br from-green-50 to-blue-50 dark:from-green-900/20 dark:to-blue-900/20 rounded-xl border-2 border-green-200 dark:border-green-800">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-gray-700 dark:text-gray-300">💰 Giá thị trường:</span>
                        <span class="text-2xl font-bold bg-gradient-to-r from-green-600 to-blue-600 bg-clip-text text-transparent">
                            ${formatPrice(product.market_price)}
                        </span>
                    </div>
                    ${product.category_price ? `
                        <div class="flex justify-between items-center pt-3 border-t border-green-200 dark:border-green-800">
                            <span class="text-lg font-semibold text-blue-700 dark:text-blue-400">
                                ⭐ Giá ${escapeHtml(product.category_name)}:
                            </span>
                            <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                ${formatPrice(product.category_price)}
                            </span>
                        </div>
                    ` : ''}
                </div>
                
                <!-- Technical Description -->
                ${product.technical_description ? `
                    <div class="prose dark:prose-invert max-w-none">
                        <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-3">📝 Mô Tả Kỹ Thuật</h3>
                        <div class="text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed bg-gray-50 dark:bg-gray-800 rounded-xl p-6">
                            ${escapeHtml(product.technical_description).replace(/\n/g, '<br>')}
                        </div>
                    </div>
                ` : ''}
                
                <!-- Technical Specs -->
                ${specsHTML}
                
                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 pt-4">
                    <button onclick="addToCart(${product.id})" 
                            class="flex-1 bg-gradient-to-r from-green-600 to-green-700 text-white font-bold py-4 px-6 rounded-xl hover:from-green-700 hover:to-green-800 transition-all shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center justify-center gap-2 text-lg"
                            ${!product.is_active ? 'disabled class="opacity-50 cursor-not-allowed"' : ''}>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>Thêm vào giỏ hàng</span>
                    </button>
                    
                    <button onclick="orderNow(${product.id})" 
                            class="flex-1 bg-gradient-to-r from-orange-500 to-red-500 text-white font-bold py-4 px-6 rounded-xl hover:from-orange-600 hover:to-red-600 transition-all shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center justify-center gap-2 text-lg"
                            ${!product.is_active ? 'disabled class="opacity-50 cursor-not-allowed"' : ''}>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span>Đặt hàng ngay</span>
                    </button>
                </div>
                
                <!-- Back Button -->
                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="pricing.html" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        <span class="font-semibold">Quay về trang Sản phẩm</span>
                    </a>
                </div>
            </div>
        </div>
    `;
    
    contentEl.innerHTML = html;
    
    // Update page title
    document.title = `${escapeHtml(product.title)} - Chi Tiết Sản Phẩm - HC Eco System`;
    
    // Initialize image zoom effect
    initImageZoom();
}

// Add to cart function (global scope for onclick handlers)
window.addToCart = async function(productId) {
    
    // Check if user is logged in
    const user = window.authUtils?.getUser();
    if (!user || !user.id) {
        showToast('⚠️ Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng', 'warning');
        setTimeout(() => {
            window.location.href = 'login.html?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
        }, 1500);
        return;
    }
    
    try {
        const response = await fetch('../api/add_to_cart.php', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                product_id: productId,
                quantity: 1
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('✅ Đã thêm vào giỏ hàng!', 'success');
            
            // Update cart count if the function exists
            if (window.fetchCartCount) {
                window.fetchCartCount();
            }
        } else {
            showToast('❌ ' + (data.message || 'Không thể thêm vào giỏ hàng'), 'error');
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        showToast('❌ Lỗi khi thêm vào giỏ hàng', 'error');
    }
}

// Order now function (global scope for onclick handlers)
window.orderNow = function(productId) {
    const user = window.authUtils?.getUser();
    if (!user || !user.id) {
        showToast('⚠️ Vui lòng đăng nhập để đặt hàng', 'warning');
        setTimeout(() => {
            window.location.href = 'login.html?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
        }, 1500);
        return;
    }
    
    // Redirect to cart with this product
    window.location.href = `gio-hang.html?add=${productId}`;
}

// Simple toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500';
    toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Change main image when clicking on gallery thumbnail
window.changeMainImage = function(imageUrl) {
    const mainImg = document.getElementById('main-product-image');
    if (mainImg) {
        mainImg.src = imageUrl;
        // Update active state in gallery
        const thumbnails = document.querySelectorAll('.gallery-thumbnail');
        thumbnails.forEach(thumb => {
            const dataUrl = thumb.getAttribute('data-image-url');
            if (dataUrl === imageUrl) {
                thumb.classList.add('active');
            } else {
                thumb.classList.remove('active');
            }
        });
        // Reinitialize zoom after image change
        setTimeout(() => {
            initImageZoom();
        }, 100);
    }
}

// Initialize image zoom effect
function initImageZoom() {
    const container = document.getElementById('main-image-container');
    const img = document.getElementById('main-product-image');
    
    if (!container || !img) return;
    
    // Wait for image to load
    img.onload = function() {
        setupZoomEffect(container, img);
    };
    
    // If image already loaded
    if (img.complete) {
        setupZoomEffect(container, img);
    }
}

// Setup zoom effect on hover
function setupZoomEffect(container, img) {
    let isZoomed = false;
    
    container.addEventListener('mouseenter', () => {
        isZoomed = true;
    });
    
    container.addEventListener('mouseleave', () => {
        isZoomed = false;
        img.style.transform = 'scale(1)';
        img.style.transformOrigin = 'center center';
    });
    
    container.addEventListener('mousemove', (e) => {
        if (!isZoomed) return;
        
        const rect = container.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        const xPercent = (x / rect.width) * 100;
        const yPercent = (y / rect.height) * 100;
        
        // Zoom 2x and move origin to cursor position
        img.style.transform = 'scale(2)';
        img.style.transformOrigin = `${xPercent}% ${yPercent}%`;
        img.style.transition = 'transform 0.1s ease-out';
    });
}

// Load product detail when page loads
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadProductDetail);
} else {
    loadProductDetail();
}

