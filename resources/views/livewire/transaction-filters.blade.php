<div class="flex flex-wrap items-center gap-4 mb-8" @tags-loaded.window="$store.tags.setTags($event.detail.tags)">
    {{-- Search Input --}}
    <div class="relative w-64">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-3.5 w-3.5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <input type="text" wire:model.live.debounce.500ms="search" placeholder="Buscar (mín. 3 letras)..."
            class="block w-full pl-9 pr-4 py-2 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-700 rounded-lg text-xs font-bold text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600 focus:ring-2 focus:ring-[#4ECDC4]/10 focus:border-[#4ECDC4]/50 transition-all shadow-sm">
    </div>

    {{-- Data Range Group --}}
    <div
        class="flex items-center bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 px-1">
        <input type="date" wire:model.live.debounce.500ms="startDate"
            class="bg-transparent border-none focus:ring-0 text-xs font-bold text-gray-600 dark:text-gray-400 py-2 px-3">
        <div class="w-px h-4 bg-gray-100 dark:bg-gray-700"></div>
        <input type="date" wire:model.live.debounce.500ms="endDate"
            class="bg-transparent border-none focus:ring-0 text-xs font-bold text-gray-600 dark:text-gray-400 py-2 px-3">
    </div>

    {{-- Status Filter --}}
    <div class="w-40">
        <x-custom-select wire:model.live="filterStatus" :options="[]" placeholder="Todos os Status"
            x-init="options = $store.options.statuses; $watch('$store.options.statuses', val => options = val)"
            class="!py-2 !text-xs !font-bold" />
    </div>

    {{-- Type Filter --}}
    <div class="w-40">
        <x-custom-select wire:model.live="filterType" :options="[]" placeholder="Todos os Tipos"
            x-init="options = $store.options.types; $watch('$store.options.types', val => options = val)"
            class="!py-2 !text-xs !font-bold" />
    </div>

    {{-- Recurrence Filter --}}
    <div class="w-44">
        <x-custom-select wire:model.live="filterRecurrence" :options="[
    ['value' => '', 'label' => 'Recorrência (Todos)'],
    ['value' => 'recurring', 'label' => 'Recorrentes'],
    ['value' => 'not_recurring', 'label' => 'Não recorrentes']
]" placeholder="Recorrência (Todos)" class="!py-2 !text-xs !font-bold" />
    </div>

    {{-- Tags Filter --}}
    <div class="w-48">
        <x-multi-select wire:model.live="selectedTags" :options="[]" placeholder="Tags"
            x-init="options = $store.tags.list; $watch('$store.tags.list', val => options = val)"
            class="!py-2 !text-xs !font-bold" />
    </div>

    <button wire:click="clearFilters" @disabled(!$this->hasActiveFilters) @class([
        'text-xs font-bold uppercase tracking-widest ml-2 transition-colors',
        'text-gray-400 hover:text-[#4ECDC4] cursor-pointer' => $this->hasActiveFilters,
        'text-gray-300 dark:text-gray-600 cursor-not-allowed opacity-50' => !$this->hasActiveFilters,
    ])>
        Limpar filtros
    </button>
</div>
