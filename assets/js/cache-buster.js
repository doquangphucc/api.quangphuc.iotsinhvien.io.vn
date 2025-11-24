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

    try {
        initPromotionsOverlay();
    } catch (e) {
        console.error('Failed to init promotions overlay:', e);
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
            // Pick primary hotline (category=phone), zalo (category=zalo), facebook (category=facebook) with smallest display_order
            const phones  = data.channels.filter(c => (c.category || '').toLowerCase() === 'phone');
            const zalos   = data.channels.filter(c => (c.category || '').toLowerCase() === 'zalo');
            const facebooks = data.channels.filter(c => (c.category || '').toLowerCase() === 'facebook');
            phones.sort((a,b)=> (a.display_order||999)-(b.display_order||999));
            zalos.sort((a,b)=> (a.display_order||999)-(b.display_order||999));
            facebooks.sort((a,b)=> (a.display_order||999)-(b.display_order||999));
            hotlineNumber = (phones[0]?.content || '').replace(/\D/g,'') || null;
            zaloNumber = (zalos[0]?.content || '').replace(/\D/g,'') || hotlineNumber || null;
            if (phones[0]?.color) hotlineColor = phones[0].color;
            if (zalos[0]?.color) zaloColor = zalos[0].color;
            // Facebook info
            if (facebooks.length > 0) {
                var facebookInfo = {
                    url: facebooks[0].content ? facebooks[0].content : '',
                    color: facebooks[0].color || '#1877f3',
                    name: facebooks[0].name || 'Facebook',
                };
            }
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
        'position:fixed','left:20px','bottom:24px','z-index:1000',
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

    // Facebook FAB (nếu có)
    if (typeof facebookInfo !== 'undefined' && facebookInfo.url) {
        const facebookFab = createFab({
            id: 'fab-facebook',
            bg: `linear-gradient(135deg, ${facebookInfo.color}, ${lighten(facebookInfo.color, 30)})`,
            icon: `<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 32 32"><rect width="32" height="32" rx="16" fill="#1877f3"/><path d="M22.75 24V17.11H25L25.35 14.04H22.75V12.23C22.75 11.36 23 10.78 24.22 10.78H25.43V8.06C25.21 8.03 24.47 7.95 23.6 7.95C21.7 7.95 20.43 9.11 20.43 11.97V14.04H18V17.11H20.43V24H22.75Z" fill="white"/></svg>`,
            label: facebookInfo.name,
            href: facebookInfo.url,
            aria: 'Facebook'
        });
        container.appendChild(facebookFab);
    }
}

// ================================
// Promotions Overlay (Floating Ads)
// ================================
const PROMOTION_DISMISS_PREFIX = 'promoDismissed';
let promotionStylesInjected = false;
let cachedPromotionsList = null; // Lưu danh sách khuyến mãi để dùng lại
let currentPageKey = null; // Lưu page key hiện tại

function shouldSkipPromotions() {
    const body = document.body;
    if (body && body.dataset.disablePromotions === 'true') return true;
    const pageKey = getCurrentPageKey();
    return pageKey.includes('admin');
}

function getCurrentPageKey() {
    let path = window.location.pathname || '';
    if (!path || path === '/') return 'index.html';
    path = path.replace(/^\/+/, '');
    return path === '' ? 'index.html' : path;
}

function normalizePromoPath(path) {
    if (!path) return 'index.html';
    const cleaned = path.replace(/^\/+/, '');
    return cleaned === '' ? 'index.html' : cleaned;
}

function getPromotionDismissKey(pageKey, promoId) {
    return `${PROMOTION_DISMISS_PREFIX}_${pageKey}_${promoId}`;
}

function isPromotionDismissed(pageKey, promoId) {
    try {
        // Dùng sessionStorage thay vì localStorage để mỗi tab có storage riêng
        // Tab mới = banner hiện lại, cùng tab đã click X thì không hiện lại dù refresh
        return sessionStorage.getItem(getPromotionDismissKey(pageKey, promoId)) === '1';
    } catch (e) {
        return false;
    }
}

function markPromotionDismissed(pageKey, promoId) {
    try {
        // Dùng sessionStorage thay vì localStorage để mỗi tab có storage riêng
        // Tab mới = banner hiện lại, cùng tab đã click X thì không hiện lại dù refresh
        sessionStorage.setItem(getPromotionDismissKey(pageKey, promoId), '1');
    } catch (e) {
        // ignore storage errors
    }
}

function ensurePromotionStyles() {
    if (promotionStylesInjected) return;
    const style = document.createElement('style');
    style.id = 'promotion-overlay-styles';
    style.textContent = `
        #promotion-overlay {
            position: fixed;
            inset: 0;
            z-index: 3000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #promotion-overlay .promotion-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.45);
            backdrop-filter: blur(2px);
        }
        #promotion-overlay .promotion-card {
            position: relative;
            max-width: min(90vw, 560px);
            max-height: min(80vh, 620px);
            background: rgba(15,23,42,0.9);
            border-radius: 24px;
            padding: 1.5rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            animation: promo-pop 0.35s ease;
        }
        #promotion-overlay .promotion-card img {
            max-width: 100%;
            max-height: 70vh;
            border-radius: 16px;
            object-fit: contain;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
        }
        #promotion-overlay .promotion-close {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 36px;
            height: 36px;
            border-radius: 999px;
            border: none;
            background: rgba(0,0,0,0.5);
            color: #fff;
            font-size: 1.25rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease, transform 0.2s ease;
        }
        #promotion-overlay .promotion-close:hover {
            background: rgba(0,0,0,0.8);
            transform: scale(1.05);
        }
        @keyframes promo-pop {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
    promotionStylesInjected = true;
}

function resolvePromotionLink(link) {
    if (!link) return '#';
    if (/^(https?:|mailto:|tel:)/i.test(link)) {
        return link;
    }
    const cleaned = link.replace(/^\/+/, '');
    return '/' + cleaned;
}

function renderPromotionOverlay(promo, pageKey, onDismissCallback = null) {
    ensurePromotionStyles();
    
    // Xóa overlay cũ nếu có
    const existingOverlay = document.getElementById('promotion-overlay');
    if (existingOverlay) {
        existingOverlay.remove();
    }
    
    const overlay = document.createElement('div');
    overlay.id = 'promotion-overlay';
    overlay.innerHTML = `
        <div class="promotion-backdrop"></div>
        <div class="promotion-card" role="dialog" aria-label="${promo.title || 'Khuyến mãi'}">
            <button class="promotion-close" aria-label="Đóng khuyến mãi">&times;</button>
            <img src="${promo.image_url}" alt="${promo.title || 'Khuyến mãi'}" class="promotion-image"/>
        </div>
    `;
    document.body.appendChild(overlay);

    const dismiss = () => {
        markPromotionDismissed(pageKey, promo.id);
        overlay.remove();
        // Gọi callback để hiển thị khuyến mãi tiếp theo
        if (onDismissCallback && typeof onDismissCallback === 'function') {
            setTimeout(() => onDismissCallback(), 300); // Delay 300ms để animation mượt hơn
        }
    };

    overlay.querySelector('.promotion-backdrop').addEventListener('click', dismiss);
    overlay.querySelector('.promotion-close').addEventListener('click', dismiss);
    overlay.querySelector('.promotion-image').addEventListener('click', () => {
        markPromotionDismissed(pageKey, promo.id);
        window.location.href = resolvePromotionLink(promo.target_link || '#');
    });
}

function showNextPromotion() {
    if (!cachedPromotionsList || !currentPageKey) return;
    
    // Tìm khuyến mãi tiếp theo chưa bị dismiss
    const promo = cachedPromotionsList.find(item => !isPromotionDismissed(currentPageKey, item.id));
    
    if (promo && promo.image_url) {
        // Hiển thị khuyến mãi tiếp theo, với callback để hiển thị tiếp nữa nếu có
        renderPromotionOverlay(promo, currentPageKey, showNextPromotion);
    }
}

async function initPromotionsOverlay() {
    if (shouldSkipPromotions()) return;
    const pageKey = normalizePromoPath(getCurrentPageKey());
    currentPageKey = pageKey; // Lưu page key để dùng sau
    
    try {
        const response = await fetch(`../api/get_promotions.php?page=${encodeURIComponent(pageKey)}&t=${Date.now()}`);
        const data = await response.json();
        if (!data.success || !Array.isArray(data.promotions) || !data.promotions.length) return;
        
        // Lưu danh sách khuyến mãi để dùng lại khi dismiss
        cachedPromotionsList = data.promotions;
        
        // Tìm và hiển thị khuyến mãi đầu tiên chưa bị dismiss
        showNextPromotion();
    } catch (error) {
        console.error('Failed to fetch promotions:', error);
    }
}