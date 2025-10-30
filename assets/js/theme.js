(() => {
    const STORAGE_KEY = 'hc-theme';
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
    const root = document.body;

    const applyTheme = (theme) => {
        if (theme === 'dark') {
            root.setAttribute('data-theme', 'dark');
        } else {
            root.removeAttribute('data-theme');
        }
    };

    const resolveInitialTheme = () => {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored === 'dark' || stored === 'light') {
            return stored;
        }
        return prefersDark.matches ? 'dark' : 'light';
    };

    const toggleTheme = () => {
        const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        localStorage.setItem(STORAGE_KEY, next);
        applyTheme(next);
    };

    document.addEventListener('DOMContentLoaded', () => {
        applyTheme(resolveInitialTheme());

        // Inject minimal global styles to keep header consistent across all pages
        try {
            const style = document.createElement('style');
            style.setAttribute('data-injected', 'hc-header-fixes');
            style.textContent = `
                /* Enforce single-line header and consistent sizing like index */
                #header .container > div{height:5rem;}
                #header nav{white-space:nowrap;}
                #header .flex.items-center.gap-2{white-space:nowrap;}
                #header nav a{font-size:0.875rem;line-height:1.25rem;padding:0.5rem 1rem;}
            `;
            document.head.appendChild(style);
        } catch(_) {}

        document.querySelectorAll('[data-theme-toggle]').forEach((toggle) => {
            toggle.addEventListener('click', toggleTheme);
            toggle.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    toggleTheme();
                }
            });
        });
    });

    prefersDark.addEventListener('change', (event) => {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (!stored) {
            applyTheme(event.matches ? 'dark' : 'light');
        }
    });
})();
