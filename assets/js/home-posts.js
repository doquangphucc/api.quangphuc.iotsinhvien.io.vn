// Home Posts Dynamic Rendering
// Load and render home posts from database

// Load home posts from API
async function loadHomePosts() {
    try {
        const response = await fetch('api/get_home_posts_public.php');
        const data = await response.json();
        
        if (data.success && data.posts && data.posts.length > 0) {
            renderHomePosts(data.posts);
        } else {
            console.warn('No home posts found or API error');
        }
    } catch (error) {
        console.error('Error loading home posts:', error);
    }
}

// Render home posts to the solutions section
function renderHomePosts(posts) {
    const solutionsSection = document.getElementById('solutions');
    if (!solutionsSection) return;
    
    // Find the container where posts should be inserted
    let postsContainer = solutionsSection.querySelector('.home-posts-container');
    
    // If container doesn't exist, create it at the END of the container (after static posts)
    if (!postsContainer) {
        postsContainer = document.createElement('div');
        postsContainer.className = 'home-posts-container mt-20'; // Add top margin to separate from static posts
        
        // Append to the end of the main container (after static posts)
        const mainContainer = solutionsSection.querySelector('.container');
        if (mainContainer) {
            mainContainer.appendChild(postsContainer);
        }
    }
    
    // Clear existing content
    postsContainer.innerHTML = '';
    
    // Render each post
    posts.forEach((post, index) => {
        const postElement = createPostElement(post, index);
        postsContainer.appendChild(postElement);
    });
}

// Create a single post element
function createPostElement(post, index) {
    const div = document.createElement('div');
    // Simple spacing between posts - no background, no border, no shadow
    div.className = index > 0 ? 'mt-20' : '';
    
    // Use hex colors directly (default to green if not set)
    const highlightColor = post.highlight_color || '#3FA34D';
    const buttonColor = post.button_color || '#3FA34D';
    
    // Calculate hover color (slightly darker)
    const buttonHoverColor = darkenColor(buttonColor, 10);
    
    // Determine layout based on image position
    const isImageLeft = post.image_position === 'left';
    
    // Create features HTML
    let featuresHTML = '';
    if (post.features && post.features.length > 0) {
        featuresHTML = post.features.map(feature => `
            <li class="flex items-center gap-3">
                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-gray-700 dark:text-gray-200">${escapeHtml(feature.text)}</span>
            </li>
        `).join('');
    }
    
    // Content section HTML
    const contentHTML = `
        <div class="${isImageLeft ? 'order-1 lg:order-2' : ''}">
            ${post.highlight_text ? `<span style="color: ${highlightColor};" class="font-semibold uppercase tracking-wider">${escapeHtml(post.highlight_text)}</span>` : ''}
            <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-4 mb-6">${escapeHtml(post.title)}</h3>
            <p class="text-gray-600 dark:text-gray-300 mb-6">${escapeHtml(post.description)}</p>
            ${featuresHTML ? `<ul class="space-y-3 mb-8">${featuresHTML}</ul>` : ''}
            ${post.button_text && post.button_url ? `
                <a href="${escapeHtml(post.button_url)}" 
                   style="background-color: ${buttonColor};" 
                   onmouseover="this.style.backgroundColor='${buttonHoverColor}'" 
                   onmouseout="this.style.backgroundColor='${buttonColor}'"
                   class="inline-block text-white font-semibold px-8 py-3 rounded-full transition-all duration-300">
                    ${escapeHtml(post.button_text)}
                </a>
            ` : ''}
        </div>
    `;
    
    // Image section HTML
    const imageHTML = `
        <div class="${isImageLeft ? 'order-2 lg:order-1' : 'order-2'}">
            <img src="${escapeHtml(post.image_url)}" alt="${escapeHtml(post.title)}" class="rounded-3xl shadow-2xl w-full">
        </div>
    `;
    
    // Media Gallery HTML
    let mediaGalleryHTML = '';
    if (post.media_gallery && post.media_gallery.length > 0) {
        mediaGalleryHTML = `
            <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
                <h4 class="text-xl font-bold text-gray-800 dark:text-white mb-4">📸 Thư viện Media (${post.media_gallery.length})</h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        `;
        
        post.media_gallery.forEach((media, idx) => {
            if (media.type === 'image') {
                mediaGalleryHTML += `
                    <div class="rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-shadow cursor-pointer" onclick="openHomeMediaLightbox('${escapeHtml(media.url)}', 'image')">
                        <img src="${escapeHtml(media.url)}" alt="Media ${idx + 1}" class="w-full h-40 object-cover" onerror="this.parentElement.style.display='none'">
                    </div>
                `;
            } else if (media.type === 'video') {
                mediaGalleryHTML += `
                    <div class="rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-shadow relative cursor-pointer" onclick="openHomeMediaLightbox('${escapeHtml(media.url)}', 'video')">
                        <video class="w-full h-40 object-cover">
                            <source src="${escapeHtml(media.url)}" type="video/mp4">
                        </video>
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none bg-black bg-opacity-30">
                            <span class="text-white text-4xl">▶️</span>
                        </div>
                    </div>
                `;
            }
        });
        
        mediaGalleryHTML += `
                </div>
            </div>
        `;
    }
    
    // Complete post HTML
    div.innerHTML = `
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            ${isImageLeft ? imageHTML + contentHTML : contentHTML + imageHTML}
        </div>
        ${mediaGalleryHTML}
    `;
    
    return div;
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Darken a hex color by a percentage
function darkenColor(hex, percent) {
    // Remove # if present
    hex = hex.replace('#', '');
    
    // Convert to RGB
    let r = parseInt(hex.substring(0, 2), 16);
    let g = parseInt(hex.substring(2, 4), 16);
    let b = parseInt(hex.substring(4, 6), 16);
    
    // Darken
    r = Math.max(0, Math.floor(r * (100 - percent) / 100));
    g = Math.max(0, Math.floor(g * (100 - percent) / 100));
    b = Math.max(0, Math.floor(b * (100 - percent) / 100));
    
    // Convert back to hex
    return '#' + [r, g, b].map(x => x.toString(16).padStart(2, '0')).join('');
}

// Lightbox functions for home media gallery
function openHomeMediaLightbox(url, type) {
    let lightbox = document.getElementById('home-media-lightbox');
    
    // Create lightbox if it doesn't exist
    if (!lightbox) {
        lightbox = document.createElement('div');
        lightbox.id = 'home-media-lightbox';
        lightbox.className = 'fixed inset-0 bg-black bg-opacity-95 z-50 hidden items-center justify-center p-4';
        lightbox.onclick = closeHomeMediaLightbox;
        lightbox.innerHTML = `
            <button onclick="closeHomeMediaLightbox()" class="absolute top-4 right-4 text-white text-4xl hover:text-gray-300 z-10">&times;</button>
            <div class="max-w-6xl w-full h-full flex items-center justify-center" onclick="event.stopPropagation()">
                <img id="home-lightbox-image" class="hidden max-w-full max-h-full rounded-lg" alt="Media preview">
                <video id="home-lightbox-video" controls class="hidden max-w-full max-h-full rounded-lg">
                    <source src="" type="video/mp4">
                    Trình duyệt của bạn không hỗ trợ video.
                </video>
            </div>
        `;
        document.body.appendChild(lightbox);
    }
    
    const lightboxImage = document.getElementById('home-lightbox-image');
    const lightboxVideo = document.getElementById('home-lightbox-video');
    
    if (type === 'image') {
        lightboxImage.src = url;
        lightboxImage.classList.remove('hidden');
        lightboxVideo.classList.add('hidden');
        lightboxVideo.pause();
    } else if (type === 'video') {
        lightboxVideo.querySelector('source').src = url;
        lightboxVideo.load();
        lightboxVideo.classList.remove('hidden');
        lightboxImage.classList.add('hidden');
    }
    
    lightbox.classList.remove('hidden');
    lightbox.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeHomeMediaLightbox() {
    const lightbox = document.getElementById('home-media-lightbox');
    const lightboxVideo = document.getElementById('home-lightbox-video');
    
    if (lightbox) {
        lightbox.classList.add('hidden');
        lightbox.classList.remove('flex');
    }
    if (lightboxVideo) {
        lightboxVideo.pause();
    }
    document.body.style.overflow = 'auto';
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadHomePosts);
} else {
    loadHomePosts();
}

