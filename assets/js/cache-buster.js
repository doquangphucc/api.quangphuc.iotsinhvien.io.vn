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
    let hotlineColor = '#16a34a';
    let zaloColor = '#2563eb';

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
            if (phones[0]?.color) hotlineColor = phones[0].color;
            if (zalos[0]?.color) zaloColor = zalos[0].color;
        }
    } catch (e) {
        console.warn('Could not load contact channels, fallback to defaults');
    }

    // Fallback default (matches DB seed) if API not reachable
    if (!hotlineNumber) hotlineNumber = '0969397434';
    if (!zaloNumber) zaloNumber = hotlineNumber;

    // Utilities
    const lighten = (hex, amt=20) => {
        try {
            hex = hex.replace('#','');
            if (hex.length === 3) hex = hex.split('').map(x=>x+x).join('');
            const num = parseInt(hex,16);
            let r = Math.min(255, (num>>16) + amt);
            let g = Math.min(255, ((num>>8)&0x00FF) + amt);
            let b = Math.min(255, (num&0x0000FF) + amt);
            return '#' + [r,g,b].map(v=>v.toString(16).padStart(2,'0')).join('');
        } catch(_) { return hex; }
    };

    // Create container
    const container = document.createElement('div');
    container.id = 'contact-fabs';
    container.style.cssText = [
        'position:fixed','right:20px','bottom:150px','z-index:1000',
        'display:flex','flex-direction:column','gap:10px'
    ].join(';');

    // Common button factory
    function createFab({ id, bg, icon, label, href, aria, rotate=false }) {
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
        bg: `linear-gradient(135deg, ${hotlineColor}, ${lighten(hotlineColor, 30)})`,
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.09 4.18 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.72c.12.81.3 1.6.52 2.36a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.72-1.72a2 2 0 0 1 2.11-.45c.76.22 1.55.4 2.36.52A2 2 0 0 1 22 16.92z"/></svg>',
        label: `Hotline ${hotlineNumber}`,
        href: `tel:${hotlineNumber}`,
        aria: 'Gọi Hotline'
    });

    // Zalo FAB
    const zaloFab = createFab({
        id: 'fab-zalo',
        bg: `linear-gradient(135deg, ${zaloColor}, ${lighten(zaloColor, 30)})`,
        // Zalo recognizable icon: chat bubble with "Zalo" text
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 48 48" fill="none"><rect rx="10" ry="10" width="48" height="48" fill="white" opacity="0.15"/><path d="M8 18c0-5 4-9 9-9h14c5 0 9 4 9 9v6c0 5-4 9-9 9h-8l-5.5 4.2c-1.1.83-2.5-.34-2.1-1.6L16 33h-1c-5 0-7-4-7-9v-6z" fill="white" opacity="0.95"/><text x="14" y="29" font-size="12" font-weight="700" fill="#1d4ed8" font-family="Arial, Helvetica, sans-serif">Zalo</text></svg>',
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