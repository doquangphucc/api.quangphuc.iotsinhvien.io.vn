// Home Posts Dynamic Rendering
// Load and render home posts from database

// Color mapping for Tailwind classes
const colorMap = {
    'green': { text: 'text-green-600', bg: 'bg-green-600', hover: 'hover:bg-green-700' },
    'blue': { text: 'text-blue-600', bg: 'bg-blue-600', hover: 'hover:bg-blue-700' },
    'red': { text: 'text-red-600', bg: 'bg-red-600', hover: 'hover:bg-red-700' },
    'yellow': { text: 'text-yellow-600', bg: 'bg-yellow-600', hover: 'hover:bg-yellow-700' },
    'purple': { text: 'text-purple-600', bg: 'bg-purple-600', hover: 'hover:bg-purple-700' },
    'orange': { text: 'text-orange-600', bg: 'bg-orange-600', hover: 'hover:bg-orange-700' },
    'amber': { text: 'text-amber-600', bg: 'bg-amber-600', hover: 'hover:bg-amber-700' }
};

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
    
    // If container doesn't exist, create it after the header
    if (!postsContainer) {
        postsContainer = document.createElement('div');
        postsContainer.className = 'home-posts-container';
        
        // Find the header div
        const header = solutionsSection.querySelector('.text-center.mb-12');
        if (header) {
            header.after(postsContainer);
        } else {
            solutionsSection.querySelector('.container').appendChild(postsContainer);
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
    div.className = index > 0 ? 'mt-16' : '';
    
    const highlightColor = colorMap[post.highlight_color] || colorMap['green'];
    const buttonColor = colorMap[post.button_color] || colorMap['green'];
    
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
            ${post.highlight_text ? `<span class="${highlightColor.text} font-semibold uppercase tracking-wider">${escapeHtml(post.highlight_text)}</span>` : ''}
            <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-4 mb-6">${escapeHtml(post.title)}</h3>
            <p class="text-gray-600 dark:text-gray-300 mb-6">${escapeHtml(post.description)}</p>
            ${featuresHTML ? `<ul class="space-y-3 mb-8">${featuresHTML}</ul>` : ''}
            ${post.button_text && post.button_url ? `
                <a href="${escapeHtml(post.button_url)}" class="inline-block ${buttonColor.bg} text-white font-semibold px-8 py-3 rounded-full ${buttonColor.hover} transition-all duration-300">
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

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadHomePosts);
} else {
    loadHomePosts();
}

