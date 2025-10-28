// =====================================================
// MEDIA GALLERY MANAGER
// Quản lý nhiều ảnh và video cho Intro Posts, Projects, Home Posts
// =====================================================

class MediaGalleryManager {
    constructor(containerId, uploadApiUrl) {
        this.container = document.getElementById(containerId);
        this.uploadApiUrl = uploadApiUrl;
        this.mediaItems = [];
        this.nextOrder = 1;
        
        this.init();
    }
    
    init() {
        if (!this.container) {
            console.error('Media gallery container not found:', containerId);
            return;
        }
        
        this.render();
    }
    
    // Load media từ JSON string
    loadFromJSON(jsonString) {
        try {
            if (!jsonString) {
                this.mediaItems = [];
                return;
            }
            
            const data = JSON.parse(jsonString);
            this.mediaItems = Array.isArray(data) ? data : [];
            this.nextOrder = this.mediaItems.length > 0 
                ? Math.max(...this.mediaItems.map(m => m.order || 0)) + 1 
                : 1;
            this.render();
        } catch (error) {
            console.error('Error loading media gallery:', error);
            this.mediaItems = [];
        }
    }
    
    // Export ra JSON string
    toJSON() {
        return JSON.stringify(this.mediaItems);
    }
    
    // Thêm media item mới
    addMedia(type, url) {
        const item = {
            type: type, // 'image' hoặc 'video'
            url: url,
            order: this.nextOrder++
        };
        
        this.mediaItems.push(item);
        this.render();
    }
    
    // Xóa media item
    removeMedia(index) {
        this.mediaItems.splice(index, 1);
        this.render();
    }
    
    // Di chuyển media lên
    moveUp(index) {
        if (index > 0) {
            const temp = this.mediaItems[index];
            this.mediaItems[index] = this.mediaItems[index - 1];
            this.mediaItems[index - 1] = temp;
            this.render();
        }
    }
    
    // Di chuyển media xuống
    moveDown(index) {
        if (index < this.mediaItems.length - 1) {
            const temp = this.mediaItems[index];
            this.mediaItems[index] = this.mediaItems[index + 1];
            this.mediaItems[index + 1] = temp;
            this.render();
        }
    }
    
    // Render UI
    render() {
        if (!this.container) return;
        
        const containerId = this.container.id;
        
        this.container.innerHTML = `
            <div class="space-y-4">
                <!-- Upload Controls -->
                <div class="flex gap-2">
                    <button type="button" onclick="window.mediaGallery_${containerId}.openImageUpload()" 
                        class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center justify-center gap-2">
                        🖼️ Thêm Ảnh
                    </button>
                    <button type="button" onclick="window.mediaGallery_${containerId}.openVideoUpload()" 
                        class="flex-1 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 flex items-center justify-center gap-2">
                        🎥 Thêm Video
                    </button>
                </div>
                
                <!-- Hidden file inputs -->
                <input type="file" id="${containerId}_image_input" accept="image/*" class="hidden" 
                    onchange="window.mediaGallery_${containerId}.handleImageUpload(event)">
                <input type="file" id="${containerId}_video_input" accept="video/*" class="hidden" 
                    onchange="window.mediaGallery_${containerId}.handleVideoUpload(event)">
                
                <!-- Media List -->
                <div class="space-y-2" id="${containerId}_list">
                    ${this.renderMediaList()}
                </div>
                
                ${this.mediaItems.length === 0 ? 
                    '<p class="text-sm text-gray-500 text-center py-4">Chưa có ảnh hoặc video nào. Click nút trên để thêm.</p>' 
                    : ''}
            </div>
        `;
    }
    
    // Render danh sách media
    renderMediaList() {
        return this.mediaItems.map((item, index) => {
            const isImage = item.type === 'image';
            const icon = isImage ? '🖼️' : '🎥';
            const bgColor = isImage ? 'bg-blue-50' : 'bg-purple-50';
            
            return `
                <div class="flex items-center gap-2 p-3 ${bgColor} rounded-lg border">
                    <div class="flex-shrink-0 text-2xl">${icon}</div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold">${isImage ? 'Ảnh' : 'Video'} #${index + 1}</div>
                        <div class="text-xs text-gray-600 truncate">${item.url}</div>
                    </div>
                    <div class="flex gap-1 flex-shrink-0">
                        ${index > 0 ? 
                            `<button type="button" onclick="window.mediaGallery_${this.container.id}.moveUp(${index})" 
                                class="px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm" title="Di chuyển lên">
                                ⬆️
                            </button>` 
                            : '<div class="w-8"></div>'}
                        ${index < this.mediaItems.length - 1 ? 
                            `<button type="button" onclick="window.mediaGallery_${this.container.id}.moveDown(${index})" 
                                class="px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm" title="Di chuyển xuống">
                                ⬇️
                            </button>` 
                            : '<div class="w-8"></div>'}
                        <button type="button" onclick="window.mediaGallery_${this.container.id}.previewMedia(${index})" 
                            class="px-2 py-1 bg-green-500 hover:bg-green-600 text-white rounded text-sm" title="Xem trước">
                            👁️
                        </button>
                        <button type="button" onclick="window.mediaGallery_${this.container.id}.removeMedia(${index})" 
                            class="px-2 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-sm" title="Xóa">
                            🗑️
                        </button>
                    </div>
                </div>
            `;
        }).join('');
    }
    
    // Mở dialog upload ảnh
    openImageUpload() {
        document.getElementById(`${this.container.id}_image_input`).click();
    }
    
    // Mở dialog upload video
    openVideoUpload() {
        document.getElementById(`${this.container.id}_video_input`).click();
    }
    
    // Xử lý upload ảnh
    async handleImageUpload(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        // Validate file size (50MB)
        if (file.size > 50 * 1024 * 1024) {
            showToast('File ảnh quá lớn (tối đa 50MB)', 'error');
            event.target.value = '';
            return;
        }
        
        await this.uploadFile(file, 'image');
        event.target.value = ''; // Reset input
    }
    
    // Xử lý upload video
    async handleVideoUpload(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        // Validate file size (50MB)
        if (file.size > 50 * 1024 * 1024) {
            showToast('File video quá lớn (tối đa 50MB)', 'error');
            event.target.value = '';
            return;
        }
        
        await this.uploadFile(file, 'video');
        event.target.value = ''; // Reset input
    }
    
    // Upload file lên server
    async uploadFile(file, type) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('media_type', type);
        
        try {
            // Show loading
            showToast(`Đang upload ${type === 'image' ? 'ảnh' : 'video'}...`, 'info');
            
            const response = await fetch(this.uploadApiUrl, {
                method: 'POST',
                credentials: 'include',
                body: formData
            });
            
            if (response.status === 413) {
                showToast('File quá lớn. Vui lòng chọn file nhỏ hơn 50MB.', 'error');
                return;
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.addMedia(type, data.url);
                showToast(`Upload ${type === 'image' ? 'ảnh' : 'video'} thành công!`, 'success');
            } else {
                showToast(data.message || 'Lỗi khi upload file', 'error');
            }
        } catch (error) {
            console.error('Upload error:', error);
            showToast('Lỗi kết nối khi upload file', 'error');
        }
    }
    
    // Preview media
    previewMedia(index) {
        const item = this.mediaItems[index];
        if (!item) return;
        
        const modalId = 'mediaPreviewModal';
        let modal = document.getElementById(modalId);
        
        // Tạo modal nếu chưa có
        if (!modal) {
            modal = document.createElement('div');
            modal.id = modalId;
            modal.className = 'modal';
            modal.style.zIndex = '9999';
            document.body.appendChild(modal);
        }
        
        const isImage = item.type === 'image';
        const contentHtml = isImage 
            ? `<img src="${item.url}" alt="Preview" class="max-w-full max-h-[70vh] rounded-lg">`
            : `<video controls class="max-w-full max-h-[70vh] rounded-lg">
                   <source src="${item.url}" type="video/mp4">
                   Trình duyệt không hỗ trợ video.
               </video>`;
        
        modal.innerHTML = `
            <div class="bg-white rounded-lg p-6 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">${isImage ? '🖼️ Xem trước ảnh' : '🎥 Xem trước video'} #${index + 1}</h3>
                    <button onclick="document.getElementById('${modalId}').classList.remove('show')" 
                        class="text-gray-500 hover:text-gray-700 text-2xl">
                        ×
                    </button>
                </div>
                <div class="flex justify-center">
                    ${contentHtml}
                </div>
                <div class="mt-4 text-sm text-gray-600 break-all">
                    <strong>URL:</strong> ${item.url}
                </div>
            </div>
        `;
        
        modal.classList.add('show');
    }
}

// Global function to initialize media gallery
function initMediaGallery(containerId, uploadApiUrl) {
    const gallery = new MediaGalleryManager(containerId, uploadApiUrl);
    window['mediaGallery_' + containerId] = gallery;
    return gallery;
}

