<div>
    <form wire:submit="save" class="space-y-6">
        {{-- Edit Scope Selector --}}
        <div class="p-4 rounded-xl bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800/30">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-500 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-bold text-blue-700 dark:text-blue-200 mb-3">
                        Escolha o escopo da edição:
                    </p>
                    <div class="space-y-2">
                        <label
                            class="flex items-start gap-3 p-3 rounded-lg cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                            :class="{ 'bg-blue-100/70 dark:bg-blue-900/30': $wire.editScope === 'future_only' }">
                            <input type="radio" wire:model.live="editScope" value="future_only"
                                class="mt-1 w-3.5 h-3.5 text-[#4ECDC4] focus:ring-1 focus:ring-[#4ECDC4] border-gray-300 dark:border-gray-600">
                            <div class="flex-1">
                                <div class="text-sm font-bold text-gray-900 dark:text-white">
                                    Alterar apenas futuras transações
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                    As transações já geradas não serão alteradas, apenas as próximas
                                </div>
                            </div>
                        </label>

                        <label
                            class="flex items-start gap-3 p-3 rounded-lg cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                            :class="{ 'bg-blue-100/70 dark:bg-blue-900/30': $wire.editScope === 'current_and_future' }">
                            <input type="radio" wire:model.live="editScope" value="current_and_future"
                                class="mt-1 w-3.5 h-3.5 text-[#4ECDC4] focus:ring-1 focus:ring-[#4ECDC4] border-gray-300 dark:border-gray-600">
                            <div class="flex-1">
                                <div class="text-sm font-bold text-gray-900 dark:text-white">
                                    Alterar transações atuais e futuras
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                    Atualiza todas as transações pendentes (atuais e futuras)
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
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
                    <input type="text" id="title" wire:model="title" placeholder="Ex: Aluguel mensal"
                        class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500">
                    @error('title')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Descrição --}}
                <div>
                    <label for="description" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Descrição
                    </label>
                    <textarea id="description" wire:model="description" rows="3"
                        placeholder="Adicione detalhes sobre esta recorrência..."
                        class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"></textarea>
                    @error('description')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Valor e Tipo Grid --}}
                <div class="grid grid-cols-2 gap-4">
                    {{-- Valor --}}
                    <div>
                        <label for="amount" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Valor *
                        </label>
                        <input type="text" id="amount" wire:model="amount" x-money placeholder="R$ 0,00"
                            class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500">
                        @error('amount')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

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
                </div>

                {{-- Tags --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Tags
                    </label>
                    <x-multi-select wire:model="selectedTags" :options="[]" x-init="options = $store.tags.list"
                        placeholder="Selecione tags" />
                    @error('selectedTags')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Recurrence Configuration --}}
        <div class="space-y-3">
            <div class="flex items-center gap-2 px-1">
                <div class="w-1.5 h-4 bg-[#4ECDC4] rounded-full"></div>
                <h3 class="text-xs font-black tracking-widest text-gray-400 uppercase dark:text-gray-300">
                    Configuração de Recorrência
                </h3>
            </div>

            <div
                class="p-4 space-y-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm">
                {{-- Frequência & Intervalo --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Frequência *
                        </label>
                        <x-custom-select wire:model="frequency" :options="[]"
                            x-init="options = $store.options.frequencies.filter(o => o.value !== '')"
                            placeholder="Selecione a frequência" />
                        @error('frequency')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Repetir a cada *
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model="interval" min="1"
                                class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                            <span class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                @if ($frequency === 'weekly')
                                    semana(s)
                                @elseif($frequency === 'monthly')
                                    mês(es)
                                @else
                                    dia(s)
                                @endif
                            </span>
                        </div>
                        @error('interval')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Início & Término --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Data de Início *
                        </label>
                        <input type="date" wire:model="startDate"
                            class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                        @error('startDate')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Data de Término
                        </label>
                        <input type="date" wire:model="endDate"
                            class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                        @error('endDate')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Ocorrências --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Ou após X ocorrências
                    </label>
                    <input type="number" wire:model="occurrences" min="1" placeholder="Ex: 12"
                        class="w-full px-4 py-2 border border-gray-400 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-[#4ECDC4] focus:border-[#4ECDC4] bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500">
                    @error('occurrences')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="pt-4">
            <x-button type="submit" wire:loading.attr="disabled" wire:target="save"
                class="w-full h-12 flex justify-center items-center rounded-xl !bg-[#4ECDC4] hover:!bg-[#3dbdb5] !text-gray-900 font-bold text-base uppercase tracking-widest transition-all duration-200 shadow-sm active:scale-[0.98] disabled:opacity-50">
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
    </form>
</div>