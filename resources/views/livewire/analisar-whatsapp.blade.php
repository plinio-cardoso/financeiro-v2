<div>
    <x-form-section submit="analyze">
        <x-slot name="title">
            {{ __('Análise de Conversas de Futebol') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Cole uma conversa do WhatsApp do seu grupo de futebol para receber um resumo e sugestões de ações automatizadas.') }}
        </x-slot>

        <x-slot name="form">
            <!-- Textarea para a conversa -->
            <div class="col-span-6">
                <x-label for="conversationText" value="{{ __('Conversa do WhatsApp') }}" />

                <textarea
                    id="conversationText"
                    wire:model="conversationText"
                    rows="12"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm"
                    placeholder="Cole aqui a conversa do grupo de futebol...

Exemplo:
João: Eu vou amanhã!
Pedro: Eu também confirmo
Maria: Quem pode fazer a lista?
João: Preciso de mais 2 pessoas
Carlos: Desisto, machucado"
                ></textarea>

                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Cole a conversa do grupo de futebol. Mínimo 10 caracteres, máximo 10.000.
                </p>

                <x-input-error for="conversationText" class="mt-2" />

                @if ($conversationText)
                    <div class="mt-3">
                        <x-secondary-button type="button" wire:click="resetForm">
                            {{ __('Limpar') }}
                        </x-secondary-button>
                    </div>
                @endif
            </div>
        </x-slot>

        <x-slot name="actions">
            <x-button wire:loading.attr="disabled" wire:target="analyze">
                <span wire:loading.remove wire:target="analyze">{{ __('Analisar Conversa') }}</span>
                <span wire:loading wire:target="analyze">{{ __('Analisando...') }}</span>
            </x-button>
        </x-slot>
    </x-form-section>

    <!-- Mensagem de Erro -->
    @if ($errorMessage)
        <div class="mt-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Erro na Análise</h3>
                    <p class="mt-1 text-sm text-red-700 dark:text-red-300">{{ $errorMessage }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Resultados da Análise -->
    @if ($analysisResult)
        <x-section-border />

        <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Resultados da Análise</h3>

            <!-- Informações de Custo -->
            <div class="mb-6 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-2">Informações de Processamento</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-blue-700 dark:text-blue-300 font-medium">Tokens de Entrada</p>
                        <p class="text-blue-900 dark:text-blue-100 text-lg font-semibold">{{ number_format($analysisResult['inputTokens']) }}</p>
                    </div>
                    <div>
                        <p class="text-blue-700 dark:text-blue-300 font-medium">Tokens de Saída</p>
                        <p class="text-blue-900 dark:text-blue-100 text-lg font-semibold">{{ number_format($analysisResult['outputTokens']) }}</p>
                    </div>
                    <div>
                        <p class="text-blue-700 dark:text-blue-300 font-medium">Total de Tokens</p>
                        <p class="text-blue-900 dark:text-blue-100 text-lg font-semibold">{{ number_format($analysisResult['inputTokens'] + $analysisResult['outputTokens']) }}</p>
                    </div>
                    <div>
                        <p class="text-blue-700 dark:text-blue-300 font-medium">Custo Estimado</p>
                        <p class="text-blue-900 dark:text-blue-100 text-lg font-semibold">${{ number_format($analysisResult['estimatedCostUsd'], 4) }}</p>
                    </div>
                </div>
            </div>

            <!-- Resumo da Conversa -->
            <div class="mb-6 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-indigo-900 dark:text-indigo-100 mb-2">📝 Resumo da Conversa</h4>
                <p class="text-indigo-900 dark:text-indigo-100">{{ $analysisResult['summary'] }}</p>
            </div>

            <!-- Lista de Ações -->
            @if (!empty($analysisResult['actions']))
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">🎯 Ações Sugeridas</h4>
                    <div class="space-y-3">
                        @foreach ($analysisResult['actions'] as $action)
                            @php
                                $priorityColors = [
                                    'alta' => 'bg-red-50 dark:bg-red-900/20 border-red-300 dark:border-red-700',
                                    'media' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-300 dark:border-yellow-700',
                                    'baixa' => 'bg-green-50 dark:bg-green-900/20 border-green-300 dark:border-green-700',
                                ];
                                $priorityBadgeColors = [
                                    'alta' => 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
                                    'media' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
                                    'baixa' => 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
                                ];
                            @endphp

                            <div class="border rounded-lg p-4 {{ $priorityColors[$action['priority']] ?? $priorityColors['media'] }}">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold px-2 py-1 rounded {{ $priorityBadgeColors[$action['priority']] ?? $priorityBadgeColors['media'] }}">
                                            {{ strtoupper($action['priority']) }}
                                        </span>
                                        <span class="text-xs font-mono text-gray-600 dark:text-gray-400">
                                            {{ $action['type'] }}
                                        </span>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-900 dark:text-gray-100 font-medium">
                                    {{ $action['description'] }}
                                </p>

                                @if (!empty($action['data']))
                                    <details class="mt-2">
                                        <summary class="text-xs text-gray-600 dark:text-gray-400 cursor-pointer hover:text-gray-900 dark:hover:text-gray-200">
                                            Ver dados
                                        </summary>
                                        <pre class="mt-2 text-xs bg-white dark:bg-gray-800 p-2 rounded border border-gray-200 dark:border-gray-700 overflow-x-auto">{{ json_encode($action['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="mb-6 bg-gray-50 dark:bg-gray-800/30 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Nenhuma ação foi sugerida para esta conversa.</p>
                </div>
            @endif

            <!-- Botão para Nova Análise -->
            <div class="mt-6">
                <x-secondary-button wire:click="resetForm">
                    {{ __('Analisar Nova Conversa') }}
                </x-secondary-button>
            </div>
        </div>
    @endif

    <!-- Loading Overlay -->
    <div wire:loading wire:target="analyze" class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/80 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-sm mx-4">
            <div class="flex items-center space-x-4">
                <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">Analisando conversa...</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Isso pode levar alguns segundos</p>
                </div>
            </div>
        </div>
    </div>
</div>
