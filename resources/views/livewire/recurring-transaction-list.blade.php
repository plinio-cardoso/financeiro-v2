<div class="px-4 sm:px-0" x-data="{
    // Modal state is now global
}" @recurring-saved.window="$wire.refreshList()" @close-modal.window="">

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
                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 whitespace-nowrap">
                        Sincronizando...
                    </span>
                </div>
            </div>

            <x-button @click="$dispatch('open-recurring-modal', { recurringId: null })"
                class="!bg-[#4ECDC4] hover:!bg-[#3dbdb5] !text-gray-900 shadow-sm py-1.5 px-4 rounded-lg active:scale-95 transition-all text-[11px] font-bold uppercase tracking-wider">
                Nova Recorrência
            </x-button>
        </div>

        {{-- Totals Summary Bar --}}
        <div class="flex items-center gap-6 px-6 py-3 bg-white dark:bg-gray-800 rounded-2xl shadow-sm relative overflow-hidden">
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-600 dark:text-gray-400">
                    <span class="sm:hidden">ITENS</span>
                    <span class="hidden sm:inline">Total de Recorrências</span>
                </span>
                <div class="relative">
                    <span wire:loading.remove wire:target="applyFilters, sortBy, gotoPage"
                        class="inline-flex items-center text-sm font-black text-[#4ECDC4] bg-[#4ECDC420] px-2 py-1 rounded-lg">
                        {{ $this->totalCount }}
                    </span>
                    <div wire:loading wire:target="applyFilters, sortBy, gotoPage" class="h-7 w-8 bg-gray-200 dark:bg-gray-700 animate-pulse rounded-lg"></div>
                </div>
            </div>

            <div class="w-px self-stretch bg-gray-100 dark:bg-gray-700"></div>

            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-600 dark:text-gray-400">
                    <span class="sm:hidden">SALDO</span>
                    <span class="hidden sm:inline">Impacto Mensal Estimado</span>
                </span>
                <div class="relative">
                    <div wire:loading.remove wire:target="applyFilters, sortBy, gotoPage">
                        @if($this->totalMonthlyAmount > 0)
                            <span
                                class="inline-flex items-center text-sm font-black px-2 py-1 rounded-lg !text-emerald-600 dark:!text-emerald-400 !bg-emerald-50 dark:!bg-emerald-500/10">
                                R$ {{ number_format(abs($this->totalMonthlyAmount), 2, ',', '.') }}
                            </span>
                        @elseif($this->totalMonthlyAmount < 0)
                            <span
                                class="inline-flex items-center text-sm font-black px-2 py-1 rounded-lg !text-rose-600 dark:!text-rose-400 !bg-rose-50 dark:!bg-rose-500/10">
                                R$ {{ number_format(abs($this->totalMonthlyAmount), 2, ',', '.') }}
                            </span>
                        @else
                            <span
                                class="inline-flex items-center text-sm font-black px-2 py-1 rounded-lg !text-gray-600 dark:!text-gray-400 !bg-gray-50 dark:!bg-gray-700/50">
                                R$ {{ number_format(abs($this->totalMonthlyAmount), 2, ',', '.') }}
                            </span>
                        @endif
                    </div>
                    <div wire:loading wire:target="applyFilters, sortBy, gotoPage" class="h-7 w-24 bg-gray-200 dark:bg-gray-700 animate-pulse rounded-lg"></div>
                </div>
            </div>
        </div>

        {{-- Tabela de Transações Recorrentes --}}
        <div class="overflow-hidden bg-white shadow dark:bg-gray-800 rounded-none relative">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" wire:click="sortBy('title')"
                                class="px-4 sm:px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center gap-1 whitespace-nowrap">
                                    Título
                                    @if ($sortField === 'title')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" wire:click="sortBy('amount')"
                                class="px-4 sm:px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center gap-1 whitespace-nowrap">
                                    Valor
                                    @if ($sortField === 'amount')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" wire:click="sortBy('type')"
                                class="hidden sm:table-cell px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center gap-1 whitespace-nowrap">
                                    Tipo
                                    @if ($sortField === 'type')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" wire:click="sortBy('frequency')"
                                class="hidden sm:table-cell px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center gap-1 whitespace-nowrap">
                                    Frequência
                                    @if ($sortField === 'frequency')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" wire:click="sortBy('next_due_date')"
                                class="hidden sm:table-cell px-4 sm:px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center gap-1 whitespace-nowrap min-w-[100px] sm:min-w-[120px]">
                                    Próximo Vencimento
                                    @if ($sortField === 'next_due_date')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col"
                                class="hidden sm:table-cell px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Status
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Ações</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @forelse ($this->recurringTransactions as $recurring)
                            <tr class="hover:bg-gray-100 dark:hover:bg-gray-700/30 transition-colors">
                                {{-- Title & Description --}}
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                    <div class="min-w-0 flex-1">
                                        <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                            {{ $recurring->title }}
                                        </span>
                                        {{-- Mobile: Show Next Due Date below title --}}
                                        <div class="sm:hidden text-[10px] font-medium text-gray-500 dark:text-gray-400 mt-0.5 uppercase tracking-wide">
                                            Vencimento: {{ $recurring->next_due_date ? $recurring->next_due_date->format('d/m/y') : '-' }}
                                        </div>
                                        @if ($recurring->description)
                                            <div class="hidden sm:block mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                {{ Str::limit($recurring->description, 50) }}
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- Amount --}}
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                        R$ {{ number_format($recurring->amount, 2, ',', '.') }}
                                    </span>
                                </td>

                                {{-- Type --}}
                                <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap">
                                    <span @class([
                                        'inline-flex px-2 text-xs font-semibold leading-5 rounded-full',
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' =>
                                            $recurring->type->value === 'debit',
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' =>
                                            $recurring->type->value === 'credit',
                                    ])>
                                        {{ $recurring->type->value === 'debit' ? 'Débito' : 'Crédito' }}
                                    </span>
                                </td>

                                {{-- Frequency --}}
                                <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900 dark:text-gray-100">
                                        @switch($recurring->frequency->value)
                                            @case('weekly')
                                                Semanal
                                            @break

                                            @case('monthly')
                                                Mensal
                                            @break

                                            @case('custom')
                                                Personalizada
                                            @break
                                        @endswitch
                                    </span>
                                </td>

                                {{-- Next Due Date --}}
                                <td class="hidden sm:table-cell px-4 sm:px-6 py-4 whitespace-nowrap">
                                    <span class="text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $recurring->next_due_date ? $recurring->next_due_date->format('d/m/y') : '-' }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap">
                                    <span @class([
                                        'inline-flex px-2 text-xs font-semibold leading-5 rounded-full',
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' =>
                                            $recurring->active,
                                        'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' =>
                                            !$recurring->active,
                                    ])>
                                        {{ $recurring->active ? 'Ativa' : 'Inativa' }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="px-2 py-4 text-sm font-medium text-right whitespace-nowrap">
                                    <div class="flex justify-end gap-2 sm:pr-8">
                                        <button @click="$dispatch('open-recurring-modal', { recurringId: {{ $recurring->id }} })"
                                            class="text-gray-400 hover:text-[#4ECDC4] dark:text-gray-500 dark:hover:text-[#4ECDC4] transition-colors p-1 rounded-full hover:bg-[#4ECDC410] dark:hover:bg-[#4ECDC420]"
                                            title="Editar recorrência">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-gray-400">
                                    Nenhuma transação recorrente encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginação --}}
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700">
                {{ $this->recurringTransactions->links() }}
            </div>
        </div>
    </div>
</div>
