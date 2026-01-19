<div x-data="{ 
    isOpen: @entangle('showCreateModal'),
    editingId: @entangle('editingTransactionId')
}" @transaction-saved.window="isOpen = false">
    {{-- Compact Filters Row --}}
    <div class="flex flex-wrap items-center gap-4 mb-8">
        {{-- Data Range Group --}}
        <div
            class="flex items-center bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 px-1">
            <input type="date" wire:model.live="startDate"
                class="bg-transparent border-none focus:ring-0 text-xs font-bold text-gray-600 dark:text-gray-400 py-2 px-3">
            <div class="w-px h-4 bg-gray-100 dark:bg-gray-700"></div>
            <input type="date" wire:model.live="endDate"
                class="bg-transparent border-none focus:ring-0 text-xs font-bold text-gray-600 dark:text-gray-400 py-2 px-3">
        </div>

        {{-- Status Filter --}}
        <div class="w-40">
            <x-custom-select wire:model.live="filterStatus" :options="[
        ['value' => '', 'label' => 'Todos os Status'],
        ['value' => 'pending', 'label' => 'Pendentes'],
        ['value' => 'paid', 'label' => 'Pagos']
    ]"            placeholder="Todos os Status" class="!py-2 !text-xs !font-bold" />
        </div>

        {{-- Type Filter --}}
        <div class="w-40">
            <x-custom-select wire:model.live="filterType" :options="[
        ['value' => '', 'label' => 'Todos os Tipos'],
        ['value' => 'debit', 'label' => 'Débitos'],
        ['value' => 'credit', 'label' => 'Créditos']
    ]"  placeholder="Todos os Tipos" class="!py-2 !text-xs !font-bold" />
        </div>

        {{-- Tags Filter --}}
        <div class="w-48">
            <x-multi-select wire:model.live="selectedTags" :options="$this->tags" placeholder="Categorias"
                class="!py-2 !text-xs !font-bold" />
        </div>

        <button wire:click="clearFilters"
            class="text-xs font-bold text-gray-400 hover:text-[#4ECDC4] transition-colors uppercase tracking-widest ml-2">
            Limpar filtros
        </button>


    </div>



    {{-- Main Content Card --}}
    <div
        class="bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden">
        {{-- List Header with Search & Actions --}}
        <div
            class="px-6 py-3 border-b border-gray-50 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/10 flex items-center justify-between">
            <div class="relative w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-3.5 w-3.5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" wire:model.live="search" placeholder="Buscar..."
                    class="block w-full pl-9 pr-4 py-1.5 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-700 rounded-lg text-[11px] font-bold text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600 focus:ring-2 focus:ring-[#4ECDC4]/10 focus:border-[#4ECDC4]/50 transition-all shadow-sm">
            </div>

            <x-button @click="isOpen = true; if(editingId !== null) $wire.createTransaction()"
                class="!bg-[#4ECDC4] hover:!bg-[#3dbdb5] !text-gray-900 shadow-sm py-1.5 px-4 rounded-lg active:scale-95 transition-all text-[11px] font-bold uppercase tracking-wider">
                Nova Transação
            </x-button>
        </div>

        {{-- Totals Summary Bar --}}
        <div class="flex items-center gap-6 px-6 py-3 mb-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm">
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-600 dark:text-gray-400">
                    Total de Itens
                </span>
                <span class="text-sm font-black text-[#4ECDC4] bg-[#4ECDC420] px-2 py-0.5 rounded-lg">
                    {{ $this->totalCount }}
                </span>
            </div>

            <div class="w-px h-4 bg-gray-100 dark:bg-gray-700"></div>

            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-600 dark:text-gray-400">
                    Saldo Período
                </span>
                @if($this->totalAmount > 0)
                    <span
                        class="text-sm font-black px-2 py-0.5 rounded-lg !text-emerald-600 dark:!text-emerald-400 !bg-emerald-50 dark:!bg-emerald-500/10">
                        R$ {{ number_format(abs($this->totalAmount), 2, ',', '.') }}
                    </span>
                @elseif($this->totalAmount < 0)
                    <span
                        class="text-sm font-black px-2 py-0.5 rounded-lg !text-rose-600 dark:!text-rose-400 !bg-rose-50 dark:!bg-rose-500/10">
                        R$ {{ number_format(abs($this->totalAmount), 2, ',', '.') }}
                    </span>
                @else
                    <span
                        class="text-sm font-black px-2 py-0.5 rounded-lg !text-gray-600 dark:!text-gray-400 !bg-gray-50 dark:!bg-gray-700/50">
                        R$ {{ number_format(abs($this->totalAmount), 2, ',', '.') }}
                    </span>
                @endif
            </div>
        </div>

        <x-slide-over wire:model.live="showCreateModal" maxWidth="md">
            <x-slot name="title">
                {{ $editingTransactionId ? __('Editar Transação') : __('Nova Transação') }}
            </x-slot>

            <div class="relative min-h-[200px]">
                <div wire:loading wire:target="createTransaction, editTransaction" class="absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-gray-900/50 z-10 rounded-xl">
                    <svg class="w-8 h-8 animate-spin text-[#4ECDC4]" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <livewire:transaction-form :transaction-id="$editingTransactionId" :key="'transaction-form-' . ($editingTransactionId ?? 'new')" />
            </div>
        </x-slide-over>

        {{-- Tabela de Transações --}}
        <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-gray-800">
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
                                Tags
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Ações</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @forelse ($this->transactions as $transaction)
                            <tr wire:key="transaction-{{ $transaction->id }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div x-data="{ 
                                            editing: false, 
                                            value: '{{ addslashes($transaction->title) }}',
                                            original: '{{ addslashes($transaction->title) }}',
                                            save() {
                                                if (this.value === this.original) { this.editing = false; return; }
                                                $wire.updateField({{ $transaction->id }}, 'title', this.value).then(() => {
                                                    this.original = this.value;
                                                    this.editing = false;
                                                });
                                            }
                                        }" class="min-w-[200px]">
                                        <div x-show="!editing" @click="editing = true"
                                            class="flex items-center gap-2 group cursor-pointer">
                                            <span class="text-sm font-bold text-gray-900 dark:text-gray-100 group-hover:text-[#4ECDC4] transition-colors">
                                                {{ $transaction->title }}
                                            </span>
                                            <svg class="w-3 h-3 text-[#4ECDC4] opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </div>
                                        <input x-show="editing" x-ref="input" x-model="value" @focusout="save()"
                                            @keydown.enter="save()" @keydown.escape="editing = false; value = original"
                                            x-effect="if(editing) { $nextTick(() => $refs.input.focus()); }" type="text"
                                            class="w-full px-2 py-1 text-sm font-bold bg-white dark:bg-gray-700 border-b-2 border-[#4ECDC4] border-t-0 border-x-0 focus:ring-0 focus:border-[#4ECDC4] text-gray-900 dark:text-gray-100 p-0">
                                    </div>
                                    <div x-data="{ 
                                            editing: false, 
                                            value: '{{ addslashes($transaction->description ?? '') }}',
                                            original: '{{ addslashes($transaction->description ?? '') }}',
                                            save() {
                                                if (this.value === this.original) { this.editing = false; return; }
                                                $wire.updateField({{ $transaction->id }}, 'description', this.value).then(() => {
                                                    this.original = this.value;
                                                    this.editing = false;
                                                });
                                            }
                                        }" class="mt-1">
                                        <div x-show="!editing" @click="editing = true"
                                            class="flex items-center gap-2 group cursor-pointer min-h-[1.25rem]">
                                            <span class="text-sm text-gray-500 dark:text-gray-400 group-hover:text-[#4ECDC4] transition-colors">
                                                @if($transaction->description)
                                                    {{ Str::limit($transaction->description, 50) }}
                                                @else
                                                    <span class="text-xs italic opacity-30">{{ __('Adicionar descrição...') }}</span>
                                                @endif
                                            </span>
                                            <svg class="w-3 h-3 text-[#4ECDC4] opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </div>
                                        <input x-show="editing" x-ref="input" x-model="value" @focusout="save()"
                                            @keydown.enter="save()" @keydown.escape="editing = false; value = original"
                                            x-effect="if(editing) { $nextTick(() => $refs.input.focus()); }" type="text"
                                            class="w-full px-2 py-0 text-sm bg-white dark:bg-gray-700 border-b border-[#4ECDC4] border-t-0 border-x-0 focus:ring-0 focus:border-[#4ECDC4] text-gray-500 dark:text-gray-400 p-0">
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div x-data="{ 
                                            editing: false, 
                                            value: '{{ number_format($transaction->amount, 2, '.', '') }}',
                                            original: '{{ number_format($transaction->amount, 2, '.', '') }}',
                                            save() {
                                                if (this.value === this.original) { this.editing = false; return; }
                                                $wire.updateField({{ $transaction->id }}, 'amount', this.value).then(() => {
                                                    this.original = this.value;
                                                    this.editing = false;
                                                });
                                            }
                                        }" class="flex flex-col items-start">
                                        <div x-show="!editing" @click="editing = true"
                                            class="flex items-center gap-2 group cursor-pointer">
                                            <span class="text-sm font-bold text-gray-900 dark:text-gray-100 group-hover:text-[#4ECDC4] transition-colors">
                                                R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                                            </span>
                                            <svg class="w-3 h-3 text-[#4ECDC4] opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </div>
                                        <div x-show="editing" class="flex items-center">
                                            <span class="text-sm mr-1 font-bold text-gray-900 dark:text-gray-100">R$</span>
                                            <input x-ref="input" x-model="value" @focusout="save()"
                                                @keydown.enter="save()" @keydown.escape="editing = false; value = original"
                                                x-effect="if(editing) { $nextTick(() => $refs.input.focus()); }" type="text"
                                                class="w-24 px-1 py-0 text-sm font-bold bg-white dark:bg-gray-700 border-b border-[#4ECDC4] border-t-0 border-x-0 focus:ring-0 focus:border-[#4ECDC4] text-gray-900 dark:text-gray-100 p-0">
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div x-data="{ 
                                            show: false, 
                                            value: '{{ $transaction->type->value }}',
                                            original: '{{ $transaction->type->value }}',
                                            options: [
                                                { label: 'Débito', value: 'debit', color: '#EF4444' },
                                                { label: 'Crédito', value: 'credit', color: '#10B981' }
                                            ],
                                            select(val) {
                                                if (val === this.original) { this.show = false; return; }
                                                this.value = val;
                                                $wire.updateField({{ $transaction->id }}, 'type', this.value).then(() => {
                                                    this.original = this.value;
                                                    this.show = false;
                                                });
                                            }
                                        }" class="relative">
                                        <div @click="show = !show" class="cursor-pointer group flex items-center gap-1">
                                            <span @class([
                                                'inline-flex px-2 text-xs font-semibold leading-5 rounded-full transition-all group-hover:ring-2 group-hover:ring-[#4ECDC4]/30',
                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' => $transaction->type->value === 'debit',
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' => $transaction->type->value === 'credit',
                                            ])>
                                                {{ $transaction->type->value === 'debit' ? 'Débito' : 'Crédito' }}
                                            </span>
                                        </div>
                                        
                                        <div x-show="show" @click.away="show = false" x-cloak 
                                            class="absolute z-50 bottom-full mb-2 left-0 w-32 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 p-1 overflow-hidden">
                                            <div @click="select('debit')" 
                                                class="flex items-center gap-2 p-2 hover:bg-[#4ECDC4]/10 dark:hover:bg-[#4ECDC4]/20 rounded-lg cursor-pointer transition-colors">
                                                <div class="w-3 h-3 rounded-full bg-red-600"></div>
                                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">Débito</span>
                                                <svg x-show="value === 'debit'" class="w-3 h-3 text-[#4ECDC4] ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                            <div @click="select('credit')" 
                                                class="flex items-center gap-2 p-2 hover:bg-[#4ECDC4]/10 dark:hover:bg-[#4ECDC4]/20 rounded-lg cursor-pointer transition-colors">
                                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">Crédito</span>
                                                <svg x-show="value === 'credit'" class="w-3 h-3 text-[#4ECDC4] ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div x-data="{ 
                                            show: false, 
                                            value: '{{ $transaction->status->value }}',
                                            original: '{{ $transaction->status->value }}',
                                            select(val) {
                                                if (val === this.original) { this.show = false; return; }
                                                this.value = val;
                                                $wire.updateField({{ $transaction->id }}, 'status', this.value).then(() => {
                                                    this.original = this.value;
                                                    this.show = false;
                                                });
                                            }
                                        }" class="relative">
                                        <div @click="show = !show" class="cursor-pointer group flex items-center gap-1">
                                            <span @class([
                                                'inline-flex px-2 text-xs font-semibold leading-5 rounded-full transition-all group-hover:ring-2 group-hover:ring-[#4ECDC4]/30',
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' => $transaction->status->value === 'paid',
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' => $transaction->status->value === 'pending',
                                            ])>
                                                {{ $transaction->status->value === 'paid' ? 'Pago' : 'Pendente' }}
                                            </span>
                                        </div>

                                        <div x-show="show" @click.away="show = false" x-cloak 
                                            class="absolute z-50 bottom-full mb-2 left-0 w-32 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 p-1 overflow-hidden">
                                            <div @click="select('pending')" 
                                                class="flex items-center gap-2 p-2 hover:bg-[#4ECDC4]/10 dark:hover:bg-[#4ECDC4]/20 rounded-lg cursor-pointer transition-colors">
                                                <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">Pendente</span>
                                                <svg x-show="value === 'pending'" class="w-3 h-3 text-[#4ECDC4] ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                            <div @click="select('paid')" 
                                                class="flex items-center gap-2 p-2 hover:bg-[#4ECDC4]/10 dark:hover:bg-[#4ECDC4]/20 rounded-lg cursor-pointer transition-colors">
                                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">Pago</span>
                                                <svg x-show="value === 'paid'" class="w-3 h-3 text-[#4ECDC4] ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div x-data="{ 
                                            editing: false, 
                                            value: '{{ $transaction->due_date->format('Y-m-d') }}',
                                            original: '{{ $transaction->due_date->format('Y-m-d') }}',
                                            save() {
                                                if (this.value === this.original) { this.editing = false; return; }
                                                $wire.updateField({{ $transaction->id }}, 'due_date', this.value).then(() => {
                                                    this.original = this.value;
                                                    this.editing = false;
                                                });
                                            }
                                        }">
                                        <div x-show="!editing" @click="editing = true"
                                            class="flex items-center gap-2 group cursor-pointer">
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-[#4ECDC4] transition-colors">
                                                {{ $transaction->due_date->format('d/m/Y') }}
                                            </span>
                                            <svg class="w-3 h-3 text-[#4ECDC4] opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </div>
                                        <input x-show="editing" x-ref="input" x-model="value" @focusout="save()"
                                            @change="save()" @keydown.enter="save()"
                                            @keydown.escape="editing = false; value = original"
                                            x-effect="if(editing) { $nextTick(() => $refs.input.focus()); }" type="date"
                                            class="px-2 py-0 text-sm bg-white dark:bg-gray-700 border-b border-[#4ECDC4] border-t-0 border-x-0 focus:ring-0 focus:border-[#4ECDC4] text-gray-900 dark:text-gray-100 p-0">
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div x-data="{ 
                                        show: false,
                                        selected: @js($transaction->tags->pluck('id')),
                                        allTags: @js($this->tags),
                                        save() {
                                            $wire.updateTags({{ $transaction->id }}, this.selected);
                                            this.show = false;
                                        },
                                        toggle(id) {
                                            if (this.selected.includes(id)) {
                                                this.selected = this.selected.filter(i => i != id);
                                            } else {
                                                this.selected.push(id);
                                            }
                                        }
                                    }" class="relative">
                                        <div @click="show = !show" class="flex flex-wrap gap-1 cursor-pointer group p-1 rounded transition-all min-h-[32px]">
                                            @forelse ($transaction->tags as $tag)
                                                <span class="inline-flex px-2 py-0.5 text-[10px] font-black rounded-md uppercase tracking-wider"
                                                    style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}; border: 1px solid {{ $tag->color }}30">
                                                    {{ $tag->name }}
                                                </span>
                                            @empty
                                                <span class="text-[10px] text-gray-600 dark:text-gray-400 group-hover:text-[#4ECDC4] transition-colors font-semibold">Gerenciar tags...</span>
                                            @endforelse
                                        </div>
                                        
                                        <div x-show="show" @click.away="save()" x-cloak 
                                            class="absolute z-50 bottom-full mb-2 left-0 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 p-2 max-h-60 overflow-y-auto">
                                            <template x-for="tag in allTags" :key="tag.id">
                                                <div @click="toggle(tag.id)" 
                                                    class="flex items-center gap-2 p-2 hover:bg-[#4ECDC4]/10 dark:hover:bg-[#4ECDC4]/20 rounded-lg cursor-pointer transition-colors">
                                                    <div class="w-4 h-4 rounded border flex items-center justify-center transition-colors"
                                                        :class="selected.includes(tag.id) ? 'bg-[#4ECDC4] border-[#4ECDC4]' : 'border-gray-300 dark:border-gray-600'">
                                                        <svg x-show="selected.includes(tag.id)" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                    <span class="text-xs font-bold" :style="'color: ' + tag.color" x-text="tag.name"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                    <div class="flex justify-end gap-2">
                                        @if ($transaction->status->value === 'pending' && $transaction->type->value === 'debit')
                                            <button wire:click="markAsPaid({{ $transaction->id }})" wire:loading.attr="disabled"
                                                wire:loading.class="opacity-50 cursor-not-allowed"
                                                wire:target="markAsPaid({{ $transaction->id }})"
                                                class="flex items-center gap-1.5 px-4 py-2 bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 rounded-xl hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-all text-[10px] font-black uppercase tracking-widest group border border-emerald-500/10 dark:border-none shadow-sm"
                                                title="Pagar">
                                                
                                                <div wire:loading.remove wire:target="markAsPaid({{ $transaction->id }})" class="flex items-center gap-1.5">
                                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5"
                                                            d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span class="text-emerald-800 dark:text-emerald-400">Pagar</span>
                                                </div>

                                                <svg wire:loading wire:target="markAsPaid({{ $transaction->id }})"
                                                    class="w-4 h-4 animate-spin text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                        stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                            </button>
                                        @endif
                                        <button @click="isOpen = true; $wire.editTransaction({{ $transaction->id }})"
                                            class="text-[#4ECDC4] hover:text-[#3dbdb5] dark:text-[#4ECDC4] dark:hover:text-[#3dbdb5] transition-colors p-1 rounded-full hover:bg-[#4ECDC410] dark:hover:bg-[#4ECDC420]"
                                            title="Editar">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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