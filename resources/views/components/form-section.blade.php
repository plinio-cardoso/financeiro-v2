@props(['submit'])

<div {{ $attributes->merge(['class' => 'md:grid md:grid-cols-3 md:gap-6']) }}>
    <x-section-title>
        <x-slot name="title">{{ $title }}</x-slot>
        <x-slot name="description">{{ $description }}</x-slot>
    </x-section-title>

    <div class="mt-5 md:mt-0 md:col-span-2">
        <form wire:submit="{{ $submit }}"
            class="overflow-hidden shadow rounded-2xl sm:rounded-[2rem] border border-gray-100 dark:border-gray-700/50">
            <div class="px-4 py-6 bg-white dark:bg-gray-800 sm:p-8">
                <div class="grid grid-cols-6 gap-6">
                    {{ $form }}
                </div>
            </div>

            @if (isset($actions))
                <div
                    class="flex items-center justify-end px-4 py-6 bg-white dark:bg-gray-800 text-end sm:px-8 border-t border-gray-100 dark:border-gray-700/50">
                    {{ $actions }}
                </div>
            @endif
        </form>
    </div>
</div>