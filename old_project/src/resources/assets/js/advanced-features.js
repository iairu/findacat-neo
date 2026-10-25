/**
 * FindACat Advanced Features
 * Toast notifications, tooltips, favorites, recent views, and more
 */

// ==========================================
// TOAST NOTIFICATION SYSTEM
// ==========================================
class ToastManager {
    constructor() {
        this.container = this.createContainer();
    }

    createContainer() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        return container;
    }

    show(message, type = 'info', duration = 4000) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type} toast-enter`;

        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };

        toast.innerHTML = `
            <span class="toast-icon">${icons[type] || icons.info}</span>
            <span class="toast-message">${message}</span>
            <button class="toast-close" onclick="this.parentElement.remove()">×</button>
        `;

        this.container.appendChild(toast);

        // Animate in
        setTimeout(() => toast.classList.add('toast-show'), 10);

        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                toast.classList.remove('toast-show');
                toast.classList.add('toast-exit');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }

        return toast;
    }

    success(message, duration) { return this.show(message, 'success', duration); }
    error(message, duration) { return this.show(message, 'error', duration); }
    warning(message, duration) { return this.show(message, 'warning', duration); }
    info(message, duration) { return this.show(message, 'info', duration); }
}

// Global toast instance
window.toast = new ToastManager();

// ==========================================
// TOOLTIP SYSTEM
// ==========================================
class TooltipManager {
    constructor() {
        this.init();
    }

    init() {
        document.addEventListener('mouseover', (e) => {
            const target = e.target.closest('[data-tooltip]');
            if (target && !target.querySelector('.tooltip')) {
                this.show(target);
            }
        });

        document.addEventListener('mouseout', (e) => {
            const target = e.target.closest('[data-tooltip]');
            if (target) {
                this.hide(target);
            }
        });
    }

    show(element) {
        const text = element.getAttribute('data-tooltip');
        const position = element.getAttribute('data-tooltip-position') || 'top';

        const tooltip = document.createElement('div');
        tooltip.className = `tooltip tooltip-${position}`;
        tooltip.textContent = text;

        element.style.position = 'relative';
        element.appendChild(tooltip);

        setTimeout(() => tooltip.classList.add('tooltip-visible'), 10);
    }

    hide(element) {
        const tooltip = element.querySelector('.tooltip');
        if (tooltip) {
            tooltip.classList.remove('tooltip-visible');
            setTimeout(() => tooltip.remove(), 200);
        }
    }
}

// Initialize tooltips
window.tooltipManager = new TooltipManager();

// ==========================================
// FAVORITES SYSTEM
// ==========================================
class FavoritesManager {
    constructor() {
        this.storageKey = 'findacat_favorites';
        this.favorites = this.load();
        this.init();
    }

    load() {
        try {
            return JSON.parse(localStorage.getItem(this.storageKey) || '[]');
        } catch {
            return [];
        }
    }

    save() {
        localStorage.setItem(this.storageKey, JSON.stringify(this.favorites));
    }

    add(catId, catName) {
        if (!this.isFavorite(catId)) {
            this.favorites.push({ id: catId, name: catName, addedAt: new Date().toISOString() });
            this.save();
            this.updateUI();
            toast.success(`${catName} added to favorites!`);
            return true;
        }
        return false;
    }

    remove(catId) {
        const cat = this.favorites.find(f => f.id === catId);
        this.favorites = this.favorites.filter(f => f.id !== catId);
        this.save();
        this.updateUI();
        if (cat) {
            toast.info(`${cat.name} removed from favorites`);
        }
        return true;
    }

    toggle(catId, catName) {
        if (this.isFavorite(catId)) {
            this.remove(catId);
        } else {
            this.add(catId, catName);
        }
    }

    isFavorite(catId) {
        return this.favorites.some(f => f.id === catId);
    }

    getAll() {
        return this.favorites;
    }

    init() {
        // Add favorite buttons to cat profiles
        document.addEventListener('DOMContentLoaded', () => {
            this.updateUI();
        });
    }

    updateUI() {
        // Update all favorite buttons
        document.querySelectorAll('[data-favorite-id]').forEach(btn => {
            const catId = btn.getAttribute('data-favorite-id');
            const isFav = this.isFavorite(catId);
            btn.innerHTML = isFav ? '★' : '☆';
            btn.classList.toggle('is-favorite', isFav);
            btn.setAttribute('data-tooltip', isFav ? 'Remove from favorites' : 'Add to favorites');
        });

        // Update favorites count
        const count = this.favorites.length;
        document.querySelectorAll('.favorites-count').forEach(el => {
            el.textContent = count;
        });
    }
}

window.favorites = new FavoritesManager();

// ==========================================
// RECENT VIEWS SYSTEM
// ==========================================
class RecentViewsManager {
    constructor() {
        this.storageKey = 'findacat_recent_views';
        this.maxItems = 10;
        this.recent = this.load();
    }

    load() {
        try {
            return JSON.parse(localStorage.getItem(this.storageKey) || '[]');
        } catch {
            return [];
        }
    }

    save() {
        localStorage.setItem(this.storageKey, JSON.stringify(this.recent));
    }

    add(catId, catName, catBreed) {
        // Remove if already exists
        this.recent = this.recent.filter(r => r.id !== catId);

        // Add to beginning
        this.recent.unshift({
            id: catId,
            name: catName,
            breed: catBreed,
            viewedAt: new Date().toISOString()
        });

        // Limit to max items
        if (this.recent.length > this.maxItems) {
            this.recent = this.recent.slice(0, this.maxItems);
        }

        this.save();
    }

    getAll() {
        return this.recent;
    }

    clear() {
        this.recent = [];
        this.save();
        toast.info('Recent views cleared');
    }
}

window.recentViews = new RecentViewsManager();

// ==========================================
// LOADING PROGRESS BAR
// ==========================================
class ProgressBar {
    constructor() {
        this.create();
    }

    create() {
        const bar = document.createElement('div');
        bar.id = 'progress-bar';
        bar.className = 'progress-bar';
        bar.innerHTML = '<div class="progress-bar-fill"></div>';
        document.body.appendChild(bar);
        this.bar = bar;
        this.fill = bar.querySelector('.progress-bar-fill');
    }

    start() {
        this.bar.classList.add('progress-bar-active');
        this.fill.style.width = '0%';
        this.fill.style.transition = 'none';

        setTimeout(() => {
            this.fill.style.transition = 'width 0.3s ease';
            this.fill.style.width = '70%';
        }, 10);
    }

    complete() {
        this.fill.style.width = '100%';
        setTimeout(() => {
            this.bar.classList.remove('progress-bar-active');
        }, 300);
    }

    reset() {
        this.bar.classList.remove('progress-bar-active');
        this.fill.style.width = '0%';
    }
}

window.progressBar = new ProgressBar();

// ==========================================
// SMOOTH SCROLL
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '#!') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
});

// ==========================================
// SEARCH ENHANCEMENTS
// ==========================================
class SearchEnhancer {
    constructor() {
        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            // Add search history
            this.setupSearchHistory();

            // Add quick filters
            this.setupQuickFilters();

            // Live search character counter
            this.setupCharCounter();
        });
    }

    setupSearchHistory() {
        const searchInputs = document.querySelectorAll('input[name="full_name"], input[name="q"]');
        searchInputs.forEach(input => {
            // Show recent searches on focus
            input.addEventListener('focus', () => {
                const history = this.getSearchHistory();
                if (history.length > 0) {
                    this.showSearchSuggestions(input, history);
                }
            });

            // Save search on submit
            input.closest('form')?.addEventListener('submit', () => {
                if (input.value.trim()) {
                    this.addToSearchHistory(input.value.trim());
                }
            });
        });
    }

    getSearchHistory() {
        try {
            return JSON.parse(localStorage.getItem('search_history') || '[]');
        } catch {
            return [];
        }
    }

    addToSearchHistory(query) {
        let history = this.getSearchHistory();
        history = [query, ...history.filter(h => h !== query)].slice(0, 5);
        localStorage.setItem('search_history', JSON.stringify(history));
    }

    showSearchSuggestions(input, suggestions) {
        // Remove existing suggestions
        const existing = document.querySelector('.search-suggestions');
        if (existing) existing.remove();

        const container = document.createElement('div');
        container.className = 'search-suggestions';
        container.innerHTML = suggestions.map(s =>
            `<div class="search-suggestion" data-value="${s}">
                <span class="search-suggestion-icon">🔍</span>
                <span class="search-suggestion-text">${s}</span>
            </div>`
        ).join('');

        input.parentElement.style.position = 'relative';
        input.parentElement.appendChild(container);

        // Click to fill
        container.querySelectorAll('.search-suggestion').forEach(item => {
            item.addEventListener('click', () => {
                input.value = item.getAttribute('data-value');
                container.remove();
                input.focus();
            });
        });

        // Close on outside click
        document.addEventListener('click', function closeHandler(e) {
            if (!container.contains(e.target) && e.target !== input) {
                container.remove();
                document.removeEventListener('click', closeHandler);
            }
        });
    }

    setupQuickFilters() {
        // Add animated filter toggle buttons
        const forms = document.querySelectorAll('form[method="get"]');
        forms.forEach(form => {
            const filters = form.querySelectorAll('select, input[type="date"]');
            if (filters.length > 3) {
                this.addFilterToggle(form);
            }
        });
    }

    addFilterToggle(form) {
        const filterButton = document.createElement('button');
        filterButton.type = 'button';
        filterButton.className = 'btn btn-filter-toggle';
        filterButton.innerHTML = '🔧 Advanced Filters';

        const filtersContainer = form.querySelector('.filters, .form-group');
        if (filtersContainer) {
            filtersContainer.classList.add('filters-collapsible', 'filters-collapsed');
            filterButton.addEventListener('click', () => {
                filtersContainer.classList.toggle('filters-collapsed');
                filterButton.innerHTML = filtersContainer.classList.contains('filters-collapsed')
                    ? '🔧 Advanced Filters'
                    : '🔧 Hide Filters';
            });
            form.insertBefore(filterButton, filtersContainer);
        }
    }

    setupCharCounter() {
        document.querySelectorAll('textarea, input[type="text"][maxlength]').forEach(input => {
            const maxLength = input.getAttribute('maxlength');
            if (maxLength) {
                const counter = document.createElement('div');
                counter.className = 'char-counter';
                counter.textContent = `0 / ${maxLength}`;
                input.parentElement.appendChild(counter);

                input.addEventListener('input', () => {
                    counter.textContent = `${input.value.length} / ${maxLength}`;
                    counter.classList.toggle('char-counter-warning', input.value.length > maxLength * 0.9);
                });
            }
        });
    }
}

window.searchEnhancer = new SearchEnhancer();

// ==========================================
// QUICK ACTIONS MENU
// ==========================================
class QuickActionsMenu {
    constructor() {
        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.createMenu();
        });
    }

    createMenu() {
        const menu = document.createElement('div');
        menu.className = 'quick-actions-menu';
        menu.innerHTML = `
            <button class="quick-action-btn" id="scrollToTop" data-tooltip="Back to top" data-tooltip-position="left">
                <span>↑</span>
            </button>
            <button class="quick-action-btn" id="viewFavorites" data-tooltip="View favorites" data-tooltip-position="left">
                <span>★</span>
                <span class="badge favorites-count">0</span>
            </button>
            <button class="quick-action-btn" id="viewRecent" data-tooltip="Recent views" data-tooltip-position="left">
                <span>🕐</span>
            </button>
        `;
        document.body.appendChild(menu);

        // Scroll to top
        document.getElementById('scrollToTop').addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Show favorites modal
        document.getElementById('viewFavorites').addEventListener('click', () => {
            this.showFavoritesModal();
        });

        // Show recent views modal
        document.getElementById('viewRecent').addEventListener('click', () => {
            this.showRecentViewsModal();
        });

        // Show/hide based on scroll
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                menu.classList.add('quick-actions-visible');
            } else {
                menu.classList.remove('quick-actions-visible');
            }
        });
    }

    showFavoritesModal() {
        const favs = favorites.getAll();
        const content = favs.length > 0
            ? favs.map(f => `
                <div class="modal-list-item">
                    <a href="/cats/${f.id}">${f.name}</a>
                    <button onclick="favorites.remove('${f.id}')" class="btn-icon">×</button>
                </div>
            `).join('')
            : '<p class="text-muted">No favorites yet. Click the star on any cat profile to add!</p>';

        this.showModal('Your Favorites ★', content);
    }

    showRecentViewsModal() {
        const recent = recentViews.getAll();
        const content = recent.length > 0
            ? recent.map(r => `
                <div class="modal-list-item">
                    <a href="/cats/${r.id}">${r.name} <small>(${r.breed})</small></a>
                </div>
            `).join('')
            : '<p class="text-muted">No recent views yet.</p>';

        this.showModal('Recent Views 🕐', content);
    }

    showModal(title, content) {
        const existing = document.querySelector('.quick-modal');
        if (existing) existing.remove();

        const modal = document.createElement('div');
        modal.className = 'quick-modal';
        modal.innerHTML = `
            <div class="quick-modal-overlay"></div>
            <div class="quick-modal-content">
                <div class="quick-modal-header">
                    <h3>${title}</h3>
                    <button class="quick-modal-close">×</button>
                </div>
                <div class="quick-modal-body">
                    ${content}
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        setTimeout(() => modal.classList.add('quick-modal-visible'), 10);

        const close = () => {
            modal.classList.remove('quick-modal-visible');
            setTimeout(() => modal.remove(), 300);
        };

        modal.querySelector('.quick-modal-close').addEventListener('click', close);
        modal.querySelector('.quick-modal-overlay').addEventListener('click', close);
    }
}

window.quickActions = new QuickActionsMenu();

// ==========================================
// AUTO-SAVE FORM DATA
// ==========================================
class FormAutoSave {
    constructor() {
        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            const forms = document.querySelectorAll('form[data-autosave]');
            forms.forEach(form => this.enableAutoSave(form));
        });
    }

    enableAutoSave(form) {
        const formId = form.getAttribute('data-autosave');

        // Load saved data
        this.loadFormData(form, formId);

        // Save on input
        form.addEventListener('input', () => {
            this.saveFormData(form, formId);
            this.showAutoSaveIndicator(form);
        });
    }

    saveFormData(form, formId) {
        const data = new FormData(form);
        const obj = {};
        data.forEach((value, key) => obj[key] = value);
        localStorage.setItem(`autosave_${formId}`, JSON.stringify(obj));
    }

    loadFormData(form, formId) {
        try {
            const saved = JSON.parse(localStorage.getItem(`autosave_${formId}`));
            if (saved) {
                Object.keys(saved).forEach(key => {
                    const input = form.elements[key];
                    if (input) input.value = saved[key];
                });
            }
        } catch {}
    }

    showAutoSaveIndicator(form) {
        let indicator = form.querySelector('.autosave-indicator');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.className = 'autosave-indicator';
            indicator.textContent = '✓ Saved';
            form.appendChild(indicator);
        }
        indicator.classList.add('autosave-indicator-visible');
        setTimeout(() => {
            indicator.classList.remove('autosave-indicator-visible');
        }, 2000);
    }
}

window.formAutoSave = new FormAutoSave();

// Initialize on page load
console.log('🐱 FindACat Advanced Features Loaded');
