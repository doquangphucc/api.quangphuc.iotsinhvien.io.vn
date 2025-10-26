// Pricing page - Load and display products and packages from database
// Updated for new product structure: title, market_price, category_price, technical_description

let allProducts = [];
let allPackages = [];
let categories = [];
let packageCategories = [];
let currentCategoryFilter = null;
let currentPackageCategoryFilter = null;

// Initialize page
document.addEventListener('DOMContentLoaded', async function() {
    await loadCategories();
    await loadPackageCategories();
    await loadProducts();
    await loadPackages();
});

// Load categories
async function loadCategories() {
    try {
        const response = await fetch('../api/get_categories_public.php');
        const data = await response.json();
        
        if (data.success) {
            categories = data.categories;
            renderCategoryFilters();
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Load package categories
async function loadPackageCategories() {
    try {
        const response = await fetch('../api/get_package_categories_public.php');
        const data = await response.json();
        
        if (data.success) {
            packageCategories = data.categories;
            renderPackageCategoryFilters();
        }
    } catch (error) {
        console.error('Error loading package categories:', error);
    }
}

// Load products
async function loadProducts() {
    try {
        const url = currentCategoryFilter 
            ? `../api/get_products_public.php?category_id=${currentCategoryFilter}`
            : '../api/get_products_public.php';
            
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            allProducts = data.products;
            renderProducts();
        }
    } catch (error) {
        console.error('Error loading products:', error);
    }
}

// Load packages
async function loadPackages() {
    try {
        const response = await fetch('../api/get_packages_public.php');
        const data = await response.json();
        
        if (data.success) {
            allPackages = data.packages;
            renderPackages();
        }
    } catch (error) {
        console.error('Error loading packages:', error);
    }
}

// Render category filters
function renderCategoryFilters() {
    const filterContainer = document.getElementById('category-filters');
    if (!filterContainer) return;
    
    let html = `
        <button onclick="filterByCategory(null)" 
                class="px-6 py-3 rounded-full font-semibold transition-all transform hover:scale-105 ${!currentCategoryFilter ? 'bg-gradient-to-r from-green-600 to-green-700 text-white shadow-lg' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 shadow'}">
            🏠 Tất cả
        </button>
    `;
    
    categories.forEach(cat => {
        const isActive = currentCategoryFilter === cat.id;
        html += `
            <button onclick="filterByCategory(${cat.id})" 
                    class="px-6 py-3 rounded-full font-semibold transition-all transform hover:scale-105 flex items-center gap-2 ${isActive ? 'bg-gradient-to-r from-green-600 to-green-700 text-white shadow-lg' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 shadow'}">
                ${cat.logo_url ? `<img src="../${cat.logo_url}" alt="${cat.name}" class="h-6 w-6 object-contain">` : ''}
                ${cat.name}
            </button>
        `;
    });
    
    filterContainer.innerHTML = html;
}

// Filter by category
function filterByCategory(categoryId) {
    currentCategoryFilter = categoryId;
    renderCategoryFilters();
    loadProducts();
}

// Render package category filters
function renderPackageCategoryFilters() {
    const filterContainer = document.getElementById('package-category-filters');
    if (!filterContainer) return;
    
    let html = `
        <button onclick="filterByPackageCategory(null)" 
                class="px-6 py-3 rounded-full font-semibold transition-all transform hover:scale-105 ${!currentPackageCategoryFilter ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-lg' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 shadow'}">
            ⚡ Tất cả
        </button>
    `;
    
    packageCategories.forEach(cat => {
        const isActive = currentPackageCategoryFilter === cat.id;
        html += `
            <button onclick="filterByPackageCategory(${cat.id})" 
                    class="px-6 py-3 rounded-full font-semibold transition-all transform hover:scale-105 flex items-center gap-2 ${isActive ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-lg' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 shadow'}">
                ${cat.logo_url ? `<img src="${cat.logo_url}" alt="${cat.name}" class="h-6 w-6 object-contain" onerror="this.style.display='none'">` : ''}
                ${cat.name}
            </button>
        `;
    });
    
    filterContainer.innerHTML = html;
}

// Filter by package category
function filterByPackageCategory(categoryId) {
    currentPackageCategoryFilter = categoryId;
    renderPackageCategoryFilters();
    renderPackages();
}

// Render products
function renderProducts() {
    const container = document.getElementById('products-container');
    if (!container) return;
    
    if (allProducts.length === 0) {
        container.innerHTML = `
            <div class="col-span-full text-center py-16">
                <div class="text-6xl mb-4">📦</div>
                <p class="text-xl text-gray-500 dark:text-gray-400">Chưa có sản phẩm nào trong danh mục này</p>
                <button onclick="filterByCategory(null)" class="mt-6 px-6 py-3 bg-green-600 text-white rounded-full hover:bg-green-700 transition-all">
                    Xem tất cả sản phẩm
                </button>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    allProducts.forEach(product => {
        const category = categories.find(c => c.id === product.category_id);
        
        html += `
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:scale-[1.02] border-2 border-transparent hover:border-green-500">
                <!-- Category Logo Bar -->
                ${category && category.logo_url ? `
                    <div class="bg-gradient-to-r from-green-50 via-blue-50 to-purple-50 dark:from-green-900/20 dark:via-blue-900/20 dark:to-purple-900/20 p-4 flex items-center justify-center gap-3 border-b-2 border-green-600">
                        <img src="../${category.logo_url}" alt="${product.category_name}" class="h-10 w-10 object-contain" onerror="this.style.display='none'">
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">${product.category_name}</span>
                    </div>
                ` : ''}
                
                <!-- Product Image -->
                <div class="relative h-64 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
                    ${product.image_url ? `
                        <img src="../${product.image_url}" 
                             alt="${product.title}" 
                             class="w-full h-full object-contain p-6 hover:scale-110 transition-transform duration-300"
                             onerror="this.src='../assets/img/logo.jpg'">
                    ` : `
                        <div class="w-full h-full flex items-center justify-center text-6xl">
                            📦
                        </div>
                    `}
                    ${!product.is_active ? `
                        <div class="absolute top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-full text-sm font-bold shadow-lg">
                            Tạm hết hàng
                        </div>
                    ` : ''}
                </div>
                
                <div class="p-6">
                    <!-- Product Title -->
                    <h3 class="text-2xl font-extrabold bg-gradient-to-r from-green-700 via-blue-600 to-purple-600 bg-clip-text text-transparent mb-4 line-clamp-2 min-h-[64px] leading-tight">
                        ${product.title}
                    </h3>
                    
                    <!-- Technical Description -->
                    ${product.technical_description ? `
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900/50 dark:to-gray-800/50 rounded-xl p-4 mb-4 max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-700">
                            <p class="text-xs text-gray-600 dark:text-gray-400 whitespace-pre-line leading-relaxed">
                                ${product.technical_description}
                            </p>
                        </div>
                    ` : ''}
                    
                    <!-- Pricing -->
                    <div class="border-t-2 border-dashed border-gray-200 dark:border-gray-700 pt-4 mb-4 space-y-3">
                        <!-- Market Price -->
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">💰 Giá thị trường:</span>
                            <span class="text-2xl font-bold bg-gradient-to-r from-green-600 to-blue-600 bg-clip-text text-transparent">
                                ${formatPrice(product.market_price)}
                            </span>
                        </div>
                        
                        <!-- Category Price -->
                        ${product.category_price ? `
                            <div class="flex justify-between items-center bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 -mx-2">
                                <span class="text-sm font-semibold text-blue-700 dark:text-blue-400">
                                    ⭐ Giá ${product.category_name}:
                                </span>
                                <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    ${formatPrice(product.category_price)}
                                </span>
                            </div>
                        ` : ''}
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <!-- Add to Cart Button -->
                        <button onclick="addToCart(${product.id})" 
                                class="w-full bg-gradient-to-r from-green-600 to-green-700 text-white font-bold py-3 rounded-xl hover:from-green-700 hover:to-green-800 transition-all shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center justify-center gap-2"
                                ${!product.is_active ? 'disabled class="opacity-50 cursor-not-allowed"' : ''}>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Thêm vào giỏ hàng
                        </button>
                        
                        <!-- Order Now Button -->
                        <button onclick="orderNow(${product.id})" 
                                class="w-full bg-gradient-to-r from-orange-500 to-red-500 text-white font-bold py-3 rounded-xl hover:from-orange-600 hover:to-red-600 transition-all shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center justify-center gap-2"
                                ${!product.is_active ? 'disabled class="opacity-50 cursor-not-allowed"' : ''}>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Đặt hàng ngay
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Render packages
function renderPackages() {
    const container = document.getElementById('packages-container');
    if (!container) return;
    
    // Filter packages by category if filter is active
    let filteredPackages = allPackages;
    if (currentPackageCategoryFilter !== null) {
        filteredPackages = allPackages.filter(pkg => pkg.category_id === currentPackageCategoryFilter);
    }
    
    if (filteredPackages.length === 0) {
        container.innerHTML = '<div class="text-center col-span-full py-12"><p class="text-gray-500 dark:text-gray-400">Không có gói dịch vụ nào.</p></div>';
        return;
    }
    
    let html = '<div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">';
    
    filteredPackages.forEach(pkg => {
        const badgeColor = pkg.badge_color || 'blue';
        
        html += `
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                ${pkg.badge_text ? `
                    <div class="bg-gradient-to-r from-${badgeColor}-600 to-${badgeColor}-700 text-white text-center py-3 font-bold text-sm uppercase tracking-wider">
                        ${pkg.badge_text}
                    </div>
                ` : ''}
                
                <!-- Category Badge -->
                ${pkg.category_name ? `
                    <div class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 px-4 py-2 border-b border-gray-200 dark:border-gray-700 flex items-center justify-center gap-2">
                        ${pkg.category_logo_url ? `
                            <img src="../${pkg.category_logo_url}" alt="${pkg.category_name}" class="h-5 w-5 object-contain" onerror="this.style.display='none'">
                        ` : ''}
                        <span class="text-xs font-bold text-blue-700 dark:text-blue-400 uppercase tracking-wider">
                            ${pkg.category_name}
                        </span>
                    </div>
                ` : ''}
                
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">${pkg.name}</h3>
                    
                    ${pkg.description ? `
                        <p class="text-gray-600 dark:text-gray-400 mb-6 text-sm">${pkg.description}</p>
                    ` : ''}
                    
                    <div class="mb-6">
                        <div class="text-4xl font-bold bg-gradient-to-r from-green-600 to-blue-600 bg-clip-text text-transparent">
                            ${pkg.price > 0 ? formatPrice(pkg.price) : 'Liên hệ'}
                        </div>
                    </div>
                    
                    ${pkg.highlights && pkg.highlights.length > 0 ? `
                        <div class="bg-gradient-to-br from-emerald-50 via-green-50 to-teal-50 dark:from-emerald-900/20 dark:via-green-900/20 dark:to-teal-900/20 rounded-xl p-4 mb-4 space-y-3 border border-emerald-200 dark:border-emerald-800">
                            <div class="text-xs font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                Điểm Nổi Bật
                            </div>
                            ${pkg.highlights.map((hl, idx) => `
                                <div class="bg-white dark:bg-gray-800/50 rounded-lg p-3 border-l-4 ${idx === 0 ? 'border-l-emerald-500' : idx === 1 ? 'border-l-blue-500' : 'border-l-purple-500'} shadow-sm hover:shadow-md transition-shadow">
                                    <p class="font-bold text-sm ${idx === 0 ? 'text-emerald-700 dark:text-emerald-400' : idx === 1 ? 'text-blue-700 dark:text-blue-400' : 'text-purple-700 dark:text-purple-400'} mb-1">
                                        ${hl.title || ''}
                                    </p>
                                    ${hl.content ? `
                                        <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                                            ${hl.content}
                                        </p>
                                    ` : ''}
                                </div>
                            `).join('')}
                        </div>
                    ` : pkg.savings_per_month || pkg.payback_period ? `
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-4 mb-4 space-y-2 border border-blue-200 dark:border-blue-800">
                            <div class="text-xs font-bold text-blue-700 dark:text-blue-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                Thông Tin Nổi Bật
                            </div>
                            ${pkg.savings_per_month ? `
                                <div class="bg-white dark:bg-gray-800/50 rounded-lg p-2.5 border-l-4 border-l-amber-500 shadow-sm">
                                    <p class="text-xs font-bold text-amber-700 dark:text-amber-400 mb-0.5">💰 Tiết kiệm/tháng</p>
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">${pkg.savings_per_month}</p>
                                </div>
                            ` : ''}
                            ${pkg.payback_period ? `
                                <div class="bg-white dark:bg-gray-800/50 rounded-lg p-2.5 border-l-4 border-l-purple-500 shadow-sm">
                                    <p class="text-xs font-bold text-purple-700 dark:text-purple-400 mb-0.5">⏱️ Hoàn vốn</p>
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">${pkg.payback_period}</p>
                                </div>
                            ` : ''}
                        </div>
                    ` : ''}
                    
                    ${pkg.items && pkg.items.length > 0 ? `
                        <div class="mb-6">
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">📦 Nội dung gói:</p>
                            <ul class="space-y-2">
                                ${pkg.items.map(item => `
                                    <li class="flex items-start gap-2 text-sm">
                                        <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-gray-700 dark:text-gray-300">${item.name || item.item_name || ''}</span>
                                    </li>
                                `).join('')}
                            </ul>
                        </div>
                    ` : ''}
                    
                    <button onclick="contactForPackage('${pkg.name.replace(/'/g, "\\'")}' )" 
                            class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold py-3 rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                        📞 Liên hệ tư vấn
                    </button>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

// Helper functions
function formatPrice(price) {
    if (!price) return 'Liên hệ';
    return new Intl.NumberFormat('vi-VN', { 
        style: 'currency', 
        currency: 'VND' 
    }).format(price);
}

// Add to cart
async function addToCart(productId) {
    const product = allProducts.find(p => p.id === productId);
    if (!product) {
        showToast('❌ Không tìm thấy sản phẩm', 'error');
        return;
    }
    
    // Check if user is logged in
    const user = window.authUtils?.getUser();
    if (!user || !user.id) {
        showToast('⚠️ Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng', 'warning');
        setTimeout(() => {
            window.location.href = 'login.html?redirect=' + encodeURIComponent(window.location.pathname);
        }, 1500);
        return;
    }
    
    // Call API to add to cart
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
        showToast('❌ Có lỗi xảy ra khi thêm vào giỏ hàng', 'error');
    }
}

// Order now - Go directly to order page with this product
function orderNow(productId) {
    const product = allProducts.find(p => p.id === productId);
    if (!product) {
        showToast('❌ Không tìm thấy sản phẩm', 'error');
        return;
    }
    
    // Check if user is logged in
    const user = window.authUtils?.getUser();
    if (!user || !user.id) {
        showToast('⚠️ Vui lòng đăng nhập để đặt hàng', 'warning');
        setTimeout(() => {
            window.location.href = 'login.html?redirect=' + encodeURIComponent(window.location.pathname);
        }, 1500);
        return;
    }
    
    // Store product in sessionStorage for order page
    sessionStorage.setItem('quickOrderProduct', JSON.stringify({
        id: product.id,
        title: product.title,
        price: product.category_price || product.market_price,
        image_url: product.image_url,
        category_name: product.category_name,
        quantity: 1
    }));
    
    // Redirect to order page
    window.location.href = 'dat-hang.html';
}

// Contact for package
function contactForPackage(packageName) {
    window.location.href = `lien-he.html?package=${encodeURIComponent(packageName)}`;
}

// Toast notification
function showToast(message, type = 'info') {
    // Check if toast container exists
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed top-4 right-4 z-50 space-y-3';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    const colors = {
        success: 'bg-green-600',
        error: 'bg-red-600',
        warning: 'bg-yellow-600',
        info: 'bg-blue-600'
    };
    
    toast.className = `${colors[type]} text-white px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full opacity-0 flex items-center gap-3 max-w-md`;
    toast.innerHTML = `
        <div class="text-xl">${type === 'success' ? '✅' : type === 'error' ? '❌' : type === 'warning' ? '⚠️' : 'ℹ️'}</div>
        <div class="font-semibold">${message}</div>
    `;
    
    container.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full', 'opacity-0');
    }, 10);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
