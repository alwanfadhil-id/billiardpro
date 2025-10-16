<div>
    <aside class="fixed inset-y-0 left-0 z-40 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300 ease-in-out transform" 
        :class="{'-translate-x-full lg:translate-x-0': !sidebarOpen, 'translate-x-0': sidebarOpen}">
        
        <div class="h-full flex flex-col">
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-2">
                    <x-application-logo class="h-8 w-auto text-gray-800 dark:text-gray-200" />
                    <span class="text-xl font-bold text-gray-800 dark:text-white">BilliardPro</span>
                </div>
                
                <!-- Close button for mobile -->
                <button @click="$dispatch('toggle-sidebar')" 
                    class="lg:hidden text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Sidebar Content -->
            <div class="flex-1 overflow-y-auto py-4">
                <nav class="space-y-1 px-2">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center px-4 py-2 text-base font-medium rounded-lg transition-colors duration-200
                        {{ request()->routeIs('dashboard') ? 'bg-blue-100 text-blue-700 dark:bg-blue-600 dark:text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </a>

                    <!-- Tables Management (for admins) -->
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('tables.manage') }}"
                        class="flex items-center px-4 py-2 text-base font-medium rounded-lg transition-colors duration-200
                        {{ request()->routeIs('tables.manage') ? 'bg-blue-100 text-blue-700 dark:bg-blue-600 dark:text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        Manage Tables
                    </a>
                    @endif

                    <!-- Products (for admins) -->
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('products.list') }}"
                        class="flex items-center px-4 py-2 text-base font-medium rounded-lg transition-colors duration-200
                        {{ request()->routeIs('products.list') ? 'bg-blue-100 text-blue-700 dark:bg-blue-600 dark:text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Products
                    </a>
                    @endif

                    <!-- Daily Report (for admins) -->
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('report.daily') }}"
                        class="flex items-center px-4 py-2 text-base font-medium rounded-lg transition-colors duration-200
                        {{ request()->routeIs('report.daily') ? 'bg-blue-100 text-blue-700 dark:bg-blue-600 dark:text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Daily Report
                    </a>
                    
                    <!-- Monthly Report (for admins) -->
                    <a href="{{ route('report.monthly') }}"
                        class="flex items-center px-4 py-2 text-base font-medium rounded-lg transition-colors duration-200
                        {{ request()->routeIs('report.monthly') ? 'bg-blue-100 text-blue-700 dark:bg-blue-600 dark:text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Monthly Report
                    </a>
                    
                    <!-- Yearly Report (for admins) -->
                    <a href="{{ route('report.yearly') }}"
                        class="flex items-center px-4 py-2 text-base font-medium rounded-lg transition-colors duration-200
                        {{ request()->routeIs('report.yearly') ? 'bg-blue-100 text-blue-700 dark:bg-blue-600 dark:text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Yearly Report
                    </a>
                    @endif

                    <!-- Users Management (for admins) -->
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('users.list') }}"
                        class="flex items-center px-4 py-2 text-base font-medium rounded-lg transition-colors duration-200
                        {{ request()->routeIs('users.list') ? 'bg-blue-100 text-blue-700 dark:bg-blue-600 dark:text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Users
                    </a>
                    @endif
                </nav>
            </div>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-700 dark:text-white">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ Auth::user()->role }}</p>
                    </div>
                </div>
                
                <!-- Version Info -->
                <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    <p>Version: {{ config('app.version') }}</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Backdrop for mobile -->
    <div x-show="sidebarOpen" 
         @click="$dispatch('toggle-sidebar')" 
         class="fixed inset-0 z-30 bg-black bg-opacity-50 lg:hidden"
         x-cloak>
    </div>
</div>