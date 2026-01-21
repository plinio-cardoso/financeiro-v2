<div x-data="{
    isOpen: false,
    editingId: @entangle('editingRecurringId').live,

    openEdit(recurringId) {
        this.editingId = recurringId;
        this.isOpen = true;
    },

    openCreate() {
        this.editingId = null;
        this.isOpen = true;
    },

    closeModalAndReset() {
        this.isOpen = false;
        $wire.closeModal();
    }
}" @recurring-saved.window="closeModalAndReset()" @close-modal.window="closeModalAndReset()"
    @keydown.escape.window="closeModalAndReset()">

    {{-- Main Content Card --}}
    <div
        class="bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden">
        
        {{-- List Header with Actions --}}
        <div
            class="px-6 py-3 border-b border-gray-50 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/10 flex items-center justify-between">
            {{-- Global Loading Indicator --}}
            <div wire:loading.flex class="items-center gap-3">
                <div class="w-4 h-4 border-2 border-[#4ECDC4] border-t-transparent rounded-full animate-spin"></div>
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 whitespace-nowrap">
                    Sincronizando...
                </span>
            </div>
            <div wire:loading.remove>
                {{-- Placeholder to maintain layout --}}
                <div class="h-10"></div>
            </div>

            <x-button @click="openCreate()"
                class="!bg-[#4ECDC4] hover:!bg-[#3dbdb5] !text-gray-900 shadow-sm py-1.5 px-4 rounded-lg active:scale-95 transition-all text-[11px] font-bold uppercase tracking-wider">
                Nova Transação
            </x-button>
        </div>

        {{-- Totals Summary Bar --}}
        <div class="flex items-center gap-6 px-6 py-3 bg-white dark:bg-gray-800 rounded-2xl shadow-sm relative overflow-hidden">
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-600 dark:text-gray-400">
                    Total de Recorrências
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
                    Impacto Mensal Estimado
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

        {{-- Slide-over Modal (Pure Alpine for speed) --}}
        <div x-show="isOpen" wire:ignore.self class="fixed inset-0 z-50 overflow-hidden" style="display: none;"
            x-transition:enter="transition ease-in-out duration-500" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in-out duration-500"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="absolute inset-0 overflow-hidden">
                {{-- Backdrop --}}
                <div class="absolute inset-0 bg-gray-500/75 dark:bg-gray-900/80 transition-opacity"
                    @click="closeModalAndReset()"></div>

                <div class="fixed inset-y-0 right-0 flex max-w-full pl-10 pointer-events-none">
                    <div x-show="isOpen" class="w-screen max-w-md pointer-events-auto shadow-2xl"
                        x-transition:enter="transform transition ease-in-out duration-500"
                        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                        x-transition:leave="transform transition ease-in-out duration-500"
                        x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">

                        <div class="flex flex-col h-full bg-white dark:bg-gray-900">
                            {{-- Header --}}
                            <div
                                class="px-6 py-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between rounded-none">
                                <h2 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tighter">
                                    Editar Recorrência
                                </h2>
                                <button type="button" @click="closeModalAndReset()"
                                    class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Content Area --}}
                            <div class="flex-1 relative overflow-hidden">
                                {{-- Loading Overlay for Modal Content --}}
                                <div wire:loading wire:target="editingRecurringId"
                                    class="absolute inset-0 z-[60] bg-white/60 dark:bg-gray-900/60 backdrop-blur-sm flex items-center justify-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <div class="w-10 h-10 border-4 border-[#4ECDC4] border-t-transparent rounded-full animate-spin"></div>
                                        <span class="text-[10px] font-black uppercase tracking-widest text-[#4ECDC4]">Carregando...</span>
                                    </div>
                                </div>

                                {{-- Component Content --}}
                                <div class="w-full h-full px-6 py-8 overflow-y-auto custom-scrollbar">
                                    {{-- Recurring Transaction Form --}}
                                    <livewire:recurring-transaction-form :recurring-id="$editingRecurringId"
                                        :key="'recurring-form-' . ($editingRecurringId ?? 'new')" />
                                </div>
                            </div>
                        </div>
                    </div>
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
                            <th scope="col" wire:click="sortBy('frequency')"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center gap-1 whitespace-nowrap">
                                    Frequência
                                    @if ($sortField === 'frequency')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" wire:click="sortBy('next_due_date')"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center gap-1 whitespace-nowrap min-w-[120px]">
                                    Próximo Vencimento
                                    @if ($sortField === 'next_due_date')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
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
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="min-w-0 flex-1">
                                        <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                            {{ $recurring->title }}
                                        </span>
                                        @if ($recurring->description)
                                            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                {{ Str::limit($recurring->description, 50) }}
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- Amount --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                        R$ {{ number_format($recurring->amount, 2, ',', '.') }}
                                    </span>
                                </td>

                                {{-- Type --}}
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $recurring->next_due_date ? $recurring->next_due_date->format('d/m/Y') : '-' }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                    <div class="flex justify-end gap-2 pr-8">
                                        <button @click="openEdit({{ $recurring->id }})"
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
