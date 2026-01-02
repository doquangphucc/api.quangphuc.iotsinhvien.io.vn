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
                    background: #ffffff !important;
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
                
                /* Modal styles - High specificity to override page styles */
                .modal-overlay,
                div.modal-overlay,
                body .modal-overlay {
                    position: fixed !important;
                    top: 0 !important;
                    left: 0 !important;
                    right: 0 !important;
                    bottom: 0 !important;
                    background: rgba(0, 0, 0, 0.6) !important;
                    backdrop-filter: none !important;
                    -webkit-backdrop-filter: none !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    z-index: 999999 !important;
                    opacity: 0;
                    visibility: hidden;
                    transition: all 0.3s ease;
                    padding: 20px !important;
                    border: none !important;
                    box-shadow: none !important;
                }
                
                .modal-overlay.show,
                div.modal-overlay.show,
                body .modal-overlay.show {
                    opacity: 1 !important;
                    visibility: visible !important;
                }
                
                .modal-dialog,
                div.modal-dialog,
                .modal-overlay .modal-dialog,
                body .modal-overlay .modal-dialog {
                    background: #ffffff !important;
                    backdrop-filter: none !important;
                    -webkit-backdrop-filter: none !important;
                    border-radius: 16px !important;
                    box-shadow: 0 25px 50px rgba(0,0,0,0.25) !important;
                    max-width: 400px !important;
                    width: 100% !important;
                    transform: scale(0.9) translateY(-20px);
                    transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                    overflow: hidden !important;
                    border: none !important;
                }
                
                .modal-overlay.show .modal-dialog,
                div.modal-overlay.show .modal-dialog,
                body .modal-overlay.show .modal-dialog {
                    transform: scale(1) translateY(0) !important;
                }
                
                .modal-header,
                .modal-dialog .modal-header,
                .modal-overlay .modal-dialog .modal-header {
                    padding: 24px 24px 0 !important;
                    text-align: center !important;
                    background: transparent !important;
                    backdrop-filter: none !important;
                    border: none !important;
                }
                
                .modal-icon,
                .modal-dialog .modal-icon {
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
                
                .modal-title,
                .modal-dialog .modal-title {
                    font-size: 18px !important;
                    font-weight: 600 !important;
                    color: #1f2937 !important;
                    margin: 0 !important;
                    background: transparent !important;
                    border: none !important;
                }
                
                .modal-body,
                .modal-dialog .modal-body,
                .modal-overlay .modal-dialog .modal-body {
                    padding: 12px 24px 24px !important;
                    text-align: center !important;
                    background: transparent !important;
                    backdrop-filter: none !important;
                    border: none !important;
                }
                
                .modal-message,
                .modal-dialog .modal-message {
                    font-size: 14px !important;
                    color: #6b7280 !important;
                    line-height: 1.6 !important;
                    margin: 0 !important;
                    background: transparent !important;
                    border: none !important;
                }
                
                .modal-footer,
                .modal-dialog .modal-footer,
                .modal-overlay .modal-dialog .modal-footer {
                    padding: 0 24px 24px !important;
                    display: flex !important;
                    gap: 12px !important;
                    justify-content: center !important;
                    background: transparent !important;
                    backdrop-filter: none !important;
                    border: none !important;
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
            overlay.style.cssText = 'position:fixed!important;top:0!important;left:0!important;right:0!important;bottom:0!important;background:rgba(0,0,0,0.6)!important;backdrop-filter:none!important;display:flex!important;align-items:center!important;justify-content:center!important;z-index:999999!important;padding:20px!important;border:none!important;box-shadow:none!important;';
            
            const dialogStyles = 'background:#ffffff!important;backdrop-filter:none!important;border-radius:16px!important;box-shadow:0 25px 50px rgba(0,0,0,0.25)!important;max-width:400px!important;width:100%!important;overflow:hidden!important;border:none!important;';
            const headerStyles = 'padding:24px 24px 0!important;text-align:center!important;background:transparent!important;backdrop-filter:none!important;border:none!important;';
            const bodyStyles = 'padding:12px 24px 24px!important;text-align:center!important;background:transparent!important;backdrop-filter:none!important;border:none!important;';
            const footerStyles = 'padding:0 24px 24px!important;display:flex!important;gap:12px!important;justify-content:center!important;background:transparent!important;backdrop-filter:none!important;border:none!important;';
            
            overlay.innerHTML = `
                <div class="modal-dialog" style="${dialogStyles}">
                    <div class="modal-header" style="${headerStyles}">
                        <div class="modal-icon ${type}">${icon || iconMap[type]}</div>
                        <h3 class="modal-title" style="background:transparent!important;border:none!important;">${title}</h3>
                    </div>
                    <div class="modal-body" style="${bodyStyles}">
                        <p class="modal-message" style="background:transparent!important;border:none!important;">${message}</p>
                    </div>
                    <div class="modal-footer" style="${footerStyles}">
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
            overlay.style.cssText = 'position:fixed!important;top:0!important;left:0!important;right:0!important;bottom:0!important;background:rgba(0,0,0,0.6)!important;backdrop-filter:none!important;display:flex!important;align-items:center!important;justify-content:center!important;z-index:999999!important;padding:20px!important;border:none!important;box-shadow:none!important;';
            
            const dialogStyles = 'background:#ffffff!important;backdrop-filter:none!important;border-radius:16px!important;box-shadow:0 25px 50px rgba(0,0,0,0.25)!important;max-width:400px!important;width:100%!important;overflow:hidden!important;border:none!important;';
            const headerStyles = 'padding:24px 24px 0!important;text-align:center!important;background:transparent!important;backdrop-filter:none!important;border:none!important;';
            const bodyStyles = 'padding:12px 24px 24px!important;text-align:center!important;background:transparent!important;backdrop-filter:none!important;border:none!important;';
            const footerStyles = 'padding:0 24px 24px!important;display:flex!important;gap:12px!important;justify-content:center!important;background:transparent!important;backdrop-filter:none!important;border:none!important;';
            
            overlay.innerHTML = `
                <div class="modal-dialog" style="${dialogStyles}">
                    <div class="modal-header" style="${headerStyles}">
                        <div class="modal-icon ${type}">${iconMap[type]}</div>
                        <h3 class="modal-title" style="background:transparent!important;border:none!important;">${title}</h3>
                    </div>
                    <div class="modal-body" style="${bodyStyles}">
                        <p class="modal-message" style="background:transparent!important;border:none!important;">${message}</p>
                    </div>
                    <div class="modal-footer" style="${footerStyles}">
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

// Cleanup any stale modals on page load (in case of errors)
window.cleanupModals = () => Modal.cleanup();

// Auto cleanup stale modals every 5 seconds (safety net)
setInterval(() => {
    // Only cleanup modals that are not visible (stuck ones)
    document.querySelectorAll('.modal-overlay:not(.show)').forEach(el => el.remove());
}, 5000);

console.log('Toast & Modal notification system loaded');
