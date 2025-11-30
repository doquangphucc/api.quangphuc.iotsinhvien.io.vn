// Admin Packages Management

let packageCategoriesData = [];
let packagesData = [];
let allProductsData = []; // Store all products for package items selection

// ===============================================
// PACKAGE CATEGORIES
// ===============================================

// Load package categories
async function loadPackageCategories() {
    try {
        const response = await fetch(`${API_BASE}/admin/get_package_categories.php?t=${Date.now()}`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success) {
            packageCategoriesData = data.categories;
            renderPackageCategories();
        } else {
            showToast('Lỗi khi tải danh mục gói: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error loading package categories:', error);
        showToast('Lỗi kết nối khi tải danh mục gói', 'error');
    }
}

function renderPackageCategories() {
    const tbody = document.getElementById('package-categories-tbody');
    if (!tbody) return;
    
    if (packageCategoriesData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                    Chưa có danh mục gói nào. Click "Thêm danh mục gói" để tạo mới.
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = packageCategoriesData.map(cat => `
        <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-3">${cat.id}</td>
            <td class="px-4 py-3">
                ${cat.logo_url ? `<img src="${cat.logo_url}" alt="${cat.name}" class="h-10 w-10 object-contain rounded">` : '-'}
            </td>
            <td class="px-4 py-3 font-semibold">${cat.name}</td>
            <td class="px-4 py-3">
                ${cat.badge_text ? `<span class="px-3 py-1 rounded-full text-xs font-bold" style="background-color: ${cat.badge_color || '#3B82F6'}; color: white;">${cat.badge_text}</span>` : '-'}
            </td>
            <td class="px-4 py-3 text-center">${cat.display_order}</td>
            <td class="px-4 py-3 text-center">
                <span class="px-3 py-1 rounded-full text-sm font-medium ${cat.is_active == 1 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                    ${cat.is_active == 1 ? 'Hoạt động' : 'Tạm dừng'}
                </span>
            </td>
            <td class="px-4 py-3 text-center">
                <button onclick="editPackageCategory(${cat.id})" class="text-blue-600 hover:underline mr-3">✏️ Sửa</button>
                <button onclick="deletePackageCategory(${cat.id})" class="text-red-600 hover:underline">🗑️ Xóa</button>
            </td>
        </tr>
    `).join('');
}

function openPackageCategoryModal(id = null) {
    const modal = document.getElementById('packageCategoryModal');
    const form = document.getElementById('packageCategoryForm');
    form.reset();
    document.getElementById('package_category_id').value = '';
    document.getElementById('package_category_logo').value = '';
    
    // Hide preview and reset hidden input
    const preview = document.getElementById('package_category_logo_preview');
    preview.classList.add('hidden');
    document.getElementById('package_category_logo_preview').querySelector('img').src = '';
    
    if (id) {
        const cat = packageCategoriesData.find(c => c.id == id);
        if (cat) {
            document.getElementById('package_category_id').value = cat.id;
            document.getElementById('package_category_name').value = cat.name;
            document.getElementById('package_category_badge_text').value = cat.badge_text || '';
            // Convert old text-based colors to hex if needed
            let badgeColor = cat.badge_color || '#3B82F6';
            if (badgeColor && !badgeColor.startsWith('#')) {
                const colorMap = {
                    'blue': '#3B82F6',
                    'green': '#10B981',
                    'red': '#EF4444',
                    'yellow': '#FBBF24',
                    'purple': '#8B5CF6',
                    'orange': '#F97316'
                };
                badgeColor = colorMap[badgeColor] || '#3B82F6';
            }
            document.getElementById('package_category_badge_color').value = badgeColor;
            document.getElementById('package_category_display_order').value = cat.display_order;
            document.getElementById('package_category_is_active').checked = cat.is_active == 1;
            
            // Show existing logo if available
            if (cat.logo_url) {
                const img = document.getElementById('package_category_logo_preview').querySelector('img');
                img.src = '../' + cat.logo_url;
                preview.classList.remove('hidden');
            }
            
            document.getElementById('packageCategoryModalTitle').textContent = 'Sửa danh mục gói';
        }
    } else {
        document.getElementById('packageCategoryModalTitle').textContent = 'Thêm danh mục gói';
    }
    
    modal.classList.add('show');
}

function closePackageCategoryModal() {
    document.getElementById('packageCategoryModal').classList.remove('show');
}

async function savePackageCategory(event) {
    event.preventDefault();
    
    const name = document.getElementById('package_category_name').value.trim();
    const badge_text = document.getElementById('package_category_badge_text').value.trim();
    const badge_color = document.getElementById('package_category_badge_color').value;
    const display_order = parseInt(document.getElementById('package_category_display_order').value) || 0;
    const is_active = document.getElementById('package_category_is_active').checked;
    const id = document.getElementById('package_category_id').value || 0;
    const logoInput = document.getElementById('package_category_logo');
    
    if (name === '') {
        showToast('Tên danh mục không được để trống', 'warning');
        return;
    }
    
    // Get existing logo URL for edit mode
    const existingLogo = id ? packageCategoriesData.find(c => c.id == id)?.logo_url || '' : '';
    
    // Use FormData to handle file upload
    const formData = new FormData();
    formData.append('id', id);
    formData.append('name', name);
    formData.append('badge_text', badge_text);
    formData.append('badge_color', badge_color);
    formData.append('display_order', display_order);
    formData.append('is_active', is_active ? 1 : 0);
    formData.append('logo_url', existingLogo); // Keep existing logo if not changed
    
    // Add logo file if selected
    if (logoInput.files && logoInput.files[0]) {
        formData.append('logo', logoInput.files[0]);
    }
    
    try {
        const response = await fetch(`${API_BASE}/admin/save_package_category.php`, {
            credentials: 'include',
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            closePackageCategoryModal();
            await loadPackageCategories();
            await loadPackages(); // Reload packages to update category names
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Error saving package category:', error);
        showToast('Có lỗi xảy ra khi lưu danh mục gói', 'error');
    }
}

function editPackageCategory(id) {
    openPackageCategoryModal(id);
}

async function deletePackageCategory(id) {
    if (!confirm('Bạn có chắc muốn xóa danh mục gói này?')) {
        showToast('Đã hủy xóa', 'info');
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE}/admin/delete_package_category.php`, {
            credentials: 'include',
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            await loadPackageCategories();
            await loadPackages();
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Error deleting package category:', error);
        showToast('Có lỗi xảy ra khi xóa danh mục gói', 'error');
    }
}

// ===============================================
// PACKAGES
// ===============================================

// Load packages
async function loadPackages() {
    try {
        const response = await fetch(`${API_BASE}/admin/get_packages.php?t=${Date.now()}`, {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success) {
            packagesData = data.packages;
            renderPackages();
        } else {
            showToast('Lỗi khi tải danh sách gói: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error loading packages:', error);
        showToast('Lỗi kết nối khi tải danh sách gói', 'error');
    }
}

function renderPackages() {
    const tbody = document.getElementById('packages-tbody');
    if (!tbody) return;
    
    if (packagesData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                    Chưa có gói sản phẩm nào. Click "Thêm gói" để tạo mới.
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = packagesData.map(pkg => `
        <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-3">${pkg.id}</td>
            <td class="px-4 py-3 font-semibold">${pkg.name}</td>
            <td class="px-4 py-3">${pkg.category_name || '-'}</td>
            <td class="px-4 py-3 text-right">${formatCurrency(pkg.price)}</td>
            <td class="px-4 py-3 text-center">${pkg.items.length}</td>
            <td class="px-4 py-3 text-center">${pkg.display_order}</td>
            <td class="px-4 py-3 text-center">
                <span class="px-3 py-1 rounded-full text-sm font-medium ${pkg.is_active == 1 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                    ${pkg.is_active == 1 ? 'Hoạt động' : 'Tạm dừng'}
                </span>
            </td>
            <td class="px-4 py-3 text-center">
                <button onclick="editPackage(${pkg.id})" class="text-blue-600 hover:underline mr-3">✏️ Sửa</button>
                <button onclick="deletePackage(${pkg.id})" class="text-red-600 hover:underline">🗑️ Xóa</button>
            </td>
        </tr>
    `).join('');
}

async function loadPackageCategoriesForSelect() {
    await loadPackageCategories();
    const select = document.getElementById('package_category_id_select');
    if (!select) {
        console.error('Package category select not found');
        return;
    }
    
    // Clear and add default option
    select.innerHTML = '<option value="">-- Chọn danh mục gói --</option>';
    
    // Debug log
    console.log('Package categories data:', packageCategoriesData);
    console.log('Number of categories:', packageCategoriesData.length);
    
    if (packageCategoriesData.length === 0) {
        select.innerHTML += '<option value="">(Chưa có danh mục gói - Vui lòng tạo danh mục trước)</option>';
        console.warn('No package categories found');
        return;
    }
    
    packageCategoriesData.forEach(cat => {
        console.log('Adding category to select:', cat.id, cat.name);
        select.innerHTML += `<option value="${cat.id}">${cat.name}</option>`;
    });
    
    console.log('Select options after loading:', select.innerHTML);
}

async function openPackageModal(id = null) {
    const modal = document.getElementById('packageModal');
    const form = document.getElementById('packageForm');
    form.reset();
    document.getElementById('package_id').value = '';
    
    // Load categories for dropdown FIRST
    await loadPackageCategoriesForSelect();
    
    // Load all products for selection
    await loadAllProducts();
    
    // Clear items container
    document.getElementById('package-items-container').innerHTML = '';
    
    // Clear highlights container
    document.getElementById('package-highlights-container').innerHTML = '';
    
    // Reset counters
    packageItemCounter = 0;
    packageHighlightCounter = 0;
    
    if (id) {
        const pkg = packagesData.find(p => p.id == id);
        if (pkg) {
            document.getElementById('package_id').value = pkg.id;
            
            // Set category dropdown AFTER categories are loaded
            const categorySelect = document.getElementById('package_category_id_select');
            if (categorySelect) {
                categorySelect.value = pkg.category_id;
                console.log('Set category_id to:', pkg.category_id, 'Current value:', categorySelect.value);
            }
            
            document.getElementById('package_name').value = pkg.name;
            document.getElementById('package_description').value = pkg.description || '';
            document.getElementById('package_price').value = pkg.price;
            document.getElementById('package_badge_text').value = pkg.badge_text || '';
            // Convert old text-based colors to hex if needed
            let badgeColor = pkg.badge_color || '#10B981';
            if (badgeColor && !badgeColor.startsWith('#')) {
                const colorMap = {
                    'blue': '#3B82F6',
                    'green': '#10B981',
                    'red': '#EF4444',
                    'yellow': '#FBBF24',
                    'purple': '#8B5CF6',
                    'orange': '#F97316'
                };
                badgeColor = colorMap[badgeColor] || '#10B981';
            }
            document.getElementById('package_badge_color').value = badgeColor;
            document.getElementById('package_display_order').value = pkg.display_order;
            document.getElementById('package_is_active').checked = pkg.is_active == 1;
            document.getElementById('packageModalTitle').textContent = 'Sửa gói sản phẩm';
            
            // Load items
            if (pkg.items && pkg.items.length > 0) {
                pkg.items.forEach(item => {
                    addPackageItem(
                        item.product_id || null,
                        item.item_name || '',
                        item.item_description || '',
                        item.quantity || 1,
                        item.price_type || 'market_price'
                    );
                });
            }
            
            // Load highlights (for backward compatibility, check both old and new fields)
            if (pkg.highlights && pkg.highlights.length > 0) {
                pkg.highlights.forEach(highlight => {
                    addPackageHighlight(highlight.title || '', highlight.content || '');
                });
            } else {
                // Fallback to old fields for backward compatibility
                if (pkg.savings_per_month || pkg.payback_period) {
                    if (pkg.savings_per_month) {
                        addPackageHighlight('Tiết kiệm/tháng', pkg.savings_per_month);
                    }
                    if (pkg.payback_period) {
                        addPackageHighlight('Hoàn vốn', pkg.payback_period);
                    }
                }
            }
        }
    } else {
        document.getElementById('packageModalTitle').textContent = 'Thêm gói sản phẩm';
    }
    
    modal.classList.add('show');
}

function closePackageModal() {
    document.getElementById('packageModal').classList.remove('show');
}

let packageItemCounter = 0;
let packageHighlightCounter = 0;

// Load all products for package items selection
async function loadAllProducts() {
    try {
        const response = await fetch(`${API_BASE}/admin/get_products.php?t=${Date.now()}`, {
            credentials: 'include'
        });
        const data = await response.json();
        if (data.success) {
            allProductsData = data.products;
        }
    } catch (error) {
        console.error('Error loading products:', error);
    }
}

function addPackageItem(productId = null, itemName = '', itemDescription = '', quantity = 1, priceType = 'market_price') {
    packageItemCounter++;
    const container = document.getElementById('package-items-container');
    const itemId = `package-item-${packageItemCounter}`;
    
    // Build product options
    let productOptions = '<option value="">-- Chọn sản phẩm (hoặc nhập tên thủ công) --</option>';
    allProductsData.forEach(product => {
        const selected = productId && product.id == productId ? 'selected' : '';
        productOptions += `<option value="${product.id}" ${selected} data-market-price="${product.market_price || 0}" data-category-price="${product.category_price || 0}">${product.title} (${product.category_name || 'N/A'})</option>`;
    });
    
    const itemHtml = `
        <div id="${itemId}" class="border border-gray-300 rounded-lg p-4 bg-gray-50 mb-3">
            <div class="flex justify-between items-center mb-3">
                <label class="block text-sm font-semibold">Sản phẩm trong gói</label>
                <button type="button" onclick="removePackageItem('${itemId}')" class="text-red-600 hover:text-red-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold mb-1">Chọn sản phẩm</label>
                    <select class="package-item-product-id w-full px-3 py-2 border rounded" onchange="updatePackageItemPrice('${itemId}', this.value)">
                        ${productOptions}
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Tên item (nếu không chọn sản phẩm)</label>
                    <input type="text" class="package-item-name w-full px-3 py-2 border rounded" placeholder="VD: 12 Tấm pin Jinko Solar 590W" value="${itemName}">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Mô tả (tùy chọn)</label>
                    <input type="text" class="package-item-description w-full px-3 py-2 border rounded text-sm" placeholder="Mô tả chi tiết" value="${itemDescription}">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Số lượng</label>
                        <input type="number" class="package-item-quantity w-full px-3 py-2 border rounded" min="1" value="${quantity}" onchange="calculatePackageTotalPrice()">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Loại giá</label>
                        <select class="package-item-price-type w-full px-3 py-2 border rounded" onchange="updatePackageItemPrice('${itemId}', document.querySelector('#${itemId} .package-item-product-id').value)">
                            <option value="market_price" ${priceType === 'market_price' ? 'selected' : ''}>Giá thị trường</option>
                            <option value="category_price" ${priceType === 'category_price' ? 'selected' : ''}>Giá danh mục</option>
                        </select>
                    </div>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded p-2">
                    <div class="text-xs font-semibold text-blue-700">Đơn giá: <span class="package-item-unit-price">0</span> VNĐ</div>
                    <div class="text-xs font-semibold text-green-700">Thành tiền: <span class="package-item-total-price">0</span> VNĐ</div>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', itemHtml);
    
    // Update price for this item if product is selected
    if (productId) {
        updatePackageItemPrice(itemId, productId);
    }
    
    // Recalculate total
    calculatePackageTotalPrice();
}

function updatePackageItemPrice(itemId, productId) {
    const itemDiv = document.getElementById(itemId);
    if (!itemDiv) return;
    
    const productSelect = itemDiv.querySelector('.package-item-product-id');
    const priceTypeSelect = itemDiv.querySelector('.package-item-price-type');
    const quantityInput = itemDiv.querySelector('.package-item-quantity');
    const unitPriceSpan = itemDiv.querySelector('.package-item-unit-price');
    const totalPriceSpan = itemDiv.querySelector('.package-item-total-price');
    
    if (!productId || productId === '') {
        unitPriceSpan.textContent = '0';
        totalPriceSpan.textContent = '0';
        calculatePackageTotalPrice();
        return;
    }
    
    const selectedOption = productSelect.options[productSelect.selectedIndex];
    const marketPrice = parseFloat(selectedOption.getAttribute('data-market-price')) || 0;
    const categoryPrice = parseFloat(selectedOption.getAttribute('data-category-price')) || 0;
    
    const priceType = priceTypeSelect.value;
    const unitPrice = priceType === 'category_price' && categoryPrice > 0 ? categoryPrice : marketPrice;
    const quantity = parseInt(quantityInput.value) || 1;
    const totalPrice = unitPrice * quantity;
    
    unitPriceSpan.textContent = formatNumber(unitPrice);
    totalPriceSpan.textContent = formatNumber(totalPrice);
    
    calculatePackageTotalPrice();
}

function calculatePackageTotalPrice() {
    const items = document.querySelectorAll('#package-items-container > div');
    let total = 0;
    
    items.forEach(itemDiv => {
        const totalPriceSpan = itemDiv.querySelector('.package-item-total-price');
        if (totalPriceSpan) {
            const priceText = totalPriceSpan.textContent.replace(/\./g, '').replace(' VNĐ', '');
            total += parseFloat(priceText) || 0;
        }
    });
    
    // Update package price field
    const priceInput = document.getElementById('package_price');
    if (priceInput) {
        priceInput.value = Math.round(total);
    }
}

function formatNumber(num) {
    return Math.round(num).toLocaleString('vi-VN');
}

function removePackageItem(itemId) {
    const item = document.getElementById(itemId);
    if (item) {
        item.remove();
        calculatePackageTotalPrice();
    }
}

function addPackageHighlight(title = '', content = '') {
    packageHighlightCounter++;
    const container = document.getElementById('package-highlights-container');
    const highlightId = `package-highlight-${packageHighlightCounter}`;
    
    const highlightHtml = `
        <div id="${highlightId}" class="flex gap-2 items-start p-3 bg-gray-50 rounded-lg">
            <div class="flex-1 grid grid-cols-2 gap-2">
                <input type="text" class="package-highlight-title w-full px-3 py-2 border rounded" placeholder="Tiêu đề (VD: Tiết kiệm/tháng)" value="${title}">
                <input type="text" class="package-highlight-content w-full px-3 py-2 border rounded" placeholder="Nội dung (VD: ~4 triệu/tháng)" value="${content}">
            </div>
            <button type="button" onclick="removePackageHighlight('${highlightId}')" class="text-red-600 hover:text-red-800 mt-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', highlightHtml);
}

function removePackageHighlight(highlightId) {
    const highlight = document.getElementById(highlightId);
    if (highlight) highlight.remove();
}

async function savePackage(event) {
    event.preventDefault();
    
    const categoryId = parseInt(document.getElementById('package_category_id_select').value);
    const name = document.getElementById('package_name').value.trim();
    const price = parseFloat(document.getElementById('package_price').value);
    
    if (name === '') {
        showToast('Tên gói không được để trống', 'warning');
        return;
    }
    if (categoryId <= 0) {
        showToast('Vui lòng chọn danh mục gói', 'warning');
        return;
    }
    if (!price || price <= 0) {
        showToast('Giá gói phải lớn hơn 0', 'warning');
        return;
    }
    
    // Collect package items
    const itemContainers = document.querySelectorAll('#package-items-container > div');
    const items = [];
    
    itemContainers.forEach(itemDiv => {
        const productIdSelect = itemDiv.querySelector('.package-item-product-id');
        const itemNameInput = itemDiv.querySelector('.package-item-name');
        const itemDescriptionInput = itemDiv.querySelector('.package-item-description');
        const quantityInput = itemDiv.querySelector('.package-item-quantity');
        const priceTypeSelect = itemDiv.querySelector('.package-item-price-type');
        
        const productId = productIdSelect?.value ? parseInt(productIdSelect.value) : null;
        const itemName = itemNameInput?.value.trim() || '';
        const itemDescription = itemDescriptionInput?.value.trim() || '';
        const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
        const priceType = priceTypeSelect?.value || 'market_price';
        
        // Must have either product_id or item_name
        if (productId || itemName) {
            items.push({
                product_id: productId,
                item_name: itemName,
                item_description: itemDescription,
                quantity: quantity,
                price_type: priceType
            });
        }
    });
    
    // Collect highlights
    const highlightTitles = document.querySelectorAll('.package-highlight-title');
    const highlightContents = document.querySelectorAll('.package-highlight-content');
    const highlights = [];
    
    highlightTitles.forEach((input, index) => {
        const title = input.value.trim();
        const content = highlightContents[index]?.value.trim() || '';
        if (title && content) {
            highlights.push({
                title: title,
                content: content
            });
        }
    });
    
    const formData = {
        id: document.getElementById('package_id').value || 0,
        category_id: categoryId,
        name: name,
        description: document.getElementById('package_description').value.trim(),
        price: price,
        badge_text: document.getElementById('package_badge_text').value.trim(),
        badge_color: document.getElementById('package_badge_color').value,
        display_order: parseInt(document.getElementById('package_display_order').value) || 0,
        is_active: document.getElementById('package_is_active').checked,
        items: items,
        highlights: highlights
    };
    
    try {
        const response = await fetch(`${API_BASE}/admin/save_package.php`, {
            credentials: 'include',
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            closePackageModal();
            await loadPackages();
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Error saving package:', error);
        showToast('Có lỗi xảy ra khi lưu gói sản phẩm', 'error');
    }
}

function editPackage(id) {
    openPackageModal(id);
}

async function deletePackage(id) {
    if (!confirm('Bạn có chắc muốn xóa gói sản phẩm này?')) {
        showToast('Đã hủy xóa', 'info');
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE}/admin/delete_package.php`, {
            credentials: 'include',
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            await loadPackages();
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Error deleting package:', error);
        showToast('Có lỗi xảy ra khi xóa gói sản phẩm', 'error');
    }
}

