/**
 * Security Helper for Frontend
 * Provides CSRF token management and secure API calls
 */

const SecurityHelper = {
    csrfToken: null,
    csrfTokenExpiry: null,
    
    /**
     * Get API base URL from config or auto-detect
     */
    getApiBaseUrl() {
        if (typeof API_BASE_URL !== 'undefined') {
            return API_BASE_URL;
        }
        // Auto-detect from current location
        return window.location.origin + '/api';
    },
    
    /**
     * Fetch CSRF token from server
     */
    async fetchCSRFToken() {
        try {
            const response = await fetch(this.getApiBaseUrl() + '/get_csrf_token.php', {
                method: 'GET',
                credentials: 'include'
            });
            
            if (!response.ok) {
                throw new Error('Failed to fetch CSRF token');
            }
            
            const data = await response.json();
            if (data.success && data.csrf_token) {
                this.csrfToken = data.csrf_token;
                // Token expires in 1 hour, refresh 5 minutes before
                this.csrfTokenExpiry = Date.now() + ((data.expires_in || 3600) - 300) * 1000;
                return this.csrfToken;
            }
            
            throw new Error('Invalid CSRF token response');
        } catch (error) {
            console.error('Error fetching CSRF token:', error);
            return null;
        }
    },
    
    /**
     * Get CSRF token (from cache or fetch new)
     */
    async getCSRFToken() {
        // Check if we have a valid cached token
        if (this.csrfToken && this.csrfTokenExpiry && Date.now() < this.csrfTokenExpiry) {
            return this.csrfToken;
        }
        
        // Fetch new token
        return await this.fetchCSRFToken();
    },
    
    /**
     * Make a secure API call with CSRF token
     */
    async secureApiFetch(url, options = {}) {
        // Default options
        const defaultOptions = {
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            }
        };
        
        // Merge options
        const fetchOptions = {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...(options.headers || {})
            }
        };
        
        // Add CSRF token for state-changing methods
        const method = (fetchOptions.method || 'GET').toUpperCase();
        if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
            const csrfToken = await this.getCSRFToken();
            if (csrfToken) {
                fetchOptions.headers['X-CSRF-Token'] = csrfToken;
            }
        }
        
        try {
            const response = await fetch(url, fetchOptions);
            
            // Handle CSRF token expiration
            if (response.status === 403) {
                const data = await response.json();
                if (data.code === 'CSRF_INVALID') {
                    // Clear cached token and retry once
                    this.csrfToken = null;
                    this.csrfTokenExpiry = null;
                    
                    const newToken = await this.getCSRFToken();
                    if (newToken) {
                        fetchOptions.headers['X-CSRF-Token'] = newToken;
                        return fetch(url, fetchOptions);
                    }
                }
            }
            
            return response;
        } catch (error) {
            console.error('API fetch error:', error);
            throw error;
        }
    },
    
    /**
     * Shorthand for GET request
     */
    async get(url, options = {}) {
        return this.secureApiFetch(url, { ...options, method: 'GET' });
    },
    
    /**
     * Shorthand for POST request
     */
    async post(url, data, options = {}) {
        return this.secureApiFetch(url, {
            ...options,
            method: 'POST',
            body: JSON.stringify(data)
        });
    },
    
    /**
     * Shorthand for PUT request
     */
    async put(url, data, options = {}) {
        return this.secureApiFetch(url, {
            ...options,
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },
    
    /**
     * Shorthand for DELETE request
     */
    async delete(url, options = {}) {
        return this.secureApiFetch(url, { ...options, method: 'DELETE' });
    },
    
    /**
     * Add CSRF token to a form
     */
    async addCSRFToForm(form) {
        const csrfToken = await this.getCSRFToken();
        if (!csrfToken) {
            console.error('Could not get CSRF token for form');
            return false;
        }
        
        // Check if hidden input already exists
        let input = form.querySelector('input[name="csrf_token"]');
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'csrf_token';
            form.appendChild(input);
        }
        input.value = csrfToken;
        
        return true;
    },
    
    /**
     * Initialize - auto-add CSRF to forms with data-csrf attribute
     */
    async init() {
        // Prefetch CSRF token
        await this.getCSRFToken();
        
        // Add CSRF to forms on submit
        document.addEventListener('submit', async (e) => {
            const form = e.target;
            if (form.tagName === 'FORM' && form.hasAttribute('data-csrf')) {
                e.preventDefault();
                
                const success = await this.addCSRFToForm(form);
                if (success) {
                    form.submit();
                } else {
                    showError('Lỗi bảo mật. Vui lòng tải lại trang.');
                }
            }
        });
        
        console.log('SecurityHelper initialized');
    }
};

// Auto-initialize if DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => SecurityHelper.init());
} else {
    SecurityHelper.init();
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SecurityHelper;
}
