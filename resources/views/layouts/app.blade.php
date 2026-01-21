<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head />
</head>

<body class="font-sans antialiased" @tags-loaded.window="$store.tags.setTags($event.detail.tags)">
    <x-toast-container />

    <div class="flex h-screen overflow-hidden bg-gray-100 dark:bg-gray-900">
        <!-- Sidebar -->
        <x-sidebar />

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow z-10">
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