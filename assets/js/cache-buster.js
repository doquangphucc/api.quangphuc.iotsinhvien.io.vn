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

    // Inject global contact FABs (Hotline, Zalo) on every page
    try {
        injectContactFABs();
    } catch (e) {
        console.error('Failed to inject contact FABs:', e);
    }
});

// Export functions for manual use
window.CacheBuster = {
    addCacheBusterToCurrentPage,
    preventCaching,
    refreshDynamicContent
};

// ================================
// Global Contact FABs (Hotline/Zalo)
// ================================
async function injectContactFABs() {
    // Avoid duplicates
    if (document.getElementById('contact-fabs')) return;

    let hotlineNumber = null;
    let zaloNumber = null;

    try {
        const res = await fetch('../api/get_contact_channels_public.php');
        const data = await res.json();
        if (data.success && Array.isArray(data.channels)) {
            // Pick primary hotline (category=phone) and zalo (category=zalo) with smallest display_order
            const phones = data.channels.filter(c => (c.category || '').toLowerCase() === 'phone');
            const zalos = data.channels.filter(c => (c.category || '').toLowerCase() === 'zalo');
            phones.sort((a,b)=> (a.display_order||999)-(b.display_order||999));
            zalos.sort((a,b)=> (a.display_order||999)-(b.display_order||999));
            hotlineNumber = (phones[0]?.content || '').replace(/\D/g,'') || null;
            zaloNumber = (zalos[0]?.content || '').replace(/\D/g,'') || hotlineNumber || null;
        }
    } catch (e) {
        console.warn('Could not load contact channels, fallback to defaults');
    }

    // Fallback default (matches DB seed) if API not reachable
    if (!hotlineNumber) hotlineNumber = '0969397434';
    if (!zaloNumber) zaloNumber = hotlineNumber;

    // Create container
    const container = document.createElement('div');
    container.id = 'contact-fabs';
    container.style.cssText = [
        'position:fixed','right:20px','bottom:150px','z-index:1000',
        'display:flex','flex-direction:column','gap:10px'
    ].join(';');

    // Common button factory
    function createFab({ id, bg, icon, label, href, aria }) {
        const a = document.createElement('a');
        a.id = id;
        a.href = href;
        a.target = '_blank';
        a.rel = 'noopener';
        a.setAttribute('aria-label', aria || label);
        a.style.cssText = [
            'width:56px','height:56px','border-radius:16px','display:flex','align-items:center','justify-content:center',
            'color:#fff','text-decoration:none','box-shadow:0 10px 20px rgba(0,0,0,0.2)','transition:transform .15s ease, box-shadow .15s ease',
            `background:${bg}`
        ].join(';');
        a.innerHTML = icon;
        a.addEventListener('mouseenter', ()=>{ a.style.transform='translateY(-2px) scale(1.03)'; a.style.boxShadow='0 14px 24px rgba(0,0,0,0.25)'; });
        a.addEventListener('mouseleave', ()=>{ a.style.transform=''; a.style.boxShadow='0 10px 20px rgba(0,0,0,0.2)'; });
        // Tooltip
        a.title = label;
        return a;
    }

    // Hotline FAB (tel:)
    const hotlineFab = createFab({
        id: 'fab-hotline',
        bg: 'linear-gradient(135deg,#16a34a,#22c55e)',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.09 4.18 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.72c.12.81.3 1.6.52 2.36a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.72-1.72a2 2 0 0 1 2.11-.45c.76.22 1.55.4 2.36.52A2 2 0 0 1 22 16.92z"/></svg>',
        label: `Hotline ${hotlineNumber}`,
        href: `tel:${hotlineNumber}`,
        aria: 'Gọi Hotline'
    });

    // Zalo FAB
    const zaloFab = createFab({
        id: 'fab-zalo',
        bg: 'linear-gradient(135deg,#2563eb,#1d4ed8)',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 5.94 2 10.8c0 2.63 1.35 4.97 3.52 6.53L4.8 22l3.66-1.94c1.08.3 2.24.47 3.54.47 5.52 0 10-3.94 10-8.8S17.52 2 12 2zm-3.5 5.5h7v2h-7v-2zm0 3.5h7v2h-7v-2z"/></svg>',
        label: 'Chat Zalo',
        href: `https://zalo.me/${zaloNumber}`,
        aria: 'Chat Zalo'
    });

    // Position above existing cart FAB if present
    // Our container starts at bottom:150px (above many page FABs). If a page has more FABs, developers can adjust easily.
    container.appendChild(zaloFab);
    container.appendChild(hotlineFab);
    document.body.appendChild(container);
}