@props(['name', 'class' => 'w-5 h-5'])

@php
    $svgs = [
        'pencil' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />',
        'check' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7" />',
        'recurring' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />',
        'spinner' => '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>',
    ];
@endphp

<svg {{ $attributes->merge(['class' => $class, 'fill' => 'none', 'viewBox' => ($name === 'spinner' ? '0 0 24 24' : '0 0 24 24'), 'stroke' => ($name === 'spinner' ? 'none' : 'currentColor')]) }}>
    {!! $svgs[$name] ?? '' !!}
</svg>