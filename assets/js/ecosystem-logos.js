// Ecosystem Logos Carousel - Load from Product Categories API
// This script loads logos from the product_categories table in the database

(function() {
    const API_BASE = (function() {
        const origin = window.location && window.location.origin;
        if (origin && origin !== 'null' && origin !== 'file://') {
            return `${origin.replace(/\/$/, '')}/api`;
        }
        return 'https://api.quangphuc.iotsinhvien.io.vn/api';
    })();
    
    const container = document.getElementById('ecosystem-logos-container');
    if (!container) return;
    
    // Determine path prefix based on current page location
    const pathPrefix = window.location.pathname.includes('/html/') ? '../' : '';
    
    let currentIndex = 0;
    let logos = [];
    
    // Load logos from API
    async function loadLogos() {
        try {
            const response = await fetch(`${API_BASE}/get_categories_public.php`, { 
                cache: 'no-cache' 
            });
            
            if (!response.ok) {
                throw new Error(`Server responded ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success && data.categories && data.categories.length > 0) {
                // Map categories to logo format
                logos = data.categories
                    .filter(cat => cat.logo_url && cat.logo_url.trim() !== '')
                    .map(cat => ({
                        src: cat.logo_url.startsWith('http') 
                            ? cat.logo_url 
                            : pathPrefix + cat.logo_url,
                        alt: cat.name || 'Ecosystem Logo'
                    }));
                
                // If we have logos, initialize carousel
                if (logos.length > 0) {
                    createLogos();
                    startCarousel();
                } else {
                    // Fallback: show message or hide container
                    container.innerHTML = '';
                }
            } else {
                // No categories found, hide container
                container.innerHTML = '';
            }
        } catch (error) {
            console.error('Error loading ecosystem logos:', error);
            // On error, hide container or show fallback
            container.innerHTML = '';
        }
    }
    
    // Create logo elements
    function createLogos() {
        if (logos.length === 0) return;
        
        container.innerHTML = '';
        const displayCount = Math.min(3, logos.length);
        
        for (let i = 0; i < displayCount; i++) {
            const logoIndex = (currentIndex + i) % logos.length;
            const img = document.createElement('img');
            img.src = logos[logoIndex].src;
            img.alt = logos[logoIndex].alt;
            img.className = 'h-16 w-auto object-contain opacity-80 hover:opacity-100 transition-all duration-500';
            img.loading = 'lazy';
            container.appendChild(img);
        }
    }
    
    // Start carousel animation
    function startCarousel() {
        if (logos.length <= 3) return; // No need to rotate if 3 or fewer logos
        
        setInterval(() => {
            currentIndex = (currentIndex + 1) % logos.length;
            createLogos();
        }, 2000);
    }
    
    // Load logos when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadLogos);
    } else {
        loadLogos();
    }
})();

