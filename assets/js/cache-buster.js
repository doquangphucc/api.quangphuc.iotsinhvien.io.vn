/**
 * Cache Buster - Prevents browser from showing cached content when navigating back
 * This ensures fresh content is always loaded when users navigate back/forward
 */

// Method 1: Force reload when page is shown from cache
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        console.log('Page loaded from cache, forcing reload...');
        window.location.reload();
    }
});

// Method 2: Add cache-busting parameter to prevent caching
function addCacheBuster() {
    const url = new URL(window.location);
    const timestamp = new Date().getTime();
    
    // Only add cache buster if not already present
    if (!url.searchParams.has('nocache')) {
        url.searchParams.set('nocache', timestamp);
        window.history.replaceState({}, '', url);
    }
}

// Method 3: Prevent caching for dynamic pages
function preventCaching() {
    // Add meta tags to prevent caching
    const metaTags = [
        '<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, proxy-revalidate">',
        '<meta http-equiv="Pragma" content="no-cache">',
        '<meta http-equiv="Expires" content="0">'
    ];
    
    metaTags.forEach(tag => {
        if (!document.querySelector(`meta[http-equiv="${tag.match(/http-equiv="([^"]+)"/)[1]}"]`)) {
            document.head.insertAdjacentHTML('beforeend', tag);
        }
    });
}

// Method 4: Handle browser back/forward navigation
window.addEventListener('popstate', function(event) {
    console.log('Browser navigation detected, reloading page...');
    window.location.reload();
});

// Method 5: Force reload on focus (when user returns to tab)
window.addEventListener('focus', function() {
    // Only reload if page was loaded more than 5 seconds ago
    const pageLoadTime = performance.timing.navigationStart;
    const currentTime = Date.now();
    
    if (currentTime - pageLoadTime > 5000) {
        console.log('Tab focused after delay, checking for updates...');
        // Check if content needs refresh (optional)
        refreshDynamicContent();
    }
});

// Method 6: Refresh dynamic content without full page reload
function refreshDynamicContent() {
    // Refresh cart counter
    if (typeof fetchCartCount === 'function') {
        fetchCartCount();
    }
    
    // Refresh user info if logged in
    if (typeof checkAuthStatus === 'function') {
        checkAuthStatus();
    }
    
    // Refresh lottery ticket count if on user profile
    if (typeof loadLotteryTicketCount === 'function') {
        loadLotteryTicketCount();
    }
}

// Method 7: Add cache-busting to all internal links
function addCacheBusterToLinks() {
    const links = document.querySelectorAll('a[href]');
    links.forEach(link => {
        const href = link.getAttribute('href');
        
        // Only add to internal links (not external URLs)
        if (href && !href.startsWith('http') && !href.startsWith('mailto:') && !href.startsWith('tel:')) {
            link.addEventListener('click', function(e) {
                const url = new URL(href, window.location.origin);
                url.searchParams.set('nocache', new Date().getTime());
                link.href = url.toString();
            });
        }
    });
}

// Initialize cache busting when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Cache buster initialized');
    
    // Add cache buster to current page
    addCacheBuster();
    
    // Prevent caching
    preventCaching();
    
    // Add cache buster to links
    addCacheBusterToLinks();
    
    // Refresh dynamic content periodically (every 30 seconds)
    setInterval(refreshDynamicContent, 30000);
});

// Export functions for manual use
window.CacheBuster = {
    addCacheBuster,
    preventCaching,
    refreshDynamicContent,
    addCacheBusterToLinks
};
