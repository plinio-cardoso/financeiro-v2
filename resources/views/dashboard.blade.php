<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards (Livewire Component) -->
            <livewire:dashboard-stats />

            <!-- Divisor -->
            <x-section-border />

            <!-- Transações do Mês -->
            <div class="mt-8">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Transações do Mês Atual
                </h3>

                <livewire:transaction-list />
            </div>
        </div>
    </div>
</x-app-layout>
