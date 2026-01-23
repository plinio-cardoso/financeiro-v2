<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-8 py-3 bg-[#4ECDC4] hover:bg-[#3dbbb2] border border-transparent rounded-lg font-black text-[11px] text-gray-900 uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-[#4ECDC4] focus:ring-offset-2 dark:focus:ring-offset-gray-900 disabled:opacity-50 transition-all duration-200 shadow-sm shadow-[#4ECDC4]/20 active:scale-95']) }}>
    <div wire:loading.remove {{ $attributes->has('wire:target') ? 'wire:target=' . $attributes->get('wire:target') : '' }}>
        {{ $slot }}
    </div>

    <div wire:loading {{ $attributes->has('wire:target') ? 'wire:target=' . $attributes->get('wire:target') : '' }}>
        <x-icon name="spinner" class="w-4 h-4 animate-spin" />
    </div>
</button>