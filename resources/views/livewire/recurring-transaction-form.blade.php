<div>
    <form wire:submit="save" class="space-y-6">
        @if ($editing)
            {{-- Edit Scope Selector --}}
            <div class="space-y-3">
                <div class="flex items-center gap-2 px-1">
                    <div class="w-1.5 h-4 bg-[#4ECDC4] rounded-full"></div>
                    <h3 class="text-xs font-black tracking-widest text-gray-400 uppercase dark:text-gray-300">
                        Escopo da Edição
                    </h3>
                </div>

                <div class="grid grid-cols-1 gap-3">
                    @foreach (['future_only' => ['title' => 'Apenas futuras', 'desc' => 'Altera apenas as próximas transações'], 'current_and_future' => ['title' => 'Atuais e futuras', 'desc' => 'Atualiza todas as transações pendentes']] as $value => $info)
                        <label
                            class="relative flex items-center gap-4 p-4 rounded-2xl cursor-pointer border-2 transition-all duration-200 group"
                            :class="$wire.editScope === '{{ $value }}'
                                    ? 'bg-[#4ECDC4]/5 border-[#4ECDC4] shadow-sm'
                                    : 'bg-white dark:bg-gray-800 border-gray-100 dark:border-gray-700 hover:border-gray-200 dark:hover:border-gray-600'">

                            <input type="radio" wire:model.live="editScope" value="{{ $value }}" class="sr-only">

                            <div class="flex-1">
                                <div class="text-sm font-black transition-colors"
                                    :class="$wire.editScope === '{{ $value }}' ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white'">
                                    {{ $info['title'] }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-500 mt-0.5">
                                    {{ $info['desc'] }}
                                </div>
                            </div>

                            <div x-show="$wire.editScope === '{{ $value }}'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-50" x-transition:enter-end="opacity-100 scale-100"
                                class="flex-shrink-0">
                                <div
                                    class="w-6 h-6 bg-[#4ECDC4] rounded-full flex items-center justify-center shadow-sm shadow-[#4ECDC4]/20">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

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


                {{-- Valor e Tipo Grid --}}
                <div class="grid grid-cols-2 gap-4">
                    {{-- Valor --}}
                    <div>
                        <label for="amount" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Valor *
                        </label>
                        <x-currency-input wire:model="amount" />
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

                {{-- Categorias --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Categorias
                    </label>
                    <x-multi-select wire:model="selectedTags" :options="[]" 
                        x-init="options = $store.tags.list; $watch('$store.tags.list', val => options = val)"
                        placeholder="Selecione categorias" />
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

        <div class="pt-4 space-y-4">
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
                <div class="p-6 rounded-2xl bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/30 animate-in fade-in zoom-in-95 duration-200 space-y-4">
                    <div class="text-center space-y-1">
                        <p class="text-sm font-black text-rose-900 dark:text-rose-300 uppercase tracking-tight">
                            Remover Recorrência
                        </p>
                        <p class="text-[11px] text-rose-700/70 dark:text-rose-400/60 font-medium">
                            Escolha o que deseja fazer com as transações vinculadas:
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        @foreach ([
                            'only_recurrence' => 'Manter todas as transações (apenas parar regra)',
                            'future' => 'Remover apenas transações futuras',
                            'all' => 'Remover TODAS as transações relacionadas'
                        ] as $val => $label)
                            <label
                                class="relative flex items-center gap-4 p-3 rounded-xl cursor-pointer border-2 transition-all duration-200 group"
                                :class="$wire.deletionOption === '{{ $val }}'
                                        ? 'bg-rose-600/5 border-rose-600 shadow-sm'
                                        : 'bg-white dark:bg-gray-800 border-gray-100 dark:border-gray-700 hover:border-gray-200 dark:hover:border-gray-600'">

                                <input type="radio" wire:model.live="deletionOption" value="{{ $val }}" class="sr-only">

                                <div class="flex-1">
                                    <div class="text-xs font-bold transition-colors"
                                        :class="$wire.deletionOption === '{{ $val }}' ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white'">
                                        {{ $label }}
                                    </div>
                                </div>

                                <div x-show="$wire.deletionOption === '{{ $val }}'"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-50" x-transition:enter-end="opacity-100 scale-100"
                                    class="flex-shrink-0">
                                    <div
                                        class="w-5 h-5 bg-rose-600 rounded-full flex items-center justify-center shadow-sm shadow-rose-600/20">
                                        <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <button type="button" wire:click="$set('confirmingDeletion', false)"
                            class="h-10 rounded-xl bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 font-bold text-[10px] uppercase tracking-wider border border-gray-200 dark:border-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="button" wire:click="deleteRecurring"
                            class="h-10 rounded-xl bg-rose-600 text-white font-bold text-[10px] uppercase tracking-wider hover:bg-rose-700 shadow-md shadow-rose-600/20">
                            Confirmar
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </form>
</div>
