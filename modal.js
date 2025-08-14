/**
 * Custom Modal Component
 * Hỗ trợ đầy đủ các trường cho tasks và wishes
 */

class CustomModal {
    constructor() {
        this.modal = null;
        this.currentModal = null;
        this.callbacks = {};
        this.type = 'task'; // 'task' hoặc 'wish'
        this.createModal();
    }

    createModal() {
        // Xóa modal cũ nếu có
        const existingModal = document.getElementById('custom-modal');
        if (existingModal) {
            existingModal.remove();
        }

        // Tạo modal HTML
        const modalHTML = `
            <div id="custom-modal" class="modal">
                <div class="modal-overlay"></div>
                <div class="modal-container">
                    <div class="modal-header">
                        <h2 class="modal-title" id="modal-title">Thêm mới</h2>
                        <button class="modal-close" type="button">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label" for="modal-content">Nội dung:</label>
                            <textarea 
                                class="form-input" 
                                id="modal-content" 
                                placeholder="Nhập nội dung..."
                                rows="2"
                            ></textarea>
                            <div class="error-message" id="content-error">Vui lòng nhập nội dung</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="modal-description">Mô tả chi tiết (tùy chọn):</label>
                            <textarea 
                                class="form-input" 
                                id="modal-description" 
                                placeholder="Mô tả chi tiết thêm..."
                                rows="2"
                            ></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Danh mục và ưu tiên:</label>
                            <div class="form-row">
                                <div class="form-col">
                                    <select class="form-input" id="modal-category">
                                        <option value="">-- Chọn danh mục --</option>
                                        <option value="work">Công việc</option>
                                        <option value="study">Học tập</option>
                                        <option value="personal">Cá nhân</option>
                                        <option value="health">Sức khỏe</option>
                                        <option value="hobby">Sở thích</option>
                                        <option value="family">Gia đình</option>
                                        <option value="other">Khác</option>
                                    </select>
                                </div>
                                <div class="form-col">
                                    <select class="form-input" id="modal-priority">
                                        <option value="low">🟢 Thấp</option>
                                        <option value="medium" selected>🟡 Trung bình</option>
                                        <option value="high">🔴 Cao</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Thời gian:</label>
                            <div class="datetime-group">
                                <div>
                                    <input 
                                        type="date" 
                                        class="datetime-input" 
                                        id="modal-date"
                                    >
                                </div>
                                <div>
                                    <input 
                                        type="time" 
                                        class="datetime-input" 
                                        id="modal-time"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="form-group" id="price-group" style="display: none;">
                            <label class="form-label" for="modal-price">Giá tiền (tùy chọn):</label>
                            <div class="form-row">
                                <div class="form-col-large">
                                    <input 
                                        type="number" 
                                        class="form-input" 
                                        id="modal-price" 
                                        placeholder="Nhập giá tiền..."
                                        min="0"
                                        step="1000"
                                    >
                                </div>
                                <div class="form-col-small">
                                    <select class="form-input" id="modal-currency">
                                        <option value="VND">VNĐ</option>
                                        <option value="USD">USD</option>
                                        <option value="EUR">EUR</option>
                                    </select>
                                </div>
                            </div>
                            <div class="error-message" id="price-error">Giá tiền không hợp lệ</div>
                        </div>

                        <div class="form-group" id="wish-extra-group" style="display: none;">
                            <label class="form-label" for="modal-url">Link sản phẩm (tùy chọn):</label>
                            <input 
                                type="url" 
                                class="form-input" 
                                id="modal-url" 
                                placeholder="https://..."
                            >
                            
                            <div style="margin-top: 15px;">
                                <label class="form-label" for="modal-purchase-status">Trạng thái mua sắm:</label>
                                <select class="form-input" id="modal-purchase-status">
                                    <option value="researching">🔍 Đang tìm hiểu</option>
                                    <option value="saving">💰 Đang tiết kiệm</option>
                                    <option value="ready_to_buy">✅ Sẵn sàng mua</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="modal-btn modal-btn-secondary" type="button" id="modal-cancel-btn">
                            Hủy
                        </button>
                        <button class="modal-btn modal-btn-primary" type="button" id="modal-confirm-btn">
                            Xác nhận
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Thêm modal vào DOM
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modal = document.getElementById('custom-modal');
        
        // Gán sự kiện
        this.setupEventListeners();
    }
    
    setupEventListeners() {
        const modal = this.modal;
        
        // Đóng modal khi click overlay
        modal.querySelector('.modal-overlay').addEventListener('click', () => this.close());
        
        // Đóng modal khi click nút close
        modal.querySelector('.modal-close').addEventListener('click', () => this.close());
        
        // Đóng modal khi click nút hủy
        modal.querySelector('#modal-cancel-btn').addEventListener('click', () => this.close());
        
        // Xác nhận khi click nút xác nhận
        modal.querySelector('#modal-confirm-btn').addEventListener('click', () => this.confirm());
    }

    // Hiển thị modal cho task
    showTask(title, callback) {
        this.type = 'task';
        this.show(title, callback);
        this.showTypeSpecificFields();
    }

    // Hiển thị modal cho wish
    showWish(title, callback) {
        this.type = 'wish';
        this.show(title, callback);
        this.showTypeSpecificFields();
    }

    // Hiển thị modal chung
    show(title, callback) {
        const modal = this.modal;
        const titleElement = modal.querySelector('#modal-title');
        const contentInput = modal.querySelector('#modal-content');

        titleElement.textContent = title;

        // Reset form
        this.resetForm();

        // Lưu callback
        this.callbacks.confirm = callback;

        // Hiển thị modal
        modal.classList.add('show');
        this.currentModal = modal;

        // Focus vào input đầu tiên
        setTimeout(() => {
            contentInput.focus();
        }, 300);

        // Xử lý ESC key
        document.addEventListener('keydown', this.handleKeyDown.bind(this));
    }

    // Hiển thị/ẩn các trường phù hợp với type
    showTypeSpecificFields() {
        const priceGroup = this.modal.querySelector('#price-group');
        const wishExtraGroup = this.modal.querySelector('#wish-extra-group');
        
        if (this.type === 'wish') {
            priceGroup.style.display = 'block';
            wishExtraGroup.style.display = 'block';
        } else {
            priceGroup.style.display = 'none';
            wishExtraGroup.style.display = 'none';
        }
    }

    // Xử lý phím tắt
    handleKeyDown(e) {
        if (e.key === 'Escape') {
            this.close();
        } else if (e.key === 'Enter' && e.ctrlKey) {
            this.confirm();
        }
    }

    // Đóng modal
    close() {
        if (!this.modal) return;
        
        const modal = this.modal;
        modal.classList.remove('show');
        this.currentModal = null;
        
        // Remove event listeners
        document.removeEventListener('keydown', this.handleKeyDown.bind(this));
        
        // Reset form sau khi animation hoàn thành
        setTimeout(() => {
            this.resetForm();
        }, 300);
    }

    // Thu thập dữ liệu từ form
    collectFormData() {
        const content = this.modal.querySelector('#modal-content').value.trim();
        const description = this.modal.querySelector('#modal-description').value.trim();
        const category = this.modal.querySelector('#modal-category').value;
        const priority = this.modal.querySelector('#modal-priority').value;
        const date = this.modal.querySelector('#modal-date').value;
        const time = this.modal.querySelector('#modal-time').value;
        
        const baseData = {
            content,
            description,
            category,
            priority,
            scheduled_date: date || null,
            scheduled_time: time || null
        };

        if (this.type === 'wish') {
            const price = this.modal.querySelector('#modal-price').value;
            const currency = this.modal.querySelector('#modal-currency').value;
            const productUrl = this.modal.querySelector('#modal-url').value.trim();
            const purchaseStatus = this.modal.querySelector('#modal-purchase-status').value;

            return {
                ...baseData,
                price: price ? parseFloat(price) : null,
                currency: currency || 'VND',
                product_url: productUrl || null,
                purchase_status: purchaseStatus
            };
        }

        return baseData;
    }

    // Xác nhận và trả về dữ liệu
    confirm() {
        // Validate
        if (!this.validateForm()) {
            return;
        }

        // Thu thập dữ liệu
        const data = this.collectFormData();

        // Gọi callback
        if (this.callbacks.confirm) {
            this.callbacks.confirm(data);
        }

        // Đóng modal
        this.close();
    }

    // Validate form
    validateForm() {
        let isValid = true;
        
        // Validate content
        const content = this.modal.querySelector('#modal-content').value.trim();
        const contentInput = this.modal.querySelector('#modal-content');
        const contentError = this.modal.querySelector('#content-error');
        
        if (!content) {
            contentInput.classList.add('error');
            contentError.classList.add('show');
            isValid = false;
        } else {
            contentInput.classList.remove('error');
            contentInput.classList.add('success');
            contentError.classList.remove('show');
        }

        // Validate price nếu có hiển thị
        const priceGroup = this.modal.querySelector('#price-group');
        if (priceGroup.style.display !== 'none') {
            const price = this.modal.querySelector('#modal-price').value;
            const priceInput = this.modal.querySelector('#modal-price');
            const priceError = this.modal.querySelector('#price-error');
            
            if (price && (isNaN(price) || parseFloat(price) < 0)) {
                priceInput.classList.add('error');
                priceError.classList.add('show');
                isValid = false;
            } else {
                priceInput.classList.remove('error');
                priceError.classList.remove('show');
            }
        }

        // Validate URL nếu có nhập
        const urlInput = this.modal.querySelector('#modal-url');
        if (urlInput && urlInput.value.trim()) {
            try {
                new URL(urlInput.value.trim());
                urlInput.classList.remove('error');
            } catch {
                urlInput.classList.add('error');
                isValid = false;
            }
        }

        return isValid;
    }

    // Reset form
    resetForm() {
        const inputs = this.modal.querySelectorAll('.form-input, .datetime-input');
        inputs.forEach(input => {
            if (input.tagName === 'SELECT') {
                // Reset select về giá trị mặc định
                if (input.id === 'modal-priority') {
                    input.value = 'medium';
                } else if (input.id === 'modal-currency') {
                    input.value = 'VND';
                } else if (input.id === 'modal-purchase-status') {
                    input.value = 'researching';
                } else {
                    input.selectedIndex = 0;
                }
            } else {
                input.value = '';
            }
            input.classList.remove('error', 'success');
        });

        const errors = this.modal.querySelectorAll('.error-message');
        errors.forEach(error => {
            error.classList.remove('show');
        });
    }

    // Pre-fill data cho chế độ edit
    fillData(data) {
        if (!data) return;

        setTimeout(() => {
            if (data.content) this.modal.querySelector('#modal-content').value = data.content;
            if (data.description) this.modal.querySelector('#modal-description').value = data.description;
            if (data.category) this.modal.querySelector('#modal-category').value = data.category;
            if (data.priority) this.modal.querySelector('#modal-priority').value = data.priority;
            if (data.scheduled_date) this.modal.querySelector('#modal-date').value = data.scheduled_date;
            if (data.scheduled_time) this.modal.querySelector('#modal-time').value = data.scheduled_time;

            // Wish specific fields
            if (this.type === 'wish') {
                if (data.price) this.modal.querySelector('#modal-price').value = data.price;
                if (data.currency) this.modal.querySelector('#modal-currency').value = data.currency;
                if (data.product_url) this.modal.querySelector('#modal-url').value = data.product_url;
                if (data.purchase_status) this.modal.querySelector('#modal-purchase-status').value = data.purchase_status;
            }
        }, 100);
    }
}

// Tạo instance global
const customModal = new CustomModal();

// Helper functions để sử dụng dễ dàng hơn
function showTaskModal(callback) {
    customModal.showTask("Thêm việc muốn làm", callback);
}

function showWishModal(callback) {
    customModal.showWish("Thêm đồ muốn mua", callback);
}

function showEditTaskModal(taskData, callback) {
    customModal.showTask("Sửa việc muốn làm", callback);
    customModal.fillData(taskData);
}

function showEditWishModal(wishData, callback) {
    customModal.showWish("Sửa đồ muốn mua", callback);
    customModal.fillData(wishData);
}

// Export cho module nếu cần
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { CustomModal, customModal };
}
