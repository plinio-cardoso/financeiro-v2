@php
    $classes = ($active ?? false)
        ? 'flex items-center px-4 py-2 text-emerald-800 dark:text-[#4ECDC4] bg-emerald-50 dark:bg-[#4ECDC420] rounded-lg group transition-all duration-200'
        : 'flex items-center px-4 py-2 text-[#1e293b] dark:text-gray-200 hover:text-emerald-800 dark:hover:text-[#4ECDC4] hover:bg-emerald-50 dark:hover:bg-[#4ECDC410] rounded-lg group transition-all duration-200';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if(isset($icon))
        <div
            class="{{ ($active ?? false) ? 'text-emerald-800 dark:text-[#4ECDC4]' : 'text-[#1e293b] dark:text-gray-400' }} group-hover:text-emerald-800 dark:group-hover:text-[#4ECDC4] transition-colors duration-200">
            {{ $icon }}
        </div>
    @endif
    <span class="ms-3 font-medium {{ ($active ?? false) ? 'text-emerald-800 dark:text-[#4ECDC4]' : 'text-[#1e293b] dark:text-gray-200' }} group-hover:text-emerald-800 dark:group-hover:text-[#4ECDC4]">{{ $slot ?? '' }}</span>
</a>
