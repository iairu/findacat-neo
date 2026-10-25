<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'FindACat') }}</title>
        <meta name="description" content="Discover and explore cat pedigrees, family trees, and breeding information.">

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
        <link rel="alternate icon" href="{{ asset('images/favicon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap">
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link href="{{ asset('css/advanced-features.css') }}" rel="stylesheet">
        <link href="{{ asset('css/enhanced-features.css') }}" rel="stylesheet">
        <link href="{{ asset('css/themes.css') }}" rel="stylesheet">
        <link href="{{ asset('css/animated-cats.css') }}" rel="stylesheet">
        <link href="{{ asset('css/theme-selector.css') }}" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/plugins/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/plugins/jquery.datetimepicker.css') }}">
        @yield('ext_css')
    </head>
    <body class="font-sans antialiased">
        <!-- Skip to main content for accessibility -->
        <a href="#main-content" class="skip-link">Skip to main content</a>

        <!-- Page Transition Overlay -->
        <div class="page-transition" id="pageTransition"></div>

        <div id="app" class="min-h-screen bg-gray-100">
            @include('layouts.partials.nav')

            <main id="main-content" class="container" role="main">
                <!-- Page Content -->
                @yield('content')
            </main>

            <!-- Footer -->
            @include('layouts.partials.footer')
        </div>

        <!-- Theme Toggle Button -->
        <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
            <span class="theme-icon">🌙</span>
        </button>

        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}"></script>
        <script src="{{ asset('js/advanced-features.js') }}"></script>
        <script src="{{ asset('js/enhanced-features.js') }}"></script>
        <script src="{{ asset('js/theme-system.js') }}"></script>
        @yield('ext_js')
        @yield('script')

        <script>
            // Theme Management
            (function() {
                const themeToggle = document.getElementById('themeToggle');
                const body = document.body;
                const themeIcon = document.querySelector('.theme-icon');

                // Load saved theme
                const savedTheme = localStorage.getItem('theme') || 'light';
                if (savedTheme === 'dark') {
                    body.classList.add('dark-mode');
                    themeIcon.textContent = '☀️';
                }

                // Toggle theme
                themeToggle.addEventListener('click', function() {
                    body.classList.toggle('dark-mode');
                    const isDark = body.classList.contains('dark-mode');
                    themeIcon.textContent = isDark ? '☀️' : '🌙';
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                });
            })();

            // Page Transitions
            (function() {
                const transition = document.getElementById('pageTransition');

                // Smooth page transitions for internal links
                document.addEventListener('click', function(e) {
                    const link = e.target.closest('a');
                    if (link && link.href && link.host === window.location.host && !link.getAttribute('target')) {
                        const href = link.getAttribute('href');
                        if (href && !href.startsWith('#') && !href.startsWith('javascript:')) {
                            e.preventDefault();
                            transition.classList.add('active');
                            setTimeout(function() {
                                window.location = href;
                            }, 300);
                        }
                    }
                });

                // Remove transition on page load
                window.addEventListener('load', function() {
                    transition.classList.remove('active');
                });
            })();

            // Enhanced title management
            $(document).ready(function() {
                var header = $('h2.page-header').contents();
                if (header.length > 0) {
                    str = '';
                    mainText = header.filter(function () {
                        return this.nodeType === 3;
                    })[0];

                    if (mainText) {
                        str += mainText.data.trim();

                        if (mainText.nextSibling) {
                            str += " - "+mainText.nextSibling.innerText;
                        }
                        $('title').prepend(str+" - ");
                    }
                }
            });
        </script>
    </body>
</html>
