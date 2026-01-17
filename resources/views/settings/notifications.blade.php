<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Configurações de Notificação') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-form-section submit="updateSettings">
                <x-slot name="title">
                    <span class="text-gray-900 dark:text-gray-100 font-black">{{ __('Notificações por E-mail') }}</span>
                </x-slot>

                <x-slot name="description">
                    <span
                        class="text-gray-500 dark:text-gray-400 font-medium">{{ __('Configure os e-mails que receberão notificações sobre contas a vencer e vencidas.') }}</span>
                </x-slot>

                <x-slot name="form">
                    <!-- Emails -->
                    <div class="col-span-6">
                        <x-label for="emails" value="{{ __('E-mails para Notificação') }}" />

                        <div id="emails-container" class="mt-2 space-y-3">
                            @foreach(old('emails', $settings->emails ?? []) as $index => $email)
                                <div class="flex gap-2">
                                    <x-input type="email" name="emails[]" value="{{ $email }}"
                                        class="flex-1 !rounded-xl border-gray-100 dark:border-gray-700 focus:border-[#4ECDC4]/50 focus:ring-[#4ECDC4]/20" />
                                    <button type="button" onclick="removeEmail(this)"
                                        class="px-4 py-2 bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400 rounded-xl hover:bg-rose-100 dark:hover:bg-rose-500/20 transition-colors font-bold text-xs uppercase tracking-wider">
                                        Remover
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" onclick="addEmail()"
                            class="mt-4 flex items-center gap-1.5 text-xs font-black uppercase tracking-widest text-[#4ECDC4] hover:text-[#3dbdb5] transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Adicionar E-mail
                        </button>

                        <x-input-error for="emails" class="mt-2" />
                    </div>

                    <!-- Notify Due Today -->
                    <div
                        class="col-span-6 bg-gray-50/50 dark:bg-gray-800/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-700/50">
                        <label class="flex items-center cursor-pointer group">
                            <input type="checkbox" name="notify_due_today" value="1" {{ old('notify_due_today', $settings->notify_due_today ?? true) ? 'checked' : '' }}
                                class="rounded-lg border-gray-300 text-[#4ECDC4] shadow-sm focus:ring-[#4ECDC4] dark:border-gray-600 dark:bg-gray-900 transition-all w-5 h-5" />
                            <span
                                class="ms-3 text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">
                                {{ __('Notificar sobre contas que vencem hoje') }}
                            </span>
                        </label>
                    </div>

                    <!-- Notify Overdue -->
                    <div
                        class="col-span-6 bg-gray-50/50 dark:bg-gray-800/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-700/50">
                        <label class="flex items-center cursor-pointer group">
                            <input type="checkbox" name="notify_overdue" value="1" {{ old('notify_overdue', $settings->notify_overdue ?? true) ? 'checked' : '' }}
                                class="rounded-lg border-gray-300 text-[#4ECDC4] shadow-sm focus:ring-[#4ECDC4] dark:border-gray-600 dark:bg-gray-900 transition-all w-5 h-5" />
                            <span
                                class="ms-3 text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">
                                {{ __('Notificar sobre contas vencidas') }}
                            </span>
                        </label>
                    </div>
                </x-slot>

                <x-slot name="actions">
                    @if (session('success'))
                        <div class="mr-3 text-sm font-bold text-emerald-600 dark:text-emerald-400">
                            {{ session('success') }}
                        </div>
                    @endif

                    <x-button
                        class="!bg-[#4ECDC4] hover:!bg-[#3dbdb5] !text-gray-900 !rounded-xl px-8 py-3 text-sm font-black uppercase tracking-widest shadow-md shadow-[#4ECDC4]/10 active:scale-95 transition-all">
                        {{ __('Salvar Configurações') }}
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
                        <input type="email" name="emails[]" 
                            class="flex-1 !rounded-xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4ECDC4]/50 focus:ring-[#4ECDC4]/20 shadow-sm transition-all" />
                        <button type="button" onclick="removeEmail(this)" 
                            class="px-4 py-2 bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400 rounded-xl hover:bg-rose-100 dark:hover:bg-rose-500/20 transition-colors font-bold text-xs uppercase tracking-wider">
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