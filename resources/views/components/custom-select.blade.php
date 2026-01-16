@props(['options', 'placeholder' => 'Selecione'])

<div x-data="{
    options: [],
    selected: @entangle($attributes->wire('model')),
    show: false,
    get selectedLabel() {
        const option = this.options.find(o => o.value == this.selected);
        return option ? option.label : '{{ $placeholder }}';
    },
    select(value) {
        this.selected = value;
        this.show = false;
    },
    init() {
        this.options = {{ json_encode($options) }};
    }
}" class="relative">
    <div class="relative">
        <button type="button" @click="show = !show" @click.away="show = false"
            class="relative w-full py-2 pl-3 pr-10 text-left bg-white border border-gray-300 rounded-md shadow-sm cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
            <span class="block truncate" x-text="selectedLabel"></span>
            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                    <path d="M7 7l3-3 3 3m0 6l-3 3-3-3" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </span>
        </button>
    </div>

    <div x-show="show" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg dark:bg-gray-800 max-h-60 overflow-hidden ring-1 ring-black ring-opacity-5 focus:outline-none">

        <ul
            class="py-1 overflow-auto text-base ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm max-h-48">
            <template x-for="option in options" :key="option.value">
                <li @click="select(option.value)"
                    class="relative py-2 pl-3 text-gray-900 cursor-pointer select-none pr-9 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                    <span class="block truncate font-normal" :class="{ 'font-semibold': selected == option.value }"
                        x-text="option.label"></span>

                    <span x-show="selected == option.value"
                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 dark:text-indigo-400">
                        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
            </template>
        </ul>
    </div>
</div>