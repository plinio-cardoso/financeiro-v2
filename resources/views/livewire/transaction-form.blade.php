<div>
    <form wire:submit="save" class="space-y-6">
        {{-- Amount Block --}}
        <div
            class="p-6 text-center rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm">
            <label class="block text-xs font-bold tracking-widest text-gray-400 uppercase dark:text-gray-300">
                Valor da Transação
            </label>
            <div class="mt-4 flex justify-center">
                <div class="relative w-full max-w-xs">
                    <x-currency-input wire:model="amount"
                        class="text-4xl font-black text-center border-none bg-transparent focus:ring-0 text-[#4ECDC4] dark:text-[#4ECDC4] placeholder-[#4ECDC4]/30 dark:placeholder-[#4ECDC4]/20" />
                </div>
            </div>
            @error('amount')
                <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Basic Information --}}
        <div class="space-y-3">
            <div class="flex items-center gap-2 px-1">
                <div class="w-1.5 h-4 bg-[#4ECDC4] rounded-full"></div>
                <h3 class="text-xs font-black tracking-widest text-gray-400 uppercase dark:text-gray-300">
                    Informações Básicas
                </h3>
            </div>

            <div
                class="p-4 space-y-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm">
                {{-- Título --}}
                <div>
                    <label for="title" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Título *
                    </label>
                    <input id="title" type="text" wire:model="title" placeholder="Ex: Aluguel, Salário, Supermercado..."
                        class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500" />
                    @error('title')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Descrição --}}
                <div>
                    <label for="description" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Descrição
                    </label>
                    <textarea id="description" wire:model="description" rows="2"
                        placeholder="Adicione detalhes importantes..."
                        class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"></textarea>
                    @error('description')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Classification --}}
        <div class="space-y-3">
            <div class="flex items-center gap-2 px-1">
                <div class="w-1.5 h-4 bg-[#4ECDC4] rounded-full"></div>
                <h3 class="text-xs font-black tracking-widest text-gray-400 uppercase dark:text-gray-300">
                    Classificação
                </h3>
            </div>

            <div
                class="p-4 space-y-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm">
                <div class="grid grid-cols-2 gap-4">
                    {{-- Tipo --}}
                    <div>
                        <label for="type" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Tipo *
                        </label>
                        <select id="type" wire:model.live="type"
                            class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                            <option value="debit">Débito</option>
                            <option value="credit">Crédito</option>
                        </select>
                        @error('type')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label for="status" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Status *
                        </label>
                        <select id="status" wire:model.live="status"
                            class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                            <option value="pending">Pendente</option>
                            <option value="paid">Pago</option>
                        </select>
                        @error('status')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Tags --}}
                <div>
                    <label for="selectedTags" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Categorias
                    </label>
                    <x-multi-select wire:model="selectedTags" :options="$this->tags"
                        placeholder="Selecione categorias..." />
                    @error('selectedTags')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Schedule --}}
        <div class="space-y-3">
            <div class="flex items-center gap-2 px-1">
                <div class="w-1.5 h-4 bg-[#4ECDC4] rounded-full"></div>
                <h3 class="text-xs font-black tracking-widest text-gray-400 uppercase dark:text-gray-300">
                    Datas
                </h3>
            </div>

            <div
                class="p-4 space-y-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm">
                <div class="grid grid-cols-1 gap-4" :class="{ 'sm:grid-cols-2': $wire.status === 'paid' }">
                    {{-- Data de Vencimento --}}
                    <div>
                        <label for="dueDate" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Data de Vencimento *
                        </label>
                        <input id="dueDate" type="date" wire:model="dueDate"
                            class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white" />
                        @error('dueDate')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Data de Pagamento --}}
                    @if ($status === 'paid')
                        <div>
                            <label for="paidAt" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Data de Pagamento
                            </label>
                            <input id="paidAt" type="datetime-local" wire:model="paidAt"
                                class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white" />
                            @error('paidAt')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recurrence --}}
        @if (!$editing)
            <div class="space-y-3">
                <div class="flex items-center gap-2 px-1">
                    <div class="w-1.5 h-4 bg-[#4ECDC4] rounded-full"></div>
                    <h3 class="text-xs font-black tracking-widest text-gray-400 uppercase dark:text-gray-300">
                        Recorrência
                    </h3>
                </div>

                <div
                    class="p-4 space-y-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm">
                    {{-- Toggle --}}
                    <div class="flex items-center justify-between">
                        <label for="isRecurring" class="text-sm font-bold text-gray-700 dark:text-gray-300">
                            Repetir transação
                        </label>
                        <div class="relative inline-block w-12 align-middle select-none transition duration-200 ease-in">
                            <label class="relative items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="isRecurring" class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 dark:peer-focus:ring-primary/30 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-400 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary dark:peer-checked:!bg-primary">
                                </div>
                            </label>
                        </div>
                    </div>

                    @if ($isRecurring)
                        <div class="pt-4 border-t border-gray-100 dark:border-gray-700 space-y-4 animate-fade-in-down">
                            <div class="grid grid-cols-2 gap-4">
                                {{-- Frequência --}}
                                <div>
                                    <label for="frequency"
                                        class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                        Frequência
                                    </label>
                                    <select id="frequency" wire:model.live="frequency"
                                        class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                                        <option value="weekly">Semanal</option>
                                        <option value="monthly">Mensal</option>
                                        <option value="custom">Personalizado (dias)</option>
                                    </select>
                                </div>

                                {{-- Intervalo --}}
                                <div>
                                    <label for="interval" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                        Intervalo
                                    </label>
                                    <input type="number" id="interval" wire:model="interval" min="1"
                                        class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white" />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                {{-- Data Final --}}
                                <div>
                                    <label for="endDate" class="block text-xs text-gray-500 dark:text-gray-400 mb-2">
                                        Data Final (opcional)
                                    </label>
                                    <input type="date" id="endDate" wire:model="endDate"
                                        class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white" />
                                </div>

                                {{-- Ocorrências --}}
                                <div>
                                    <label for="occurrences" class="block text-xs text-gray-500 dark:text-gray-400 mb-2">
                                        Nº Ocorrências (opcional)
                                    </label>
                                    <input type="number" id="occurrences" wire:model="occurrences" min="1"
                                        class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white" />
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Actions --}}
        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="submit" wire:loading.attr="disabled"
                class="w-full px-6 py-3 bg-[#4ECDC4] hover:bg-[#3dbdb5] text-gray-900 font-black uppercase tracking-wider rounded-xl transition-all text-sm disabled:opacity-50 shadow-sm active:scale-[0.98] flex justify-center items-center">
                <span wire:loading.remove wire:target="save">
                    {{ $editing ? 'Salvar Alterações' : 'Criar Transação' }}
                </span>
                <span wire:loading wire:target="save" class="flex items-center justify-center gap-2 whitespace-nowrap">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Salvando...
                </span>
            </button>
        </div>
    </form>
</div>

@script
<script>
    Livewire.on('validation-failed', () => {
        setTimeout(() => {
            const firstError = document.querySelector('.text-red-600');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 100);
    });
</script>
@endscript