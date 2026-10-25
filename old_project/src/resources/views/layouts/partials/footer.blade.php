<footer class="site-footer">
    <div class="container">
        <div class="row">
            <!-- About Section -->
            <div class="col-md-4 footer-section">
                <h4>{{ __('app.about', 'About') }}</h4>
                <p style="color: var(--text-secondary);">
                    {{ config('app.name', 'FindACat') }} is a comprehensive cat pedigree database for tracking lineages, breeding information, and family trees.
                </p>
            </div>

            <!-- Sitemap -->
            <div class="col-md-4 footer-section">
                <h4>{{ __('app.sitemap', 'Sitemap') }}</h4>
                <ul>
                    <li><a href="{{ url('/') }}">{{ __('app.home', 'Home') }}</a></li>
                    <li><a href="{{ route('cats.search') }}">{{ __('app.search', 'Search Cats') }}</a></li>
                    <li><a href="/test/1/1/1/1">{{ __('cat.test_mating', 'Test Mating') }}</a></li>
                    @if(Auth::check() && Auth::user()->is_admin)
                        <li><a href="{{ route('register-cat') }}">{{ __('app.register-a-cat', 'Register a Cat') }}</a></li>
                        <li><a href="{{ route('backups.index') }}">{{ __('app.backups', 'Backups') }}</a></li>
                    @endif
                </ul>
            </div>

            <!-- Accessibility & Support -->
            <div class="col-md-4 footer-section">
                <h4>{{ __('app.accessibility', 'Accessibility') }}</h4>
                <ul>
                    <li>
                        <button id="increaseFontSize" class="btn-link" style="background: none; border: none; padding: 0; color: var(--text-secondary); cursor: pointer;">
                            {{ __('app.increase_font_size', 'Increase Font Size') }}
                        </button>
                    </li>
                    <li>
                        <button id="decreaseFontSize" class="btn-link" style="background: none; border: none; padding: 0; color: var(--text-secondary); cursor: pointer;">
                            {{ __('app.decrease_font_size', 'Decrease Font Size') }}
                        </button>
                    </li>
                    <li>
                        <button id="resetFontSize" class="btn-link" style="background: none; border: none; padding: 0; color: var(--text-secondary); cursor: pointer;">
                            {{ __('app.reset_font_size', 'Reset Font Size') }}
                        </button>
                    </li>
                    <li>
                        <span style="color: var(--text-secondary);">
                            {{ __('app.keyboard_navigation', 'Keyboard Navigation Supported') }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>
                &copy; {{ date('Y') }} {{ config('app.name', 'FindACat') }}. {{ __('app.all_rights_reserved', 'All rights reserved.') }}
                <br>
                <small>
                    {{ __('app.made_with_love', 'Made with') }} ❤️ {{ __('app.for_cat_lovers', 'for cat lovers everywhere') }}
                </small>
            </p>
        </div>
    </div>

    <script>
        // Font size accessibility controls
        (function() {
            const htmlElement = document.documentElement;
            let currentFontSize = parseFloat(localStorage.getItem('fontSize')) || 100;

            // Apply saved font size
            if (currentFontSize !== 100) {
                htmlElement.style.fontSize = currentFontSize + '%';
            }

            document.getElementById('increaseFontSize').addEventListener('click', function() {
                currentFontSize = Math.min(currentFontSize + 10, 150);
                htmlElement.style.fontSize = currentFontSize + '%';
                localStorage.setItem('fontSize', currentFontSize);
            });

            document.getElementById('decreaseFontSize').addEventListener('click', function() {
                currentFontSize = Math.max(currentFontSize - 10, 80);
                htmlElement.style.fontSize = currentFontSize + '%';
                localStorage.setItem('fontSize', currentFontSize);
            });

            document.getElementById('resetFontSize').addEventListener('click', function() {
                currentFontSize = 100;
                htmlElement.style.fontSize = currentFontSize + '%';
                localStorage.setItem('fontSize', currentFontSize);
            });
        })();
    </script>
</footer>
