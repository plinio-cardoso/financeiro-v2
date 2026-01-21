<div>
    <form wire:submit="save" class="space-y-6">
        {{-- Basic Information --}}
        <div class="space-y-3">
            <div class="flex items-center gap-2 px-1">
                <div class="w-1.5 h-4 bg-[#4ECDC4] rounded-full"></div>
                <h3 class="text-xs font-black tracking-widest text-gray-400 uppercase dark:text-gray-300">
                    Informações da Transação
                </h3>
            </div>

            <div
                class="p-4 space-y-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm">
                {{-- Title --}}
                <div>
                    <label for="title" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Título *
                    </label>
                    <input type="text" id="title" wire:model="title" placeholder="Ex: Mercado mensal"
                        class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500">
                    @error('title')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Amount & Date Grid --}}
                <div class="grid grid-cols-2 gap-4">
                    {{-- Amount --}}
                    <div>
                        <label for="amount" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Valor *
                        </label>
                        <x-currency-input wire:model="amount" />
                        @error('amount')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Due Date --}}
                    <div>
                        <label for="dueDate" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Vencimento *
                        </label>
                        <input type="date" id="dueDate" wire:model="dueDate"
                            class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                        @error('dueDate')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>


                {{-- Type & Status Grid --}}
                <div class="grid grid-cols-2 gap-4">
                    {{-- Tipo --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Tipo *
                        </label>
                        <x-custom-select wire:model="type" :options="[]"
                            x-init="options = $store.options.types.filter(o => o.value !== '')"
                            placeholder="Selecione o tipo" />
                        @error('type')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Status *
                        </label>
                        <x-custom-select wire:model="status" :options="[]"
                            x-init="options = $store.options.statuses.filter(o => o.value !== '')"
                            placeholder="Selecione o status" />
                        @error('status')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Categorias --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Categorias
                    </label>
                    <x-multi-select wire:model="selectedTags" :options="[]"
                        x-init="options = $store.tags.list; $watch('$store.tags.list', val => options = val)"
                        placeholder="Selecione categorias" />
                </div>
            </div>
        </div>



        <div class="pt-4 space-y-3">
            <div class="flex gap-3">
                @if ($editing)
                    <button type="button" wire:click="$set('confirmingDeletion', true)"
                        class="flex-1 h-12 flex justify-center items-center rounded-xl border-2 border-rose-500/20 text-rose-600 dark:text-rose-400 font-bold text-xs uppercase tracking-widest transition-all duration-200 hover:bg-rose-50 dark:hover:bg-rose-500/10 active:scale-[0.98]">
                        Remover
                    </button>
                @endif

                <x-button type="submit" wire:loading.attr="disabled" wire:target="save"
                    class="flex-1 h-12 flex justify-center items-center rounded-xl !bg-[#4ECDC4] hover:!bg-[#3dbdb5] !text-gray-900 font-bold text-base uppercase tracking-widest transition-all duration-200 shadow-sm active:scale-[0.98] disabled:opacity-50">
                    <span wire:loading.remove wire:target="save">Salvar</span>
                    <div wire:loading wire:target="save" class="flex items-center justify-center">
                        <svg class="animate-spin h-5 w-5 text-gray-900" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </div>
                </x-button>
            </div>

            {{-- Deletion Confirmation Overlay --}}
            @if ($confirmingDeletion)
                <div
                    class="p-6 rounded-2xl bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/30 animate-in fade-in zoom-in-95 duration-200">
                    <p class="text-xs font-bold text-rose-800 dark:text-rose-300 mb-4 text-center">
                        Tem certeza que deseja remover esta transação?
                    </p>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" wire:click="$set('confirmingDeletion', false)"
                            class="h-10 rounded-xl bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 font-bold text-[10px] uppercase tracking-wider border border-gray-200 dark:border-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="button" wire:click="deleteTransaction"
                            class="h-10 rounded-xl bg-rose-600 text-white font-bold text-[10px] uppercase tracking-wider hover:bg-rose-700 shadow-md shadow-rose-600/20">
                            Sim, Remover
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </form>
</div>