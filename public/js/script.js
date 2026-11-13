// Nnaco_SA Global Scripts & Universal Theme Engine
(function() {
    'use strict';

    // 1. Core Toggle Function
    window.toggleAppTheme = function() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || localStorage.getItem('nnaco_theme') || 'light';
        const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
        window.applyAppTheme(nextTheme, true);
    };

    // 2. Core Apply Function
    window.applyAppTheme = function(choice, showFeedback) {
        let effectiveTheme = choice;
        const systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (choice === 'system') {
            effectiveTheme = systemDark ? 'dark' : 'light';
            localStorage.setItem('nnaco_theme_mode', 'system');
        } else if (choice === 'dark' || choice === 'light') {
            effectiveTheme = choice;
            localStorage.setItem('nnaco_theme_mode', choice);
        } else {
            effectiveTheme = 'light';
            localStorage.setItem('nnaco_theme_mode', 'light');
        }

        // Apply attributes to both <html> and <body> for maximum CSS & Bootstrap 5.3 compatibility
        document.documentElement.setAttribute('data-theme', effectiveTheme);
        document.documentElement.setAttribute('data-bs-theme', effectiveTheme);
        if (document.body) {
            document.body.setAttribute('data-theme', effectiveTheme);
            document.body.setAttribute('data-bs-theme', effectiveTheme);
            document.body.classList.toggle('white-content', effectiveTheme === 'light');
            document.body.classList.toggle('theme-dark', effectiveTheme === 'dark');
        }

        localStorage.setItem('nnaco_theme', effectiveTheme);

        // Update Button States & Animations
        updateThemeButtons(effectiveTheme);

        // Update settings UI if on settings page
        if (typeof window.updateSettingsUI === 'function') {
            window.updateSettingsUI();
        }

        // Floating Toast Notification
        if (showFeedback) {
            const toastMessage = effectiveTheme === 'dark' ? '🌙 Mode Gelap Aktif' : '☀️ Mode Terang Aktif';
            showThemeToast(toastMessage);
        }
    };

    function updateThemeButtons(theme) {
        const desktopBtn = document.getElementById('themeToggleBtn');
        const mobileBtn = document.getElementById('themeToggleBtnMobile');

        const titleText = theme === 'dark' ? 'Ganti ke Mode Terang ☀️' : 'Ganti ke Mode Gelap 🌙';
        
        [desktopBtn, mobileBtn].forEach(btn => {
            if (btn) {
                btn.setAttribute('title', titleText);
                btn.setAttribute('aria-label', titleText);
                
                // Animate pulse
                btn.style.transform = 'scale(0.85) rotate(35deg)';
                setTimeout(() => { btn.style.transform = ''; }, 220);
            }
        });
    }

    function showThemeToast(msg) {
        let toast = document.getElementById('globalThemeToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'globalThemeToast';
            toast.style.position = 'fixed';
            toast.style.bottom = '25px';
            toast.style.right = '25px';
            toast.style.padding = '10px 22px';
            toast.style.background = 'var(--text-primary)';
            toast.style.color = 'var(--bg-body)';
            toast.style.borderRadius = '50px';
            toast.style.boxShadow = '0 10px 30px rgba(0,0,0,0.3)';
            toast.style.zIndex = '99999';
            toast.style.fontWeight = '700';
            toast.style.fontSize = '0.9rem';
            toast.style.transition = 'all 0.25s ease';
            toast.style.pointerEvents = 'none';
            document.body.appendChild(toast);
        }
        toast.textContent = msg;
        toast.style.opacity = '1';
        toast.style.display = 'block';

        if (window._themeToastTimer) clearTimeout(window._themeToastTimer);
        window._themeToastTimer = setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => { toast.style.display = 'none'; }, 250);
        }, 1800);
    }

    // Initialize on DOM Ready
    function initTheme() {
        const savedMode = localStorage.getItem('nnaco_theme_mode');
        const savedTheme = localStorage.getItem('nnaco_theme');
        const initial = savedMode || savedTheme || 'light';
        
        window.applyAppTheme(initial, false);

        // Bind Desktop Button
        const desktopBtn = document.getElementById('themeToggleBtn');
        if (desktopBtn) {
            desktopBtn.onclick = function(e) {
                e.preventDefault();
                window.toggleAppTheme();
            };
        }

        // Bind Mobile Button
        const mobileBtn = document.getElementById('themeToggleBtnMobile');
        if (mobileBtn) {
            mobileBtn.onclick = function(e) {
                e.preventDefault();
                window.toggleAppTheme();
            };
        }

        // Listen for OS scheme change if user chose 'system'
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                if (localStorage.getItem('nnaco_theme_mode') === 'system') {
                    window.applyAppTheme('system', false);
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTheme);
    } else {
        initTheme();
    }
})();
