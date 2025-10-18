/**
 * Cache Buster - Prevents browser from showing cached content when navigating back
 * This ensures fresh content is always loaded when users navigate back/forward
 * VERSION 2.0 - Fixed to prevent 404 errors
 */

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

// Method 3: Handle browser back/forward navigation (MODIFIED - SAFER)
window.addEventListener('popstate', function(event) {
    console.log('Browser navigation detected, refreshing dynamic content...');
    // Instead of full reload, just refresh dynamic content
    refreshDynamicContent();
});

// Method 4: Force reload on focus (when user returns to tab) - DISABLED
// This was too aggressive and causing 404 errors
/*
window.addEventListener('focus', function() {
    const pageLoadTime = performance.timing.navigationStart;
    const currentTime = Date.now();
    
    if (currentTime - pageLoadTime > 5000) {
        console.log('Tab focused after delay, checking for updates...');
        refreshDynamicContent();
    }
});
*/

// Method 5: Add cache-busting to all internal links (MODIFIED - SAFER)
function addCacheBusterToLinks() {
    const links = document.querySelectorAll('a[href]');
    links.forEach(link => {
        const href = link.getAttribute('href');
        
        // Only add to internal links (not external URLs)
        if (href && !href.startsWith('http') && !href.startsWith('mailto:') && !href.startsWith('tel:') && !href.includes('#')) {
            link.addEventListener('click', function(e) {
                // Only add cache buster for dynamic pages
                const dynamicPages = ['gio-hang.html', 'dat-hang.html', 'user_profile.html', 'order_history.html', 'survey_history.html'];
                const isDynamicPage = dynamicPages.some(page => href.includes(page));
                
                if (isDynamicPage) {
                    const url = new URL(href, window.location.origin);
                    url.searchParams.set('nocache', new Date().getTime());
                    link.href = url.toString();
                }
            });
        }
    });
}

// Method 6: Prevent caching for dynamic pages only (MODIFIED)
function preventCaching() {
    // Only add cache prevention meta tags for dynamic pages
    const currentPage = window.location.pathname;
    const dynamicPages = ['gio-hang.html', 'dat-hang.html', 'user_profile.html', 'order_history.html', 'survey_history.html'];
    const isDynamicPage = dynamicPages.some(page => currentPage.includes(page));
    
    if (isDynamicPage) {
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
}

// Method 7: Add cache-busting parameter to prevent caching (MODIFIED - SAFER)
function addCacheBuster() {
    const currentPage = window.location.pathname;
    const dynamicPages = ['gio-hang.html', 'dat-hang.html', 'user_profile.html', 'order_history.html', 'survey_history.html'];
    const isDynamicPage = dynamicPages.some(page => currentPage.includes(page));
    
    if (isDynamicPage) {
        const url = new URL(window.location);
        const timestamp = new Date().getTime();
        
        // Only add cache buster if not already present
        if (!url.searchParams.has('nocache')) {
            url.searchParams.set('nocache', timestamp);
            window.history.replaceState({}, '', url);
        }
    }
}

// Initialize cache busting when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Cache buster initialized (v2.0 - safer version)');
    
    // Add cache buster to current page (only for dynamic pages)
    addCacheBuster();
    
    // Prevent caching (only for dynamic pages)
    preventCaching();
    
    // Add cache buster to links (only for dynamic pages)
    addCacheBusterToLinks();
    
    // Refresh dynamic content periodically (every 60 seconds - reduced frequency)
    setInterval(refreshDynamicContent, 60000);
});

// Export functions for manual use
window.CacheBuster = {
    addCacheBuster,
    preventCaching,
    refreshDynamicContent,
    addCacheBusterToLinks
};