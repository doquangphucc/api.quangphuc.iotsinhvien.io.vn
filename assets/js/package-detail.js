// Package Detail Page - Load and display package detail

let packageData = null;

// Initialize page
document.addEventListener('DOMContentLoaded', async function() {
    const urlParams = new URLSearchParams(window.location.search);
    const packageId = urlParams.get('id');
    
    if (!packageId) {
        showError('Không tìm thấy ID gói sản phẩm');
        return;
    }
    
    await loadPackageDetail(packageId);
});

// Load package detail
async function loadPackageDetail(packageId) {
    try {
        const response = await fetch(`../api/get_package_detail_public.php?id=${packageId}`);
        const data = await response.json();
        
        if (data.success && data.package) {
            packageData = data.package;
            renderPackageDetail();
        } else {
            showError(data.message || 'Không tìm thấy gói sản phẩm');
        }
    } catch (error) {
        console.error('Error loading package detail:', error);
        showError('Có lỗi xảy ra khi tải chi tiết gói');
    }
}

// Render package detail
function renderPackageDetail() {
    if (!packageData) return;
    
    const container = document.getElementById('package-detail-container');
    if (!container) return;
    
    const badgeStyle = getBadgeStyle(packageData.badge_color || 'blue');
    const categoryBadgeStyle = getBadgeStyle(packageData.category_badge_color);
    
    let html = `
        <div class="max-w-6xl mx-auto">
            <!-- Package Header -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
                <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between mb-4">
                    <div class="flex-1">
                        ${packageData.badge_text ? `
                            <div class="inline-block mb-2">
                                <span class="px-3 py-1 rounded-full text-xs font-bold text-white uppercase" style="${badgeStyle.style}">
                                    ${packageData.badge_text}
                                </span>
                            </div>
                        ` : ''}
                        <h1 class="text-3xl md:text-4xl font-bold text-gray-800 dark:text-white mb-2">
                            ${packageData.name}
                        </h1>
                        ${packageData.category_name ? `
                            <div class="flex items-center gap-2 mt-2">
                                ${packageData.category_logo_url ? `
                                    <img src="../${packageData.category_logo_url}" alt="${packageData.category_name}" class="h-5 w-5 object-contain" onerror="this.style.display='none'">
                                ` : ''}
                                <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">
                                    ${packageData.category_name}
                                </span>
                                ${packageData.category_badge_text ? `
                                    <span class="px-2 py-0.5 rounded text-xs font-bold text-white" style="${categoryBadgeStyle.style || ''}">
                                        ${packageData.category_badge_text}
                                    </span>
                                ` : ''}
                            </div>
                        ` : ''}
                    </div>
                    <div class="text-right">
                        <div class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-green-600 to-blue-600 bg-clip-text text-transparent">
                            ${packageData.price > 0 ? formatPrice(packageData.price) : 'Liên hệ'}
                        </div>
                        ${packageData.calculated_total > 0 && packageData.calculated_total !== packageData.price ? `
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                (Tính từ sản phẩm: ${formatPrice(packageData.calculated_total)})
                            </div>
                        ` : ''}
                    </div>
                </div>
                
                ${packageData.description ? `
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        ${packageData.description}
                    </p>
                ` : ''}
                
                ${packageData.highlights && packageData.highlights.length > 0 ? `
                    <div class="bg-gradient-to-br from-emerald-50 via-green-50 to-teal-50 dark:from-emerald-900/20 dark:via-green-900/20 dark:to-teal-900/20 rounded-lg p-4 mb-4 border border-emerald-200 dark:border-emerald-800">
                        <div class="text-sm font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            Điểm Nổi Bật
                        </div>
                        <div class="grid md:grid-cols-2 gap-3">
                            ${packageData.highlights.map((hl, idx) => `
                                <div class="bg-white dark:bg-gray-800/50 rounded p-3 border-l-4 ${idx === 0 ? 'border-l-emerald-500' : idx === 1 ? 'border-l-blue-500' : 'border-l-purple-500'} shadow-sm">
                                    <p class="font-bold text-sm ${idx === 0 ? 'text-emerald-700 dark:text-emerald-400' : idx === 1 ? 'text-blue-700 dark:text-blue-400' : 'text-purple-700 dark:text-purple-400'} mb-1">
                                        ${hl.title || ''}
                                    </p>
                                    ${hl.content ? `
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            ${hl.content}
                                        </p>
                                    ` : ''}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
                
                <div class="flex flex-wrap gap-3">
                    <a href="lien-he.html?package=${encodeURIComponent(packageData.name)}" 
                       class="bg-gradient-to-r from-green-600 to-green-700 text-white font-bold px-6 py-3 rounded-lg hover:from-green-700 hover:to-green-800 transition-all shadow-lg">
                        📞 Liên hệ tư vấn
                    </a>
                    <a href="pricing.html" 
                       class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-semibold px-6 py-3 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-all">
                        ← Quay lại danh sách
                    </a>
                </div>
            </div>
            
            <!-- Package Items -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6 flex items-center gap-2">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Chi tiết sản phẩm trong gói
                </h2>
                
                ${packageData.items && packageData.items.length > 0 ? `
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b-2 border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">STT</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">Hình ảnh</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">Tên sản phẩm</th>
                                    <th class="text-center py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">Số lượng</th>
                                    <th class="text-right py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">Đơn giá</th>
                                    <th class="text-right py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${packageData.items.map((item, index) => {
                                    const hasProduct = item.product && item.product.id;
                                    const unitPrice = item.unit_price || 0;
                                    const totalPrice = item.total_price || 0;
                                    
                                    return `
                                        <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="py-4 px-4 text-gray-700 dark:text-gray-300">${index + 1}</td>
                                            <td class="py-4 px-4">
                                                ${hasProduct && item.product.image_url ? `
                                                    <img src="../${item.product.image_url}" 
                                                         alt="${item.product.title}" 
                                                         class="w-16 h-16 object-contain rounded-lg border border-gray-200 dark:border-gray-600"
                                                         onerror="this.src='../assets/img/logo.jpg'">
                                                ` : `
                                                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center text-2xl">
                                                        📦
                                                    </div>
                                                `}
                                            </td>
                                            <td class="py-4 px-4">
                                                <div class="font-semibold text-gray-800 dark:text-white">
                                                    ${hasProduct ? `
                                                        <a href="product-detail.html?id=${item.product.id}" class="hover:text-green-600 dark:hover:text-green-400">
                                                            ${item.name}
                                                        </a>
                                                    ` : item.name}
                                                </div>
                                                ${item.description ? `
                                                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                        ${item.description}
                                                    </div>
                                                ` : ''}
                                                ${hasProduct && item.product.category_name ? `
                                                    <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                                        ${item.product.category_name}
                                                    </div>
                                                ` : ''}
                                                ${hasProduct ? `
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                        Loại giá: ${item.price_type === 'category_price' ? 'Giá danh mục' : 'Giá thị trường'}
                                                    </div>
                                                ` : ''}
                                            </td>
                                            <td class="py-4 px-4 text-center text-gray-700 dark:text-gray-300 font-semibold">
                                                ${item.quantity || 1}
                                            </td>
                                            <td class="py-4 px-4 text-right text-gray-700 dark:text-gray-300">
                                                ${hasProduct ? formatPrice(unitPrice) : '-'}
                                            </td>
                                            <td class="py-4 px-4 text-right font-bold text-green-600 dark:text-green-400">
                                                ${hasProduct ? formatPrice(totalPrice) : '-'}
                                            </td>
                                        </tr>
                                    `;
                                }).join('')}
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-gray-300 dark:border-gray-600">
                                    <td colspan="5" class="py-4 px-4 text-right font-bold text-lg text-gray-800 dark:text-white">
                                        Tổng cộng:
                                    </td>
                                    <td class="py-4 px-4 text-right font-bold text-xl text-green-600 dark:text-green-400">
                                        ${packageData.calculated_total > 0 ? formatPrice(packageData.calculated_total) : formatPrice(packageData.price)}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                ` : `
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                        Chưa có sản phẩm nào trong gói này.
                    </p>
                `}
            </div>
        </div>
    `;
    
    container.innerHTML = html;
    
    // Update page title
    document.title = `${packageData.name} - HC Eco System`;
}

// Helper functions
function formatPrice(price) {
    if (!price) return 'Liên hệ';
    const roundedPrice = Math.round(price);
    return roundedPrice.toLocaleString('vi-VN') + '₫';
}

function getBadgeStyle(color) {
    if (!color) return { class: 'bg-gray-600', style: '' };
    
    if (color.startsWith('#')) {
        const hex = color.replace('#', '');
        const r = parseInt(hex.substring(0, 2), 16);
        const g = parseInt(hex.substring(2, 4), 16);
        const b = parseInt(hex.substring(4, 6), 16);
        const darkerR = Math.max(0, Math.floor(r * 0.8));
        const darkerG = Math.max(0, Math.floor(g * 0.8));
        const darkerB = Math.max(0, Math.floor(b * 0.8));
        const darkerHex = `#${darkerR.toString(16).padStart(2, '0')}${darkerG.toString(16).padStart(2, '0')}${darkerB.toString(16).padStart(2, '0')}`;
        
        return {
            class: '',
            style: `background: linear-gradient(to right, ${color}, ${darkerHex}); color: white;`
        };
    }
    
    const colorMap = {
        'blue': { from: '#2563eb', to: '#1d4ed8' },
        'green': { from: '#16a34a', to: '#15803d' },
        'red': { from: '#dc2626', to: '#b91c1c' },
        'yellow': { from: '#ca8a04', to: '#a16207' },
        'purple': { from: '#9333ea', to: '#7e22ce' },
        'orange': { from: '#ea580c', to: '#c2410c' }
    };
    
    const mapped = colorMap[color.toLowerCase()] || colorMap['blue'];
    return {
        class: '',
        style: `background: linear-gradient(to right, ${mapped.from}, ${mapped.to}); color: white;`
    };
}

function showError(message) {
    const container = document.getElementById('package-detail-container');
    if (container) {
        container.innerHTML = `
            <div class="max-w-2xl mx-auto text-center py-16">
                <div class="text-6xl mb-4">❌</div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">${message}</h2>
                <a href="pricing.html" class="inline-block bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-all">
                    Quay lại danh sách gói
                </a>
            </div>
        `;
    }
}

