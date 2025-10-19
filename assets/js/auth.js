(function() {
    const STORAGE_KEY = 'authUser';

    function getStoredUser() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return null;
            return JSON.parse(raw);
        } catch (error) {
            console.error('Cannot parse stored user', error);
            return null;
        }
    }

    function storeUser(user) {
        if (!user) {
            localStorage.removeItem(STORAGE_KEY);
            return;
        }
        localStorage.setItem(STORAGE_KEY, JSON.stringify(user));
    }

    function renderAuthUI() {
        const buttonsContainer = document.getElementById('auth-buttons');
        const mobileButtonsContainer = document.getElementById('mobile-auth-buttons');
        
        if (!buttonsContainer && !mobileButtonsContainer) {
            return;
        }

        // Clear current buttons to rebuild them
        if (buttonsContainer) buttonsContainer.innerHTML = '';
        if (mobileButtonsContainer) mobileButtonsContainer.innerHTML = '';

        const user = getStoredUser();

        if (user && user.full_name) {
            // --- LOGGED IN STATE ---

            // Determine the correct path based on current location
            const isInHtmlFolder = window.location.pathname.includes('/html/');
            const profilePath = isInHtmlFolder ? 'user_profile.html' : 'html/user_profile.html';

            // 1. Create User Profile Link
            const userLink = document.createElement('a');
            userLink.className = 'auth-btn auth-user-name'; // Use this class for styling
            userLink.textContent = user.full_name;
            userLink.title = 'Quản lý tài khoản';
            userLink.href = profilePath;

            // 2. Create Logout Button
            const logoutBtn = document.createElement('a');
            logoutBtn.className = 'auth-btn auth-btn--register'; // Re-use class for style consistency
            logoutBtn.textContent = 'Đăng xuất';
            logoutBtn.href = '#';
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                storeUser(null); // Clear user from storage
                
                // Clear cart from localStorage
                localStorage.removeItem('cartItems');
                
                // Clear cart counter before reload
                const fabCounter = document.getElementById('fab-cart-count');
                if (fabCounter) {
                    fabCounter.style.display = 'none';
                    fabCounter.textContent = '0';
                }
                
                window.location.reload(); // Reload the page to reset state
            });

            // Add to desktop container
            if (buttonsContainer) {
                buttonsContainer.appendChild(userLink.cloneNode(true));
                buttonsContainer.appendChild(logoutBtn.cloneNode(true));
            }
            
            // Add to mobile container
            if (mobileButtonsContainer) {
                mobileButtonsContainer.appendChild(userLink.cloneNode(true));
                mobileButtonsContainer.appendChild(logoutBtn.cloneNode(true));
            }

        } else {
            // --- LOGGED OUT STATE ---

            // Determine the correct path based on current location
            const isInHtmlFolder = window.location.pathname.includes('/html/');
            const loginPath = isInHtmlFolder ? 'login.html' : 'html/login.html';
            const registerPath = isInHtmlFolder ? 'register.html' : 'html/register.html';

            // 1. Create Login Button
            const loginBtn = document.createElement('a');
            loginBtn.className = 'auth-btn auth-btn--login';
            loginBtn.href = loginPath;
            loginBtn.textContent = 'Đăng nhập';

            // 2. Create Register Button
            const registerBtn = document.createElement('a');
            registerBtn.className = 'auth-btn auth-btn--register';
            registerBtn.href = registerPath;
            registerBtn.textContent = 'Đăng ký';

            // Add to desktop container
            if (buttonsContainer) {
                buttonsContainer.appendChild(loginBtn.cloneNode(true));
                buttonsContainer.appendChild(registerBtn.cloneNode(true));
            }
            
            // Add to mobile container
            if (mobileButtonsContainer) {
                mobileButtonsContainer.appendChild(loginBtn.cloneNode(true));
                mobileButtonsContainer.appendChild(registerBtn.cloneNode(true));
            }
        }
    }

    document.addEventListener('DOMContentLoaded', renderAuthUI);

    window.authUtils = {
        setUser(user) {
            storeUser(user);
            renderAuthUI();
        },
        clearUser() {
            storeUser(null);
            renderAuthUI();
        },
        getUser: getStoredUser
    };
})();