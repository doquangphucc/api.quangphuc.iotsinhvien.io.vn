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
        if (!buttonsContainer) {
            return;
        }

        // Clear current buttons to rebuild them
        buttonsContainer.innerHTML = '';

        const user = getStoredUser();

        if (user && user.full_name) {
            // --- LOGGED IN STATE ---

            // 1. Create User Profile Link
            const userLink = document.createElement('a');
            userLink.className = 'auth-btn auth-user-name'; // Use this class for styling
            userLink.textContent = user.full_name;
            userLink.title = 'Quản lý tài khoản';
            userLink.href = 'user_profile.html';

            // 2. Create Logout Button
            const logoutBtn = document.createElement('a');
            logoutBtn.className = 'auth-btn auth-btn--register'; // Re-use class for style consistency
            logoutBtn.textContent = 'Đăng xuất';
            logoutBtn.href = '#';
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                storeUser(null); // Clear user from storage
                window.location.reload(); // Reload the page to reset state
            });

            buttonsContainer.appendChild(userLink);
            buttonsContainer.appendChild(logoutBtn);

        } else {
            // --- LOGGED OUT STATE ---

            // 1. Create Login Button
            const loginBtn = document.createElement('a');
            loginBtn.className = 'auth-btn auth-btn--login';
            loginBtn.href = 'login.html';
            loginBtn.textContent = 'Đăng nhập';

            // 2. Create Register Button
            const registerBtn = document.createElement('a');
            registerBtn.className = 'auth-btn auth-btn--register';
            registerBtn.href = 'register.html';
            registerBtn.textContent = 'Đăng ký';

            buttonsContainer.appendChild(loginBtn);
            buttonsContainer.appendChild(registerBtn);
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