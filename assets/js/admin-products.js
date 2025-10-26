// Admin Products Management - Updated Structure
// This file contains the NEW product management functions with simplified fields

let productImagesData = [];
let productsData = [];

// Load products list
async function loadProducts() {
    const categoryId = document.getElementById('product-category-filter')?.value || '';
    try {
        const response = await fetch(`${API_BASE}/admin/get_products.php?category_id=${categoryId}&t=${Date.now()}`, {
            credentials: 'include'
        });
        const data = await response.json();
        if (data.success) {
            productsData = data.products;
            renderProducts(data.products);
        }
    } catch (error) {
        console.error('Error loading products:', error);
    }
}

// Render products table
function renderProducts(products) {
    const tbody = document.getElementById('products-tbody');
    if (products.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                    Chưa có sản phẩm nào. Click "Thêm sản phẩm" để tạo mới.
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = products.map(p => `
        <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-3">${p.id}</td>
            <td class="px-4 py-3">
                ${p.image_url ? `<img src="${p.image_url}" class="h-16 w-16 object-cover rounded border" onerror="this.src='../assets/img/logo.jpg'">` : '<span class="text-gray-400">Chưa có</span>'}
            </td>
            <td class="px-4 py-3 font-semibold">${p.title}</td>
            <td class="px-4 py-3">${p.category_name || '—'}</td>
            <td class="px-4 py-3 text-right">${formatCurrency(p.market_price)}</td>
            <td class="px-4 py-3 text-right">${p.category_price ? formatCurrency(p.category_price) : '—'}</td>
            <td class="px-4 py-3 text-center">
                <span class="px-3 py-1 rounded-full text-sm font-medium ${p.is_active == 1 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                    ${p.is_active == 1 ? 'Hoạt động' : 'Tạm dừng'}
                </span>
            </td>
            <td class="px-4 py-3 text-center">
                <button onclick="editProduct(${p.id})" class="text-blue-600 hover:underline mr-3">✏️ Sửa</button>
                <button onclick="deleteProduct(${p.id})" class="text-red-600 hover:underline">🗑️ Xóa</button>
            </td>
        </tr>
    `).join('');
}

// Open product modal
async function openProductModal(id = null) {
    const modal = document.getElementById('productModal');
    const form = document.getElementById('productForm');
    form.reset();
    
    // Load danh sách ảnh sản phẩm
    await loadProductImages();
    
    // Load danh mục để điền vào dropdown
    await loadCategoriesForProducts();
    
    if (id) {
        const product = productsData.find(p => p.id == id);
        if (product) {
            document.getElementById('product_id').value = product.id;
            document.getElementById('product_category_id').value = product.category_id;
            document.getElementById('product_title').value = product.title;
            document.getElementById('product_market_price').value = product.market_price;
            document.getElementById('product_category_price').value = product.category_price || '';
            document.getElementById('product_image_url').value = product.image_url || '';
            document.getElementById('product_description').value = product.description || '';
            document.getElementById('product_specifications').value = product.specifications || '';
            document.getElementById('product_is_active').checked = product.is_active == 1;
            document.getElementById('productModalTitle').textContent = 'Sửa sản phẩm';
            
            // Update category price label and preview image
            updateCategoryPriceLabel();
            previewProductImage();
        }
    } else {
        document.getElementById('productModalTitle').textContent = 'Thêm sản phẩm';
    }
    
    modal.classList.add('show');
}

function closeProductModal() {
    document.getElementById('productModal').classList.remove('show');
}

// Load categories for product dropdown
async function loadCategoriesForProducts() {
    try {
        const response = await fetch(`${API_BASE}/admin/get_categories.php?t=${Date.now()}`, {
            credentials: 'include'
        });
        const data = await response.json();
        if (data.success) {
            const select = document.getElementById('product_category_id');
            select.innerHTML = '<option value="">-- Chọn danh mục --</option>' + 
                data.categories.filter(c => c.is_active == 1).map(c => 
                    `<option value="${c.id}">${c.name}</option>`
                ).join('');
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Load product images from server
async function loadProductImages() {
    try {
        const response = await fetch(`${API_BASE}/admin/get_product_images.php?t=${Date.now()}`, {
            credentials: 'include'
        });
        const data = await response.json();
        if (data.success) {
            productImagesData = data.images;
            const select = document.getElementById('product_image_url');
            select.innerHTML = '<option value="">-- Hoặc chọn ảnh có sẵn --</option>' + 
                data.images.map(img => 
                    `<option value="${img.path}">${img.filename}</option>`
                ).join('');
        }
    } catch (error) {
        console.error('Error loading product images:', error);
    }
}

// Update category price label dynamically
function updateCategoryPriceLabel() {
    const categorySelect = document.getElementById('product_category_id');
    const selectedOption = categorySelect.options[categorySelect.selectedIndex];
    const categoryName = selectedOption ? selectedOption.text : 'danh mục';
    const label = document.getElementById('product_category_price_label');
    if (label && categoryName !== '-- Chọn danh mục --') {
        label.textContent = `Giá ${categoryName} (VNĐ)`;
    } else {
        label.textContent = 'Giá danh mục (VNĐ)';
    }
}

// Upload product image
async function uploadProductImage(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('image', file);
    
    try {
        const response = await fetch(`${API_BASE}/admin/upload_product_image.php`, {
            method: 'POST',
            credentials: 'include',
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            showToast('Upload ảnh thành công!', 'success');
            // Set image URL
            document.getElementById('product_image_url').value = data.path;
            // Reload images list
            await loadProductImages();
            // Select the uploaded image
            document.getElementById('product_image_url').value = data.path;
            // Preview
            previewProductImage();
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Error uploading image:', error);
        showToast('Lỗi khi upload ảnh', 'error');
    }
}

// Preview product image
function previewProductImage() {
    const imageUrl = document.getElementById('product_image_url').value;
    const preview = document.getElementById('product_image_preview');
    const previewImg = document.getElementById('product_image_preview_img');
    
    if (imageUrl) {
        previewImg.src = imageUrl;
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
    }
}

// Save product
async function saveProduct(event) {
    event.preventDefault();
    
    const categoryId = parseInt(document.getElementById('product_category_id').value);
    const title = document.getElementById('product_title').value.trim();
    const marketPrice = parseFloat(document.getElementById('product_market_price').value);
    const categoryPriceVal = document.getElementById('product_category_price').value;
    
    if (!title) {
        showToast('Vui lòng nhập tiêu đề sản phẩm', 'warning');
        return;
    }
    
    if (!categoryId) {
        showToast('Vui lòng chọn danh mục', 'warning');
        return;
    }
    
    if (!marketPrice || marketPrice <= 0) {
        showToast('Giá thị trường phải lớn hơn 0', 'warning');
        return;
    }
    
    const formData = {
        id: document.getElementById('product_id').value || 0,
        category_id: categoryId,
        title: title,
        market_price: marketPrice,
        category_price: categoryPriceVal ? parseFloat(categoryPriceVal) : null,
        image_url: document.getElementById('product_image_url').value || '',
        description: document.getElementById('product_description').value.trim(),
        specifications: document.getElementById('product_specifications').value.trim(),
        is_active: document.getElementById('product_is_active').checked
    };

    try {
        const response = await fetch(`${API_BASE}/admin/save_product.php`, {
            credentials: 'include', 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        const data = await response.json();
        if (data.success) {
            showToast(data.message, 'success');
            closeProductModal();
            loadProducts();
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Error saving product:', error);
        showToast('Có lỗi xảy ra', 'error');
    }
}

// Edit product
function editProduct(id) {
    openProductModal(id);
}

// Delete product
async function deleteProduct(id) {
    if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;
    
    try {
        const response = await fetch(`${API_BASE}/admin/delete_product.php`, {
            credentials: 'include', 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await response.json();
        if (data.success) {
            showToast(data.message, 'success');
            loadProducts();
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Error deleting product:', error);
        showToast('Có lỗi xảy ra', 'error');
    }
}

