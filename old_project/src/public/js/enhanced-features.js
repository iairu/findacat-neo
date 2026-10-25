/**
 * FindACat Enhanced Features
 * Drag-and-drop uploads, PawPeds integration, SPA navigation, live stats
 * Enhanced with comprehensive error handling and robustness
 */

// Utility: Network Request with Retry
class NetworkRetry {
    static async fetchWithRetry(url, options = {}, maxRetries = 3) {
        const delays = [1000, 2000, 4000]; // Exponential backoff

        for (let i = 0; i <= maxRetries; i++) {
            try {
                const controller = new AbortController();
                const timeout = setTimeout(() => controller.abort(), options.timeout || 30000);

                const response = await fetch(url, {
                    ...options,
                    signal: controller.signal
                });

                clearTimeout(timeout);

                if (!response.ok && i < maxRetries && response.status >= 500) {
                    throw new Error(`HTTP ${response.status}`);
                }

                return response;

            } catch (error) {
                clearTimeout(timeout);

                if (i === maxRetries || error.name === 'AbortError') {
                    throw error;
                }

                // Wait before retry
                if (i < maxRetries) {
                    console.log(`Retry ${i + 1}/${maxRetries} after ${delays[i]}ms`);
                    await new Promise(resolve => setTimeout(resolve, delays[i]));
                }
            }
        }
    }
}

// ==========================================
// SPA-LIKE PAGE NAVIGATION (YouTube Style)
// ==========================================
class SPANavigator {
    constructor() {
        this.logger = window.ErrorLogger ? new window.ErrorLogger('SPANavigator') : null;
        this.navigating = false;
        this.maxRetries = 2;
        this.init();
    }

    init() {
        try {
            if (this.initialized) {
                console.warn('SPANavigator already initialized');
                return;
            }

            // Intercept all internal links
            const clickHandler = (e) => {
                try {
                    const link = e.target.closest('a');

                    if (link &&
                        link.href &&
                        link.host === window.location.host &&
                        !link.getAttribute('target') &&
                        !link.hasAttribute('download') &&
                        !link.href.startsWith('javascript:') &&
                        !link.href.includes('#') &&
                        !link.closest('[data-no-spa]')) {

                        e.preventDefault();
                        this.navigateTo(link.href);
                    }
                } catch (error) {
                    this.logError(error, { method: 'clickHandler' });
                }
            };

            document.addEventListener('click', clickHandler);

            // Handle browser back/forward
            const popStateHandler = (e) => {
                try {
                    if (e.state && e.state.url) {
                        this.loadPage(e.state.url, false);
                    }
                } catch (error) {
                    this.logError(error, { method: 'popStateHandler' });
                }
            };

            window.addEventListener('popstate', popStateHandler);

            this.initialized = true;
            console.log('✅ SPANavigator initialized');

        } catch (error) {
            this.logError(error, { method: 'init' });
        }
    }

    async navigateTo(url) {
        try {
            if (this.navigating) {
                console.warn('Navigation already in progress');
                return;
            }

            // Validate URL
            if (!url || typeof url !== 'string') {
                throw new Error('Invalid URL');
            }

            this.navigating = true;

            // Start loading indicators with safe checks
            if (typeof progressBar !== 'undefined' && progressBar.start) {
                progressBar.start();
            }

            if (document.body) {
                document.body.classList.add('page-loading');
            }

            // Add to history
            try {
                window.history.pushState({ url }, '', url);
            } catch (historyError) {
                this.logError(historyError, { method: 'navigateTo.pushState', url });
            }

            // Load the page
            await this.loadPage(url, true);

        } catch (error) {
            this.logError(error, { method: 'navigateTo', url });
            this.fallbackNavigation(url);
        } finally {
            this.navigating = false;
        }
    }

    async loadPage(url, addToHistory = true) {
        try {
            if (!url || typeof url !== 'string') {
                throw new Error('Invalid URL for page load');
            }

            const response = await NetworkRetry.fetchWithRetry(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                timeout: 15000
            }, this.maxRetries);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const html = await response.text();

            if (!html || html.length === 0) {
                throw new Error('Empty response received');
            }

            // Parse the HTML safely
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            if (!doc) {
                throw new Error('Failed to parse HTML');
            }

            // Check for parse errors
            const parserError = doc.querySelector('parsererror');
            if (parserError) {
                throw new Error('HTML parse error: ' + parserError.textContent);
            }

            // Extract main content
            const newContent = doc.querySelector('#main-content') || doc.querySelector('.container');
            const currentContent = document.querySelector('#main-content') || document.querySelector('.container');

            if (!newContent) {
                throw new Error('Main content not found in response');
            }

            if (!currentContent) {
                throw new Error('Main content container not found on page');
            }

            // Smooth fade out
            try {
                currentContent.style.transition = 'opacity 0.3s ease';
                currentContent.style.opacity = '0';
            } catch (styleError) {
                this.logError(styleError, { method: 'loadPage.fadeOut' });
            }

            setTimeout(() => {
                try {
                    // Replace content
                    currentContent.innerHTML = newContent.innerHTML;

                    // Update title safely
                    if (doc.title) {
                        document.title = doc.title;
                    }

                    // Scroll to top with fallback
                    try {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } catch (scrollError) {
                        window.scrollTo(0, 0);
                    }

                    // Fade in
                    currentContent.style.opacity = '1';

                    // Re-initialize page-specific scripts
                    this.reinitScripts();

                    // Complete loading
                    if (typeof progressBar !== 'undefined' && progressBar.complete) {
                        progressBar.complete();
                    }

                    if (document.body) {
                        document.body.classList.remove('page-loading');
                    }

                    // Show success toast
                    if (typeof toast !== 'undefined' && toast.success) {
                        toast.success('Page loaded!', 1500);
                    }

                } catch (updateError) {
                    this.logError(updateError, { method: 'loadPage.updateContent' });
                    throw updateError;
                }
            }, 300);

        } catch (error) {
            this.logError(error, { method: 'loadPage', url });

            // Clean up loading states
            if (typeof progressBar !== 'undefined' && progressBar.reset) {
                progressBar.reset();
            }

            if (document.body) {
                document.body.classList.remove('page-loading');
            }

            // Show error toast
            if (typeof toast !== 'undefined' && toast.error) {
                toast.error('Failed to load page. Redirecting...', 3000);
            }

            // Fallback to traditional navigation
            this.fallbackNavigation(url);
        }
    }

    reinitScripts() {
        try {
            // Re-initialize tooltips
            if (window.tooltipManager && typeof window.tooltipManager.init === 'function') {
                try {
                    window.tooltipManager.init();
                } catch (e) {
                    this.logError(e, { method: 'reinitScripts.tooltips' });
                }
            }

            // Re-initialize favorites UI
            if (window.favorites && typeof window.favorites.updateUI === 'function') {
                try {
                    window.favorites.updateUI();
                } catch (e) {
                    this.logError(e, { method: 'reinitScripts.favorites' });
                }
            }

            // Re-init Select2 if present
            if (typeof $ !== 'undefined' && $.fn && $.fn.select2) {
                try {
                    $('select').select2();
                } catch (e) {
                    this.logError(e, { method: 'reinitScripts.select2' });
                }
            }

            // Re-init drag-drop uploader
            if (window.dragDropUploader && typeof window.dragDropUploader.init === 'function') {
                try {
                    window.dragDropUploader.init();
                } catch (e) {
                    this.logError(e, { method: 'reinitScripts.dragDrop' });
                }
            }

            // Trigger custom event for other scripts
            try {
                const event = new CustomEvent('spa:pageLoaded', {
                    bubbles: true,
                    cancelable: false
                });
                document.dispatchEvent(event);
            } catch (e) {
                this.logError(e, { method: 'reinitScripts.customEvent' });
            }

        } catch (error) {
            this.logError(error, { method: 'reinitScripts' });
        }
    }

    fallbackNavigation(url) {
        try {
            if (url && typeof url === 'string') {
                console.log('Falling back to traditional navigation:', url);
                setTimeout(() => {
                    window.location.href = url;
                }, 1000);
            }
        } catch (error) {
            this.logError(error, { method: 'fallbackNavigation', url });
        }
    }

    logError(error, context = {}) {
        if (this.logger) {
            this.logger.log(error, context);
        } else {
            console.error('[SPANavigator]', error, context);
        }
    }
}

// Initialize SPA navigation with error handling
(function initSPANavigator() {
    try {
        if (window.spaNavigator) {
            console.warn('SPANavigator already exists');
            return;
        }
        window.spaNavigator = new SPANavigator();
    } catch (error) {
        console.error('Failed to initialize SPANavigator:', error);
        window.spaNavigator = null;
    }
})();

// ==========================================
// DRAG AND DROP PHOTO UPLOAD
// ==========================================
class DragDropUploader {
    constructor() {
        this.logger = window.ErrorLogger ? new window.ErrorLogger('DragDropUploader') : null;
        this.maxFileSize = 10 * 1024 * 1024; // 10MB
        this.allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        this.init();
    }

    init() {
        try {
            // Wait for DOM
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    this.enhanceInputs();
                }, { once: true });
            } else {
                this.enhanceInputs();
            }

            // Re-init on SPA page load
            document.addEventListener('spa:pageLoaded', () => {
                this.enhanceInputs();
            });

        } catch (error) {
            this.logError(error, { method: 'init' });
        }
    }

    enhanceInputs() {
        try {
            // Find all file inputs for photos
            const photoInputs = document.querySelectorAll('input[type="file"][accept*="image"]');

            photoInputs.forEach(input => {
                try {
                    this.enhanceInput(input);
                } catch (error) {
                    this.logError(error, { method: 'enhanceInputs.forEach' });
                }
            });

        } catch (error) {
            this.logError(error, { method: 'enhanceInputs' });
        }
    }

    enhanceInput(input) {
        try {
            if (!input || !input.parentElement) {
                throw new Error('Invalid input element');
            }

            // Check if already enhanced
            if (input.dataset.enhanced === 'true') {
                return;
            }

            // Create drop zone wrapper
            const dropZone = document.createElement('div');
            dropZone.className = 'drag-drop-zone';
            dropZone.innerHTML = `
                <div class="drag-drop-inner">
                    <div class="drag-drop-icon">📸</div>
                    <div class="drag-drop-text">
                        <strong>Drag & drop photo here</strong>
                        <span>or click to browse</span>
                        <small>Max size: ${this.formatBytes(this.maxFileSize)}</small>
                    </div>
                    <div class="drag-drop-preview"></div>
                </div>
            `;

            // Insert after input
            input.style.display = 'none';
            input.parentElement.insertBefore(dropZone, input.nextSibling);
            input.dataset.enhanced = 'true';

            // Handle clicks with error handling
            dropZone.addEventListener('click', (e) => {
                try {
                    e.preventDefault();
                    input.click();
                } catch (error) {
                    this.logError(error, { method: 'dropZone.click' });
                }
            });

            // Handle drag events
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => this.preventDefaults(e), false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => {
                    dropZone.classList.add('drag-over');
                });
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => {
                    dropZone.classList.remove('drag-over');
                });
            });

            // Handle drop
            dropZone.addEventListener('drop', (e) => {
                try {
                    const files = e.dataTransfer.files;
                    if (files && files.length > 0) {
                        const validation = this.validateFile(files[0]);
                        if (validation.valid) {
                            input.files = files;
                            this.handleFiles(files, dropZone);
                            if (typeof toast !== 'undefined' && toast.success) {
                                toast.success('Photo added!');
                            }
                        } else {
                            if (typeof toast !== 'undefined' && toast.error) {
                                toast.error(validation.error);
                            }
                        }
                    }
                } catch (error) {
                    this.logError(error, { method: 'dropZone.drop' });
                }
            });

            // Handle file selection
            input.addEventListener('change', (e) => {
                try {
                    if (e.target.files && e.target.files.length > 0) {
                        const validation = this.validateFile(e.target.files[0]);
                        if (validation.valid) {
                            this.handleFiles(e.target.files, dropZone);
                        } else {
                            e.target.value = '';
                            if (typeof toast !== 'undefined' && toast.error) {
                                toast.error(validation.error);
                            }
                        }
                    }
                } catch (error) {
                    this.logError(error, { method: 'input.change' });
                }
            });

        } catch (error) {
            this.logError(error, { method: 'enhanceInput' });
        }
    }

    validateFile(file) {
        try {
            if (!file) {
                return { valid: false, error: 'No file provided' };
            }

            if (!this.allowedTypes.includes(file.type)) {
                return {
                    valid: false,
                    error: `Invalid file type. Allowed: ${this.allowedTypes.join(', ')}`
                };
            }

            if (file.size > this.maxFileSize) {
                return {
                    valid: false,
                    error: `File too large. Max size: ${this.formatBytes(this.maxFileSize)}`
                };
            }

            return { valid: true };

        } catch (error) {
            this.logError(error, { method: 'validateFile' });
            return { valid: false, error: 'File validation failed' };
        }
    }

    preventDefaults(e) {
        try {
            e.preventDefault();
            e.stopPropagation();
        } catch (error) {
            this.logError(error, { method: 'preventDefaults' });
        }
    }

    handleFiles(files, dropZone) {
        try {
            if (!files || files.length === 0) {
                return;
            }

            const file = files[0];

            if (!file || !file.type.startsWith('image/')) {
                return;
            }

            // Show preview
            const reader = new FileReader();

            reader.onerror = (error) => {
                this.logError(error, { method: 'FileReader.onerror' });
                if (typeof toast !== 'undefined' && toast.error) {
                    toast.error('Failed to read file');
                }
            };

            reader.onload = (e) => {
                try {
                    if (!e.target || !e.target.result) {
                        throw new Error('FileReader result is empty');
                    }

                    const preview = dropZone.querySelector('.drag-drop-preview');
                    if (preview) {
                        preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 100%; max-height: 200px;">`;
                        dropZone.classList.add('has-file');
                    }
                } catch (error) {
                    this.logError(error, { method: 'FileReader.onload' });
                }
            };

            reader.readAsDataURL(file);

        } catch (error) {
            this.logError(error, { method: 'handleFiles' });
        }
    }

    formatBytes(bytes) {
        try {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        } catch (error) {
            return bytes + ' bytes';
        }
    }

    logError(error, context = {}) {
        if (this.logger) {
            this.logger.log(error, context);
        } else {
            console.error('[DragDropUploader]', error, context);
        }
    }
}

// Initialize drag-drop uploader with error handling
(function initDragDropUploader() {
    try {
        if (window.dragDropUploader) {
            console.warn('DragDropUploader already exists');
            return;
        }
        window.dragDropUploader = new DragDropUploader();
    } catch (error) {
        console.error('Failed to initialize DragDropUploader:', error);
        window.dragDropUploader = null;
    }
})();

// ==========================================
// PAWPEDS INTEGRATION
// ==========================================
class PawPedsIntegration {
    constructor() {
        this.logger = window.ErrorLogger ? new window.ErrorLogger('PawPedsIntegration') : null;
        this.maxRetries = 3;
        this.timeout = 30000; // 30 seconds
        this.init();
    }

    init() {
        try {
            document.addEventListener('DOMContentLoaded', () => {
                this.addFetchButtons();
            });

            // Re-init on SPA page load
            document.addEventListener('spa:pageLoaded', () => {
                this.addFetchButtons();
            });

        } catch (error) {
            this.logError(error, { method: 'init' });
        }
    }

    addFetchButtons() {
        try {
            // Find forms with registration number fields
            const regInputs = document.querySelectorAll('input[name*="reg_num"], input[name*="registration"]');

            regInputs.forEach(input => {
                try {
                    // Don't add button if already exists
                    if (input.nextElementSibling?.classList.contains('pawpeds-fetch-btn')) {
                        return;
                    }

                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'btn btn-info pawpeds-fetch-btn';
                    button.innerHTML = '🐾 Fetch from PawPeds';
                    button.style.marginTop = '10px';

                    button.addEventListener('click', () => this.fetchFromPawPeds(input));

                    if (input.parentElement) {
                        input.parentElement.appendChild(button);
                    }
                } catch (error) {
                    this.logError(error, { method: 'addFetchButtons.forEach' });
                }
            });

        } catch (error) {
            this.logError(error, { method: 'addFetchButtons' });
        }
    }

    async fetchFromPawPeds(regInput) {
        try {
            if (!regInput || !regInput.value) {
                throw new Error('Invalid registration input');
            }

            const regNumber = regInput.value.trim();

            if (!regNumber) {
                if (typeof toast !== 'undefined' && toast.warning) {
                    toast.warning('Please enter a registration number first');
                }
                return;
            }

            // Validate reg number format (basic validation)
            if (regNumber.length < 2 || regNumber.length > 50) {
                if (typeof toast !== 'undefined' && toast.error) {
                    toast.error('Invalid registration number format');
                }
                return;
            }

            const button = regInput.nextElementSibling;
            if (button && button.classList.contains('pawpeds-fetch-btn')) {
                button.disabled = true;
                button.innerHTML = '⏳ Fetching...';
            }

            if (typeof toast !== 'undefined' && toast.info) {
                toast.info('Fetching data from PawPeds...', 3000);
            }

            try {
                // Get CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

                if (!csrfToken) {
                    console.warn('CSRF token not found');
                }

                const response = await NetworkRetry.fetchWithRetry('/api/fetch-pawpeds', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        reg_number: regNumber,
                        breed: 'nfo' // Default, can be made dynamic
                    }),
                    timeout: this.timeout
                }, this.maxRetries);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const result = await response.json();

                if (!result) {
                    throw new Error('Empty response from server');
                }

                if (result.success && result.data) {
                    this.fillFormWithData(result.data);
                    if (typeof toast !== 'undefined' && toast.success) {
                        toast.success('Data fetched successfully!');
                    }
                } else {
                    const errorMsg = result.message || 'No data found for this registration number';
                    if (typeof toast !== 'undefined' && toast.error) {
                        toast.error(errorMsg);
                    }
                }

            } catch (fetchError) {
                this.logError(fetchError, { method: 'fetchFromPawPeds.fetch', regNumber });

                let errorMessage = 'Failed to fetch data from PawPeds';
                if (fetchError.name === 'AbortError') {
                    errorMessage = 'Request timed out. Please try again.';
                } else if (fetchError.message) {
                    errorMessage += ': ' + fetchError.message;
                }

                if (typeof toast !== 'undefined' && toast.error) {
                    toast.error(errorMessage, 5000);
                }
            } finally {
                if (button && button.classList.contains('pawpeds-fetch-btn')) {
                    button.disabled = false;
                    button.innerHTML = '🐾 Fetch from PawPeds';
                }
            }

        } catch (error) {
            this.logError(error, { method: 'fetchFromPawPeds' });
            if (typeof toast !== 'undefined' && toast.error) {
                toast.error('An error occurred. Please try again.');
            }
        }
    }

    fillFormWithData(data) {
        try {
            if (!data || typeof data !== 'object') {
                throw new Error('Invalid data format');
            }

            const fieldMappings = {
                'full_name': ['full_name', 'name', 'cat_name'],
                'ems_color': ['ems_color', 'color', 'ems'],
                'breed': ['breed'],
                'dob': ['dob', 'date_of_birth', 'birth_date'],
                'gender_id': ['gender_id', 'gender'],
                'breeder': ['breeder'],
                'current_owner': ['current_owner', 'owner'],
                'titles_before_name': ['titles_before_name', 'titles_before'],
                'titles_after_name': ['titles_after_name', 'titles_after'],
            };

            let filledCount = 0;

            Object.keys(data).forEach(key => {
                try {
                    const value = data[key];
                    if (!value) return; // Skip empty values

                    const possibleNames = fieldMappings[key] || [key];

                    possibleNames.forEach(name => {
                        try {
                            const input = document.querySelector(
                                `input[name="${name}"], select[name="${name}"], textarea[name="${name}"]`
                            );

                            if (input && !input.value) {
                                // Sanitize value before setting
                                const sanitizedValue = String(value).trim();
                                input.value = sanitizedValue;
                                input.classList.add('auto-filled');

                                // Trigger change event
                                const event = new Event('change', {
                                    bubbles: true,
                                    cancelable: true
                                });
                                input.dispatchEvent(event);

                                filledCount++;
                            }
                        } catch (inputError) {
                            this.logError(inputError, { method: 'fillFormWithData.setInput', name });
                        }
                    });
                } catch (keyError) {
                    this.logError(keyError, { method: 'fillFormWithData.processKey', key });
                }
            });

            if (filledCount > 0) {
                if (typeof toast !== 'undefined' && toast.success) {
                    toast.success(`${filledCount} field${filledCount > 1 ? 's' : ''} auto-filled!`, 3000);
                }

                // Highlight auto-filled fields briefly
                setTimeout(() => {
                    try {
                        document.querySelectorAll('.auto-filled').forEach(el => {
                            try {
                                el.classList.remove('auto-filled');
                            } catch (e) {
                                // Ignore
                            }
                        });
                    } catch (e) {
                        this.logError(e, { method: 'fillFormWithData.removeHighlight' });
                    }
                }, 3000);
            } else {
                if (typeof toast !== 'undefined' && toast.info) {
                    toast.info('Data received but no empty fields to fill', 3000);
                }
            }

        } catch (error) {
            this.logError(error, { method: 'fillFormWithData' });
            if (typeof toast !== 'undefined' && toast.error) {
                toast.error('Failed to fill form data');
            }
        }
    }

    logError(error, context = {}) {
        if (this.logger) {
            this.logger.log(error, context);
        } else {
            console.error('[PawPedsIntegration]', error, context);
        }
    }
}

// Initialize PawPeds integration with error handling
(function initPawPedsIntegration() {
    try {
        if (window.pawPedsIntegration) {
            console.warn('PawPedsIntegration already exists');
            return;
        }
        window.pawPedsIntegration = new PawPedsIntegration();
    } catch (error) {
        console.error('Failed to initialize PawPedsIntegration:', error);
        window.pawPedsIntegration = null;
    }
})();

// ==========================================
// LIVE STATS INDICATOR
// ==========================================
class LiveStatsIndicator {
    constructor() {
        this.logger = window.ErrorLogger ? new window.ErrorLogger('LiveStatsIndicator') : null;
        this.stats = {
            total_cats: 0,
            living_cats: 0
        };
        this.updateInterval = 300000; // 5 minutes
        this.intervalId = null;
        this.maxRetries = 3;
        this.init();
    }

    init() {
        try {
            this.createIndicator();
            this.fetchStats();

            // Update every 5 minutes
            this.intervalId = setInterval(() => {
                try {
                    this.fetchStats();
                } catch (error) {
                    this.logError(error, { method: 'init.interval' });
                }
            }, this.updateInterval);

        } catch (error) {
            this.logError(error, { method: 'init' });
        }
    }

    createIndicator() {
        try {
            // Check if already exists
            if (document.getElementById('live-stats-indicator')) {
                console.log('Live stats indicator already exists');
                return;
            }

            const indicator = document.createElement('div');
            indicator.id = 'live-stats-indicator';
            indicator.className = 'live-stats-indicator';
            indicator.setAttribute('role', 'status');
            indicator.setAttribute('aria-live', 'polite');
            indicator.innerHTML = `
                <span class="stats-blink" aria-hidden="true">●</span>
                <span class="stats-text">
                    <span class="stats-number">...</span> cats meowing
                </span>
            `;

            // Add to navbar safely
            const navbar = document.querySelector('.navbar-nav');
            if (navbar) {
                const li = document.createElement('li');
                li.appendChild(indicator);
                navbar.appendChild(li);
            } else {
                // Fallback: add to body
                document.body.appendChild(indicator);
            }

        } catch (error) {
            this.logError(error, { method: 'createIndicator' });
        }
    }

    async fetchStats() {
        try {
            const response = await NetworkRetry.fetchWithRetry('/api/stats', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                },
                timeout: 10000
            }, this.maxRetries);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();

            if (!data || typeof data !== 'object') {
                throw new Error('Invalid response format');
            }

            if (data.total_cats !== undefined) {
                this.stats = {
                    total_cats: parseInt(data.total_cats) || 0,
                    living_cats: parseInt(data.living_cats) || 0
                };
                this.updateIndicator();
            }

        } catch (error) {
            this.logError(error, { method: 'fetchStats' });
            // Don't show error to user for stats, just log it
        }
    }

    updateIndicator() {
        try {
            const numberSpan = document.querySelector('.stats-number');
            if (!numberSpan) {
                return;
            }

            const currentValue = parseInt(numberSpan.textContent) || 0;
            const newValue = this.stats.total_cats;

            // Animate number change
            this.animateNumber(numberSpan, currentValue, newValue);

        } catch (error) {
            this.logError(error, { method: 'updateIndicator' });
        }
    }

    animateNumber(element, from, to) {
        try {
            if (!element) return;

            const duration = 1000;
            const steps = 20;
            const stepValue = (to - from) / steps;
            let current = from;
            let step = 0;

            const interval = setInterval(() => {
                try {
                    current += stepValue;
                    step++;

                    element.textContent = Math.round(current);

                    if (step >= steps) {
                        element.textContent = to;
                        clearInterval(interval);
                    }
                } catch (error) {
                    clearInterval(interval);
                    this.logError(error, { method: 'animateNumber.interval' });
                }
            }, duration / steps);

        } catch (error) {
            this.logError(error, { method: 'animateNumber' });
        }
    }

    destroy() {
        try {
            if (this.intervalId) {
                clearInterval(this.intervalId);
                this.intervalId = null;
            }

            const indicator = document.getElementById('live-stats-indicator');
            if (indicator) {
                indicator.remove();
            }

        } catch (error) {
            this.logError(error, { method: 'destroy' });
        }
    }

    logError(error, context = {}) {
        if (this.logger) {
            this.logger.log(error, context);
        } else {
            console.error('[LiveStatsIndicator]', error, context);
        }
    }
}

// Initialize live stats with error handling
(function initLiveStatsIndicator() {
    try {
        if (window.liveStatsIndicator) {
            console.warn('LiveStatsIndicator already exists');
            return;
        }
        window.liveStatsIndicator = new LiveStatsIndicator();
    } catch (error) {
        console.error('Failed to initialize LiveStatsIndicator:', error);
        window.liveStatsIndicator = null;
    }
})();

// ==========================================
// ENHANCED BACKUP SYSTEM
// ==========================================
class EnhancedBackupSystem {
    constructor() {
        this.logger = window.ErrorLogger ? new window.ErrorLogger('EnhancedBackupSystem') : null;
        this.maxFileSize = 50 * 1024 * 1024; // 50MB
        this.allowedExtensions = ['.csv', '.json'];
        this.init();
    }

    init() {
        try {
            document.addEventListener('DOMContentLoaded', () => {
                this.enhanceBackupPage();
            });

            // Re-init on SPA page load
            document.addEventListener('spa:pageLoaded', () => {
                this.enhanceBackupPage();
            });

        } catch (error) {
            this.logError(error, { method: 'init' });
        }
    }

    enhanceBackupPage() {
        try {
            // Check if we're on the backup page
            const backupForm = document.querySelector('form[action*="backups"], form[action*="backup"]');
            if (!backupForm) {
                return;
            }

            // Add drag-drop zone for imports
            const fileInputs = document.querySelectorAll('input[type="file"]');

            fileInputs.forEach(input => {
                try {
                    this.addDragDropToBackup(input);
                } catch (error) {
                    this.logError(error, { method: 'enhanceBackupPage.forEach' });
                }
            });

            // Add progress bars to export/import buttons
            this.addProgressToButtons();

        } catch (error) {
            this.logError(error, { method: 'enhanceBackupPage' });
        }
    }

    addDragDropToBackup(input) {
        try {
            if (!input || !input.parentElement) {
                return;
            }

            // Check if already enhanced
            if (input.dataset.backupEnhanced === 'true') {
                return;
            }

            const dropZone = document.createElement('div');
            dropZone.className = 'backup-drag-zone';
            dropZone.innerHTML = `
                <div class="backup-drag-inner">
                    <div class="backup-drag-icon">📁</div>
                    <div class="backup-drag-text">
                        <strong>Drop CSV file here</strong>
                        <span>or click to browse</span>
                        <small>Max size: ${this.formatBytes(this.maxFileSize)}</small>
                    </div>
                    <div class="backup-file-name"></div>
                    <div class="backup-progress">
                        <div class="backup-progress-bar"></div>
                        <div class="backup-progress-text">0%</div>
                    </div>
                </div>
            `;

            input.style.display = 'none';
            input.parentElement.insertBefore(dropZone, input.nextSibling);
            input.dataset.backupEnhanced = 'true';

            // Handle clicks
            dropZone.addEventListener('click', (e) => {
                try {
                    e.preventDefault();
                    input.click();
                } catch (error) {
                    this.logError(error, { method: 'dropZone.click' });
                }
            });

            // Drag events
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    try {
                        e.preventDefault();
                        e.stopPropagation();
                    } catch (error) {
                        this.logError(error, { method: 'dragEvent' });
                    }
                });
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => {
                    dropZone.classList.add('drag-active');
                });
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => {
                    dropZone.classList.remove('drag-active');
                });
            });

            dropZone.addEventListener('drop', (e) => {
                try {
                    const files = e.dataTransfer.files;
                    if (files && files.length > 0) {
                        const validation = this.validateBackupFile(files[0]);
                        if (validation.valid) {
                            input.files = files;
                            this.showFileName(dropZone, files[0]);
                            if (typeof toast !== 'undefined' && toast.success) {
                                toast.success('File ready for import!');
                            }
                        } else {
                            if (typeof toast !== 'undefined' && toast.error) {
                                toast.error(validation.error);
                            }
                        }
                    }
                } catch (error) {
                    this.logError(error, { method: 'dropZone.drop' });
                }
            });

            input.addEventListener('change', (e) => {
                try {
                    if (e.target.files && e.target.files.length > 0) {
                        const validation = this.validateBackupFile(e.target.files[0]);
                        if (validation.valid) {
                            this.showFileName(dropZone, e.target.files[0]);
                        } else {
                            e.target.value = '';
                            if (typeof toast !== 'undefined' && toast.error) {
                                toast.error(validation.error);
                            }
                        }
                    }
                } catch (error) {
                    this.logError(error, { method: 'input.change' });
                }
            });

        } catch (error) {
            this.logError(error, { method: 'addDragDropToBackup' });
        }
    }

    validateBackupFile(file) {
        try {
            if (!file) {
                return { valid: false, error: 'No file provided' };
            }

            const fileName = file.name.toLowerCase();
            const hasValidExt = this.allowedExtensions.some(ext => fileName.endsWith(ext));

            if (!hasValidExt) {
                return {
                    valid: false,
                    error: `Invalid file type. Allowed: ${this.allowedExtensions.join(', ')}`
                };
            }

            if (file.size > this.maxFileSize) {
                return {
                    valid: false,
                    error: `File too large. Max size: ${this.formatBytes(this.maxFileSize)}`
                };
            }

            return { valid: true };

        } catch (error) {
            this.logError(error, { method: 'validateBackupFile' });
            return { valid: false, error: 'File validation failed' };
        }
    }

    showFileName(dropZone, file) {
        try {
            if (!dropZone || !file) return;

            const fileNameEl = dropZone.querySelector('.backup-file-name');
            if (fileNameEl) {
                fileNameEl.textContent = `📄 ${file.name} (${this.formatBytes(file.size)})`;
                dropZone.classList.add('has-file');
            }

        } catch (error) {
            this.logError(error, { method: 'showFileName' });
        }
    }

    formatBytes(bytes) {
        try {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        } catch (error) {
            return bytes + ' bytes';
        }
    }

    addProgressToButtons() {
        try {
            const submitButtons = document.querySelectorAll('button[type="submit"], input[type="submit"]');

            submitButtons.forEach(button => {
                try {
                    button.addEventListener('click', (e) => {
                        try {
                            if (button.form) {
                                const formData = new FormData(button.form);

                                // If has file, show progress
                                let hasFile = false;
                                for (let pair of formData.entries()) {
                                    if (pair[1] instanceof File && pair[1].size > 0) {
                                        hasFile = true;
                                        break;
                                    }
                                }

                                if (hasFile) {
                                    this.showUploadProgress(button);
                                }
                            }
                        } catch (error) {
                            this.logError(error, { method: 'button.click' });
                        }
                    });
                } catch (error) {
                    this.logError(error, { method: 'addProgressToButtons.forEach' });
                }
            });

        } catch (error) {
            this.logError(error, { method: 'addProgressToButtons' });
        }
    }

    showUploadProgress(button) {
        try {
            if (!button) return;

            button.disabled = true;
            button.dataset.originalText = button.textContent || button.value;

            const updateText = (text) => {
                if (button.tagName === 'INPUT') {
                    button.value = text;
                } else {
                    button.textContent = text;
                }
            };

            updateText('Uploading... 0%');

            // Simulate progress (in real implementation, use XMLHttpRequest for actual progress)
            let progress = 0;
            const interval = setInterval(() => {
                try {
                    progress += Math.random() * 15;
                    if (progress > 95) progress = 95;

                    updateText(`Uploading... ${Math.round(progress)}%`);

                    if (progress >= 95) {
                        clearInterval(interval);
                        updateText('Processing...');
                    }
                } catch (error) {
                    clearInterval(interval);
                    this.logError(error, { method: 'showUploadProgress.interval' });
                }
            }, 200);

        } catch (error) {
            this.logError(error, { method: 'showUploadProgress' });
        }
    }

    logError(error, context = {}) {
        if (this.logger) {
            this.logger.log(error, context);
        } else {
            console.error('[EnhancedBackupSystem]', error, context);
        }
    }
}

// Initialize enhanced backup system with error handling
(function initEnhancedBackupSystem() {
    try {
        if (window.enhancedBackupSystem) {
            console.warn('EnhancedBackupSystem already exists');
            return;
        }
        window.enhancedBackupSystem = new EnhancedBackupSystem();
    } catch (error) {
        console.error('Failed to initialize EnhancedBackupSystem:', error);
        window.enhancedBackupSystem = null;
    }
})();

// Global error handler
window.addEventListener('error', (event) => {
    if (event.filename && event.filename.includes('enhanced-features.js')) {
        console.error('Unhandled error in enhanced-features.js:', event.error);
    }
});

console.log('🚀 FindACat Enhanced Features Loaded (with error handling)');
