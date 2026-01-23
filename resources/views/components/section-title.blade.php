<div class="md:col-span-1 flex justify-between">
    <div class="px-4 sm:px-0">
        <h3 class="text-sm font-black uppercase tracking-widest text-gray-900 dark:text-white">{{ $title }}</h3>

        <p class="mt-2 text-[11px] font-bold text-gray-500 dark:text-gray-400 leading-relaxed uppercase tracking-wide">
            {{ $description }}
        </p>
    </div>

    <div class="px-4 sm:px-0">
        {{ $aside ?? '' }}
    </div>
</div>