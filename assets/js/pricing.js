// Pricing page - Load and display products and packages from database

let allProducts = [];
let allPackages = [];
let categories = [];
let currentCategoryFilter = null;

// Initialize page
document.addEventListener('DOMContentLoaded', async function() {
    await loadCategories();
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
                class="px-6 py-2 rounded-lg font-semibold transition-all ${!currentCategoryFilter ? 'bg-green-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300'}">
            Tất cả
        </button>
    `;
    
    categories.forEach(cat => {
        const isActive = currentCategoryFilter === cat.id;
        html += `
            <button onclick="filterByCategory(${cat.id})" 
                    class="px-6 py-2 rounded-lg font-semibold transition-all ${isActive ? 'bg-green-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300'}">
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

// Render products
function renderProducts() {
    const container = document.getElementById('products-container');
    if (!container) return;
    
    if (allProducts.length === 0) {
        container.innerHTML = '<div class="col-span-full text-center py-12 text-gray-500">Không có sản phẩm nào</div>';
        return;
    }
    
    let html = '';
    
    allProducts.forEach(product => {
        html += `
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                ${product.category_logo ? `
                    <div class="bg-gradient-to-r from-green-50 to-blue-50 dark:from-green-900/20 dark:to-blue-900/20 p-3">
                        <img src="${product.category_logo}" alt="${product.category_name}" class="h-8 object-contain mx-auto">
                    </div>
                ` : ''}
                
                <div class="p-6">
                    ${product.image_url ? `
                        <img src="${product.image_url}" alt="${product.name}" class="w-full h-48 object-cover rounded-lg mb-4">
                    ` : ''}
                    
                    <div class="mb-2">
                        <span class="text-xs font-semibold px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full">
                            ${product.category_name}
                        </span>
                    </div>
                    
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">${product.name}</h3>
                    
                    ${product.brand ? `<p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Hãng: ${product.brand}</p>` : ''}
                    ${product.model ? `<p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Model: ${product.model}</p>` : ''}
                    ${product.power_rating ? `<p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Công suất: ${product.power_rating}</p>` : ''}
                    
                    ${product.description ? `
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3">${product.description}</p>
                    ` : ''}
                    
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                        <div class="mb-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Giá niêm yết:</span>
                            <p class="text-2xl font-bold text-green-600">${formatPrice(product.price)}</p>
                        </div>
                        ${product.price_installation ? `
                            <div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Giá lắp đặt trọn gói:</span>
                                <p class="text-xl font-bold text-blue-600">${formatPrice(product.price_installation)}</p>
                            </div>
                        ` : ''}
                    </div>
                    
                    <button onclick="addToCart(${product.id})" class="w-full mt-4 bg-gradient-to-r from-green-600 to-green-700 text-white font-semibold py-3 rounded-xl hover:from-green-700 hover:to-green-800 transition-all shadow-lg">
                        Thêm vào giỏ
                    </button>
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
    
    if (allPackages.length === 0) {
        container.innerHTML = '';
        return;
    }
    
    // Group packages by category
    const packagesByCategory = {};
    allPackages.forEach(pkg => {
        if (!packagesByCategory[pkg.category_name]) {
            packagesByCategory[pkg.category_name] = [];
        }
        packagesByCategory[pkg.category_name].push(pkg);
    });
    
    let html = '';
    
    Object.keys(packagesByCategory).forEach(categoryName => {
        const packages = packagesByCategory[categoryName];
        
        html += `
            <div class="mb-12">
                <h2 class="text-3xl font-bold text-center mb-8 text-gray-800 dark:text-white">${categoryName}</h2>
                
                <div class="grid md:grid-cols-3 gap-8">
        `;
        
        packages.forEach(pkg => {
            html += `
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition-all duration-300">
                    ${pkg.badge_text ? `
                        <div class="bg-${pkg.badge_color}-600 text-white text-center py-2 font-semibold">
                            ${pkg.badge_text}
                        </div>
                    ` : ''}
                    
                    <div class="p-8">
                        <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">${pkg.name}</h3>
                        
                        ${pkg.description ? `
                            <p class="text-gray-600 dark:text-gray-400 mb-6">${pkg.description}</p>
                        ` : ''}
                        
                        <div class="mb-6">
                            <span class="text-4xl font-bold text-green-600">${formatPrice(pkg.price)}</span>
                        </div>
                        
                        ${pkg.savings_per_month ? `
                            <p class="text-sm text-blue-600 dark:text-blue-400 mb-2">💰 Tiết kiệm: ${pkg.savings_per_month}</p>
                        ` : ''}
                        ${pkg.payback_period ? `
                            <p class="text-sm text-purple-600 dark:text-purple-400 mb-4">⏱️ Hoàn vốn: ${pkg.payback_period}</p>
                        ` : ''}
                        
                        ${pkg.items.length > 0 ? `
                            <ul class="space-y-2 mb-6">
                                ${pkg.items.map(item => `
                                    <li class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-gray-700 dark:text-gray-300">${item.name}</span>
                                    </li>
                                `).join('')}
                            </ul>
                        ` : ''}
                        
                        <button onclick="contactForPackage('${pkg.name}')" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold py-3 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg">
                            Liên hệ tư vấn
                        </button>
                    </div>
                </div>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Helper functions
function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', { 
        style: 'currency', 
        currency: 'VND' 
    }).format(price);
}

function addToCart(productId) {
    const product = allProducts.find(p => p.id === productId);
    if (!product) return;
    
    if (window.ShoppingCart) {
        window.ShoppingCart.addItem({
            id: product.id,
            name: product.name,
            price: product.price_installation || product.price,
            image_url: product.image_url,
            quantity: 1
        });
        alert('Đã thêm sản phẩm vào giỏ hàng!');
    } else {
        alert('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng');
    }
}

function contactForPackage(packageName) {
    window.location.href = `lien-he.html?package=${encodeURIComponent(packageName)}`;
}

