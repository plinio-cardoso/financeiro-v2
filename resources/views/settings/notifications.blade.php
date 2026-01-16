<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Configurações de Notificação') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-form-section submit="updateSettings">
                <x-slot name="title">
                    {{ __('Notificações por E-mail') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('Configure os e-mails que receberão notificações sobre contas a vencer e vencidas.') }}
                </x-slot>

                <x-slot name="form">
                    <!-- Emails -->
                    <div class="col-span-6">
                        <x-label for="emails" value="{{ __('E-mails para Notificação') }}" />

                        <div id="emails-container" class="mt-2 space-y-2">
                            @foreach(old('emails', $settings->emails ?? []) as $index => $email)
                                <div class="flex gap-2">
                                    <x-input type="email" name="emails[]" value="{{ $email }}" class="flex-1" />
                                    <button type="button" onclick="removeEmail(this)" class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                                        Remover
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" onclick="addEmail()" class="mt-2 text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            + Adicionar E-mail
                        </button>

                        <x-input-error for="emails" class="mt-2" />
                    </div>

                    <!-- Notify Due Today -->
                    <div class="col-span-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="notify_due_today" value="1" {{ old('notify_due_today', $settings->notify_due_today ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:focus:ring-indigo-600" />
                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Notificar sobre contas que vencem hoje') }}
                            </span>
                        </label>
                    </div>

                    <!-- Notify Overdue -->
                    <div class="col-span-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="notify_overdue" value="1" {{ old('notify_overdue', $settings->notify_overdue ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:focus:ring-indigo-600" />
                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Notificar sobre contas vencidas') }}
                            </span>
                        </label>
                    </div>
                </x-slot>

                <x-slot name="actions">
                    <x-action-message class="me-3" on="saved">
                        {{ __('Salvo.') }}
                    </x-action-message>

                    <x-button>
                        {{ __('Salvar') }}
                    </x-button>
                </x-slot>
            </x-form-section>
        </div>
    </div>

    @push('scripts')
    <script>
        function addEmail() {
            const container = document.getElementById('emails-container');
            const div = document.createElement('div');
            div.className = 'flex gap-2';
            div.innerHTML = `
                <input type="email" name="emails[]" class="flex-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" />
                <button type="button" onclick="removeEmail(this)" class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                    Remover
                </button>
            `;
            container.appendChild(div);
        }

        function removeEmail(button) {
            button.parentElement.remove();
        }
    </script>
    @endpush
</x-app-layout>
