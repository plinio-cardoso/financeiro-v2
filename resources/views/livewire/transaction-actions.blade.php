<div class="flex gap-2">
    {{-- Botão Toggle Paid/Pending --}}
    <button type="button" wire:click="togglePaidStatus" wire:loading.attr="disabled"
            class="inline-flex items-center px-3 py-2 text-sm font-medium text-white rounded-md
                   {{ $transaction->status->value === 'paid' ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-600 hover:bg-green-700' }}
                   focus:outline-none focus:ring-2 focus:ring-offset-2
                   {{ $transaction->status->value === 'paid' ? 'focus:ring-yellow-500' : 'focus:ring-green-500' }}">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        {{ $transaction->status->value === 'paid' ? 'Marcar como Pendente' : 'Marcar como Pago' }}
    </button>

    {{-- Botão Excluir --}}
    <button type="button" wire:click="confirmDelete" wire:loading.attr="disabled"
            class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
        </svg>
        Excluir
    </button>

    {{-- Modal de Confirmação de Exclusão --}}
    <x-confirmation-modal wire:model.live="confirmingDelete">
        <x-slot name="title">
            Excluir Transação
        </x-slot>

        <x-slot name="content">
            Tem certeza que deseja excluir esta transação?
            <div class="mt-4">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ $transaction->title }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Valor: R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                </p>
            </div>
            <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                Esta ação não pode ser desfeita.
            </p>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingDelete')" wire:loading.attr="disabled">
                Cancelar
            </x-secondary-button>

            <x-danger-button class="ms-3" wire:click="delete" wire:loading.attr="disabled">
                Excluir
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
