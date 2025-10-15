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
