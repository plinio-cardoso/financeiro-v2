<tr class="hover:bg-gray-100 dark:hover:bg-gray-700/30 transition-colors">
    {{-- Title & Description (inline editable) --}}
    <td class="px-6 py-4 whitespace-nowrap">
        <div x-data="{
            editing: false,
            value: @js($transaction->title),
            original: @js($transaction->title),
            saving: false,
            save() {
                if (!this.value || this.value.trim() === '') {
                    this.value = this.original;
                    this.editing = false;
                    return;
                }
                if (this.value === this.original) {
                    this.editing = false;
                    return;
                }
                this.saving = true;
                $wire.updateField('title', this.value)
                    .then(() => {
                        this.original = this.value;
                        this.editing = false;
                        this.saving = false;
                    })
                    .catch(() => {
                        this.value = this.original;
                        this.editing = false;
                        this.saving = false;
                    });
            }
        }" class="min-w-[200px]">
            <div x-show="!editing" @click="editing = true" class="flex items-center gap-2 group cursor-pointer">
                @if($transaction->recurring_transaction_id)
                    <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" title="Transação recorrente">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                @endif
                <span
                    class="text-sm font-bold text-gray-900 dark:text-gray-100 group-hover:text-[#4ECDC4] transition-colors"
                    x-text="value"></span>
                <svg class="w-3 h-3 text-[#4ECDC4] opacity-0 group-hover:opacity-100 transition-opacity" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
            </div>
            <input x-show="editing" x-cloak x-ref="input" x-model="value" @focusout="save()" @keydown.enter="save()"
                @keydown.escape="editing = false; value = original"
                x-effect="if(editing) { $nextTick(() => $refs.input.focus()); }" type="text"
                class="w-full px-2 py-1 text-sm font-bold bg-white dark:bg-gray-700 border-b-2 border-[#4ECDC4] border-t-0 border-x-0 focus:ring-0 focus:border-[#4ECDC4] text-gray-900 dark:text-gray-100 p-0">

            {{-- Saving Spinner --}}
            <div x-show="saving" x-cloak class="absolute right-0 top-1/2 -translate-y-1/2">
                <div class="w-3.5 h-3.5 border-2 border-[#4ECDC4] border-t-transparent rounded-full animate-spin"></div>
            </div>
        </div>


    </td>

    {{-- Amount (inline editable) --}}
    <td class="px-6 py-4 whitespace-nowrap">
        <div x-data="{
            editing: false,
            value: @js(number_format($transaction->amount, 2, ',', '.')),
            original: @js(number_format($transaction->amount, 2, ',', '.')),
            
            init() {
                this.$watch('editing', val => {
                    if (val) {
                        this.value = @js(number_format($transaction->amount, 2, ',', '.'));
                        this.original = this.value;
                    }
                });
            },

            saving: false,
            save() {
                if (this.value === this.original) {
                    this.editing = false;
                    return;
                }
                this.saving = true;
                $wire.updateField('amount', this.value)
                    .then(() => {
                        this.original = this.value;
                        this.editing = false;
                        this.saving = false;
                    })
                    .catch(() => {
                        this.value = this.original;
                        this.editing = false;
                        this.saving = false;
                    });
            },
            formatDisplay() {
                return 'R$ ' + this.value;
            },
            handleInput(e) {
                let digits = e.target.value.replace(/\D/g, '');
                if (!digits) {
                    this.value = '0,00';
                    return;
                }
                let numeric = parseFloat(digits) / 100;
                this.value = new Intl.NumberFormat('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(numeric);
                
                this.$nextTick(() => {
                    e.target.setSelectionRange(e.target.value.length, e.target.value.length);
                });
            }
        }" class="flex flex-col items-start">
            <div x-show="!editing" @click="editing = true" class="flex items-center gap-2 group cursor-pointer">
                <span
                    class="text-sm font-bold text-gray-900 dark:text-gray-100 group-hover:text-[#4ECDC4] transition-colors"
                    x-text="formatDisplay()"></span>
                <svg class="w-3 h-3 text-[#4ECDC4] opacity-0 group-hover:opacity-100 transition-opacity" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
            </div>
            <div x-show="editing" x-cloak class="flex items-center">
                <span class="text-sm mr-1 font-bold text-gray-900 dark:text-gray-100">R$</span>
                <input x-ref="input" x-model="value" @input="handleInput($event)" @focusout="save()"
                    @keydown.enter="save()" @keydown.escape="editing = false; value = original"
                    x-effect="if(editing) { $nextTick(() => $refs.input.focus()); }" type="text"
                    class="w-32 px-1 py-0 text-sm font-bold bg-white dark:bg-gray-700 border-b border-[#4ECDC4] border-t-0 border-x-0 focus:ring-0 focus:border-[#4ECDC4] text-gray-900 dark:text-gray-100 p-0 text-left">

                {{-- Saving Spinner --}}
                <div x-show="saving" x-cloak class="ml-2">
                    <div class="w-3.5 h-3.5 border-2 border-[#4ECDC4] border-t-transparent rounded-full animate-spin">
                    </div>
                </div>
            </div>
        </div>
    </td>

    {{-- Type (badge only - click opens modal) --}}
    <td class="px-6 py-4 whitespace-nowrap">
        <div @click="$dispatch('open-edit-modal', { transactionId: {{ $transaction->id }} })"
            class="cursor-pointer inline-block">
            <span @class([
                'inline-flex px-2 text-xs font-semibold leading-5 rounded-full transition-all hover:ring-2 hover:ring-[#4ECDC4]/30',
                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' => $transaction->type->value === 'debit',
                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' => $transaction->type->value === 'credit',
            ])>
                {{ $transaction->type->value === 'debit' ? 'Débito' : 'Crédito' }}
            </span>
        </div>
    </td>

    {{-- Status (badge only - click opens modal) --}}
    <td class="px-6 py-4 whitespace-nowrap">
        <div @click="$dispatch('open-edit-modal', { transactionId: {{ $transaction->id }} })"
            class="cursor-pointer inline-block">
            <span @class([
                'inline-flex px-2 text-xs font-semibold leading-5 rounded-full transition-all hover:ring-2 hover:ring-[#4ECDC4]/30',
                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' => $transaction->status->value === 'paid',
                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' => $transaction->status->value === 'pending',
            ])>
                {{ $transaction->status->value === 'paid' ? 'Pago' : 'Pendente' }}
            </span>
        </div>
    </td>

    {{-- Due Date (inline editable) --}}
    <td class="px-6 py-4 whitespace-nowrap">
        <div x-data="{
            editing: false,
            value: @js($transaction->due_date->format('Y-m-d')),
            original: @js($transaction->due_date->format('Y-m-d')),
            saving: false,
            save() {
                if (!this.value) {
                    this.value = this.original;
                    this.editing = false;
                    return;
                }
                if (this.value === this.original) {
                    this.editing = false;
                    return;
                }
                this.saving = true;
                $wire.updateField('due_date', this.value)
                    .then(() => {
                        this.original = this.value;
                        this.editing = false;
                        this.saving = false;
                    })
                    .catch(() => {
                        this.value = this.original;
                        this.editing = false;
                        this.saving = false;
                    });
            },
            formatDisplay() {
                if (!this.value) return '';
                const [year, month, day] = this.value.split('-');
                return day + '/' + month + '/' + year;
            }
        }">
            <div x-show="!editing" @click="editing = true" class="flex items-center gap-2 group cursor-pointer">
                <span
                    class="text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-[#4ECDC4] transition-colors"
                    x-text="formatDisplay()"></span>
                <svg class="w-3 h-3 text-[#4ECDC4] opacity-0 group-hover:opacity-100 transition-opacity" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
            </div>
            <input x-show="editing" x-cloak x-ref="input" x-model="value" @focusout="save()" @change="save()"
                @keydown.enter="save()" @keydown.escape="editing = false; value = original"
                x-effect="if(editing) { $nextTick(() => $refs.input.focus()); }" type="date"
                class="px-2 py-0 text-sm bg-white dark:bg-gray-700 border-b border-[#4ECDC4] border-t-0 border-x-0 focus:ring-0 focus:border-[#4ECDC4] text-gray-900 dark:text-gray-100 p-0">

            {{-- Saving Spinner --}}
            <div x-show="saving" x-cloak class="absolute right-0 top-1/2 -translate-y-1/2">
                <div class="w-3.5 h-3.5 border-2 border-[#4ECDC4] border-t-transparent rounded-full animate-spin"></div>
            </div>
        </div>
    </td>

    {{-- Tags (badge list - click opens modal) --}}
    <td class="px-6 py-4 whitespace-nowrap">
        @if($transaction->tags->isNotEmpty())
            <div @click="$dispatch('open-edit-modal', { transactionId: {{ $transaction->id }} })"
                class="flex flex-wrap gap-1 cursor-pointer p-1">
                @foreach($transaction->tags as $tag)
                    <span
                        class="inline-flex items-center px-2 py-0.5 text-[10px] font-black rounded-md uppercase tracking-wider transition-opacity hover:opacity-80"
                        style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}; border: 1px solid {{ $tag->color }}30">
                        {{ $tag->name }}
                    </span>
                @endforeach
            </div>
        @else
            <div @click="$dispatch('open-edit-modal', { transactionId: {{ $transaction->id }} })"
                class="cursor-pointer p-1">
                <span class="text-xs text-gray-400 dark:text-gray-500 italic hover:text-[#4ECDC4] transition-colors">Sem
                    tags</span>
            </div>
        @endif
    </td>

    {{-- Actions --}}
    <td class="px-2 py-4 text-sm font-medium text-right whitespace-nowrap">
        <div class="flex justify-end gap-2 pr-8">
            {{-- Pay button (only for pending debits) - ANTES do botão editar --}}
            @if($transaction->status->value === 'pending' && $transaction->type->value === 'debit')
                <button wire:click="markAsPaid" wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed" wire:target="markAsPaid"
                    class="flex items-center gap-1.5 px-4 py-2 bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 rounded-xl hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-all text-[10px] font-black uppercase tracking-widest group border border-emerald-500/10 dark:border-none shadow-sm"
                    title="Pagar">
                    <div wire:loading.remove wire:target="markAsPaid" class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-emerald-800 dark:text-emerald-400">Pagar</span>
                    </div>
                    <svg wire:loading wire:target="markAsPaid"
                        class="w-4 h-4 animate-spin text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </button>
            @endif

            {{-- Always show Edit button --}}
            <button @click="$dispatch('open-edit-modal', { transactionId: {{ $transaction->id }} })"
                class="text-gray-400 hover:text-[#4ECDC4] dark:text-gray-500 dark:hover:text-[#4ECDC4] transition-colors p-1 rounded-full hover:bg-[#4ECDC410] dark:hover:bg-[#4ECDC420]"
                title="Editar transação">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
            </button>
        </div>
    </td>
</tr>