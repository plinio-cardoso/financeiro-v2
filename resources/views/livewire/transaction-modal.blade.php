<div x-data="{ 
    isOpen: false,
    close() {
        this.isOpen = false;
        $wire.closeModal();
    }
}" @open-modal.window="isOpen = true" @close-modal.window="isOpen = false" @keydown.escape.window="close()"
    class="relative z-50">

    <div x-show="isOpen" x-transition:enter="transition ease-in-out duration-500" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in-out duration-500"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 transition-opacity" @click="close()"></div>

    <div x-show="isOpen" class="fixed inset-y-0 right-0 flex max-w-full pl-10 pointer-events-none"
        style="display: none;">

        <div x-show="isOpen" x-transition:enter="transform transition ease-in-out duration-500"
            x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in-out duration-500" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full" class="w-screen max-w-md pointer-events-auto shadow-2xl">

            <div class="flex flex-col h-full bg-white dark:bg-gray-900">
                {{-- Header --}}
                <div
                    class="px-6 py-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between rounded-none">
                    <h2 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tighter">
                        @if($mode === 'transaction')
                            {{ $transactionId ? 'Editar Transação' : 'Nova Transação' }}
                        @else
                            {{ $recurringId ? 'Editar Recorrência' : 'Nova Recorrência' }}
                        @endif
                    </h2>
                    <button type="button" @click="close()"
                        class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Content Area --}}
                <div class="flex-1 relative overflow-hidden">
                    {{-- Loader --}}
                    <div wire:loading wire:target="transactionId, recurringId"
                        class="absolute inset-0 z-[60] bg-white/60 dark:bg-gray-900/60 backdrop-blur-sm flex items-center justify-center">
                        <div class="flex flex-col items-center gap-4">
                            <div
                                class="w-10 h-10 border-4 border-[#4ECDC4] border-t-transparent rounded-full animate-spin">
                            </div>
                            <span
                                class="text-[10px] font-black uppercase tracking-widest text-[#4ECDC4]">Carregando...</span>
                        </div>
                    </div>

                    {{-- Form Component --}}
                    <div class="w-full h-full px-6 py-8 overflow-y-auto custom-scrollbar">
                        @if($mode === 'transaction')
                            <livewire:transaction-form :transaction-id="$transactionId" :key="'tx-form-' . $transactionId" />
                        @else
                            <livewire:recurring-transaction-form :recurring-id="$recurringId"
                                :key="'rec-form-' . $recurringId" />
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>