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
</body>
</html>
