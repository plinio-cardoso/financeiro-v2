<div>
    {{-- Filtros --}}
    <div class="mb-6 bg-white shadow sm:rounded-lg dark:bg-gray-800">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Filtros</h3>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {{-- Search --}}
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Buscar
                    </label>
                    <input type="text" id="search" wire:model.live="search"
                           class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                           placeholder="Título ou descrição">
                </div>

                {{-- Data Início --}}
                <div>
                    <label for="startDate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Data Início
                    </label>
                    <input type="date" id="startDate" wire:model.live="startDate"
                           class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                </div>

                {{-- Data Fim --}}
                <div>
                    <label for="endDate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Data Fim
                    </label>
                    <input type="date" id="endDate" wire:model.live="endDate"
                           class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                </div>

                {{-- Status --}}
                <div>
                    <label for="filterStatus" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status
                    </label>
                    <select id="filterStatus" wire:model.live="filterStatus"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        <option value="">Todos</option>
                        <option value="pending">Pendente</option>
                        <option value="paid">Pago</option>
                    </select>
                </div>

                {{-- Tipo --}}
                <div>
                    <label for="filterType" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tipo
                    </label>
                    <select id="filterType" wire:model.live="filterType"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        <option value="">Todos</option>
                        <option value="debit">Débito</option>
                        <option value="credit">Crédito</option>
                    </select>
                </div>

                {{-- Tags --}}
                <div>
                    <label for="selectedTags" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tags
                    </label>
                    <select id="selectedTags" wire:model.live="selectedTags" multiple
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        @foreach ($this->tags as $tag)
                            <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <button type="button" wire:click="clearFilters"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                    Limpar Filtros
                </button>
            </div>
        </div>
    </div>

    {{-- Tabela de Transações --}}
    <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" wire:click="sortBy('title')"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                            Título
                            @if ($sortBy === 'title')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" wire:click="sortBy('amount')"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                            Valor
                            @if ($sortBy === 'amount')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" wire:click="sortBy('type')"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                            Tipo
                            @if ($sortBy === 'type')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" wire:click="sortBy('status')"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                            Status
                            @if ($sortBy === 'status')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" wire:click="sortBy('due_date')"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                            Vencimento
                            @if ($sortBy === 'due_date')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                            Tags
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Ações</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse ($this->transactions as $transaction)
                        <tr wire:key="transaction-{{ $transaction->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $transaction->title }}
                                </div>
                                @if ($transaction->description)
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ Str::limit($transaction->description, 50) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                    R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full
                                    {{ $transaction->type->value === 'debit' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }}">
                                    {{ $transaction->type->value === 'debit' ? 'Débito' : 'Crédito' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full
                                    {{ $transaction->status->value === 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                    {{ $transaction->status->value === 'paid' ? 'Pago' : 'Pendente' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
                                {{ $transaction->due_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($transaction->tags as $tag)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                              style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                <a href="#" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-gray-400">
                                Nenhuma transação encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700">
            {{ $this->transactions->links() }}
        </div>
    </div>

    <!-- Loading overlay -->
    <div wire:loading class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
            <p class="mt-4 text-gray-900 dark:text-gray-100">Carregando...</p>
        </div>
    </div>
</div>
