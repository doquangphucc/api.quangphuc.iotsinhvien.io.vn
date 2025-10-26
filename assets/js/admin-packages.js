// Admin Packages Management

let packageCategoriesData = [];
let packagesData = [];

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
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                    Chưa có danh mục gói nào. Click "Thêm danh mục gói" để tạo mới.
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = packageCategoriesData.map(cat => `
        <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-3">${cat.id}</td>
            <td class="px-4 py-3 font-semibold">${cat.name}</td>
            <td class="px-4 py-3">
                ${cat.badge_text ? `<span class="px-3 py-1 rounded-full text-xs font-bold bg-${cat.badge_color}-100 text-${cat.badge_color}-800">${cat.badge_text}</span>` : '-'}
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
    
    if (id) {
        const cat = packageCategoriesData.find(c => c.id == id);
        if (cat) {
            document.getElementById('package_category_id').value = cat.id;
            document.getElementById('package_category_name').value = cat.name;
            document.getElementById('package_category_badge_text').value = cat.badge_text || '';
            document.getElementById('package_category_badge_color').value = cat.badge_color || 'blue';
            document.getElementById('package_category_display_order').value = cat.display_order;
            document.getElementById('package_category_is_active').checked = cat.is_active == 1;
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
    
    const formData = {
        id: document.getElementById('package_category_id').value || 0,
        name: document.getElementById('package_category_name').value.trim(),
        badge_text: document.getElementById('package_category_badge_text').value.trim(),
        badge_color: document.getElementById('package_category_badge_color').value,
        display_order: parseInt(document.getElementById('package_category_display_order').value) || 0,
        is_active: document.getElementById('package_category_is_active').checked
    };
    
    if (formData.name === '') {
        showToast('Tên danh mục không được để trống', 'warning');
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE}/admin/save_package_category.php`, {
            credentials: 'include',
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
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

function openPackageModal(id = null) {
    const modal = document.getElementById('packageModal');
    const form = document.getElementById('packageForm');
    form.reset();
    document.getElementById('package_id').value = '';
    
    // Load categories for dropdown
    loadPackageCategoriesForSelect();
    
    // Clear items container
    document.getElementById('package-items-container').innerHTML = '';
    
    if (id) {
        const pkg = packagesData.find(p => p.id == id);
        if (pkg) {
            document.getElementById('package_id').value = pkg.id;
            document.getElementById('package_category_id_select').value = pkg.category_id;
            document.getElementById('package_name').value = pkg.name;
            document.getElementById('package_description').value = pkg.description || '';
            document.getElementById('package_price').value = pkg.price;
            document.getElementById('package_badge_text').value = pkg.badge_text || '';
            document.getElementById('package_badge_color').value = pkg.badge_color || 'blue';
            document.getElementById('package_savings_per_month').value = pkg.savings_per_month || '';
            document.getElementById('package_payback_period').value = pkg.payback_period || '';
            document.getElementById('package_display_order').value = pkg.display_order;
            document.getElementById('package_is_active').checked = pkg.is_active == 1;
            document.getElementById('packageModalTitle').textContent = 'Sửa gói sản phẩm';
            
            // Load items
            if (pkg.items && pkg.items.length > 0) {
                pkg.items.forEach(item => {
                    addPackageItem(item.item_name, item.item_description);
                });
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

function addPackageItem(itemName = '', itemDescription = '') {
    packageItemCounter++;
    const container = document.getElementById('package-items-container');
    const itemId = `package-item-${packageItemCounter}`;
    
    const itemHtml = `
        <div id="${itemId}" class="flex gap-2 items-start p-3 bg-gray-50 rounded-lg">
            <div class="flex-1">
                <input type="text" class="package-item-name w-full px-3 py-2 border rounded mb-2" placeholder="Tên item (VD: 12 Tấm pin Jinko Solar 590W)" value="${itemName}">
                <input type="text" class="package-item-description w-full px-3 py-2 border rounded text-sm" placeholder="Mô tả (tùy chọn)" value="${itemDescription}">
            </div>
            <button type="button" onclick="removePackageItem('${itemId}')" class="text-red-600 hover:text-red-800 mt-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', itemHtml);
}

function removePackageItem(itemId) {
    const item = document.getElementById(itemId);
    if (item) item.remove();
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
    const itemNames = document.querySelectorAll('.package-item-name');
    const itemDescriptions = document.querySelectorAll('.package-item-description');
    const items = [];
    
    itemNames.forEach((input, index) => {
        const itemName = input.value.trim();
        if (itemName) {
            items.push({
                item_name: itemName,
                item_description: itemDescriptions[index]?.value.trim() || ''
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
        savings_per_month: document.getElementById('package_savings_per_month').value.trim(),
        payback_period: document.getElementById('package_payback_period').value.trim(),
        display_order: parseInt(document.getElementById('package_display_order').value) || 0,
        is_active: document.getElementById('package_is_active').checked,
        items: items
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

