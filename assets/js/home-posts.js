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
    
    // Complete post HTML
    div.innerHTML = `
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            ${isImageLeft ? imageHTML + contentHTML : contentHTML + imageHTML}
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

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadHomePosts);
} else {
    loadHomePosts();
}

