/**
 * HC ECO SYSTEM - UI Enhancements
 * Professional micro-interactions & UX improvements
 */

(function() {
    'use strict';

    // ==================== HEADER SCROLL EFFECT ====================
    function initHeaderScroll() {
        const header = document.querySelector('header');
        if (!header) return;

        let lastScroll = 0;
        const scrollThreshold = 50;

        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            // Add/remove scrolled class
            if (currentScroll > scrollThreshold) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            
            lastScroll = currentScroll;
        }, { passive: true });
    }

    // ==================== BACK TO TOP BUTTON ====================
    function initBackToTop() {
        // Create button if it doesn't exist
        let btn = document.querySelector('.hc-back-to-top');
        if (!btn) {
            btn = document.createElement('button');
            btn.className = 'hc-back-to-top';
            btn.setAttribute('aria-label', 'Về đầu trang');
            btn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                </svg>
            `;
            document.body.appendChild(btn);
        }

        // Show/hide based on scroll
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 400) {
                btn.classList.add('visible');
            } else {
                btn.classList.remove('visible');
            }
        }, { passive: true });

        // Scroll to top on click
        btn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // ==================== SMOOTH REVEAL ANIMATIONS ====================
    function initScrollReveal() {
        const observerOptions = {
            root: null,
            rootMargin: '0px 0px -50px 0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    // Optionally unobserve after reveal
                    // observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe elements with reveal class
        document.querySelectorAll('.reveal-on-scroll, section, article, .card').forEach(el => {
            if (!el.classList.contains('no-reveal')) {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            }
        });

        // Add revealed styles
        const style = document.createElement('style');
        style.textContent = `
            .revealed {
                opacity: 1 !important;
                transform: translateY(0) !important;
            }
        `;
        document.head.appendChild(style);
    }

    // ==================== BUTTON RIPPLE EFFECT ====================
    function initRippleEffect() {
        document.addEventListener('click', (e) => {
            const button = e.target.closest('button, .btn, .auth-btn, [role="button"]');
            if (!button) return;

            const rect = button.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const ripple = document.createElement('span');
            ripple.className = 'ripple-effect';
            ripple.style.cssText = `
                position: absolute;
                width: 0;
                height: 0;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.4);
                transform: translate(-50%, -50%);
                left: ${x}px;
                top: ${y}px;
                animation: ripple-animation 0.6s ease-out;
                pointer-events: none;
            `;

            // Ensure button has relative positioning
            const currentPosition = getComputedStyle(button).position;
            if (currentPosition === 'static') {
                button.style.position = 'relative';
            }
            button.style.overflow = 'hidden';
            
            button.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });

        // Add ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple-animation {
                to {
                    width: 300px;
                    height: 300px;
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }

    // ==================== ENHANCED FORM INTERACTIONS ====================
    function initFormEnhancements() {
        // Floating labels
        document.querySelectorAll('input, textarea, select').forEach(input => {
            // Add focus class to parent
            input.addEventListener('focus', () => {
                input.parentElement?.classList.add('input-focused');
            });
            
            input.addEventListener('blur', () => {
                input.parentElement?.classList.remove('input-focused');
                if (input.value) {
                    input.parentElement?.classList.add('input-filled');
                } else {
                    input.parentElement?.classList.remove('input-filled');
                }
            });

            // Check initial state
            if (input.value) {
                input.parentElement?.classList.add('input-filled');
            }
        });
    }

    // ==================== LOADING STATE HELPERS ====================
    window.HCUtils = {
        // Show loading on button
        setButtonLoading: function(btn, loading = true) {
            if (loading) {
                btn.dataset.originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = `
                    <span class="spinner" style="width: 18px; height: 18px; border-width: 2px;"></span>
                    <span>Đang xử lý...</span>
                `;
            } else {
                btn.disabled = false;
                btn.innerHTML = btn.dataset.originalText || btn.innerHTML;
            }
        },

        // Show toast notification
        showToast: function(message, type = 'info', duration = 4000) {
            const container = document.getElementById('toast-container') || (() => {
                const div = document.createElement('div');
                div.id = 'toast-container';
                div.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                `;
                document.body.appendChild(div);
                return div;
            })();

            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.style.cssText = `
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 1rem 1.25rem;
                background: white;
                border-radius: 12px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
                border-left: 4px solid ${
                    type === 'success' ? '#10b981' :
                    type === 'error' ? '#ef4444' :
                    type === 'warning' ? '#f59e0b' : '#3b82f6'
                };
                animation: toast-slide-in 0.3s ease-out;
                max-width: 400px;
            `;
            
            const icon = type === 'success' ? '✓' : 
                        type === 'error' ? '✕' : 
                        type === 'warning' ? '⚠' : 'ℹ';
            
            toast.innerHTML = `
                <span style="font-size: 1.25rem;">${icon}</span>
                <span style="font-size: 0.9375rem; color: #374151;">${message}</span>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'toast-slide-out 0.3s ease-in forwards';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        },

        // Smooth scroll to element
        scrollTo: function(element, offset = 80) {
            const el = typeof element === 'string' ? document.querySelector(element) : element;
            if (!el) return;
            
            const top = el.getBoundingClientRect().top + window.pageYOffset - offset;
            window.scrollTo({
                top,
                behavior: 'smooth'
            });
        },

        // Format currency
        formatCurrency: function(amount) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(amount);
        },

        // Format number with separator
        formatNumber: function(num) {
            return new Intl.NumberFormat('vi-VN').format(num);
        }
    };

    // ==================== IMAGE LAZY LOADING ====================
    function initLazyImages() {
        if ('loading' in HTMLImageElement.prototype) {
            // Native lazy loading supported
            document.querySelectorAll('img[data-src]').forEach(img => {
                img.src = img.dataset.src;
                img.loading = 'lazy';
            });
        } else {
            // Fallback with IntersectionObserver
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.add('loaded');
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }

    // ==================== COUNTER ANIMATION ====================
    function initCounterAnimation() {
        const counters = document.querySelectorAll('[data-counter]');
        if (!counters.length) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = parseInt(counter.dataset.counter);
                    const duration = parseInt(counter.dataset.duration) || 2000;
                    const suffix = counter.dataset.suffix || '';
                    
                    let start = 0;
                    const step = target / (duration / 16);
                    
                    const animate = () => {
                        start += step;
                        if (start < target) {
                            counter.textContent = Math.floor(start).toLocaleString('vi-VN') + suffix;
                            requestAnimationFrame(animate);
                        } else {
                            counter.textContent = target.toLocaleString('vi-VN') + suffix;
                        }
                    };
                    
                    animate();
                    observer.unobserve(counter);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(counter => observer.observe(counter));
    }

    // ==================== MOBILE MENU ENHANCEMENT ====================
    function initMobileMenu() {
        const menuBtn = document.querySelector('#mobile-menu-btn, .mobile-menu-btn, [data-mobile-menu]');
        const mobileMenu = document.querySelector('#mobile-menu, .mobile-menu, [data-mobile-menu-content]');
        
        if (!menuBtn || !mobileMenu) return;

        // Create backdrop
        let backdrop = document.querySelector('.hc-menu-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'hc-menu-backdrop';
            document.body.appendChild(backdrop);
        }

        const toggleMenu = (open) => {
            mobileMenu.classList.toggle('open', open);
            backdrop.classList.toggle('open', open);
            document.body.style.overflow = open ? 'hidden' : '';
        };

        menuBtn.addEventListener('click', () => toggleMenu(true));
        backdrop.addEventListener('click', () => toggleMenu(false));
        
        // Close on ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') toggleMenu(false);
        });
    }

    // ==================== ACCESSIBILITY IMPROVEMENTS ====================
    function initAccessibility() {
        // Add skip link
        const skipLink = document.createElement('a');
        skipLink.href = '#main-content';
        skipLink.className = 'skip-link';
        skipLink.textContent = 'Bỏ qua đến nội dung chính';
        skipLink.style.cssText = `
            position: fixed;
            top: -100%;
            left: 50%;
            transform: translateX(-50%);
            background: #16a34a;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0 0 12px 12px;
            z-index: 10000;
            transition: top 0.2s ease;
            text-decoration: none;
            font-weight: 600;
        `;
        
        skipLink.addEventListener('focus', () => {
            skipLink.style.top = '0';
        });
        
        skipLink.addEventListener('blur', () => {
            skipLink.style.top = '-100%';
        });
        
        document.body.insertBefore(skipLink, document.body.firstChild);

        // Add main content id if missing
        const main = document.querySelector('main');
        if (main && !main.id) {
            main.id = 'main-content';
        }
    }

    // ==================== INITIALIZE ALL ====================
    function init() {
        // Wait for DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAll);
        } else {
            initAll();
        }
    }

    function initAll() {
        initHeaderScroll();
        initBackToTop();
        // initScrollReveal(); // Commented out - can enable if desired
        initRippleEffect();
        initFormEnhancements();
        initLazyImages();
        initCounterAnimation();
        initMobileMenu();
        initAccessibility();
    }

    // Start initialization
    init();

})();
