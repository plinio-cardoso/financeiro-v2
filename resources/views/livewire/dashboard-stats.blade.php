<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
    {{-- Total a pagar no mês --}}
    <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-gray-800">
        <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                Total a Pagar (Mês Atual)
            </dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                R$ {{ number_format($stats['total_to_pay'] ?? 0, 2, ',', '.') }}
            </dd>
        </div>
    </div>

    {{-- Total já pago no mês --}}
    <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-gray-800">
        <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                Total Pago (Mês Atual)
            </dt>
            <dd class="mt-1 text-3xl font-semibold text-green-600 dark:text-green-400">
                R$ {{ number_format($stats['total_paid'] ?? 0, 2, ',', '.') }}
            </dd>
        </div>
    </div>

    {{-- Total previsto próximo mês --}}
    <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-gray-800">
        <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                Previsto (Próximo Mês)
            </dt>
            <dd class="mt-1 text-3xl font-semibold text-blue-600 dark:text-blue-400">
                R$ {{ number_format($stats['next_month_forecast'] ?? 0, 2, ',', '.') }}
            </dd>
        </div>
    </div>

    {{-- Transações vencidas --}}
    <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-gray-800">
        <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                Transações Vencidas
            </dt>
            <dd class="mt-1 text-3xl font-semibold text-red-600 dark:text-red-400">
                {{ $stats['overdue_count'] ?? 0 }}
            </dd>
        </div>
    </div>
</div>
