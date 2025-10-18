/**
 * Cache Buster - Minimal and Safe Version
 * Prevents browser from showing cached content when navigating back
 * VERSION 3.0 - Ultra Safe Implementation
 */

// Only apply cache busting to very specific dynamic pages
const DYNAMIC_PAGES = ['gio-hang.html', 'dat-hang.html', 'user_profile.html', 'order_history.html', 'survey_history.html'];

// Method 1: Force reload when page is shown from cache (SAFE)
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        console.log('Page loaded from cache, forcing reload...');
        // Use setTimeout to prevent navigation conflicts
        setTimeout(() => {
            window.location.reload();
        }, 100);
    }
});

// Method 2: Refresh dynamic content without full page reload (SAFE)
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

// Method 3: Handle browser back/forward navigation (SAFER)
window.addEventListener('popstate', function(event) {
    console.log('Browser navigation detected, refreshing dynamic content...');
    // Instead of full reload, just refresh dynamic content
    refreshDynamicContent();
});

// Method 4: Add cache-busting only to current page if it's dynamic (SAFER)
function addCacheBusterToCurrentPage() {
    const currentPage = window.location.pathname;
    const isDynamicPage = DYNAMIC_PAGES.some(page => currentPage.includes(page));
    
    if (isDynamicPage && !window.location.search.includes('nocache=')) {
        const url = new URL(window.location);
        url.searchParams.set('nocache', new Date().getTime());
        window.history.replaceState({}, '', url);
    }
}

// Method 5: Prevent caching for dynamic pages only (SAFER)
function preventCaching() {
    const currentPage = window.location.pathname;
    const isDynamicPage = DYNAMIC_PAGES.some(page => currentPage.includes(page));
    
    if (isDynamicPage) {
        const metaTags = [
            '<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, proxy-revalidate">',
            '<meta http-equiv="Pragma" content="no-cache">',
            '<meta http-equiv="Expires" content="0">'
        ];
        
        metaTags.forEach(tag => {
            const httpEquiv = tag.match(/http-equiv="([^"]+)"/)[1];
            if (!document.querySelector(`meta[http-equiv="${httpEquiv}"]`)) {
                document.head.insertAdjacentHTML('beforeend', tag);
            }
        });
    }
}

// Initialize cache busting when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Cache buster initialized (v3.0 - ultra safe version)');
    
    // Add cache buster to current page (only for dynamic pages)
    addCacheBusterToCurrentPage();
    
    // Prevent caching (only for dynamic pages)
    preventCaching();
    
    // Refresh dynamic content periodically (every 60 seconds)
    setInterval(refreshDynamicContent, 60000);
});

// Export functions for manual use
window.CacheBuster = {
    addCacheBusterToCurrentPage,
    preventCaching,
    refreshDynamicContent
};