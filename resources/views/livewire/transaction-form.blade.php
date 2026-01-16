<div>
    <x-form-section submit="save">
        <x-slot name="title">
            {{ $editing ? 'Editar Transação' : 'Nova Transação' }}
        </x-slot>

        <x-slot name="description">
            {{ $editing ? 'Atualize os dados da transação.' : 'Preencha os dados da nova transação.' }}
        </x-slot>

        <x-slot name="form">
            {{-- Título --}}
            <div class="col-span-6 sm:col-span-4">
                <x-label for="title" value="Título" />
                <x-input id="title" type="text" class="block w-full mt-1" wire:model="title" />
                <x-input-error for="title" class="mt-2" />
            </div>

            {{-- Descrição --}}
            <div class="col-span-6 sm:col-span-4">
                <x-label for="description" value="Descrição" />
                <textarea id="description" wire:model="description" rows="3"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"></textarea>
                <x-input-error for="description" class="mt-2" />
            </div>

            {{-- Valor --}}
            <div class="col-span-6 sm:col-span-4">
                <x-label for="amount" value="Valor (R$)" />
                <x-currency-input wire:model="amount" />
                <x-input-error for="amount" class="mt-2" />
            </div>

            {{-- Tipo --}}
            <div class="col-span-6 sm:col-span-4">
                <x-label for="type" value="Tipo" />
                <x-custom-select wire:model.live="type" :options="[
        ['value' => 'debit', 'label' => 'Débito'],
        ['value' => 'credit', 'label' => 'Crédito']
    ]" />
                <x-input-error for="type" class="mt-2" />
            </div>

            {{-- Status --}}
            <div class="col-span-6 sm:col-span-4">
                <x-label for="status" value="Status" />
                <x-custom-select wire:model.live="status" :options="[
        ['value' => 'pending', 'label' => 'Pendente'],
        ['value' => 'paid', 'label' => 'Pago']
    ]" />
                <x-input-error for="status" class="mt-2" />
            </div>

            {{-- Data de Vencimento --}}
            <div class="col-span-6 sm:col-span-4">
                <x-label for="dueDate" value="Data de Vencimento" />
                <x-input id="dueDate" type="date" class="block w-full mt-1" wire:model="dueDate" />
                <x-input-error for="dueDate" class="mt-2" />
            </div>

            {{-- Data de Pagamento (visível apenas se status = paid) --}}
            @if ($status === 'paid')
                <div class="col-span-6 sm:col-span-4">
                    <x-label for="paidAt" value="Data de Pagamento" />
                    <x-input id="paidAt" type="datetime-local" class="block w-full mt-1" wire:model="paidAt" />
                    <x-input-error for="paidAt" class="mt-2" />
                </div>
            @endif

            {{-- Tags --}}
            <div class="col-span-6 sm:col-span-4">
                <x-label for="selectedTags" value="Tags" />
                <x-multi-select wire:model="selectedTags" :options="$this->tags" placeholder="Selecione as Tags" />
                <x-input-error for="selectedTags" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="actions">
            <x-action-message class="me-3" on="saved">
                Salvo.
            </x-action-message>

            <x-button wire:loading.attr="disabled">
                {{ $editing ? 'Atualizar' : 'Criar' }}
            </x-button>
        </x-slot>
    </x-form-section>
</div>