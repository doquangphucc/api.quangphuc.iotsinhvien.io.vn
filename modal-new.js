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
                <div class="modal-overlay" onclick="customModal.close()"></div>
                <div class="modal-container">
                    <div class="modal-header">
                        <h2 class="modal-title" id="modal-title">Thêm mới</h2>
                        <button class="modal-close" onclick="customModal.close()">&times;</button>
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


                    </div>
                    <div class="modal-footer">
                        <button class="modal-btn modal-btn-secondary" onclick="customModal.close()">
                            Hủy
                        </button>
                        <button class="modal-btn modal-btn-primary" onclick="customModal.confirm()">
                            Xác nhận
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Thêm modal vào DOM
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modal = document.getElementById('custom-modal');
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
        // Không còn trường đặc biệt nào cho wish
        // Tất cả các trường đều dùng chung cho cả task và wish
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
        const modal = document.getElementById('custom-modal');
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
        const date = this.modal.querySelector('#modal-date').value;
        const time = this.modal.querySelector('#modal-time').value;
        
        return {
            content,
            description,
            scheduled_date: date || null,
            scheduled_time: time || null
        };
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

        return isValid;
    }

    // Reset form
    resetForm() {
        const inputs = this.modal.querySelectorAll('.form-input, .datetime-input');
        inputs.forEach(input => {
            input.value = '';
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
            if (data.scheduled_date) this.modal.querySelector('#modal-date').value = data.scheduled_date;
            if (data.scheduled_time) this.modal.querySelector('#modal-time').value = data.scheduled_time;
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
