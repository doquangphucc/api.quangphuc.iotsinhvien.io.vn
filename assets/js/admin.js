// Admin Panel JavaScript
const API_BASE = '../api';
let categoriesData = [];
let productsData = [];
let rewardTemplatesData = [];
let usersData = [];
let ticketsData = [];

// Products Functions
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

function renderProducts(products) {
    const tbody = document.getElementById('products-tbody');
    tbody.innerHTML = products.map(p => `
        <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-3">${p.id}</td>
            <td class="px-4 py-3">
                ${p.image_url ? `<img src="../${p.image_url}" class="h-12 w-12 object-cover rounded">` : '-'}
            </td>
            <td class="px-4 py-3 font-semibold">${p.name}</td>
            <td class="px-4 py-3">${p.category_name}</td>
            <td class="px-4 py-3 text-right">${formatCurrency(p.price)}</td>
            <td class="px-4 py-3 text-right">${p.price_installation ? formatCurrency(p.price_installation) : '-'}</td>
            <td class="px-4 py-3 text-center">
                <span class="px-3 py-1 rounded-full text-sm ${p.is_available ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                    ${p.is_available ? 'Còn hàng' : 'Hết hàng'}
                </span>
            </td>
            <td class="px-4 py-3 text-center">
                <button onclick="editProduct(${p.id})" class="text-blue-600 hover:underline mr-2">Sửa</button>
                <button onclick="deleteProduct(${p.id})" class="text-red-600 hover:underline">Xóa</button>
            </td>
        </tr>
    `).join('');
}

function openProductModal(id = null) {
    const modal = document.getElementById('productModal');
    const form = document.getElementById('productForm');
    form.reset();
    
    if (id) {
        const product = productsData.find(p => p.id == id);
        if (product) {
            document.getElementById('product_id').value = product.id;
            document.getElementById('product_category_id').value = product.category_id;
            document.getElementById('product_name').value = product.name;
            document.getElementById('product_brand').value = product.brand || '';
            document.getElementById('product_model').value = product.model || '';
            document.getElementById('product_price').value = product.price;
            document.getElementById('product_price_installation').value = product.price_installation || '';
            document.getElementById('product_image_url').value = product.image_url || '';
            document.getElementById('product_description').value = product.description || '';
            document.getElementById('product_specifications').value = product.specifications || '';
            document.getElementById('product_is_available').checked = product.is_available == 1;
            document.getElementById('productModalTitle').textContent = 'Sửa sản phẩm';
        }
    } else {
        document.getElementById('productModalTitle').textContent = 'Thêm sản phẩm';
    }
    
    modal.classList.add('show');
}

function closeProductModal() {
    document.getElementById('productModal').classList.remove('show');
}

async function saveProduct(event) {
    event.preventDefault();
    const formData = {
        id: document.getElementById('product_id').value || 0,
        category_id: parseInt(document.getElementById('product_category_id').value),
        name: document.getElementById('product_name').value,
        brand: document.getElementById('product_brand').value,
        model: document.getElementById('product_model').value,
        price: parseFloat(document.getElementById('product_price').value),
        price_installation: document.getElementById('product_price_installation').value || null,
        image_url: document.getElementById('product_image_url').value,
        description: document.getElementById('product_description').value,
        specifications: document.getElementById('product_specifications').value,
        is_available: document.getElementById('product_is_available').checked
    };

    try {
        const response = await fetch(`${API_BASE}/admin/save_product.php`, {credentials: 'include', 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        const data = await response.json();
        if (data.success) {
            alert(data.message);
            closeProductModal();
            loadProducts();
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error saving product:', error);
        alert('Có lỗi xảy ra');
    }
}

function editProduct(id) {
    openProductModal(id);
}

async function deleteProduct(id) {
    if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;
    
    try {
        const response = await fetch(`${API_BASE}/admin/delete_product.php`, {credentials: 'include', 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await response.json();
        alert(data.message);
        if (data.success) loadProducts();
    } catch (error) {
        console.error('Error deleting product:', error);
        alert('Có lỗi xảy ra');
    }
}

// Orders Functions
async function loadOrders() {
    const status = document.getElementById('order-status-filter')?.value || '';
    try {
        const response = await fetch(`${API_BASE}/admin/get_orders.php?status=${status}&t=${Date.now()}`, {credentials: 'include'});
        const data = await response.json();
        if (data.success) {
            renderOrders(data.orders);
        }
    } catch (error) {
        console.error('Error loading orders:', error);
    }
}

function renderOrders(orders) {
    const container = document.getElementById('orders-list');
    if (orders.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-500 py-8">Không có đơn hàng nào</p>';
        return;
    }
    
    container.innerHTML = orders.map(order => `
        <div class="border rounded-lg p-4 mb-4 ${order.order_status === 'pending' ? 'bg-yellow-50 border-yellow-200' : 'bg-white'}">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <div class="font-bold text-lg">Đơn hàng #${order.id}</div>
                    <div class="text-sm text-gray-600">
                        <span>Khách hàng: ${order.full_name}</span> | 
                        <span>SĐT: ${order.phone}</span>
                    </div>
                    <div class="text-sm text-gray-600">
                        Địa chỉ: ${order.address}, ${order.district}, ${order.city}
                    </div>
                    <div class="text-sm text-gray-500 mt-1">
                        ${new Date(order.created_at).toLocaleString('vi-VN')}
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-xl text-green-600">${formatCurrency(order.total_amount)}</div>
                    <span class="px-3 py-1 rounded-full text-sm inline-block mt-2 ${getOrderStatusClass(order.order_status)}">
                        ${getOrderStatusText(order.order_status)}
                    </span>
                </div>
            </div>
            
            <div class="border-t pt-3 mb-3">
                <div class="font-semibold mb-2">Chi tiết sản phẩm:</div>
                ${order.items.map(item => `
                    <div class="flex items-center gap-3 mb-2 text-sm">
                        ${item.image_url ? `<img src="../${item.image_url}" class="h-12 w-12 object-cover rounded">` : ''}
                        <div class="flex-1">
                            <div class="font-semibold">${item.product_name}</div>
                            <div class="text-gray-600">Số lượng: ${item.quantity} x ${formatCurrency(item.price)}</div>
                        </div>
                    </div>
                `).join('')}
            </div>
            
            <div class="mt-4 flex gap-2">
                ${order.order_status === 'pending' ? `
                    <button onclick="approveOrder(${order.id})" class="flex-1 bg-green-600 text-white py-2 rounded-lg hover:bg-green-700">
                        ✓ Duyệt đơn hàng & Tặng vé quay
                    </button>
                ` : ''}
                <div class="${order.order_status === 'pending' ? 'flex-1' : 'w-full'}">
                    <select onchange="updateOrderStatus(${order.id}, this.value)" class="w-full px-4 py-2 border rounded-lg">
                        <option value="pending" ${order.order_status === 'pending' ? 'selected' : ''}>Chờ xử lý</option>
                        <option value="approved" ${order.order_status === 'approved' ? 'selected' : ''}>Đã duyệt</option>
                        <option value="processing" ${order.order_status === 'processing' ? 'selected' : ''}>Đang xử lý</option>
                        <option value="shipping" ${order.order_status === 'shipping' ? 'selected' : ''}>Đang giao hàng</option>
                        <option value="shipped" ${order.order_status === 'shipped' ? 'selected' : ''}>Đã giao hàng</option>
                        <option value="delivered" ${order.order_status === 'delivered' ? 'selected' : ''}>Đã nhận hàng</option>
                        <option value="cancelled" ${order.order_status === 'cancelled' ? 'selected' : ''}>Đã hủy</option>
                    </select>
                </div>
            </div>
        </div>
    `).join('');
}

function getOrderStatusClass(status) {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        'approved': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        'processing': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
        'shipping': 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-300',
        'shipped': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
        'delivered': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        'cancelled': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
    };
    return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
}

function getOrderStatusText(status) {
    const texts = {
        'pending': 'Chờ xử lý',
        'approved': 'Đã duyệt',
        'processing': 'Đang xử lý',
        'shipping': 'Đang giao hàng',
        'shipped': 'Đã giao hàng',
        'delivered': 'Đã nhận hàng',
        'cancelled': 'Đã hủy'
    };
    return texts[status] || 'Không xác định';
}

async function approveOrder(orderId) {
    if (!confirm('Duyệt đơn hàng này? Khách hàng sẽ nhận được 1 vé quay may mắn.')) return;
    
    try {
        const response = await fetch(`${API_BASE}/admin/approve_order.php`, {credentials: 'include', 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId })
        });
        const data = await response.json();
        alert(data.message);
        if (data.success) loadOrders();
    } catch (error) {
        console.error('Error approving order:', error);
        alert('Có lỗi xảy ra');
    }
}

async function updateOrderStatus(orderId, newStatus) {
    if (!confirm('Thay đổi trạng thái đơn hàng?')) {
        // Reset dropdown to current status
        loadOrders();
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE}/admin/update_order_status.php`, {
            credentials: 'include', 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, status: newStatus })
        });
        const data = await response.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) loadOrders();
    } catch (error) {
        console.error('Error updating order status:', error);
        showToast('Có lỗi xảy ra khi cập nhật trạng thái', 'error');
        loadOrders();
    }
}

// Tickets Functions
async function loadTickets() {
    try {
        const response = await fetch(`${API_BASE}/admin/get_tickets.php?t=${Date.now()}`, {credentials: 'include'});
        const data = await response.json();
        if (data.success) {
            ticketsData = data.tickets;
            renderTickets(data.tickets);
        }
    } catch (error) {
        console.error('Error loading tickets:', error);
    }
}

function renderTickets(tickets) {
    const tbody = document.getElementById('tickets-tbody');
    tbody.innerHTML = tickets.map(t => `
        <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-3">${t.id}</td>
            <td class="px-4 py-3">
                <div class="font-semibold">${t.full_name}</div>
                <div class="text-sm text-gray-600">${t.username} | ${t.phone}</div>
            </td>
            <td class="px-4 py-3">
                <span class="px-2 py-1 rounded text-sm ${getTicketTypeClass(t.ticket_type)}">
                    ${getTicketTypeText(t.ticket_type)}
                </span>
            </td>
            <td class="px-4 py-3">${t.pre_assigned_reward_name || '-'}</td>
            <td class="px-4 py-3 text-center">
                <span class="px-3 py-1 rounded-full text-sm ${getTicketStatusClass(t.status)}">
                    ${getTicketStatusText(t.status)}
                </span>
            </td>
            <td class="px-4 py-3">${new Date(t.created_at).toLocaleString('vi-VN')}</td>
            <td class="px-4 py-3 text-center">
                <button onclick="editTicket(${t.id})" class="text-blue-600 hover:underline mr-2">Sửa</button>
                <button onclick="deleteTicket(${t.id})" class="text-red-600 hover:underline">Xóa</button>
            </td>
        </tr>
    `).join('');
}

function getTicketTypeClass(type) {
    const classes = {
        'purchase': 'bg-green-100 text-green-800',
        'bonus': 'bg-blue-100 text-blue-800',
        'promotion': 'bg-purple-100 text-purple-800'
    };
    return classes[type] || 'bg-gray-100 text-gray-800';
}

function getTicketTypeText(type) {
    const texts = {
        'purchase': 'Mua hàng',
        'bonus': 'Khuyến mãi',
        'promotion': 'Sự kiện'
    };
    return texts[type] || type;
}

function getTicketStatusClass(status) {
    const classes = {
        'active': 'bg-green-100 text-green-800',
        'used': 'bg-gray-100 text-gray-800',
        'expired': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function getTicketStatusText(status) {
    const texts = {
        'active': 'Hoạt động',
        'used': 'Đã dùng',
        'expired': 'Hết hạn'
    };
    return texts[status] || status;
}

function openTicketModal(id = null) {
    const modal = document.getElementById('ticketModal');
    const form = document.getElementById('ticketForm');
    form.reset();
    updateTicketUsers();
    updateTicketRewards();
    
    if (id) {
        // Load ticket data
        const ticket = ticketsData.find(t => t.id == id);
        if (ticket) {
            document.getElementById('ticket_id').value = ticket.id;
            document.getElementById('ticket_user_id').value = ticket.user_id;
            document.getElementById('ticket_type').value = ticket.ticket_type;
            document.getElementById('ticket_status').value = ticket.status;
            document.getElementById('ticket_pre_assigned_reward_id').value = ticket.pre_assigned_reward_id || '';
            document.getElementById('ticketModalTitle').textContent = 'Sửa vé';
        }
    } else {
        document.getElementById('ticketModalTitle').textContent = 'Thêm vé';
    }
    
    modal.classList.add('show');
}

function closeTicketModal() {
    document.getElementById('ticketModal').classList.remove('show');
}

function updateTicketUsers() {
    const select = document.getElementById('ticket_user_id');
    select.innerHTML = usersData.map(u => 
        `<option value="${u.id}">${u.full_name} (${u.username})</option>`
    ).join('');
}

function updateTicketRewards() {
    const select = document.getElementById('ticket_pre_assigned_reward_id');
    select.innerHTML = '<option value="">Không set sẵn</option>' + 
        rewardTemplatesData.filter(r => r.is_active).map(r => 
            `<option value="${r.id}">${r.reward_name}</option>`
        ).join('');
}

async function saveTicket(event) {
    event.preventDefault();
    const ticketId = document.getElementById('ticket_id').value;
    const formData = {
        id: ticketId ? parseInt(ticketId) : null,
        user_id: parseInt(document.getElementById('ticket_user_id').value),
        ticket_type: document.getElementById('ticket_type').value,
        status: document.getElementById('ticket_status').value,
        pre_assigned_reward_id: document.getElementById('ticket_pre_assigned_reward_id').value || null
    };

    try {
        const response = await fetch(`${API_BASE}/admin/save_ticket.php`, {credentials: 'include', 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        const data = await response.json();
        if (data.success) {
            alert(data.message);
            closeTicketModal();
            loadTickets();
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error saving ticket:', error);
        alert('Có lỗi xảy ra');
    }
}

function editTicket(id) {
    // Implement edit
    openTicketModal(id);
}

async function deleteTicket(id) {
    if (!confirm('Bạn có chắc muốn xóa vé này?')) return;
    
    try {
        const response = await fetch(`${API_BASE}/admin/delete_ticket.php`, {credentials: 'include', 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await response.json();
        alert(data.message);
        if (data.success) loadTickets();
    } catch (error) {
        console.error('Error deleting ticket:', error);
        alert('Có lỗi xảy ra');
    }
}

// Rewards Functions
async function loadRewards() {
    try {
        const response = await fetch(`${API_BASE}/admin/get_reward_templates.php?t=${Date.now()}`, {credentials: 'include'});
        const data = await response.json();
        if (data.success) {
            rewardTemplatesData = data.templates;
            renderRewards(data.templates);
        }
    } catch (error) {
        console.error('Error loading rewards:', error);
    }
}

function renderRewards(rewards) {
    const container = document.getElementById('rewards-list');
    container.innerHTML = rewards.map(r => `
        <div class="bg-white border-2 rounded-lg p-4 ${r.is_active ? 'border-green-200' : 'border-gray-200'}">
            <div class="flex justify-between items-start mb-3">
                <div class="font-bold text-lg">${r.reward_name}</div>
                <span class="px-3 py-1 rounded-full text-sm ${getRewardTypeClass(r.reward_type)}">
                    ${getRewardTypeText(r.reward_type)}
                </span>
            </div>
            ${r.reward_value ? `<div class="text-2xl font-bold text-green-600 mb-2">${formatCurrency(r.reward_value)}</div>` : ''}
            <div class="text-sm text-gray-600 mb-3">${r.reward_description || '-'}</div>
            ${r.reward_quantity ? `<div class="text-sm text-gray-600 mb-3">Số lượng: ${r.reward_quantity}</div>` : ''}
            <div class="flex gap-2 mt-3">
                <button onclick="editReward(${r.id})" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 text-sm">
                    Sửa
                </button>
                <button onclick="deleteReward(${r.id})" class="flex-1 bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 text-sm">
                    Xóa
                </button>
            </div>
        </div>
    `).join('');
}

function getRewardTypeClass(type) {
    const classes = {
        'voucher': 'bg-purple-100 text-purple-800',
        'cash': 'bg-green-100 text-green-800',
        'gift': 'bg-blue-100 text-blue-800'
    };
    return classes[type] || 'bg-gray-100 text-gray-800';
}

function getRewardTypeText(type) {
    const texts = {
        'voucher': 'Voucher',
        'cash': 'Tiền mặt',
        'gift': 'Quà tặng'
    };
    return texts[type] || type;
}

function openRewardModal(id = null) {
    const modal = document.getElementById('rewardModal');
    const form = document.getElementById('rewardForm');
    form.reset();
    
    if (id) {
        const reward = rewardTemplatesData.find(r => r.id == id);
        if (reward) {
            document.getElementById('reward_id').value = reward.id;
            document.getElementById('reward_name').value = reward.reward_name;
            document.getElementById('reward_type').value = reward.reward_type;
            document.getElementById('reward_value').value = reward.reward_value || '';
            document.getElementById('reward_description').value = reward.reward_description || '';
            document.getElementById('reward_quantity').value = reward.reward_quantity || '';
            document.getElementById('reward_is_active').checked = reward.is_active == 1;
            document.getElementById('rewardModalTitle').textContent = 'Sửa phần thưởng';
            updateRewardFields();
        }
    } else {
        document.getElementById('rewardModalTitle').textContent = 'Thêm phần thưởng';
        updateRewardFields();
    }
    
    modal.classList.add('show');
}

function closeRewardModal() {
    document.getElementById('rewardModal').classList.remove('show');
}

function updateRewardFields() {
    const type = document.getElementById('reward_type').value;
    const valueField = document.getElementById('reward_value_field');
    const quantityField = document.getElementById('reward_quantity_field');
    
    if (type === 'gift') {
        valueField.style.display = 'none';
        quantityField.style.display = 'block';
    } else {
        valueField.style.display = 'block';
        quantityField.style.display = 'none';
    }
}

async function saveReward(event) {
    event.preventDefault();
    const formData = {
        id: document.getElementById('reward_id').value || 0,
        reward_name: document.getElementById('reward_name').value,
        reward_type: document.getElementById('reward_type').value,
        reward_value: document.getElementById('reward_value').value || null,
        reward_description: document.getElementById('reward_description').value,
        reward_quantity: document.getElementById('reward_quantity').value || null,
        is_active: document.getElementById('reward_is_active').checked
    };

    console.log('Sending reward data:', formData);
    
    try {
        const response = await fetch(`${API_BASE}/admin/save_reward_template.php`, {credentials: 'include', 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        
        console.log('Response status:', response.status);
        
        const data = await response.json();
        console.log('Response data:', data);
        
        if (data.success) {
            alert(data.message);
            closeRewardModal();
            loadRewards();
            loadRewardTemplates(); // Reload for dropdowns
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error saving reward:', error);
        console.error('Error details:', error.message);
        alert('Có lỗi xảy ra: ' + error.message);
    }
}

function editReward(id) {
    openRewardModal(id);
}

async function deleteReward(id) {
    if (!confirm('Bạn có chắc muốn xóa phần thưởng này?')) return;
    
    try {
        const response = await fetch(`${API_BASE}/admin/delete_reward_template.php`, {credentials: 'include', 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await response.json();
        alert(data.message);
        if (data.success) {
            loadRewards();
            loadRewardTemplates();
        }
    } catch (error) {
        console.error('Error deleting reward:', error);
        alert('Có lỗi xảy ra');
    }
}

// Load users
async function loadUsers() {
    try {
        const response = await fetch(`${API_BASE}/admin/get_users.php?t=${Date.now()}`, {credentials: 'include'});
        const data = await response.json();
        if (data.success) {
            usersData = data.users;
        }
    } catch (error) {
        console.error('Error loading users:', error);
    }
}

// Load reward templates for dropdowns
async function loadRewardTemplates() {
    try {
        const response = await fetch(`${API_BASE}/admin/get_reward_templates.php?t=${Date.now()}`, {credentials: 'include'});
        const data = await response.json();
        if (data.success) {
            rewardTemplatesData = data.templates;
        }
    } catch (error) {
        console.error('Error loading reward templates:', error);
    }
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
}

