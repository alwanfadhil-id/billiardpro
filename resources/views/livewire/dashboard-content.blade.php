<div class="flex h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Sidebar -->
    <div class="hidden md:flex md:w-64 lg:w-72 flex-col bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center space-x-3">
                <div class="bg-blue-500 text-white p-2 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">BilliardPro</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Manajemen Meja Profesional</p>
                </div>
            </div>
        </div>
        
        <nav class="flex-1 px-2 py-4 space-y-1">
            <a href="{{ route('dashboard') }}" 
               class="flex items-center px-4 py-3 rounded-lg mb-2 {{ request()->routeIs('dashboard') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4z" />
                    <path d="M3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6z" />
                    <path d="M14 4a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                    <path d="M14 10a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                </svg>
                Dashboard
            </a>
            
            <a href="{{ route('reports.index') }}" 
               class="flex items-center px-4 py-3 rounded-lg mb-2 {{ request()->routeIs('reports.*') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 17a2 2 0 114 0 2 2 0 014 0M9 17v-4a3 3 0 015.356-2.877M15 17v-4a3 3 0 00-5.356-2.877M15 10a3 3 0 00-2.877-2.877A5.5 5.5 0 0010 10M10 10a5.5 5.5 0 002.877 2.877A3 3 0 0010 10z" />
                </svg>
                Laporan
            </a>
            
            <a href="{{ route('tables.manage') }}" 
               class="flex items-center px-4 py-3 rounded-lg mb-2 {{ request()->routeIs('tables.*') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414l5 5a1 1 0 001.414 0l7-7a1 1 0 00-1.414-1.414L10.707 2.293z" />
                </svg>
                Kelola Meja
            </a>
            
            <a href="{{ route('users.list') }}" 
               class="flex items-center px-4 py-3 rounded-lg mb-2 {{ request()->routeIs('users.*') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 12l2 2 4-4m6 6a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Kelola User
            </a>
        </nav>
        
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center w-full px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                    <div class="flex items-center flex-1">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="ml-3 text-left">
                            <div class="font-medium text-gray-900 dark:text-white">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()->role }}</div>
                        </div>
                    </div>
                    <div>
                        <svg :class="{'rotate-180': open}" class="fill-current h-4 w-4 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>

                <div x-show="open" @click.away="open = false" x-cloak class="mt-2 z-50 absolute right-0 w-52 origin-top-right rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none border border-gray-200 dark:border-gray-700">
                    <div class="py-1" role="none">
                        <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                            Profile
                        </a>
                        <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                            Dashboard
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="block">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex flex-col flex-1 overflow-hidden">
        <!-- Top Navigation -->
        <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 py-3 shadow-sm">
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                        Dashboard
                    </h2>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Dark mode toggle -->
                    <div class="dropdown dropdown-end">
                        <button class="btn btn-ghost btn-circle" onclick="document.documentElement.classList.toggle('dark')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </button>
                    </div>
                    
                    <!-- User Profile -->
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-circle avatar">
                            <div class="w-10 rounded-full bg-primary text-white flex items-center justify-center">
                                <span class="font-semibold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                            </div>
                        </label>
                        <ul tabindex="0" class="mt-3 z-[1] p-2 shadow menu menu-sm dropdown-content bg-base-100 dark:bg-gray-800 rounded-box w-52 border border-gray-200 dark:border-gray-700">
                            <li>
                                <a class="justify-between" href="{{ route('profile') }}">
                                    Profile
                                    <span class="badge">New</span>
                                </a>
                            </li>
                            <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="p-4 md:p-6 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 min-h-screen">
            <div class="max-w-7xl mx-auto">
                <!-- Time and Date Widget -->
                <div class="time-widget">
                    <div class="time-display">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="time-label">Waktu Sekarang</h3>
                                <div class="time-value" id="current-time">{{ now()->format('H:i:s') }}</div>
                                <div class="time-subtitle">{{ now()->format('l, j F Y') }}</div>
                            </div>
                            <div class="time-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <!-- Revenue Card -->
                    <div class="summary-card">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="summary-title">Pendapatan Hari Ini</p>
                                <h3 class="summary-value text-green-600 dark:text-green-400">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</h3>
                                <p class="summary-subtitle">{{ $completedTransactions }} transaksi selesai</p>
                            </div>
                            <div class="summary-icon bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Available Tables Card -->
                    <div class="summary-card">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="summary-title">Meja Tersedia</p>
                                <h3 class="summary-value text-blue-600 dark:text-blue-400">{{ $availableTables }}/{{ $totalTables }}</h3>
                                <p class="summary-subtitle">{{ $occupiedTables }} meja terpakai</p>
                            </div>
                            <div class="summary-icon bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Active Sessions Card -->
                    <div class="summary-card">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="summary-title">Sesi Aktif</p>
                                <h3 class="summary-value text-yellow-500 dark:text-yellow-400">{{ $activeSessions }}</h3>
                                <p class="summary-subtitle">Sedang berjalan</p>
                            </div>
                            <div class="summary-icon bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Maintenance Tables Card -->
                    <div class="summary-card">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="summary-title">Meja Maintenance</p>
                                <h3 class="summary-value text-red-500 dark:text-red-400">{{ $maintenanceTables }}</h3>
                                <p class="summary-subtitle">Tidak dapat digunakan</p>
                            </div>
                            <div class="summary-icon bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search and Filter Bar -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-5 mb-6 border border-gray-100 dark:border-gray-700">
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <div class="form-control relative">
                                <div class="relative">
                                    <input
                                        wire:model.live="search"
                                        type="text"
                                        placeholder="Cari nomor atau tipe meja..."
                                        class="search-input">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="md:w-48">
                            <select wire:model.live="filterStatus" class="filter-select">
                                <option value="all">Semua Status</option>
                                <option value="available">Tersedia</option>
                                <option value="occupied">Terpakai</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center gap-4">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Legenda:</span>
                        <div class="legend-item">
                            <div class="legend-dot bg-green-500"></div>
                            <span class="legend-text">Tersedia</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-dot bg-red-500"></div>
                            <span class="legend-text">Terpakai</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-dot bg-gray-500"></div>
                            <span class="legend-text">Maintenance</span>
                        </div>
                    </div>
                </div>

                <!-- Table Grid -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-5 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Daftar Meja</h3>
                    <div class="table-grid">
                        <livewire:dashboard.table-grid />
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>