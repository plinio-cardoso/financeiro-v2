<div>
    <form wire:submit.prevent="save" class="space-y-6">
        {{-- Amount Block --}}
        <div
            class="p-6 text-center rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm">
            <label class="block text-xs font-bold tracking-widest text-gray-400 uppercase dark:text-gray-300">
                Valor Recorrente
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
                    Detalhes da Regra
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
                </div>

                {{-- Tipo --}}
                <div>
                    <label for="type" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Tipo *
                    </label>
                    <select id="type" wire:model="type"
                        class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                        <option value="debit">Débito</option>
                        <option value="credit">Crédito</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Configuration --}}
        <div class="space-y-3">
            <div class="flex items-center gap-2 px-1">
                <div class="w-1.5 h-4 bg-[#4ECDC4] rounded-full"></div>
                <h3 class="text-xs font-black tracking-widest text-gray-400 uppercase dark:text-gray-300">
                    Configuração
                </h3>
            </div>

            <div
                class="p-4 space-y-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm">
                <div class="grid grid-cols-2 gap-4">
                    {{-- Frequency --}}
                    <div>
                        <label for="frequency" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Frequência *
                        </label>
                        <select id="frequency" wire:model="frequency"
                            class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                            <option value="weekly">Semanal</option>
                            <option value="monthly">Mensal</option>
                            <option value="custom">Personalizado (dias)</option>
                        </select>
                    </div>

                    {{-- Interval --}}
                    <div>
                        <label for="interval" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Intervalo *
                        </label>
                        <input type="number" id="interval" wire:model="interval" min="1"
                            class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                        @error('interval') <span
                        class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Start Date --}}
                <div>
                    <label for="startDate" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Data de Início *
                    </label>
                    <input type="date" id="startDate" wire:model="startDate"
                        class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                    @error('startDate') <span class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- End Date --}}
                    <div>
                        <label for="endDate" class="block text-xs text-gray-500 dark:text-gray-400 mb-2">
                            Data Final (opcional)
                        </label>
                        <input type="date" id="endDate" wire:model="endDate"
                            class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                    </div>

                    {{-- Occurrences --}}
                    <div>
                        <label for="occurrences" class="block text-xs text-gray-500 dark:text-gray-400 mb-2">
                            Nº Ocorrências (opcional)
                        </label>
                        <input type="number" id="occurrences" wire:model="occurrences" min="1"
                            class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                    </div>
                </div>
            </div>
        </div>

        {{-- Configuration Scope --}}
        <div class="space-y-3">
            <div class="flex items-center gap-2 px-1">
                <div class="w-1.5 h-4 bg-[#4ECDC4] rounded-full"></div>
                <h3 class="text-xs font-black tracking-widest text-gray-400 uppercase dark:text-gray-300">
                    Aplicação das Alterações
                </h3>
            </div>

            <div
                class="p-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm space-y-2">
                <label class="flex items-start gap-3 p-3 border rounded-xl cursor-pointer transition-colors"
                    :class="$wire.editScope === 'future' ? 'border-[#4ECDC4] bg-[#4ECDC4]/5 dark:bg-[#4ECDC4]/10' : 'border-gray-200 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-600'">
                    <input type="radio" wire:model.live="editScope" value="future"
                        class="mt-0.5 text-[#4ECDC4] focus:ring-[#4ECDC4]">
                    <div class="flex-1">
                        <div class="text-sm font-bold text-gray-900 dark:text-white">Apenas futuras
                            ({{ $this->futureTransactionsCount }} transações)</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Atualiza apenas transações futuras
                            pendentes</div>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3 border rounded-xl cursor-pointer transition-colors"
                    :class="$wire.editScope === 'all' ? 'border-[#4ECDC4] bg-[#4ECDC4]/5 dark:bg-[#4ECDC4]/10' : 'border-gray-200 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-600'">
                    <input type="radio" wire:model.live="editScope" value="all"
                        class="mt-0.5 text-[#4ECDC4] focus:ring-[#4ECDC4]">
                    <div class="flex-1">
                        <div class="text-sm font-bold text-gray-900 dark:text-white">Todas pendentes
                            ({{ $this->totalTransactionsCount }} transações)</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Atualiza todas as transações
                            pendentes (passadas e futuras)</div>
                    </div>
                </label>
            </div>
        </div>

        {{-- Active Status --}}
        <div class="p-4 border-2 rounded-xl transition-all"
            :class="$wire.active ? 'border-[#4ECDC4] bg-[#4ECDC4]/5 dark:bg-[#4ECDC4]/10' : 'border-amber-500 bg-amber-50 dark:bg-amber-900/20'">
            <div class="flex items-start gap-3">
                <input type="checkbox" id="active" wire:model.live="active"
                    class="mt-1 w-5 h-5 text-[#4ECDC4] focus:ring-[#4ECDC4] rounded cursor-pointer">
                <div class="flex-1">
                    <label for="active" class="text-sm font-bold cursor-pointer block"
                        :class="$wire.active ? 'text-gray-900 dark:text-white' : 'text-amber-800 dark:text-amber-300'">
                        Recorrência ativa
                    </label>
                    <p class="text-xs mt-1"
                        :class="$wire.active ? 'text-gray-600 dark:text-gray-400' : 'text-amber-700 dark:text-amber-400'">
                        <span x-show="$wire.active">Novas transações serão geradas automaticamente</span>
                        <span x-show="!$wire.active">⚠️ Desativada - Nenhuma transação futura será criada</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="submit" wire:loading.attr="disabled"
                class="w-full px-6 py-3 bg-[#4ECDC4] hover:bg-[#3dbdb5] text-gray-900 font-black uppercase tracking-wider rounded-xl transition-all text-sm disabled:opacity-50 shadow-sm active:scale-[0.98] flex justify-center items-center">
                <span wire:loading.remove wire:target="save">Salvar Alterações</span>
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