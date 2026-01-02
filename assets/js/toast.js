/**
 * Custom Toast & Modal Notification System
 * Replaces ugly browser alert() and confirm() dialogs
 */

const Toast = {
    container: null,
    
    /**
     * Initialize toast container
     */
    init() {
        if (this.container) return;
        
        this.container = document.createElement('div');
        this.container.id = 'toast-container';
        this.container.innerHTML = '';
        document.body.appendChild(this.container);
        
        // Add styles if not already added
        if (!document.getElementById('toast-styles')) {
            const style = document.createElement('style');
            style.id = 'toast-styles';
            style.textContent = `
                #toast-container {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 99999;
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                    max-width: 400px;
                    pointer-events: none;
                }
                
                .toast {
                    display: flex;
                    align-items: flex-start;
                    gap: 12px;
                    padding: 16px 20px;
                    border-radius: 12px;
                    background: #fff;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.15), 0 2px 10px rgba(0,0,0,0.1);
                    transform: translateX(120%);
                    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                    pointer-events: auto;
                    max-width: 100%;
                    border-left: 4px solid #10b981;
                }
                
                .toast.show {
                    transform: translateX(0);
                }
                
                .toast.hiding {
                    transform: translateX(120%);
                    opacity: 0;
                }
                
                .toast-icon {
                    flex-shrink: 0;
                    width: 24px;
                    height: 24px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 14px;
                }
                
                .toast-content {
                    flex: 1;
                    min-width: 0;
                }
                
                .toast-title {
                    font-weight: 600;
                    font-size: 14px;
                    color: #1f2937;
                    margin-bottom: 2px;
                }
                
                .toast-message {
                    font-size: 13px;
                    color: #6b7280;
                    word-wrap: break-word;
                }
                
                .toast-close {
                    flex-shrink: 0;
                    width: 24px;
                    height: 24px;
                    border: none;
                    background: transparent;
                    cursor: pointer;
                    color: #9ca3af;
                    font-size: 18px;
                    line-height: 1;
                    transition: color 0.2s;
                    padding: 0;
                }
                
                .toast-close:hover {
                    color: #374151;
                }
                
                /* Toast types */
                .toast.success {
                    border-left-color: #10b981;
                }
                .toast.success .toast-icon {
                    background: #d1fae5;
                    color: #10b981;
                }
                
                .toast.error {
                    border-left-color: #ef4444;
                }
                .toast.error .toast-icon {
                    background: #fee2e2;
                    color: #ef4444;
                }
                
                .toast.warning {
                    border-left-color: #f59e0b;
                }
                .toast.warning .toast-icon {
                    background: #fef3c7;
                    color: #f59e0b;
                }
                
                .toast.info {
                    border-left-color: #3b82f6;
                }
                .toast.info .toast-icon {
                    background: #dbeafe;
                    color: #3b82f6;
                }
                
                /* Progress bar */
                .toast-progress {
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    height: 3px;
                    background: currentColor;
                    opacity: 0.3;
                    border-radius: 0 0 0 12px;
                }
                
                /* Modal styles */
                .modal-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0, 0, 0, 0.5);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 99999;
                    opacity: 0;
                    visibility: hidden;
                    transition: all 0.3s ease;
                    padding: 20px;
                }
                
                .modal-overlay.show {
                    opacity: 1;
                    visibility: visible;
                }
                
                .modal-dialog {
                    background: #fff;
                    border-radius: 16px;
                    box-shadow: 0 25px 50px rgba(0,0,0,0.25);
                    max-width: 400px;
                    width: 100%;
                    transform: scale(0.9) translateY(-20px);
                    transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                    overflow: hidden;
                }
                
                .modal-overlay.show .modal-dialog {
                    transform: scale(1) translateY(0);
                }
                
                .modal-header {
                    padding: 24px 24px 0;
                    text-align: center;
                }
                
                .modal-icon {
                    width: 64px;
                    height: 64px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 16px;
                    font-size: 28px;
                }
                
                .modal-icon.confirm {
                    background: #fef3c7;
                    color: #f59e0b;
                }
                
                .modal-icon.danger {
                    background: #fee2e2;
                    color: #ef4444;
                }
                
                .modal-icon.info {
                    background: #dbeafe;
                    color: #3b82f6;
                }
                
                .modal-icon.success {
                    background: #d1fae5;
                    color: #10b981;
                }
                
                .modal-title {
                    font-size: 18px;
                    font-weight: 600;
                    color: #1f2937;
                    margin: 0;
                }
                
                .modal-body {
                    padding: 12px 24px 24px;
                    text-align: center;
                }
                
                .modal-message {
                    font-size: 14px;
                    color: #6b7280;
                    line-height: 1.6;
                    margin: 0;
                }
                
                .modal-footer {
                    padding: 0 24px 24px;
                    display: flex;
                    gap: 12px;
                    justify-content: center;
                }
                
                .modal-btn {
                    flex: 1;
                    padding: 12px 24px;
                    border-radius: 10px;
                    font-size: 14px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s;
                    border: none;
                    max-width: 160px;
                }
                
                .modal-btn-cancel {
                    background: #f3f4f6;
                    color: #374151;
                }
                
                .modal-btn-cancel:hover {
                    background: #e5e7eb;
                }
                
                .modal-btn-confirm {
                    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                    color: #fff;
                }
                
                .modal-btn-confirm:hover {
                    transform: translateY(-1px);
                    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
                }
                
                .modal-btn-danger {
                    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                    color: #fff;
                }
                
                .modal-btn-danger:hover {
                    transform: translateY(-1px);
                    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
                }
                
                /* Responsive */
                @media (max-width: 480px) {
                    #toast-container {
                        left: 10px;
                        right: 10px;
                        max-width: none;
                    }
                    
                    .modal-dialog {
                        margin: 10px;
                    }
                    
                    .modal-footer {
                        flex-direction: column;
                    }
                    
                    .modal-btn {
                        max-width: none;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    },
    
    /**
     * Get icon for toast type
     */
    getIcon(type) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        return icons[type] || icons.info;
    },
    
    /**
     * Show a toast notification
     */
    show(message, type = 'info', duration = 4000, title = null) {
        this.init();
        
        const titles = {
            success: 'Thành công',
            error: 'Lỗi',
            warning: 'Cảnh báo',
            info: 'Thông báo'
        };
        
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="toast-icon">${this.getIcon(type)}</div>
            <div class="toast-content">
                <div class="toast-title">${title || titles[type]}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">×</button>
        `;
        
        this.container.appendChild(toast);
        
        // Trigger animation
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });
        
        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                toast.classList.add('hiding');
                setTimeout(() => toast.remove(), 400);
            }, duration);
        }
        
        return toast;
    },
    
    /**
     * Shorthand methods
     */
    success(message, title = null, duration = 4000) {
        return this.show(message, 'success', duration, title);
    },
    
    error(message, title = null, duration = 5000) {
        return this.show(message, 'error', duration, title);
    },
    
    warning(message, title = null, duration = 4500) {
        return this.show(message, 'warning', duration, title);
    },
    
    info(message, title = null, duration = 4000) {
        return this.show(message, 'info', duration, title);
    }
};

/**
 * Custom Modal/Confirm Dialog
 */
const Modal = {
    /**
     * Remove all existing modal overlays
     */
    cleanup() {
        document.querySelectorAll('.modal-overlay').forEach(el => el.remove());
    },
    
    /**
     * Show a confirm dialog
     * @returns {Promise<boolean>}
     */
    confirm(message, options = {}) {
        // Cleanup any existing modals first
        this.cleanup();
        
        return new Promise((resolve) => {
            const {
                title = 'Xác nhận',
                confirmText = 'Xác nhận',
                cancelText = 'Hủy',
                type = 'confirm', // confirm, danger, info, success
                icon = null
            } = options;
            
            const iconMap = {
                confirm: '?',
                danger: '!',
                info: 'ℹ',
                success: '✓'
            };
            
            const overlay = document.createElement('div');
            overlay.className = 'modal-overlay';
            overlay.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-header">
                        <div class="modal-icon ${type}">${icon || iconMap[type]}</div>
                        <h3 class="modal-title">${title}</h3>
                    </div>
                    <div class="modal-body">
                        <p class="modal-message">${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button class="modal-btn modal-btn-cancel">${cancelText}</button>
                        <button class="modal-btn ${type === 'danger' ? 'modal-btn-danger' : 'modal-btn-confirm'}">${confirmText}</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(overlay);
            
            // Show animation
            requestAnimationFrame(() => {
                overlay.classList.add('show');
            });
            
            // Handle buttons
            const [cancelBtn, confirmBtn] = overlay.querySelectorAll('.modal-btn');
            
            let closed = false;
            const close = (result) => {
                if (closed) return; // Prevent multiple calls
                closed = true;
                overlay.classList.remove('show');
                // Remove immediately after short animation
                setTimeout(() => {
                    if (overlay.parentNode) overlay.remove();
                    resolve(result);
                }, 200);
            };
            
            cancelBtn.onclick = () => close(false);
            confirmBtn.onclick = () => close(true);
            
            // Close on overlay click
            overlay.onclick = (e) => {
                if (e.target === overlay) close(false);
            };
            
            // Close on Escape key
            const handleKey = (e) => {
                if (e.key === 'Escape') {
                    document.removeEventListener('keydown', handleKey);
                    close(false);
                }
            };
            document.addEventListener('keydown', handleKey);
            
            // Focus confirm button
            confirmBtn.focus();
        });
    },
    
    /**
     * Show an alert dialog (single OK button)
     * @returns {Promise<void>}
     */
    alert(message, options = {}) {
        // Cleanup any existing modals first
        this.cleanup();
        
        return new Promise((resolve) => {
            const {
                title = 'Thông báo',
                okText = 'Đã hiểu',
                type = 'info'
            } = options;
            
            const iconMap = {
                info: 'ℹ',
                success: '✓',
                warning: '⚠',
                error: '✕'
            };
            
            const overlay = document.createElement('div');
            overlay.className = 'modal-overlay';
            overlay.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-header">
                        <div class="modal-icon ${type}">${iconMap[type]}</div>
                        <h3 class="modal-title">${title}</h3>
                    </div>
                    <div class="modal-body">
                        <p class="modal-message">${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button class="modal-btn modal-btn-confirm">${okText}</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(overlay);
            
            requestAnimationFrame(() => {
                overlay.classList.add('show');
            });
            
            let closed = false;
            const close = () => {
                if (closed) return; // Prevent multiple calls
                closed = true;
                overlay.classList.remove('show');
                setTimeout(() => {
                    if (overlay.parentNode) overlay.remove();
                    resolve();
                }, 200);
            };
            
            overlay.querySelector('.modal-btn').onclick = close;
            overlay.onclick = (e) => {
                if (e.target === overlay) close();
            };
            
            const handleKey = (e) => {
                if (e.key === 'Escape' || e.key === 'Enter') {
                    document.removeEventListener('keydown', handleKey);
                    close();
                }
            };
            document.addEventListener('keydown', handleKey);
            
            overlay.querySelector('.modal-btn').focus();
        });
    }
};

/**
 * Global helper functions to replace native alert/confirm
 */
window.showToast = (message, type = 'info') => Toast.show(message, type);
window.showSuccess = (message) => Toast.success(message);
window.showError = (message) => Toast.error(message);
window.showWarning = (message) => Toast.warning(message);
window.showInfo = (message) => Toast.info(message);

// Custom confirm that returns a Promise
window.customConfirm = (message, options) => Modal.confirm(message, options);
window.customAlert = (message, options) => Modal.alert(message, options);

// Override native alert for gradual migration (optional)
// Uncomment if you want to auto-replace all alerts
// window.nativeAlert = window.alert;
// window.alert = (message) => Toast.warning(message);

console.log('Toast & Modal notification system loaded');
