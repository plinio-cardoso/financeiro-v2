<tr class="hover:bg-gray-100 dark:hover:bg-gray-700/30 transition-colors">
    {{-- Title & Description (inline editable) --}}
    <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
        <div x-data="inlineEdit({{ $transaction->id }}, 'title', @js($transaction->title), { required: true })"
            class="min-w-[120px] sm:min-w-[200px] relative">
            {{-- Desktop: Inline Editable --}}
            <div class="hidden sm:flex items-center gap-2 group cursor-pointer" x-show="!editing"
                @click="editing = true">
                @if($transaction->recurring_transaction_id)
                    <x-icon name="recurring" class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0"
                        title="Transação recorrente" />
                @endif
                <span
                    class="text-sm font-bold text-gray-900 dark:text-gray-100 group-hover:text-[#4ECDC4] transition-colors"
                    x-text="value"></span>
                <x-icon name="pencil"
                    class="w-3 h-3 text-[#4ECDC4] opacity-0 group-hover:opacity-100 transition-opacity" />
            </div>

            {{-- Mobile: Static with Date & Amount below --}}
            <div class="sm:hidden flex flex-col gap-1"
                @click="$dispatch('open-edit-modal', { transactionId: {{ $transaction->id }} })">
                <div class="flex items-center gap-2">
                    @if($transaction->recurring_transaction_id)
                        <x-icon name="recurring" class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500" />
                    @endif
                    <span class="text-sm font-black text-gray-900 dark:text-gray-100 truncate">
                        {{ $transaction->title }}
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-black {{ $transaction->getAmountColorClass() }}">
                        {{ $transaction->getFormattedAmount() }}
                    </span>
                    <span class="text-gray-300 dark:text-gray-700">|</span>
                    <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ $transaction->due_date->format('d/m/Y') }}
                    </span>
                </div>
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
    <td class="hidden sm:table-cell px-2 sm:px-6 py-4 whitespace-nowrap">
        <div x-data="inlineEdit({{ $transaction->id }}, 'amount', @js(number_format($transaction->amount, 2, ',', '.')), { type: 'amount' })"
            class="flex flex-col items-end sm:items-start relative">
            {{-- Desktop: Inline Editable --}}
            <div class="hidden sm:flex items-center gap-2 group cursor-pointer" x-show="!editing"
                @click="editing = true">
                <span
                    class="text-sm font-bold text-gray-900 dark:text-gray-100 group-hover:text-[#4ECDC4] transition-colors"
                    x-text="formatDisplay()"></span>
                <x-icon name="pencil"
                    class="w-3 h-3 text-[#4ECDC4] opacity-0 group-hover:opacity-100 transition-opacity" />
            </div>

            {{-- Mobile: Static --}}
            <div class="sm:hidden" @click="$dispatch('open-edit-modal', { transactionId: {{ $transaction->id }} })">
                <span class="text-sm font-black text-gray-900 dark:text-gray-100">
                    {{ $transaction->getFormattedAmount() }}
                </span>
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
    <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap text-center">
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
    <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap text-center">
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
    <td class="hidden sm:table-cell px-4 sm:px-6 py-4 whitespace-nowrap">
        <div x-data="inlineEdit({{ $transaction->id }}, 'due_date', @js($transaction->due_date->format('Y-m-d')), { type: 'date' })"
            class="min-w-[100px] sm:min-w-[120px] relative">
            <div x-show="!editing" @click="editing = true" class="flex items-center gap-2 group cursor-pointer">
                <span
                    class="text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-[#4ECDC4] transition-colors"
                    x-text="formatDisplay()"></span>
                <x-icon name="pencil"
                    class="w-3 h-3 text-[#4ECDC4] opacity-0 group-hover:opacity-100 transition-opacity" />
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
    <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap">
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
                class="cursor-pointer p-1 min-h-[1.5rem]">
            </div>
        @endif
    </td>

    {{-- Actions --}}
    <td class="px-2 py-4 text-sm font-medium text-right whitespace-nowrap">
        <div class="flex justify-end gap-2 sm:pr-8">
            {{-- Pay button (only for pending debits) --}}
            @if($transaction->status->value === 'pending' && $transaction->type->value === 'debit')
                <button wire:click="markAsPaid" wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed" wire:target="markAsPaid"
                    class="flex items-center gap-1.5 px-3 sm:px-4 py-2 bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 rounded-xl hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-all text-[10px] font-black uppercase tracking-widest group border border-emerald-500/10 dark:border-none shadow-sm"
                    title="Pagar">
                    <div wire:loading.remove wire:target="markAsPaid" class="flex items-center gap-1.5">
                        <x-icon name="check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                        <span class="text-emerald-800 dark:text-emerald-400">Pagar</span>
                    </div>
                    <x-icon wire:loading wire:target="markAsPaid" name="spinner"
                        class="w-4 h-4 animate-spin text-emerald-600 dark:text-emerald-400" />
                </button>
            @endif

            {{-- Always show Edit button --}}
            <button @click="$dispatch('open-edit-modal', { transactionId: {{ $transaction->id }} })"
                class="text-gray-400 hover:text-[#4ECDC4] dark:text-gray-500 dark:hover:text-[#4ECDC4] transition-colors p-1 rounded-full hover:bg-[#4ECDC410] dark:hover:bg-[#4ECDC420]"
                title="Editar transação">
                <x-icon name="pencil" />
            </button>
        </div>
    </td>
</tr>