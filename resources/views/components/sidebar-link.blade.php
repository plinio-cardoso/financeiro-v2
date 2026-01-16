@php
    $classes = ($active ?? false)
        ? 'flex items-center px-4 py-2 text-indigo-600 bg-indigo-50 dark:bg-indigo-900/20 dark:text-indigo-400 rounded-lg group transition-all duration-200'
        : 'flex items-center px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded-lg group transition-all duration-200';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if(isset($icon))
        <div
            class="{{ ($active ?? false) ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400' }} transition-colors duration-200">
            {{ $icon }}
        </div>
    @endif
    <span class="ms-3 font-medium">{{ $slot ?? '' }}</span>
</a>