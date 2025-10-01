
document.addEventListener('DOMContentLoaded', () => {
    const cartItemsContainer = document.getElementById('cart-items');
    const selectAllCheckbox = document.getElementById('select-all');
    const checkoutBtn = document.getElementById('checkout-btn');
    const summarySection = document.querySelector('.cart-summary');
    const headerRow = document.querySelector('.cart-header');
    const selectAllWrapper = document.querySelector('.select-all');

    if (!cartItemsContainer || !selectAllCheckbox || !checkoutBtn) {
        return;
    }

    let cartItems = [];
    let selectedItemIds = new Set();

    function formatPrice(price) {
        const value = Number(price) || 0;
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
    }

    function showConfirmModal(message) {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
                animation: fadeIn 0.2s ease;
            `;

            const dialog = document.createElement('div');
            dialog.style.cssText = `
                background: white;
                border-radius: 16px;
                padding: 2rem;
                max-width: 400px;
                width: 90%;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                animation: slideUp 0.3s ease;
            `;

            dialog.innerHTML = `
                <style>
                    @keyframes fadeIn {
                        from { opacity: 0; }
                        to { opacity: 1; }
                    }
                    @keyframes slideUp {
                        from { transform: translateY(20px); opacity: 0; }
                        to { transform: translateY(0); opacity: 1; }
                    }
                </style>
                <h3 style="color: #3FA34D; margin: 0 0 1rem 0; font-size: 1.3rem;">Xác nhận</h3>
                <p style="color: #666; margin-bottom: 2rem; line-height: 1.6;">${message}</p>
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button id="modal-cancel" style="
                        padding: 0.75rem 1.5rem;
                        border: 2px solid #ddd;
                        background: white;
                        color: #666;
                        border-radius: 8px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.3s ease;
                    ">Hủy</button>
                    <button id="modal-confirm" style="
                        padding: 0.75rem 1.5rem;
                        border: none;
                        background: linear-gradient(135deg, #ef4444, #dc2626);
                        color: white;
                        border-radius: 8px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
                    ">Xóa</button>
                </div>
            `;

            modal.appendChild(dialog);
            document.body.appendChild(modal);

            const cancelBtn = dialog.querySelector('#modal-cancel');
            const confirmBtn = dialog.querySelector('#modal-confirm');

            // Hover effects
            cancelBtn.addEventListener('mouseenter', () => {
                cancelBtn.style.borderColor = '#3FA34D';
                cancelBtn.style.color = '#3FA34D';
            });
            cancelBtn.addEventListener('mouseleave', () => {
                cancelBtn.style.borderColor = '#ddd';
                cancelBtn.style.color = '#666';
            });
            confirmBtn.addEventListener('mouseenter', () => {
                confirmBtn.style.transform = 'translateY(-2px)';
                confirmBtn.style.boxShadow = '0 6px 20px rgba(239, 68, 68, 0.4)';
            });
            confirmBtn.addEventListener('mouseleave', () => {
                confirmBtn.style.transform = 'translateY(0)';
                confirmBtn.style.boxShadow = '0 4px 12px rgba(239, 68, 68, 0.3)';
            });

            cancelBtn.addEventListener('click', () => {
                modal.remove();
                resolve(false);
            });

            confirmBtn.addEventListener('click', () => {
                modal.remove();
                resolve(true);
            });

            // Close on backdrop click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                    resolve(false);
                }
            });
        });
    }

    function syncCartIndicator() {
        if (typeof updateAllCartCounters === 'function') {
            const totalItems = cartItems.reduce((sum, item) => sum + (Number(item.quantity) || 0), 0);
            updateAllCartCounters(totalItems);
        }
    }

    function toggleCartSections(isVisible) {
        const displayValue = isVisible ? '' : 'none';
        if (summarySection) summarySection.style.display = displayValue;
        if (headerRow) headerRow.style.display = displayValue;
        if (selectAllWrapper) selectAllWrapper.style.display = displayValue;

        if (!isVisible) {
            if (selectAllCheckbox) selectAllCheckbox.checked = false;
            const selectedCountEl = document.getElementById('selected-count');
            const subtotalEl = document.getElementById('subtotal');
            const totalEl = document.getElementById('total-amount');

            if (selectedCountEl) selectedCountEl.textContent = '0';
            if (subtotalEl) subtotalEl.textContent = formatPrice(0);
            if (totalEl) totalEl.textContent = formatPrice(0);

            checkoutBtn.disabled = true;
            checkoutBtn.style.opacity = '0.5';
        }
    }

    function renderEmptyCart() {
        cartItems = [];
        selectedItemIds = new Set();
        toggleCartSections(false);
        cartItemsContainer.innerHTML = `
            <div class="empty-cart">
                <div class="icon">&#128722;</div>
                <h3>Giỏ hàng của bạn đang trống</h3>
                <p>Hãy khám phá các sản phẩm của chúng tôi và thêm vào giỏ nhé!</p>
                <a href="pricing.html" class="btn primary" style="margin-top: 1rem;">Bảng giá sản phẩm</a>
            </div>
        `;
        syncCartIndicator();
    }

    function renderNotLoggedIn() {
        cartItems = [];
        selectedItemIds = new Set();
        toggleCartSections(false);
        cartItemsContainer.innerHTML = `
            <div class="empty-cart">
                <div class="icon">&#128274;</div>
                <h3>Vui lòng đăng nhập</h3>
                <p>Bạn cần đăng nhập để xem giỏ hàng và mua sắm.</p>
                <a href="login.html" class="btn primary" style="margin-top: 1rem;">Đăng nhập ngay</a>
            </div>
        `;
        syncCartIndicator();
    }

    function renderCartItems() {
        if (!cartItems.length) {
            renderEmptyCart();
            return;
        }

        toggleCartSections(true);

        const wasSelectAllChecked = selectAllCheckbox.checked;
        const previousSelection = new Set(selectedItemIds);

        cartItemsContainer.innerHTML = cartItems.map(item => {
            // Fix image URL path - ensure it works from html/ folder
            let imageUrl = item.image_url || '';
            if (imageUrl && !imageUrl.startsWith('http') && !imageUrl.startsWith('../')) {
                // If it's a relative path without ../, add ../
                imageUrl = '../' + imageUrl;
            }
            
            return `
            <div class="cart-item" data-id="${item.id}">
                <input type="checkbox" class="item-checkbox">
                <div class="item-info">
                    <img src="${imageUrl}" alt="${item.name}" class="item-image" onerror="this.src='../assets/img/logo.jpg'">
                    <div class="item-details">
                        <h4>${item.name}</h4>
                        <p>${item.specifications || ''}</p>
                    </div>
                </div>
                <div class="item-price">${formatPrice(item.price)}</div>
                <div class="item-quantity">
                    <button class="qty-btn" data-id="${item.id}" data-change="-1" type="button">-</button>
                    <input type="number" class="qty-input" value="${item.quantity}" min="1" data-id="${item.id}">
                    <button class="qty-btn" data-id="${item.id}" data-change="1" type="button">+</button>
                </div>
                <div class="item-total">${formatPrice(item.price * item.quantity)}</div>
                <button class="remove-btn" type="button" data-id="${item.id}" title="Xoa san pham">&times;</button>
            </div>
        `;
        }).join('');

        selectedItemIds = new Set();

        const checkboxes = cartItemsContainer.querySelectorAll('.item-checkbox');
        checkboxes.forEach(checkbox => {
            const id = checkbox.closest('.cart-item').dataset.id;
            const shouldCheck = previousSelection.size > 0 ? previousSelection.has(id) : false;
            if (shouldCheck) {
                checkbox.checked = true;
                selectedItemIds.add(id);
            }
        });

        if (wasSelectAllChecked && cartItems.length > 0 && selectedItemIds.size < cartItems.length) {
            selectedItemIds = new Set(cartItems.map(item => item.id));
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
        }

        updateCartSummary();
        syncCartIndicator();
    }

    async function updateQuantity(cartItemId, newQuantity) {
        const parsedQuantity = Number(newQuantity);
        if (!Number.isInteger(parsedQuantity) || parsedQuantity < 0) {
            return;
        }

        const targetItem = cartItems.find(item => item.id === cartItemId);
        if (!targetItem || targetItem.quantity === parsedQuantity) {
            return;
        }

        const previousCartItems = cartItems.map(item => ({ ...item }));
        const previousSelection = new Set(selectedItemIds);

        if (parsedQuantity === 0) {
            cartItems = cartItems.filter(item => item.id !== cartItemId);
            selectedItemIds.delete(cartItemId);
        } else {
            cartItems = cartItems.map(item =>
                item.id === cartItemId ? { ...item, quantity: parsedQuantity } : item
            );
        }

        renderCartItems();

        try {
            const response = await fetch('../api/update_cart_item.php', {
                credentials: 'include',
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    cart_item_id: cartItemId,
                    quantity: parsedQuantity
                })
            });

            if (response.status === 401) {
                if (window.authUtils) {
                    authUtils.clearUser();
                }
                renderNotLoggedIn();
                return;
            }

            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message || 'Cập nhật thất bại');
            }
        } catch (error) {
            console.error('Failed to update quantity:', error);
            cartItems = previousCartItems;
            selectedItemIds = previousSelection;
            renderCartItems();
            alert('Lỗi: Không thể cập nhật số lượng.');
        }
    }

    async function removeItem(cartItemId) {
        const previousCartItems = cartItems.map(item => ({ ...item }));
        const previousSelection = new Set(selectedItemIds);

        cartItems = cartItems.filter(item => item.id !== cartItemId);
        selectedItemIds.delete(cartItemId);
        renderCartItems();

        try {
            const response = await fetch('../api/remove_from_cart.php', {
                credentials: 'include',
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart_item_id: cartItemId })
            });

            if (response.status === 401) {
                if (window.authUtils) {
                    authUtils.clearUser();
                }
                renderNotLoggedIn();
                return;
            }

            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message || 'Xóa thất bại');
            }
        } catch (error) {
            console.error('Failed to remove item:', error);
            cartItems = previousCartItems;
            selectedItemIds = previousSelection;
            renderCartItems();
            alert('Lỗi: Không thể xóa sản phẩm.');
        }
    }

    function updateCartSummary() {
        const selectedCheckboxes = cartItemsContainer.querySelectorAll('.item-checkbox:checked');
        const selectedItems = Array.from(selectedCheckboxes)
            .map(cb => {
                const id = cb.closest('.cart-item').dataset.id;
                return cartItems.find(item => item.id === id);
            })
            .filter(Boolean);

        selectedItemIds = new Set(selectedItems.map(item => item.id));

        const subtotal = selectedItems.reduce((sum, item) => sum + item.price * item.quantity, 0);
        const totalQuantity = selectedItems.reduce((sum, item) => sum + item.quantity, 0);

        const selectedCountEl = document.getElementById('selected-count');
        const subtotalEl = document.getElementById('subtotal');
        const totalEl = document.getElementById('total-amount');

        if (selectedCountEl) selectedCountEl.textContent = String(totalQuantity);
        if (subtotalEl) subtotalEl.textContent = formatPrice(subtotal);
        if (totalEl) totalEl.textContent = formatPrice(subtotal);

        const hasSelection = selectedItems.length > 0;
        checkoutBtn.disabled = !hasSelection;
        checkoutBtn.style.opacity = hasSelection ? '1' : '0.5';

        if (selectAllCheckbox) {
            const allCheckboxes = cartItemsContainer.querySelectorAll('.item-checkbox');
            if (!allCheckboxes.length) {
                selectAllCheckbox.checked = false;
            } else {
                const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
            }
        }
    }

    function proceedToCheckout() {
        if (!selectedItemIds.size) {
            alert('Vui lòng chọn sản phẩm để thanh toán.');
            return;
        }

        const checkoutItems = cartItems.filter(item => selectedItemIds.has(item.id));
        if (!checkoutItems.length) {
            alert('Vui lòng chọn sản phẩm để thanh toán.');
            return;
        }

        localStorage.setItem('checkoutItems', JSON.stringify(checkoutItems));
        window.location.href = 'dat-hang.html';
    }

    async function fetchCart() {
        // First check if user is logged in via authUtils
        const currentUser = window.authUtils?.getUser();
        if (!currentUser) {
            // Clear cart localStorage immediately if no user
            localStorage.removeItem('cartItems');
            // Update cart counter to 0
            if (typeof updateAllCartCounters === 'function') {
                updateAllCartCounters(0);
            }
            renderNotLoggedIn();
            return;
        }
        
        try {
            const response = await fetch('../api/get_cart.php', { credentials: 'include' });

            if (!response.ok) {
                localStorage.removeItem('cartItems');
                if (typeof updateAllCartCounters === 'function') {
                    updateAllCartCounters(0);
                }
                renderNotLoggedIn();
                return;
            }

            const result = await response.json();
            
            // Check if user is not logged in
            if (result.success && result.data.logged_in === false) {
                localStorage.removeItem('cartItems');
                if (typeof updateAllCartCounters === 'function') {
                    updateAllCartCounters(0);
                }
                renderNotLoggedIn();
                return;
            }
            
            if (result.success && result.data && Array.isArray(result.data.cart)) {
                const previousSelection = new Set(selectedItemIds);
                cartItems = result.data.cart.map(item => ({
                    ...item,
                    id: String(item.id),
                    quantity: Math.max(1, Number(item.quantity) || 1),
                    price: Number(item.price) || 0,
                    image_url: item.image_url || '',
                    specifications: item.specifications || ''
                }));
                selectedItemIds = new Set(cartItems.filter(item => previousSelection.has(item.id)).map(item => item.id));
                renderCartItems();
            } else {
                renderEmptyCart();
            }
        } catch (error) {
            console.error('Failed to fetch cart:', error);
            cartItems = [];
            selectedItemIds = new Set();
            toggleCartSections(false);
            cartItemsContainer.innerHTML = `
                <div class="empty-cart">
                    <p>Không thể tải giỏ hàng. Vui lòng thử lại.</p>
                </div>
            `;
            syncCartIndicator();
        }
    }

    selectAllCheckbox.addEventListener('change', () => {
        const shouldSelectAll = selectAllCheckbox.checked;
        const allCheckboxes = cartItemsContainer.querySelectorAll('.item-checkbox');
        allCheckboxes.forEach(checkbox => {
            checkbox.checked = shouldSelectAll;
        });
        updateCartSummary();
    });

    checkoutBtn.addEventListener('click', proceedToCheckout);

    cartItemsContainer.addEventListener('click', (event) => {
        const target = event.target;
        const cartItem = target.closest('.cart-item');
        if (!cartItem) {
            return;
        }
        const cartItemId = cartItem.dataset.id;

        if (target.matches('.qty-btn')) {
            const change = Number(target.dataset.change);
            if (Number.isInteger(change)) {
                const item = cartItems.find(i => i.id === cartItemId);
                if (item) {
                    const nextQuantity = item.quantity + change;
                    if (nextQuantity > 0) {
                        updateQuantity(cartItemId, nextQuantity);
                    }
                }
            }
        }

        if (target.matches('.remove-btn')) {
            showConfirmModal('Bạn chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?').then(confirmed => {
                if (confirmed) {
                    removeItem(cartItemId);
                }
            });
        }
    });

    cartItemsContainer.addEventListener('change', (event) => {
        const target = event.target;
        if (target.matches('.item-checkbox')) {
            updateCartSummary();
            return;
        }

        if (target.matches('.qty-input')) {
            const cartItemId = target.closest('.cart-item').dataset.id;
            const newQuantity = Number(target.value);
            if (!Number.isNaN(newQuantity) && newQuantity > 0) {
                updateQuantity(cartItemId, newQuantity);
            } else {
                const item = cartItems.find(i => i.id === cartItemId);
                if (item) {
                    target.value = item.quantity;
                }
            }
        }
    });

    fetchCart();
});
