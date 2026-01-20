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

            <div class="p-4 space-y-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm">
                {{-- Descrição --}}
                <div>
                    <label for="description" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Descrição
                    </label>
                    <textarea id="description" wire:model="description" rows="3"
                        placeholder="Adicione detalhes importantes sobre esta transação..."
                        class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"></textarea>
                    @error('description')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Type & Status Grid --}}
                <div class="grid grid-cols-2 gap-4">
                    {{-- Tipo --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Tipo *
                        </label>
                        <x-custom-select wire:model="type" :options="[
                            ['value' => 'debit', 'label' => 'Débito'],
                            ['value' => 'credit', 'label' => 'Crédito']
                        ]" placeholder="Selecione o tipo" />
                        @error('type')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Status *
                        </label>
                        <x-custom-select wire:model="status" :options="[
                            ['value' => 'pending', 'label' => 'Pendente'],
                            ['value' => 'paid', 'label' => 'Pago']
                        ]" placeholder="Selecione o status" />
                        @error('status')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Tags --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Categorias
                    </label>
                    <x-multi-select wire:model="selectedTags" :options="$this->tags" placeholder="Selecione categorias" />
                    @error('selectedTags')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Submit Button --}}
        <div class="flex justify-end gap-3 pt-4">
            <x-secondary-button type="button" @click="$dispatch('close-modal')">
                Cancelar
            </x-secondary-button>
            <x-button type="submit" wire:loading.attr="disabled" wire:target="save"
                class="!bg-[#4ECDC4] hover:!bg-[#3dbdb5] !text-gray-900">
                <span wire:loading.remove wire:target="save">Salvar</span>
                <span wire:loading wire:target="save">Salvando...</span>
            </x-button>
        </div>
    </form>
</div>
