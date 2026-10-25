/**
 * FindACat Theme System
 * Multi-theme support with localStorage persistence
 * Enhanced with comprehensive error handling and robustness
 */

// Utility: Error Logger
class ErrorLogger {
    constructor(moduleName) {
        this.moduleName = moduleName;
        this.errors = [];
        this.maxErrors = 100; // Prevent memory leak
    }

    log(error, context = {}) {
        try {
            const errorEntry = {
                timestamp: new Date().toISOString(),
                module: this.moduleName,
                message: error.message || String(error),
                stack: error.stack || new Error().stack,
                context: context,
                userAgent: navigator.userAgent
            };

            this.errors.push(errorEntry);

            // Keep only recent errors
            if (this.errors.length > this.maxErrors) {
                this.errors.shift();
            }

            // Log to console in development
            if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                console.error(`[${this.moduleName}]`, error, context);
            }

            // Store critical errors in sessionStorage for debugging
            try {
                sessionStorage.setItem(`${this.moduleName}_last_error`, JSON.stringify(errorEntry));
            } catch (storageError) {
                // sessionStorage might be full or disabled
                console.warn('Could not store error in sessionStorage:', storageError);
            }

            return errorEntry;
        } catch (logError) {
            // Fail silently if logging fails
            console.error('Error logger failed:', logError);
        }
    }

    getErrors() {
        return [...this.errors];
    }

    clearErrors() {
        this.errors = [];
        try {
            sessionStorage.removeItem(`${this.moduleName}_last_error`);
        } catch (e) {
            // Ignore
        }
    }
}

// Utility: Safe Storage
class SafeStorage {
    constructor() {
        this.available = this.checkAvailability();
        this.fallbackStorage = new Map();
    }

    checkAvailability() {
        try {
            const test = '__storage_test__';
            localStorage.setItem(test, test);
            localStorage.removeItem(test);
            return true;
        } catch (e) {
            console.warn('localStorage is not available, using fallback storage');
            return false;
        }
    }

    getItem(key, defaultValue = null) {
        try {
            if (this.available) {
                const value = localStorage.getItem(key);
                return value !== null ? value : defaultValue;
            } else {
                return this.fallbackStorage.has(key) ? this.fallbackStorage.get(key) : defaultValue;
            }
        } catch (e) {
            console.error('Error reading from storage:', e);
            return defaultValue;
        }
    }

    setItem(key, value) {
        try {
            if (this.available) {
                localStorage.setItem(key, value);
                return true;
            } else {
                this.fallbackStorage.set(key, value);
                return true;
            }
        } catch (e) {
            console.error('Error writing to storage:', e);
            // Try fallback
            try {
                this.fallbackStorage.set(key, value);
                return true;
            } catch (fallbackError) {
                console.error('Fallback storage also failed:', fallbackError);
                return false;
            }
        }
    }

    removeItem(key) {
        try {
            if (this.available) {
                localStorage.removeItem(key);
            } else {
                this.fallbackStorage.delete(key);
            }
            return true;
        } catch (e) {
            console.error('Error removing from storage:', e);
            return false;
        }
    }
}

class ThemeSystem {
    constructor() {
        try {
            this.logger = new ErrorLogger('ThemeSystem');
            this.storage = new SafeStorage();
            this.initialized = false;
            this.eventListeners = [];

            this.themes = {
                classic: {
                    name: 'Classic',
                    description: 'Modern purple gradient theme',
                    icon: '🎨',
                    preview: 'linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%)'
                },
                neon: {
                    name: 'Neon',
                    description: 'Cyberpunk neon lights',
                    icon: '⚡',
                    preview: 'linear-gradient(135deg, #00ffff 0%, #ff00ff 100%)'
                },
                retro: {
                    name: 'Retro',
                    description: '80s/90s vintage style',
                    icon: '📺',
                    preview: 'linear-gradient(135deg, #e67e22 0%, #d35400 100%)'
                },
                future: {
                    name: 'Future',
                    description: 'Futuristic sci-fi theme',
                    icon: '🚀',
                    preview: 'linear-gradient(135deg, #00d9ff 0%, #00ff88 100%)'
                },
                soothing: {
                    name: 'Soothing',
                    description: 'Calm and peaceful zen',
                    icon: '🍃',
                    preview: 'linear-gradient(135deg, #4caf50 0%, #81c784 100%)'
                },
                macos: {
                    name: 'macOS',
                    description: 'Apple design language',
                    icon: '🍎',
                    preview: 'linear-gradient(135deg, #007aff 0%, #5856d6 100%)'
                },
                windows: {
                    name: 'Windows',
                    description: 'Microsoft Fluent design',
                    icon: '🪟',
                    preview: 'linear-gradient(135deg, #0078d4 0%, #107c10 100%)'
                },
                ocean: {
                    name: 'Ocean',
                    description: 'Deep blue sea vibes',
                    icon: '🌊',
                    preview: 'linear-gradient(135deg, #00acc1 0%, #0097a7 100%)'
                },
                sunset: {
                    name: 'Sunset',
                    description: 'Warm evening colors',
                    icon: '🌅',
                    preview: 'linear-gradient(135deg, #ff6f00 0%, #ff5722 100%)'
                },
                midnight: {
                    name: 'Midnight',
                    description: 'Dark blue night theme',
                    icon: '🌙',
                    preview: 'linear-gradient(135deg, #415a77 0%, #778da9 100%)'
                }
            };

            this.currentTheme = this.loadTheme();
            this.init();

        } catch (error) {
            this.logger?.log(error, { method: 'constructor' });
            console.error('ThemeSystem initialization failed:', error);
            // Set minimal defaults
            this.currentTheme = 'classic';
            this.themes = this.themes || {};
        }
    }

    init() {
        try {
            if (this.initialized) {
                console.warn('ThemeSystem already initialized');
                return;
            }

            // Apply saved theme with error handling
            this.applyTheme(this.currentTheme);

            // Create theme selector if on homepage
            this.createThemeSelector();

            // Listen for storage changes (multi-tab sync) with error handling
            const storageListener = (e) => {
                try {
                    if (e.key === 'selected_theme' && e.newValue) {
                        const newTheme = this.validateTheme(e.newValue);
                        this.applyTheme(newTheme);
                    }
                } catch (error) {
                    this.logger.log(error, { method: 'storageListener', event: e });
                }
            };

            window.addEventListener('storage', storageListener);
            this.eventListeners.push({ element: window, event: 'storage', handler: storageListener });

            this.initialized = true;
            console.log('✅ ThemeSystem initialized successfully');

        } catch (error) {
            this.logger.log(error, { method: 'init' });
            console.error('ThemeSystem init failed:', error);
        }
    }

    validateTheme(theme) {
        // Validate theme is a safe string
        if (typeof theme !== 'string') {
            this.logger.log(new Error('Invalid theme type'), { theme, type: typeof theme });
            return 'classic';
        }

        // Check if theme exists
        if (!this.themes[theme]) {
            this.logger.log(new Error('Unknown theme'), { theme });
            return 'classic';
        }

        // Sanitize theme name (prevent XSS)
        const sanitized = theme.replace(/[^a-z]/gi, '').toLowerCase();
        if (this.themes[sanitized]) {
            return sanitized;
        }

        return 'classic';
    }

    loadTheme() {
        try {
            const savedTheme = this.storage.getItem('selected_theme', 'classic');
            return this.validateTheme(savedTheme);
        } catch (error) {
            this.logger.log(error, { method: 'loadTheme' });
            return 'classic';
        }
    }

    saveTheme(theme) {
        try {
            const validTheme = this.validateTheme(theme);
            const success = this.storage.setItem('selected_theme', validTheme);
            if (!success) {
                this.logger.log(new Error('Failed to save theme'), { theme: validTheme });
                // Show user notification if toast is available
                if (typeof toast !== 'undefined' && toast.warning) {
                    toast.warning('Theme preference could not be saved', 3000);
                }
            }
            return success;
        } catch (error) {
            this.logger.log(error, { method: 'saveTheme', theme });
            return false;
        }
    }

    applyTheme(theme) {
        try {
            const validTheme = this.validateTheme(theme);

            // Check if document is ready
            if (!document.documentElement) {
                this.logger.log(new Error('Document not ready'), { method: 'applyTheme' });
                // Retry after DOM is ready
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', () => {
                        this.applyTheme(validTheme);
                    }, { once: true });
                }
                return false;
            }

            // Set data attribute on html element
            document.documentElement.setAttribute('data-theme', validTheme);
            this.currentTheme = validTheme;

            // Update theme toggle icon if it exists
            try {
                const themeIcon = document.querySelector('.theme-icon');
                if (themeIcon && this.themes[validTheme]) {
                    themeIcon.textContent = this.themes[validTheme].icon;
                }
            } catch (iconError) {
                this.logger.log(iconError, { method: 'applyTheme.updateIcon' });
            }

            // Trigger theme change event with error handling
            try {
                const event = new CustomEvent('themeChanged', {
                    detail: { theme: validTheme },
                    bubbles: true,
                    cancelable: false
                });
                document.dispatchEvent(event);
            } catch (eventError) {
                this.logger.log(eventError, { method: 'applyTheme.dispatchEvent' });
            }

            // Show success notification if toast is available
            try {
                if (typeof toast !== 'undefined' && toast.success && this.themes[validTheme]) {
                    toast.success(`${this.themes[validTheme].name} theme applied!`, 2000);
                }
            } catch (toastError) {
                // Toast might not be available, that's okay
            }

            return true;

        } catch (error) {
            this.logger.log(error, { method: 'applyTheme', theme });
            return false;
        }
    }

    switchTheme(theme) {
        try {
            const validTheme = this.validateTheme(theme);
            const applied = this.applyTheme(validTheme);
            if (applied) {
                this.saveTheme(validTheme);
            }
            return applied;
        } catch (error) {
            this.logger.log(error, { method: 'switchTheme', theme });
            return false;
        }
    }

    createThemeSelector() {
        try {
            // Only on homepage/search page
            const isHomepage = window.location.pathname === '/' ||
                              window.location.pathname.includes('search') ||
                              window.location.pathname.includes('profile-search');

            if (!isHomepage) return;

            // Check if already exists
            if (document.getElementById('theme-selector-panel')) {
                console.log('Theme selector already exists');
                return;
            }

            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    this.createThemeSelector();
                }, { once: true });
                return;
            }

            const panel = document.createElement('div');
            panel.id = 'theme-selector-panel';
            panel.className = 'theme-selector-panel';

            // Build theme cards HTML with error handling
            let themesHTML = '';
            try {
                themesHTML = Object.keys(this.themes).map(themeKey => {
                    try {
                        const theme = this.themes[themeKey];
                        if (!theme || !theme.name || !theme.preview) {
                            throw new Error('Invalid theme data');
                        }

                        return `
                            <div class="theme-card ${this.currentTheme === themeKey ? 'active' : ''}"
                                 data-theme="${this.escapeHtml(themeKey)}"
                                 onclick="themeSystem.switchTheme('${this.escapeHtml(themeKey)}')">
                                <div class="theme-preview" style="background: ${this.escapeHtml(theme.preview)}">
                                    <div class="theme-icon">${this.escapeHtml(theme.icon || '🎨')}</div>
                                </div>
                                <div class="theme-info">
                                    <div class="theme-name">${this.escapeHtml(theme.name)}</div>
                                    <div class="theme-description">${this.escapeHtml(theme.description || '')}</div>
                                </div>
                                <div class="theme-checkmark">✓</div>
                            </div>
                        `;
                    } catch (themeError) {
                        this.logger.log(themeError, { method: 'createThemeSelector.mapTheme', themeKey });
                        return ''; // Skip invalid theme
                    }
                }).filter(html => html).join('');
            } catch (mapError) {
                this.logger.log(mapError, { method: 'createThemeSelector.mapThemes' });
                themesHTML = '<p>Error loading themes</p>';
            }

            panel.innerHTML = `
                <div class="theme-selector-header">
                    <h3>🎨 Choose Your Theme</h3>
                    <p>Pick a style that suits your mood</p>
                </div>
                <div class="theme-selector-grid">
                    ${themesHTML}
                </div>
            `;

            // Insert into DOM safely
            try {
                const container = document.querySelector('.container') || document.querySelector('main');
                if (!container) {
                    throw new Error('Container element not found');
                }

                const heroBanner = container.querySelector('.hero-banner');
                if (heroBanner) {
                    heroBanner.after(panel);
                } else {
                    container.insertBefore(panel, container.firstChild);
                }
            } catch (insertError) {
                this.logger.log(insertError, { method: 'createThemeSelector.insert' });
                // Try appending to body as fallback
                document.body.appendChild(panel);
            }

            // Add event listener for theme changes
            const themeChangeListener = (e) => {
                try {
                    if (!e.detail || !e.detail.theme) return;

                    // Update active state on all cards
                    const cards = document.querySelectorAll('.theme-card');
                    cards.forEach(card => {
                        try {
                            card.classList.remove('active');
                        } catch (e) {
                            // Ignore individual card errors
                        }
                    });

                    // Add active class to selected theme
                    const activeCard = document.querySelector(`.theme-card[data-theme="${this.escapeHtml(e.detail.theme)}"]`);
                    if (activeCard) {
                        activeCard.classList.add('active');
                    }
                } catch (error) {
                    this.logger.log(error, { method: 'themeChangeListener' });
                }
            };

            document.addEventListener('themeChanged', themeChangeListener);
            this.eventListeners.push({
                element: document,
                event: 'themeChanged',
                handler: themeChangeListener
            });

        } catch (error) {
            this.logger.log(error, { method: 'createThemeSelector' });
        }
    }

    escapeHtml(text) {
        if (typeof text !== 'string') return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    destroy() {
        try {
            // Clean up event listeners
            this.eventListeners.forEach(({ element, event, handler }) => {
                try {
                    element.removeEventListener(event, handler);
                } catch (e) {
                    this.logger.log(e, { method: 'destroy.removeEventListener' });
                }
            });
            this.eventListeners = [];

            // Remove theme selector
            const panel = document.getElementById('theme-selector-panel');
            if (panel) {
                panel.remove();
            }

            this.initialized = false;
            console.log('ThemeSystem destroyed');
        } catch (error) {
            this.logger.log(error, { method: 'destroy' });
        }
    }
}

// Initialize theme system with error handling
(function initThemeSystem() {
    try {
        if (window.themeSystem) {
            console.warn('ThemeSystem already exists');
            return;
        }

        window.themeSystem = new ThemeSystem();
        console.log('🎨 Theme System Loaded with', Object.keys(window.themeSystem.themes).length, 'themes');

    } catch (error) {
        console.error('Failed to initialize ThemeSystem:', error);
        // Create a minimal fallback
        window.themeSystem = {
            switchTheme: () => console.warn('ThemeSystem failed to load'),
            applyTheme: () => console.warn('ThemeSystem failed to load'),
            currentTheme: 'classic',
            themes: {}
        };
    }
})();

// Enhanced theme toggle with long-press support
document.addEventListener('DOMContentLoaded', () => {
    try {
        const themeToggle = document.getElementById('themeToggle');
        if (!themeToggle) {
            console.log('Theme toggle button not found');
            return;
        }

        let pressTimer = null;
        let touchHandled = false;

        // Mouse events
        const mouseDownHandler = (e) => {
            try {
                touchHandled = false;
                pressTimer = setTimeout(() => {
                    if (!touchHandled) {
                        showQuickThemeSelector(e);
                    }
                }, 500);
            } catch (error) {
                console.error('Mouse down handler error:', error);
            }
        };

        const mouseUpHandler = () => {
            try {
                if (pressTimer) {
                    clearTimeout(pressTimer);
                    pressTimer = null;
                }
            } catch (error) {
                console.error('Mouse up handler error:', error);
            }
        };

        // Touch events
        const touchStartHandler = (e) => {
            try {
                touchHandled = true;
                if (pressTimer) {
                    clearTimeout(pressTimer);
                }
                pressTimer = setTimeout(() => {
                    showQuickThemeSelector(e);
                }, 500);
            } catch (error) {
                console.error('Touch start handler error:', error);
            }
        };

        const touchEndHandler = () => {
            try {
                if (pressTimer) {
                    clearTimeout(pressTimer);
                    pressTimer = null;
                }
            } catch (error) {
                console.error('Touch end handler error:', error);
            }
        };

        // Add event listeners with error handling
        themeToggle.addEventListener('mousedown', mouseDownHandler);
        themeToggle.addEventListener('mouseup', mouseUpHandler);
        themeToggle.addEventListener('mouseleave', mouseUpHandler);
        themeToggle.addEventListener('touchstart', touchStartHandler, { passive: true });
        themeToggle.addEventListener('touchend', touchEndHandler);
        themeToggle.addEventListener('touchcancel', touchEndHandler);

    } catch (error) {
        console.error('Error setting up theme toggle:', error);
    }
});

function showQuickThemeSelector(e) {
    try {
        e.preventDefault();
        e.stopPropagation();

        // Remove existing selector
        const existing = document.querySelector('.quick-theme-selector');
        if (existing) {
            existing.remove();
            return;
        }

        // Check if themeSystem is available
        if (!window.themeSystem || !window.themeSystem.themes) {
            console.error('ThemeSystem not available');
            return;
        }

        const selector = document.createElement('div');
        selector.className = 'quick-theme-selector';

        try {
            selector.innerHTML = `
                <div class="quick-theme-header">Select Theme</div>
                <div class="quick-theme-list">
                    ${Object.keys(window.themeSystem.themes).map(themeKey => {
                        try {
                            const theme = window.themeSystem.themes[themeKey];
                            const isActive = window.themeSystem.currentTheme === themeKey;
                            return `
                                <div class="quick-theme-item ${isActive ? 'active' : ''}"
                                     data-theme="${themeKey}"
                                     onclick="handleQuickThemeClick('${themeKey}')">
                                    <span class="quick-theme-icon">${theme.icon || '🎨'}</span>
                                    <span class="quick-theme-name">${theme.name || themeKey}</span>
                                    <span class="quick-theme-check">✓</span>
                                </div>
                            `;
                        } catch (themeError) {
                            console.error('Error creating theme item:', themeError);
                            return '';
                        }
                    }).filter(html => html).join('')}
                </div>
            `;
        } catch (htmlError) {
            console.error('Error building theme selector HTML:', htmlError);
            selector.innerHTML = '<div class="quick-theme-header">Error loading themes</div>';
        }

        document.body.appendChild(selector);

        // Add animation class after a frame
        requestAnimationFrame(() => {
            selector.classList.add('show');
        });

        // Close on outside click
        setTimeout(() => {
            const closeHandler = (event) => {
                try {
                    if (!selector.contains(event.target)) {
                        selector.classList.remove('show');
                        setTimeout(() => selector.remove(), 300);
                        document.removeEventListener('click', closeHandler);
                    }
                } catch (error) {
                    console.error('Error in close handler:', error);
                    selector.remove();
                    document.removeEventListener('click', closeHandler);
                }
            };
            document.addEventListener('click', closeHandler);
        }, 100);

        // Show helpful toast
        try {
            if (typeof toast !== 'undefined' && toast.info) {
                toast.info('Long-press theme button to see all themes!', 3000);
            }
        } catch (toastError) {
            // Toast not available
        }

    } catch (error) {
        console.error('Error showing quick theme selector:', error);
    }
}

// Global handler for quick theme clicks
window.handleQuickThemeClick = function(themeKey) {
    try {
        if (window.themeSystem && window.themeSystem.switchTheme) {
            window.themeSystem.switchTheme(themeKey);
        }

        const selector = document.querySelector('.quick-theme-selector');
        if (selector) {
            selector.classList.remove('show');
            setTimeout(() => selector.remove(), 300);
        }
    } catch (error) {
        console.error('Error handling theme click:', error);
    }
};

// Animated cats with enhanced error handling
class AnimatedCats {
    constructor() {
        try {
            this.logger = new ErrorLogger('AnimatedCats');
            this.cats = [];
            this.container = null;
            this.init();
        } catch (error) {
            console.error('AnimatedCats initialization failed:', error);
        }
    }

    init() {
        try {
            // Only on homepage
            const isHomepage = window.location.pathname === '/' ||
                              window.location.pathname.includes('search') ||
                              window.location.pathname.includes('profile-search');

            if (!isHomepage) return;

            // Wait for DOM if needed
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    this.createCats();
                }, { once: true });
            } else {
                this.createCats();
            }
        } catch (error) {
            this.logger.log(error, { method: 'init' });
        }
    }

    createCats() {
        try {
            // Check if already exists
            if (document.querySelector('.animated-cats-container')) {
                console.log('Animated cats already exist');
                return;
            }

            // Check if user prefers reduced motion
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (prefersReducedMotion) {
                console.log('User prefers reduced motion, skipping animated cats');
                return;
            }

            this.container = document.createElement('div');
            this.container.className = 'animated-cats-container';
            this.container.setAttribute('aria-hidden', 'true'); // Accessibility: decorative only

            // Create 8 animated cats
            for (let i = 1; i <= 8; i++) {
                try {
                    const cat = this.createCat(i);
                    if (cat) {
                        this.cats.push(cat);
                        this.container.appendChild(cat);
                    }
                } catch (catError) {
                    this.logger.log(catError, { method: 'createCats', catIndex: i });
                }
            }

            // Add to body
            if (document.body) {
                document.body.insertBefore(this.container, document.body.firstChild);
            } else {
                throw new Error('document.body not available');
            }

            // Add float-up animation style
            this.addFloatUpAnimation();

            console.log(`✅ Created ${this.cats.length} animated cats`);

        } catch (error) {
            this.logger.log(error, { method: 'createCats' });
        }
    }

    createCat(index) {
        try {
            const cat = document.createElement('div');
            cat.className = `animated-cat cat-${index}`;
            cat.setAttribute('role', 'presentation');
            cat.style.cursor = 'pointer';

            // Add click event for fun interaction
            const clickHandler = () => {
                try {
                    this.handleCatClick(cat);
                } catch (error) {
                    this.logger.log(error, { method: 'catClickHandler', index });
                }
            };

            cat.addEventListener('click', clickHandler);

            return cat;

        } catch (error) {
            this.logger.log(error, { method: 'createCat', index });
            return null;
        }
    }

    handleCatClick(cat) {
        try {
            // Reset animation
            const currentAnimation = cat.style.animation;
            cat.style.animation = 'none';

            // Force reflow
            cat.offsetHeight;

            // Restore animation
            setTimeout(() => {
                cat.style.animation = currentAnimation || '';
            }, 10);

            // Random meow sound text
            const meows = ['Meow!', 'Purr~', 'Mrow!', '😸', '😻', '😺', 'Nya!', '🐾'];
            const meow = meows[Math.floor(Math.random() * meows.length)];

            const meowText = document.createElement('div');
            meowText.textContent = meow;
            meowText.className = 'meow-text';
            meowText.setAttribute('aria-live', 'polite');
            meowText.style.cssText = `
                position: absolute;
                top: -30px;
                left: 50%;
                transform: translateX(-50%);
                color: var(--primary, #6c5ce7);
                font-weight: 700;
                font-size: 20px;
                animation: float-up 1s ease-out;
                pointer-events: none;
                z-index: 1000;
            `;

            cat.style.position = 'relative';
            cat.appendChild(meowText);

            setTimeout(() => {
                try {
                    if (meowText.parentNode) {
                        meowText.remove();
                    }
                } catch (e) {
                    // Ignore removal errors
                }
            }, 1000);

        } catch (error) {
            this.logger.log(error, { method: 'handleCatClick' });
        }
    }

    addFloatUpAnimation() {
        try {
            if (document.getElementById('float-up-animation')) {
                return; // Already exists
            }

            const style = document.createElement('style');
            style.id = 'float-up-animation';
            style.textContent = `
                @keyframes float-up {
                    from {
                        opacity: 1;
                        transform: translateX(-50%) translateY(0);
                    }
                    to {
                        opacity: 0;
                        transform: translateX(-50%) translateY(-50px);
                    }
                }
            `;

            if (document.head) {
                document.head.appendChild(style);
            } else {
                throw new Error('document.head not available');
            }

        } catch (error) {
            this.logger.log(error, { method: 'addFloatUpAnimation' });
        }
    }

    destroy() {
        try {
            if (this.container && this.container.parentNode) {
                this.container.remove();
            }
            this.cats = [];
            this.container = null;
            console.log('AnimatedCats destroyed');
        } catch (error) {
            this.logger.log(error, { method: 'destroy' });
        }
    }
}

// Initialize animated cats with error handling
(function initAnimatedCats() {
    try {
        if (window.animatedCats) {
            console.warn('AnimatedCats already exists');
            return;
        }

        window.animatedCats = new AnimatedCats();
        console.log('🐱 Animated Cats Loaded');

    } catch (error) {
        console.error('Failed to initialize AnimatedCats:', error);
        window.animatedCats = {
            destroy: () => console.warn('AnimatedCats failed to load')
        };
    }
})();

// Global error handler for unhandled errors
window.addEventListener('error', (event) => {
    if (event.filename && event.filename.includes('theme-system.js')) {
        console.error('Unhandled error in theme-system.js:', event.error);
    }
});

// Expose utilities for debugging
if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    window.themeSystemDebug = {
        getErrors: () => window.themeSystem?.logger?.getErrors() || [],
        getCatsErrors: () => window.animatedCats?.logger?.getErrors() || [],
        clearErrors: () => {
            window.themeSystem?.logger?.clearErrors();
            window.animatedCats?.logger?.clearErrors();
        },
        testTheme: (theme) => window.themeSystem?.switchTheme(theme),
        resetTheme: () => {
            window.themeSystem?.switchTheme('classic');
            window.themeSystem?.storage?.removeItem('selected_theme');
        }
    };
    console.log('🔧 Debug utilities available: window.themeSystemDebug');
}
