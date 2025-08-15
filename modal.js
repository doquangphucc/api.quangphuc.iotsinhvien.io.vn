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
                                        type="text" 
                                        class="datetime-input" 
                                        id="modal-date"
                                        placeholder="dd/mm/yyyy"
                                        maxlength="10"
                                    >
                                </div>
                                <div>
                                    <input 
                                        type="text" 
                                        class="datetime-input" 
                                        id="modal-time"
                                        placeholder="hh:mm"
                                        maxlength="5"
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
        
        // Setup date input formatting
        this.setupDateInput();

        // Attach custom date picker
        this.initCustomDatePicker();
        // Attach custom time picker
        this.initCustomTimePicker();
        // Enforce 24h time (native time input already 24h in most locales, but normalize value)
        const timeInput = modal.querySelector('#modal-time');
        // Input formatting while typing (hhmm -> hh:mm)
        timeInput.addEventListener('input', () => {
            let v = timeInput.value.replace(/\D/g,'').slice(0,4);
            if (v.length >= 3) v = v.slice(0,2)+':'+v.slice(2);
            timeInput.value = v;
        });
        timeInput.addEventListener('blur', () => this.normalizeTimeInput(timeInput));
        timeInput.addEventListener('focus', () => this.showTimePicker(timeInput.value));
        timeInput.addEventListener('click', () => this.showTimePicker(timeInput.value));
    }
    
    setupDateInput() {
        const dateInput = this.modal.querySelector('#modal-date');
        
        // Format input as user types
        dateInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
            
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2);
            }
            if (value.length >= 5) {
                value = value.substring(0, 5) + '/' + value.substring(5, 9);
            }
            
            e.target.value = value;
        });
        
        // Validate date on blur
        dateInput.addEventListener('blur', (e) => {
            this.validateDateInput(e.target);
        });
    }
    
    validateDateInput(input) {
        const value = input.value;
        if (!value) return true;
        
        const datePattern = /^(\d{2})\/(\d{2})\/(\d{4})$/;
        const match = value.match(datePattern);
        
        if (!match) {
            input.classList.add('error');
            return false;
        }
        
        const day = parseInt(match[1]);
        const month = parseInt(match[2]);
        const year = parseInt(match[3]);
        
        // Basic validation
        if (month < 1 || month > 12 || day < 1 || day > 31) {
            input.classList.add('error');
            return false;
        }
        
        // More detailed validation
        const date = new Date(year, month - 1, day);
        if (date.getFullYear() !== year || date.getMonth() !== month - 1 || date.getDate() !== day) {
            input.classList.add('error');
            return false;
        }
        
        input.classList.remove('error');
        return true;
    }

    // --- Custom Date Picker (scroll lists) ---
    initCustomDatePicker() {
        const dateInput = this.modal.querySelector('#modal-date');
        if (!dateInput) return;

        // Create overlay once
        if (!document.getElementById('custom-date-picker')) {
            const overlay = document.createElement('div');
            overlay.id = 'custom-date-picker';
            overlay.className = 'date-picker-overlay';
            overlay.innerHTML = `
                <div class="date-picker-panel">
                    <div class="date-picker-header">Chọn ngày</div>
                    <div class="date-wheel-wrapper">
                        <div class="date-wheel" data-unit="day"></div>
                        <div class="date-wheel" data-unit="month"></div>
                        <div class="date-wheel" data-unit="year"></div>
                    </div>
                    <div class="date-picker-actions">
                        <button type="button" class="date-picker-btn cancel">Hủy</button>
                        <button type="button" class="date-picker-btn confirm">Xác nhận</button>
                    </div>
                </div>`;
            document.body.appendChild(overlay);

            // Populate lists
            const dayWheel = overlay.querySelector('[data-unit="day"]');
            for (let d=1; d<=31; d++) dayWheel.appendChild(this.buildWheelItem(d));
            const monthWheel = overlay.querySelector('[data-unit="month"]');
            for (let m=1; m<=12; m++) monthWheel.appendChild(this.buildWheelItem(m));
            const yearWheel = overlay.querySelector('[data-unit="year"]');
            for (let y=2025; y<=2100; y++) yearWheel.appendChild(this.buildWheelItem(y));

            // Selection logic
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) this.hideDatePicker();
            });
            overlay.querySelector('.cancel').addEventListener('click', ()=>this.hideDatePicker());
            overlay.querySelector('.confirm').addEventListener('click', ()=>{
                const selDay = overlay.querySelector('[data-unit="day"] .selected');
                const selMonth = overlay.querySelector('[data-unit="month"] .selected');
                const selYear = overlay.querySelector('[data-unit="year"] .selected');
                if (selDay && selMonth && selYear) {
                    const day = selDay.dataset.value.padStart(2,'0');
                    const month = selMonth.dataset.value.padStart(2,'0');
                    const year = selYear.dataset.value;
                    dateInput.value = `${day}/${month}/${year}`;
                }
                this.validateDateInput(dateInput);
                this.hideDatePicker();
            });
        }

        dateInput.addEventListener('focus', ()=> this.showDatePicker(dateInput.value));
        dateInput.addEventListener('click', ()=> this.showDatePicker(dateInput.value));
    }

    buildWheelItem(value) {
        const div = document.createElement('div');
        div.className = 'date-wheel-item';
        div.textContent = value;
        div.dataset.value = String(value);
        div.addEventListener('click', () => {
            const parent = div.parentElement;
            parent.querySelectorAll('.date-wheel-item').forEach(i=>i.classList.remove('selected'));
            div.classList.add('selected');
        });
        return div;
    }

    showDatePicker(currentValue) {
        const overlay = document.getElementById('custom-date-picker');
        if (!overlay) return;
        overlay.classList.add('show');
        // Preselect
        let d=null,m=null,y=null;
        const match = currentValue && currentValue.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        if (match) { d = parseInt(match[1]); m = parseInt(match[2]); y = parseInt(match[3]); }
        this.preselectWheel(overlay.querySelector('[data-unit="day"]'), d || new Date().getDate());
        this.preselectWheel(overlay.querySelector('[data-unit="month"]'), m || (new Date().getMonth()+1));
        this.preselectWheel(overlay.querySelector('[data-unit="year"]'), y || new Date().getFullYear());
    }

    hideDatePicker() {
        const overlay = document.getElementById('custom-date-picker');
        if (overlay) overlay.classList.remove('show');
    }

    preselectWheel(wheel, value) {
        let target=null;
        wheel.querySelectorAll('.date-wheel-item').forEach(item => {
            if (parseInt(item.dataset.value) === value) target = item;
            item.classList.remove('selected');
        });
        if (target) {
            target.classList.add('selected');
            // Scroll into center view
            wheel.scrollTop = target.offsetTop - wheel.clientHeight/2 + target.clientHeight/2;
        }
    }

    // ---- TIME PICKER ----
    initCustomTimePicker() {
        if (document.getElementById('custom-time-picker')) return; // already
        const overlay = document.createElement('div');
        overlay.id = 'custom-time-picker';
        overlay.className = 'date-picker-overlay';
        overlay.innerHTML = `
            <div class="date-picker-panel">
                <div class="date-picker-header">Chọn thời gian</div>
                <div class="date-wheel-wrapper">
                    <div class="date-wheel" data-unit="hour"></div>
                    <div class="date-wheel" data-unit="minute"></div>
                </div>
                <div class="date-picker-actions">
                    <button type="button" class="date-picker-btn cancel">Hủy</button>
                    <button type="button" class="date-picker-btn confirm">Xác nhận</button>
                </div>
            </div>`;
        document.body.appendChild(overlay);
        const hourWheel = overlay.querySelector('[data-unit="hour"]');
        for (let h=0; h<24; h++) hourWheel.appendChild(this.buildWheelItem(String(h).padStart(2,'0')));
        const minuteWheel = overlay.querySelector('[data-unit="minute"]');
        for (let m=0; m<60; m++) minuteWheel.appendChild(this.buildWheelItem(String(m).padStart(2,'0')));

        overlay.addEventListener('click', e => { if (e.target === overlay) this.hideTimePicker(); });
        overlay.querySelector('.cancel').addEventListener('click', ()=> this.hideTimePicker());
        overlay.querySelector('.confirm').addEventListener('click', ()=> {
            const hSel = overlay.querySelector('[data-unit="hour"] .selected');
            const mSel = overlay.querySelector('[data-unit="minute"] .selected');
            const input = this.modal.querySelector('#modal-time');
            if (hSel && mSel) {
                input.value = `${hSel.dataset.value}:${mSel.dataset.value}`;
            }
            this.normalizeTimeInput(input);
            this.hideTimePicker();
        });
    }

    showTimePicker(currentValue) {
        const overlay = document.getElementById('custom-time-picker');
        if (!overlay) return;
        overlay.classList.add('show');
        let h=null, m=null;
        const match = currentValue && currentValue.match(/^(\d{2}):(\d{2})$/);
        if (match) { h=parseInt(match[1]); m=parseInt(match[2]); }
        this.preselectWheel(overlay.querySelector('[data-unit="hour"]'), h ?? new Date().getHours());
        this.preselectWheel(overlay.querySelector('[data-unit="minute"]'), m ?? new Date().getMinutes());
    }

    hideTimePicker() {
        const overlay = document.getElementById('custom-time-picker');
        if (overlay) overlay.classList.remove('show');
    }

    normalizeTimeInput(input) {
        if (!input.value) return;
        const m = input.value.match(/^(\d{1,2})(?::(\d{1,2}))?$/);
        if (m) {
            let hh = parseInt(m[1]);
            let mm = parseInt(m[2] ?? '0');
            if (hh>23) hh=23; if (mm>59) mm=59;
            input.value = `${String(hh).padStart(2,'0')}:${String(mm).padStart(2,'0')}`;
        }
    }
    
    // Convert dd/mm/yyyy to yyyy-mm-dd for database
    formatDateForDatabase(dateString) {
        if (!dateString) return null;
        
        const datePattern = /^(\d{2})\/(\d{2})\/(\d{4})$/;
        const match = dateString.match(datePattern);
        
        if (!match) return null;
        
        const day = match[1];
        const month = match[2]; 
        const year = match[3];
        
        return `${year}-${month}-${day}`;
    }
    
    // Convert yyyy-mm-dd to dd/mm/yyyy for display
    formatDateForDisplay(dateString) {
        if (!dateString) return '';
        
        const datePattern = /^(\d{4})-(\d{2})-(\d{2})$/;
        const match = dateString.match(datePattern);
        
        if (!match) return dateString;
        
        const year = match[1];
        const month = match[2];
        const day = match[3];
        
        return `${day}/${month}/${year}`;
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
        const contentEl = this.modal.querySelector('#modal-content');
        const descriptionEl = this.modal.querySelector('#modal-description');
        const categoryEl = this.modal.querySelector('#modal-category');
        const priorityEl = this.modal.querySelector('#modal-priority');
        const dateEl = this.modal.querySelector('#modal-date');
        const timeEl = this.modal.querySelector('#modal-time');
        
        const content = contentEl ? contentEl.value.trim() : '';
        const description = descriptionEl ? descriptionEl.value.trim() : '';
        const category = categoryEl ? categoryEl.value : '';
        const priority = priorityEl ? priorityEl.value : 'medium';
        const date = dateEl ? dateEl.value : '';
        const time = timeEl ? timeEl.value : '';
        
        // Convert date format from dd/mm/yyyy to yyyy-mm-dd
        const scheduledDate = this.formatDateForDatabase(date);
        
        const baseData = {
            content,
            description,
            category,
            priority,
            scheduled_date: scheduledDate,
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
                purchase_status: purchaseStatus,
                scheduled_date: scheduledDate,
                scheduled_time: time || null,
                target_date: scheduledDate // For backward compatibility
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

        // Validate date format
        const dateInput = this.modal.querySelector('#modal-date');
        if (dateInput.value && !this.validateDateInput(dateInput)) {
            isValid = false;
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
            if (data.scheduled_date) {
                // Convert yyyy-mm-dd to dd/mm/yyyy for display
                this.modal.querySelector('#modal-date').value = this.formatDateForDisplay(data.scheduled_date);
            }
            if (data.scheduled_time) this.modal.querySelector('#modal-time').value = data.scheduled_time;

            // Wish specific fields
            if (this.type === 'wish') {
                if (data.price) this.modal.querySelector('#modal-price').value = data.price;
                if (data.currency) this.modal.querySelector('#modal-currency').value = data.currency;
                if (data.product_url) this.modal.querySelector('#modal-url').value = data.product_url;
                if (data.purchase_status) this.modal.querySelector('#modal-purchase-status').value = data.purchase_status;
                
                // For wishes, also check target_date
                if (data.target_date && !data.scheduled_date) {
                    this.modal.querySelector('#modal-date').value = this.formatDateForDisplay(data.target_date);
                }
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
