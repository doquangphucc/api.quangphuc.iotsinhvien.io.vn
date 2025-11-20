// Home Posts Dynamic Rendering
// Load and render home posts from database

const HOME_POSTS_API_URL = (function() {
    const origin = window.location && window.location.origin;
    if (origin && origin !== 'null' && origin !== 'file://') {
        return `${origin.replace(/\/$/, '')}/api/get_home_posts_public.php`;
    }
    return 'https://api.quangphuc.iotsinhvien.io.vn/api/get_home_posts_public.php';
})();

// Load home posts from API
async function loadHomePosts() {
    try {
        const response = await fetch(HOME_POSTS_API_URL, { cache: 'no-cache' });
        if (!response.ok) {
            throw new Error(`Server responded ${response.status}`);
        }
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
    const dynamicSection = document.getElementById('home-dynamic-section');
    if (!dynamicSection) return;

    let postsContainer = dynamicSection.querySelector('.home-posts-container');
    if (!postsContainer) {
        postsContainer = document.createElement('div');
        postsContainer.className = 'home-posts-container space-y-20';
        const container = dynamicSection.querySelector('.container') || dynamicSection;
        container.appendChild(postsContainer);
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
    div.className = index > 0 ? 'mt-16' : '';
    
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
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:${highlightColor};">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-gray-700 dark:text-gray-200">${escapeHtml(feature.text)}</span>
            </li>
        `).join('');
    }
    
    // Content section HTML
    const contentHTML = `
        <div class="${isImageLeft ? 'order-1 lg:order-2' : ''}">
            ${post.highlight_text ? `
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold tracking-[0.2em] uppercase" style="color:${highlightColor};background-color:${highlightColor}1a">
                    <span class="h-2 w-2 rounded-full" style="background-color:${highlightColor};"></span>
                    ${escapeHtml(post.highlight_text)}
                </div>` : ''}
            <h3 class="home-post-title text-3xl sm:text-4xl lg:text-5xl font-extrabold mt-5 mb-6 leading-tight">${escapeHtml(post.title)}</h3>
            <p class="text-lg text-gray-700 dark:text-gray-200 mb-6 leading-relaxed">${escapeHtml(post.description)}</p>
            ${featuresHTML ? `<div class="bg-white/70 dark:bg-slate-900/40 border border-white/50 dark:border-white/5 rounded-2xl p-6 mb-8 shadow-inner">
                    <ul class="space-y-3">${featuresHTML}</ul>
                </div>` : ''}
            ${post.button_text && post.button_url ? `
                <a href="${escapeHtml(post.button_url)}" 
                   style="background-color: ${buttonColor};" 
                   onmouseover="this.style.backgroundColor='${buttonHoverColor}'" 
                   onmouseout="this.style.backgroundColor='${buttonColor}'"
                   class="inline-flex items-center gap-3 text-white font-semibold px-8 py-4 rounded-full transition-all duration-300 shadow-lg text-lg">
                    ${escapeHtml(post.button_text)}
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            ` : ''}
        </div>
    `;
    
    // Image section HTML
    const imageHTML = `
        <div class="${isImageLeft ? 'order-2 lg:order-1' : 'order-2'} flex items-center justify-center">
            <div class="relative w-full max-w-md lg:max-w-lg">
                <div class="absolute inset-0 bg-gradient-to-br from-white/60 to-transparent rounded-[32px] blur-2xl"></div>
                <img src="${escapeHtml(post.image_url)}" alt="${escapeHtml(post.title)}" class="rounded-[32px] shadow-2xl w-full object-cover border border-white/60 dark:border-white/5">
            </div>
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
        <div class="relative p-1 rounded-[40px] bg-gradient-to-br from-white/40 via-white/10 to-transparent dark:from-white/10 dark:via-white/5 dark:to-transparent">
            <div class="rounded-[36px] bg-white/90 dark:bg-slate-900/70 border border-white/40 dark:border-white/5 shadow-2xl overflow-hidden">
                <div class="grid lg:grid-cols-2 gap-12 items-center p-8 sm:p-12">
                    ${isImageLeft ? imageHTML + contentHTML : contentHTML + imageHTML}
                </div>
                ${mediaGalleryHTML}
            </div>
        </div>
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

