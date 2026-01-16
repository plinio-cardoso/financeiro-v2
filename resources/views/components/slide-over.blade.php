@props(['id', 'maxWidth'])

@php
    $id = $id ?? md5($attributes->wire('model'));

    $maxWidth = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
    ][$maxWidth ?? '2xl'];
@endphp

<div x-data="{ show: @entangle($attributes->wire('model')) }" x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false" x-show="show" id="{{ $id }}" class="fixed inset-0 z-50 overflow-hidden"
    style="display: none;">
    <div class="absolute inset-0 overflow-hidden">
        {{-- Overlay --}}
        <div x-show="show"
            class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity dark:bg-gray-900 dark:bg-opacity-80"
            x-on:click="show = false" x-transition:enter="ease-in-out duration-500" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-500"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        </div>

        <div class="fixed inset-y-0 right-0 flex max-w-full pl-10 pointer-events-none">
            {{-- Slide-over panel --}}
            <div x-show="show" class="w-screen {{ $maxWidth }} pointer-events-auto" x-trap.inert.noscroll="show"
                x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">

                <div class="flex flex-col h-full bg-white dark:bg-gray-900 shadow-2xl">
                    {{-- Header --}}
                    @if (isset($title) || isset($header))
                        <div class="px-6 py-6 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                    {{ $title ?? $header }}
                                </h2>
                                <div class="flex items-center ml-3 h-7">
                                    <button type="button"
                                        class="p-2 -mr-2 text-gray-400 hover:text-gray-500 focus:outline-none dark:hover:text-gray-300 transition-colors"
                                        x-on:click="show = false">
                                        <span class="sr-only">Fechar</span>
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            @if (isset($description))
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $description }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Scrollable Content --}}
                    <div class="relative flex-1 px-6 py-8 overflow-y-auto custom-scrollbar">
                        {{ $slot }}
                    </div>

                    {{-- Footer/Actions --}}
                    @if (isset($footer) || isset($actions))
                        <div
                            class="flex-shrink-0 px-6 py-6 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-3 bg-gray-50/50 dark:bg-gray-800/50">
                            {{ $footer ?? $actions }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>