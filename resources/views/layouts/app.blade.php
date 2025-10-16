<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased" x-data="{ sidebarOpen: true }" @toggle-sidebar.window="sidebarOpen = !sidebarOpen">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <livewire:layout.navigation />
        <livewire:layout.sidebar />

        <div :class="sidebarOpen ? 'lg:pl-64' : 'lg:pl-20'" class="transition-all duration-300 ease-in-out">
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main>
                {{ $slot }}
            </main>
            
            <!-- Footer with version -->
            <footer class="py-4 px-6 text-center text-xs text-gray-500 dark:text-gray-400 border-t border-gray-200 dark:border-gray-700">
                <p>Version: {{ config('app.version') }}</p>
            </footer>
        </div>
    </div>

    <!-- Dark Mode Toggle Script -->
    <script>
        // Initialize dark mode
        function initializeDarkMode() {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            // Determine initial theme
            let initialTheme = 'light';
            if (savedTheme) {
                initialTheme = savedTheme;
            } else if (prefersDark) {
                initialTheme = 'dark';
            }
            
            // Apply initial theme - both data-theme for DaisyUI and class for Tailwind
            document.documentElement.setAttribute('data-theme', initialTheme);
            
            // Add or remove dark class based on theme for Tailwind dark: prefix
            if (initialTheme === 'dark') {
                document.documentElement.classList.add('dark');
                const themeIcon = document.getElementById('theme-icon');
                if (themeIcon) {
                    themeIcon.textContent = '☀️';
                }
            } else {
                document.documentElement.classList.remove('dark');
                const themeIcon = document.getElementById('theme-icon');
                if (themeIcon) {
                    themeIcon.textContent = '🌙';
                }
            }
        }

        // Toggle dark mode function
        function toggleDarkMode() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            // Update data-theme attribute for DaisyUI components
            document.documentElement.setAttribute('data-theme', newTheme);
            
            // Update dark class for Tailwind dark: prefix classes
            if (newTheme === 'dark') {
                document.documentElement.classList.add('dark');
                const themeIcon = document.getElementById('theme-icon');
                if (themeIcon) {
                    themeIcon.textContent = '☀️';
                }
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                const themeIcon = document.getElementById('theme-icon');
                if (themeIcon) {
                    themeIcon.textContent = '🌙';
                }
                localStorage.setItem('theme', 'light');
            }
        }

        // Initialize when DOM is loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeDarkMode);
        } else {
            initializeDarkMode();
        }
    </script>
</body>
</html>
