@php
    $classes = ($active ?? false)
        ? 'flex items-center px-4 py-2 text-[#4ECDC4] hover:!text-[#4ECDC4] dark:hover:!text-[#4ECDC4] bg-[#4ECDC420] rounded-lg group transition-all duration-200'
        : 'flex items-center px-4 py-2 text-gray-600 dark:text-gray-400 hover:!text-[#4ECDC4] dark:hover:!text-[#4ECDC4] hover:!bg-[#4ECDC410] dark:hover:!bg-[#4ECDC410] rounded-lg group transition-all duration-200';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if(isset($icon))
        <div
            class="{{ ($active ?? false) ? 'text-[#4ECDC4]' : 'text-gray-400' }} group-hover:!text-[#4ECDC4] dark:group-hover:!text-[#4ECDC4] transition-colors duration-200">
            {{ $icon }}
        </div>
    @endif
    <span class="ms-3 font-medium">{{ $slot ?? '' }}</span>
</a>