<!-- Overlay for Mobile -->
<div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="mobileMenuOpen = false"
    class="fixed inset-0 z-20 bg-black/50 lg:hidden" x-cloak>
</div>

<aside
    class="fixed inset-y-0 left-0 z-30 transition-transform duration-300 transform lg:static lg:translate-x-0 w-64 h-screen px-4 py-8 overflow-y-auto bg-white border-r rtl:border-r-0 rtl:border-l dark:bg-gray-800 dark:border-gray-700/50 -translate-x-full"
    :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'">
    <div class="flex items-center px-2 mb-8">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <x-application-mark class="w-7 h-7 text-[#4ECDC4]" />
            <span class="text-xl font-bold tracking-tight text-gray-900 dark:text-white uppercase">Financeiro</span>
        </a>
    </div>

    <div class="flex flex-col justify-between flex-1">
        <nav class="space-y-1">
            <x-sidebar-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </x-slot>
                Dashboard
            </x-sidebar-link>

            <x-sidebar-link href="{{ route('transactions.index') }}" :active="request()->routeIs('transactions.*')">
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </x-slot>
                Transações
            </x-sidebar-link>

            <x-sidebar-link href="{{ route('recurring-transactions.index') }}"
                :active="request()->routeIs('recurring-transactions.*')">
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </x-slot>
                Recorrentes
            </x-sidebar-link>
        </nav>

        <div class="mt-auto pt-4 space-y-4">
            <hr class="border-gray-200 dark:border-gray-800">

            <nav class="space-y-1">
                <x-sidebar-link href="{{ route('settings.notifications.edit') }}"
                    :active="request()->routeIs('settings.*')">
                    <x-slot name="icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot>
                    Configurações
                </x-sidebar-link>

                <button @click="toggleTheme()" type="button"
                    class="flex items-center w-full px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-[#4ECDC4] dark:hover:text-[#4ECDC4] hover:bg-[#4ECDC410] dark:hover:bg-[#4ECDC410] rounded-lg group transition-all duration-200">
                    <div
                        class="text-gray-400 group-hover:text-[#4ECDC4] dark:group-hover:text-[#4ECDC4] transition-colors duration-200">
                        <svg x-show="darkMode" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg x-show="!darkMode" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </div>
                    <span class="ms-3 font-medium" x-text="darkMode ? 'Modo Claro' : 'Modo Escuro'"></span>
                </button>
            </nav>

            <div
                class="flex items-center gap-3 px-3 py-4 bg-gray-50/50 dark:bg-gray-800/40 rounded-2xl border border-gray-100 dark:border-gray-800/50 group/user transition-all">
                <a href="{{ route('profile.show') }}" class="flex-1 flex items-center gap-3 min-w-0">
                    <div class="relative flex-shrink-0">
                        <img class="object-cover w-10 h-10 rounded-xl shadow-sm border border-white dark:border-gray-700 group-hover/user:border-[#4ECDC4] transition-colors"
                            src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
                        <div
                            class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full">
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h1
                            class="text-sm font-bold text-gray-900 dark:text-white truncate group-hover/user:text-[#4ECDC4] transition-colors">
                            {{ Auth::user()->name }}
                        </h1>
                        <p class="text-[10px] font-medium text-gray-500 dark:text-gray-400 truncate tracking-wide">
                            {{ Auth::user()->email }}
                        </p>
                    </div>
                </a>
                <form method="POST" action="{{ route('logout') }}" x-data class="flex-shrink-0">
                    @csrf
                    <button @click.prevent="$root.submit();"
                        class="p-2 text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors duration-200 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>