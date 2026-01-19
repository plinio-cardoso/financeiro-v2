@props(['options', 'placeholder' => 'Select options'])

<div x-data="{
    options: [],
    selected: @entangle($attributes->wire('model')),
    show: false,
    filter: '',
    get filteredOptions() {
        if (this.filter === '') return this.options;
        return this.options.filter(option => option.name.toLowerCase().includes(this.filter.toLowerCase()));
    },
    toggle(id) {
        if (this.selected.includes(id)) {
            this.selected = this.selected.filter(item => item != id);
        } else {
            this.selected.push(id);
        }
    },
    isSelected(id) {
        return this.selected.includes(id);
    },
    init() {
        this.options = {{ json_encode($options) }};
        if (!this.selected) this.selected = [];
        // Ensure selected are all integers if options ids are integers, or match types
        // Livewire might send selected as strings
    }
}" class="relative">
    <div class="relative">
        <button type="button" @click="show = !show" @click.away="show = false" {{ $attributes->merge(['class' => 'relative w-full py-2 pl-3 pr-10 text-left bg-white border border-gray-100 dark:border-gray-700/50 rounded-xl shadow-none cursor-default focus:outline-none focus:ring-2 focus:ring-[#4ECDC4]/10 focus:border-[#4ECDC4]/50 sm:text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-300 transition-all']) }}>
            <span class="block truncate font-bold">
                <span x-text="selected.length === 0 ? '{{ $placeholder }}' : selected.length + ' selecionados'"></span>
            </span>
            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path d="M19 9l-7 7-7-7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </span>
        </button>
    </div>

    <div x-show="show" x-cloak x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg dark:bg-gray-800 max-h-60 overflow-hidden ring-1 ring-black ring-opacity-5 focus:outline-none">

        <div class="p-2 border-b border-gray-200 dark:border-gray-700">
            <input x-model="filter" type="text" placeholder="Buscar..."
                class="block w-full px-3 py-2 text-sm border-gray-300 rounded-md focus:ring-[#4ECDC4] focus:border-[#4ECDC4] dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100 placeholder-gray-500">
        </div>

        <ul
            class="py-1 overflow-auto text-base ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm max-h-48">
            <template x-for="option in filteredOptions" :key="option.id">
                <li @click="toggle(option.id)"
                    class="relative py-2 pl-3 text-gray-900 cursor-pointer select-none pr-9 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                    <div class="flex items-center">
                        <span class="block truncate font-normal" :class="{ 'font-semibold': isSelected(option.id) }"
                            x-text="option.name"></span>
                        <span x-show="option.color" x-cloak class="w-2 h-2 ml-2 rounded-full"
                            :style="'background-color: ' + option.color"></span>
                    </div>

                    <span x-show="isSelected(option.id)" x-cloak
                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-[#4ECDC4]">
                        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
            </template>
            <div x-show="filteredOptions.length === 0" x-cloak class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                Nenhum resultado encontrado.
            </div>
        </ul>
    </div>
</div>