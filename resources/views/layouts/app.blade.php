<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head />
</head>

<body class="font-sans antialiased" @tags-loaded.window="$store.tags.setTags($event.detail.tags)">
    <x-toast-container />

    <div class="flex h-screen overflow-hidden bg-gray-100 dark:bg-gray-900" x-data="{ 
            mobileMenuOpen: false,
            darkMode: localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
            toggleTheme() {
                this.darkMode = !this.darkMode;
                localStorage.theme = this.darkMode ? 'dark' : 'light';
                if (this.darkMode) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                // Dispatch event to notify charts to update
                window.dispatchEvent(new CustomEvent('theme-changed', { detail: { darkMode: this.darkMode } }));
            }
        }">
        <!-- Sidebar -->
        <x-sidebar />

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header for Mobile -->
            <header
                class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700/50 lg:hidden px-4 py-3 flex items-center justify-between shadow-sm sticky top-0 z-20">
                <div class="flex items-center gap-2">
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 ml-1">
                        <x-application-mark class="w-6 h-6 text-[#4ECDC4]" />
                        <span
                            class="text-lg font-black tracking-tighter text-gray-900 dark:text-white uppercase">Financeiro</span>
                    </a>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Theme Switcher (Icon only on mobile) --}}
                    <button @click="toggleTheme()" type="button"
                        class="p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg x-show="darkMode" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg x-show="!darkMode" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    {{-- User Profile --}}
                    <a href="{{ route('profile.show') }}" class="flex-shrink-0">
                        <img class="w-8 h-8 rounded-lg object-cover border border-gray-100 dark:border-gray-700"
                            src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
                    </a>
                </div>
            </header>

            <!-- Page Heading (Desktop) -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow z-10 hidden lg:block">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('modals')
    <livewire:transaction-modal />

    @livewireScripts
    @stack('scripts')
</body>

</html>