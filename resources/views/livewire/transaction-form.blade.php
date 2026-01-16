<div>
    <form wire:submit="save" class="space-y-8">
        {{-- Amount Block --}}
        <div
            class="p-8 text-center rounded-3xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm">
            <label class="block text-xs font-bold tracking-widest text-gray-400 uppercase dark:text-gray-500">
                Valor da Transação
            </label>
            <div class="mt-4 flex justify-center">
                <div class="relative w-full max-w-xs">
                    <x-currency-input wire:model="amount"
                        class="text-4xl font-black text-center border-none bg-transparent focus:ring-0 text-indigo-600 dark:text-indigo-400 placeholder-indigo-300 dark:placeholder-indigo-900/50" />
                </div>
            </div>
            <x-input-error for="amount" class="mt-2" />
        </div>

        {{-- Basic Information --}}
        <div class="space-y-4">
            <div class="flex items-center gap-2 px-1">
                <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div>
                <h3 class="text-xs font-black tracking-widest text-gray-400 uppercase dark:text-gray-500">
                    Informações Básicas
                </h3>
            </div>

            <div
                class="p-6 space-y-6 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm">
                {{-- Título --}}
                <div class="space-y-1.5">
                    <x-label for="title" value="Título" class="text-xs font-bold text-gray-700 dark:text-gray-400" />
                    <x-input id="title" type="text"
                        class="block w-full border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 focus:ring-indigo-500/20 rounded-xl"
                        placeholder="Ex: Aluguel, Salário, Supermercado..." wire:model="title" />
                    <x-input-error for="title" />
                </div>

                {{-- Descrição --}}
                <div class="space-y-1.5">
                    <x-label for="description" value="Descrição (Opcional)"
                        class="text-xs font-bold text-gray-700 dark:text-gray-400" />
                    <textarea id="description" wire:model="description" rows="3"
                        class="block w-full border-gray-200 text-gray-900 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 focus:border-indigo-500/50 focus:ring-indigo-500/20 rounded-xl shadow-sm dark:text-gray-300"
                        placeholder="Adicione detalhes importantes..."></textarea>
                    <x-input-error for="description" />
                </div>
            </div>
        </div>

        {{-- Classification --}}
        <div class="space-y-4">
            <div class="flex items-center gap-2 px-1">
                <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div>
                <h3 class="text-xs font-black tracking-widest text-gray-400 uppercase dark:text-gray-500">
                    Classificação
                </h3>
            </div>

            <div
                class="p-6 space-y-6 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm">
                <div class="grid grid-cols-2 gap-6">
                    {{-- Tipo --}}
                    <div class="space-y-1.5">
                        <x-label for="type" value="Tipo" class="text-xs font-bold text-gray-500 dark:text-gray-400" />
                        <x-custom-select wire:model.live="type" :options="[
        ['value' => 'debit', 'label' => 'Débito'],
        ['value' => 'credit', 'label' => 'Crédito']
    ]" />
                        <x-input-error for="type" />
                    </div>

                    {{-- Status --}}
                    <div class="space-y-1.5">
                        <x-label for="status" value="Status"
                            class="text-xs font-bold text-gray-500 dark:text-gray-400" />
                        <x-custom-select wire:model.live="status" :options="[
        ['value' => 'pending', 'label' => 'Pendente'],
        ['value' => 'paid', 'label' => 'Pago']
    ]" />
                        <x-input-error for="status" />
                    </div>
                </div>

                {{-- Tags --}}
                <div class="space-y-1.5">
                    <x-label for="selectedTags" value="Tags"
                        class="text-xs font-bold text-gray-500 dark:text-gray-400" />
                    <x-multi-select wire:model="selectedTags" :options="$this->tags" placeholder="Selecione..." />
                    <x-input-error for="selectedTags" />
                </div>
            </div>
        </div>

        {{-- Schedule --}}
        <div class="space-y-4">
            <div class="flex items-center gap-2 px-1">
                <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div>
                <h3 class="text-xs font-black tracking-widest text-gray-400 uppercase dark:text-gray-500">
                    Agendamento
                </h3>
            </div>

            <div
                class="p-6 space-y-6 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm">
                <div class="grid grid-cols-1 gap-6 {{ $status === 'paid' ? 'sm:grid-cols-2' : '' }}">
                    {{-- Data de Vencimento --}}
                    <div class="space-y-1.5">
                        <x-label for="dueDate" value="Vencimento"
                            class="text-xs font-bold text-gray-500 dark:text-gray-400" />
                        <x-input id="dueDate" type="date"
                            class="block w-full border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 focus:ring-indigo-500/20 rounded-xl"
                            wire:model="dueDate" />
                        <x-input-error for="dueDate" />
                    </div>

                    {{-- Data de Pagamento --}}
                    @if ($status === 'paid')
                        <div class="space-y-1.5">
                            <x-label for="paidAt" value="Pagamento"
                                class="text-xs font-bold text-gray-500 dark:text-gray-400" />
                            <x-input id="paidAt" type="datetime-local"
                                class="block w-full border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 focus:ring-indigo-500/20 rounded-xl"
                                wire:model="paidAt" />
                            <x-input-error for="paidAt" />
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="pt-6">
            <x-button
                class="w-full justify-center py-4 text-base font-black uppercase tracking-widest shadow-md shadow-indigo-500/10 active:scale-95 transition-transform">
                {{ $editing ? 'Salvar Alterações' : 'Criar Transação' }}
            </x-button>
        </div>
    </form>
</div>