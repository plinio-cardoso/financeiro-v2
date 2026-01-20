@props(['property', 'options' => [], 'placeholder' => 'Selecione'])

<div x-data="customSelect(@entangle($attributes->wire('model')), {{ json_encode($options) }}, '{{ $placeholder }}')"
    class="relative">
    <div class="relative">
        <button type="button" @click="show = !show" @click.away="show = false" {{ $attributes->merge(['class' => 'relative w-full py-2 pl-3 pr-10 text-left bg-white border border-gray-400 dark:border-gray-700 rounded-xl shadow-none cursor-default focus:outline-none focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] sm:text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-300 transition-all']) }}>
            <span class="block truncate" x-text="selectedLabel"></span>
            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path d="M19 9l-7 7-7-7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </span>
        </button>
    </div>

    <div x-show="show" x-cloak x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg dark:bg-gray-800 max-h-60 overflow-hidden ring-1 ring-black ring-opacity-5 focus:outline-none">
        <ul
            class="py-1 overflow-auto text-base ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm max-h-48">
            <template x-for="option in options" :key="option.value">
                <li @click="select(option.value)"
                    class="relative py-2 pl-3 text-gray-900 cursor-pointer select-none pr-9 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                    <span class="block truncate font-normal" :class="{ 'font-semibold': selected == option.value }"
                        x-text="option.label"></span>

                    <span x-show="selected == option.value" x-cloak
                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-[#4ECDC4]">
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