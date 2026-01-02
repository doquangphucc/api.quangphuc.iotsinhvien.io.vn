// Admin Products Management - Updated Structure
// This file contains the NEW product management functions with simplified fields
// Note: productsData is already declared in admin.js, so we don't re-declare it here

let productImagesData = [];
let currentProductGalleryImages = []; // Lưu danh sách ảnh gallery của sản phẩm đang chỉnh sửa

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
                <td colspan="9" class="px-4 py-8 text-center text-gray-500">
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
                ${p.image_url ? `<img src="../${p.image_url}" class="h-16 w-16 object-cover rounded border" onerror="this.src='../assets/img/logo.jpg'">` : '<span class="text-gray-400">Chưa có</span>'}
            </td>
            <td class="px-4 py-3 font-semibold">${p.title}</td>
            <td class="px-4 py-3">${p.category_name || '—'}</td>
            <td class="px-4 py-3 text-right">${formatCurrency(p.market_price)}</td>
            <td class="px-4 py-3 text-right">${p.category_price ? formatCurrency(p.category_price) : '—'}</td>
            <td class="px-4 py-3 text-center font-semibold">${p.display_order || 0}</td>
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
            document.getElementById('product_technical_description').value = product.technical_description || '';
            document.getElementById('product_display_order').value = product.display_order !== undefined && product.display_order !== null ? product.display_order : 1;
            document.getElementById('product_is_active').checked = product.is_active == 1;
            document.getElementById('productModalTitle').textContent = 'Sửa sản phẩm';
            
            // Update category price label and preview image
            updateCategoryPriceLabel();
            previewProductImage();
            
            // Load gallery images for this product
            await loadProductGalleryImages(product.id);
        }
    } else {
        document.getElementById('productModalTitle').textContent = 'Thêm sản phẩm';
        // Tính display_order mặc định = max + 1 của danh mục hiện tại
        await updateDefaultDisplayOrder();
        // Reset gallery
        currentProductGalleryImages = [];
        renderProductGallery();
    }
    
    modal.classList.add('show');
}

// Update default display order when category changes (for new products)
async function updateDefaultDisplayOrder() {
    const categoryId = parseInt(document.getElementById('product_category_id').value);
    const productId = parseInt(document.getElementById('product_id').value);
    
    // Chỉ tính display_order mặc định khi thêm mới (productId = 0)
    if (!categoryId || productId > 0) return;
    
    try {
        // Lấy max display_order của các sản phẩm trong danh mục
        const productsInCategory = productsData.filter(p => p.category_id == categoryId);
        const maxOrder = productsInCategory.length > 0 
            ? Math.max(...productsInCategory.map(p => p.display_order || 0)) 
            : 0;
        
        document.getElementById('product_display_order').value = maxOrder + 1;
    } catch (error) {
        console.error('Error calculating default display order:', error);
        document.getElementById('product_display_order').value = 1;
    }
}

function closeProductModal() {
    document.getElementById('productModal').classList.remove('show');
    // Reset gallery
    currentProductGalleryImages = [];
    renderProductGallery();
}

// Load gallery images for a product
async function loadProductGalleryImages(productId) {
    if (!productId) {
        currentProductGalleryImages = [];
        renderProductGallery();
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE}/get_product_images.php?product_id=${productId}&t=${Date.now()}`);
        const data = await response.json();
        if (data.success) {
            currentProductGalleryImages = data.images || [];
            renderProductGallery();
        } else {
            currentProductGalleryImages = [];
            renderProductGallery();
        }
    } catch (error) {
        console.error('Error loading gallery images:', error);
        currentProductGalleryImages = [];
        renderProductGallery();
    }
}

// Render gallery images
function renderProductGallery() {
    const galleryContainer = document.getElementById('product_gallery_images');
    if (!galleryContainer) return;
    
    if (currentProductGalleryImages.length === 0) {
        galleryContainer.innerHTML = '<p class="col-span-4 text-center text-gray-400 text-sm py-4">Chưa có ảnh nào. Hãy thêm ảnh vào gallery.</p>';
        return;
    }
    
    galleryContainer.innerHTML = currentProductGalleryImages.map((img, index) => `
        <div class="relative group">
            <img src="${img.image_url}" alt="Gallery ${index + 1}" 
                 class="w-full h-24 object-cover rounded-lg border-2 border-gray-300 hover:border-green-500 transition-all cursor-pointer">
            <button onclick="removeProductGalleryImage(${img.id})" 
                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-xs hover:bg-red-600">
                ×
            </button>
            <div class="absolute bottom-1 left-1 bg-black/50 text-white text-xs px-2 py-1 rounded">
                #${index + 1}
            </div>
        </div>
    `).join('');
}

// Upload multiple images to gallery
async function uploadProductGalleryImages(event) {
    const files = event.target.files;
    if (!files || files.length === 0) return;
    
    const productId = parseInt(document.getElementById('product_id').value) || 0;
    
    if (productId === 0) {
        showToast('⚠️ Vui lòng lưu sản phẩm trước khi thêm ảnh vào gallery', 'warning');
        event.target.value = '';
        return;
    }
    
    // Limit to 10 files at a time
    const filesToUpload = Array.from(files).slice(0, 10);
    
    for (const file of filesToUpload) {
        // Upload each file
        const formData = new FormData();
        formData.append('image', file);
        
        try {
            const uploadResponse = await fetch(`${API_BASE}/admin/upload_product_image.php`, {
                method: 'POST',
                credentials: 'include',
                body: formData
            });
            const uploadData = await uploadResponse.json();
            
            if (uploadData.success) {
                // Add image to gallery
                const addResponse = await fetch(`${API_BASE}/admin/add_product_image.php`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        product_id: productId,
                        image_url: uploadData.path
                    })
                });
                const addData = await addResponse.json();
                
                if (addData.success) {
                    // Reload gallery
                    await loadProductGalleryImages(productId);
                }
            }
        } catch (error) {
            console.error('Error uploading gallery image:', error);
        }
    }
    
    // Reset file input
    event.target.value = '';
    showToast(`✅ Đã thêm ${filesToUpload.length} ảnh vào gallery`, 'success');
}

// Remove image from gallery
async function removeProductGalleryImage(imageId) {
    const confirmed = await customConfirm('Bạn có chắc muốn xóa ảnh này khỏi gallery?', {
        title: 'Xóa ảnh',
        type: 'danger',
        confirmText: 'Xóa',
        cancelText: 'Hủy'
    });
    if (!confirmed) return;
    
    try {
        const response = await fetch(`${API_BASE}/admin/delete_product_image.php`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ image_id: imageId })
        });
        const data = await response.json();
        
        if (data.success) {
            showToast('✅ Đã xóa ảnh khỏi gallery', 'success');
            const productId = parseInt(document.getElementById('product_id').value) || 0;
            await loadProductGalleryImages(productId);
        } else {
            showToast(data.message || 'Lỗi khi xóa ảnh', 'error');
        }
    } catch (error) {
        console.error('Error removing gallery image:', error);
        showToast('Có lỗi xảy ra', 'error');
    }
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
    
    // Update default display order when category changes (for new products)
    updateDefaultDisplayOrder();
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
    
    const productId = parseInt(document.getElementById('product_id').value) || 0;
    const displayOrderInput = document.getElementById('product_display_order').value.trim();
    let displayOrder = 0;
    
    if (displayOrderInput) {
        displayOrder = parseInt(displayOrderInput);
        if (isNaN(displayOrder) || displayOrder < 1) {
            displayOrder = 0; // Giá trị không hợp lệ
        }
    }
    
    // Nếu là thêm mới và không có giá trị, tính mặc định
    if (productId === 0 && displayOrder === 0) {
        displayOrder = 1;
    }
    
    const formData = {
        id: document.getElementById('product_id').value || 0,
        category_id: categoryId,
        title: title,
        market_price: marketPrice,
        category_price: categoryPriceVal ? parseFloat(categoryPriceVal) : null,
        image_url: document.getElementById('product_image_url').value || '',
        technical_description: document.getElementById('product_technical_description').value.trim(),
        display_order: displayOrder,
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
    const confirmed = await customConfirm('Bạn có chắc muốn xóa sản phẩm này?', {
        title: 'Xóa sản phẩm',
        type: 'danger',
        confirmText: 'Xóa',
        cancelText: 'Hủy'
    });
    if (!confirmed) return;
    
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

