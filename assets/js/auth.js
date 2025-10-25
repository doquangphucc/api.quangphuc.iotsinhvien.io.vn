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
            logoutBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                
                try {
                    // Call logout API first
                    const response = await fetch('../api/logout.php', {
                        method: 'POST',
                        credentials: 'include'
                    });
                    
                    if (response.ok) {
                        const result = await response.json();
                        console.log('Logout successful:', result.message);
                    } else {
                        console.warn('Logout API failed, but continuing with local cleanup');
                    }
                } catch (error) {
                    console.error('Error calling logout API:', error);
                    // Continue with local cleanup even if API fails
                }
                
                // Clear user from storage
                storeUser(null);
                
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
                buttonsContainer.appendChild(userLink);
                buttonsContainer.appendChild(logoutBtn);
            }
            
            // Add to mobile container
            if (mobileButtonsContainer) {
                // Create separate instances for mobile
                const mobileUserLink = userLink.cloneNode(true);
                const mobileLogoutBtn = logoutBtn.cloneNode(true);
                
                // Re-attach event listener for mobile logout button
                mobileLogoutBtn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    
                    try {
                        // Call logout API first
                        const response = await fetch('../api/logout.php', {
                            method: 'POST',
                            credentials: 'include'
                        });
                        
                        if (response.ok) {
                            const result = await response.json();
                            console.log('Logout successful:', result.message);
                        } else {
                            console.warn('Logout API failed, but continuing with local cleanup');
                        }
                    } catch (error) {
                        console.error('Error calling logout API:', error);
                        // Continue with local cleanup even if API fails
                    }
                    
                    // Clear user from storage
                    storeUser(null);
                    
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
                
                mobileButtonsContainer.appendChild(mobileUserLink);
                mobileButtonsContainer.appendChild(mobileLogoutBtn);
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