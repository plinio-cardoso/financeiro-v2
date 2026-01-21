<div x-data="{
    // No longer needs to manage modal state
}" @transaction-saved.window="$wire.refreshAggregates()" @recurring-saved.window="$wire.refreshAggregates()">

    {{-- Main Content Card --}}
    <div
        class="bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden">
        {{-- List Header with Actions --}}
        <div
            class="px-6 py-3 border-b border-gray-50 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/10 flex items-center justify-between">
            <div class="h-10 flex items-center">
                {{-- Global Loading Indicator --}}
                <div wire:loading.flex class="items-center gap-3">
                    <div class="w-4 h-4 border-2 border-[#4ECDC4] border-t-transparent rounded-full animate-spin"></div>
                    <span
                        class="text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 whitespace-nowrap">
                        Sincronizando...
                    </span>
                </div>
            </div>

            <x-button @click="$dispatch('open-edit-modal', { transactionId: null })"
                class="!bg-[#4ECDC4] hover:!bg-[#3dbdb5] !text-gray-900 shadow-sm py-1.5 px-4 rounded-lg active:scale-95 transition-all text-[11px] font-bold uppercase tracking-wider">
                Nova Transação
            </x-button>
        </div>

        {{-- Totals Summary Bar --}}
        <div
            class="flex items-center gap-6 px-6 py-3 bg-white dark:bg-gray-800 rounded-2xl shadow-sm relative overflow-hidden">
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-600 dark:text-gray-400">
                    Total de Itens
                </span>
                <div class="relative">
                    <span wire:loading.remove wire:target="applyFilters, sortBy, gotoPage"
                        class="inline-flex items-center text-sm font-black text-[#4ECDC4] bg-[#4ECDC420] px-2 py-1 rounded-lg">
                        {{ $this->totalCount }}
                    </span>
                    <div wire:loading wire:target="applyFilters, sortBy, gotoPage"
                        class="h-7 w-8 bg-gray-200 dark:bg-gray-700 animate-pulse rounded-lg"></div>
                </div>
            </div>

            <div class="w-px self-stretch bg-gray-100 dark:bg-gray-700"></div>

            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-600 dark:text-gray-400">
                    Saldo Período
                </span>
                <div class="relative">
                    <div wire:loading.remove wire:target="applyFilters, sortBy, gotoPage">
                        @if($this->totalAmount > 0)
                            <span
                                class="inline-flex items-center text-sm font-black px-2 py-1 rounded-lg !text-emerald-600 dark:!text-emerald-400 !bg-emerald-50 dark:!bg-emerald-500/10">
                                R$ {{ number_format(abs($this->totalAmount), 2, ',', '.') }}
                            </span>
                        @elseif($this->totalAmount < 0)
                            <span
                                class="inline-flex items-center text-sm font-black px-2 py-1 rounded-lg !text-rose-600 dark:!text-rose-400 !bg-rose-50 dark:!bg-rose-500/10">
                                R$ {{ number_format(abs($this->totalAmount), 2, ',', '.') }}
                            </span>
                        @else
                            <span
                                class="inline-flex items-center text-sm font-black px-2 py-1 rounded-lg !text-gray-600 dark:!text-gray-400 !bg-gray-50 dark:!bg-gray-700/50">
                                R$ {{ number_format(abs($this->totalAmount), 2, ',', '.') }}
                            </span>
                        @endif
                    </div>
                    <div wire:loading wire:target="applyFilters, sortBy, gotoPage"
                        class="h-7 w-24 bg-gray-200 dark:bg-gray-700 animate-pulse rounded-lg"></div>
                </div>
            </div>
        </div>

        {{-- Tabela de Transações --}}
        <div class="overflow-hidden bg-white shadow dark:bg-gray-800 rounded-none relative">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" wire:click="sortBy('title')"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center gap-1 whitespace-nowrap">
                                    Título
                                    @if ($sortField === 'title')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" wire:click="sortBy('amount')"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center gap-1 whitespace-nowrap">
                                    Valor
                                    @if ($sortField === 'amount')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" wire:click="sortBy('type')"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center gap-1 whitespace-nowrap">
                                    Tipo
                                    @if ($sortField === 'type')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" wire:click="sortBy('status')"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center gap-1 whitespace-nowrap">
                                    Status
                                    @if ($sortField === 'status')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" wire:click="sortBy('due_date')"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center gap-1 whitespace-nowrap min-w-[120px]">
                                    Vencimento
                                    @if ($sortField === 'due_date')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Categorias
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Ações</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @forelse ($this->transactions as $transaction)
                            <livewire:transaction-row :transaction="$transaction"
                                wire:key="row-{{ $transaction->id }}-{{ $transaction->updated_at?->timestamp ?? time() }}" />
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-gray-400">
                                    Nenhuma transação encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginação --}}
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700">
                {{ $this->transactions->links() }}
            </div>
        </div>
    </div>
</div>