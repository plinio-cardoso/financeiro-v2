@props(['placeholder' => '0,00'])

<div x-data="{
    displayValue: '',
    realValue: @entangle($attributes->wire('model')),
    
    init() {
        if (this.realValue) {
            this.displayValue = this.format(this.realValue);
        }
        
        this.$watch('realValue', value => {
            if (value === null || value === '') {
                this.displayValue = '';
                return;
            }
            // Sync display if realValue changes from outside
            let currentParsed = this.parse(this.displayValue);
            if (parseFloat(value) !== currentParsed) {
                this.displayValue = this.format(value);
            }
        });
    },

    format(val) {
        if (val === null || val === '' || isNaN(val)) return '';
        return new Intl.NumberFormat('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(val);
    },

    parse(val) {
        if (!val) return null;
        let clean = val.replace(/\D/g, '');
        if (!clean) return 0;
        return parseFloat(clean) / 100;
    },

    handleInput(e) {
        let val = e.target.value;
        // Keep only digits
        let digits = val.replace(/\D/g, '');
        
        if (!digits) {
            this.displayValue = '';
            this.realValue = null;
            return;
        }

        let numeric = parseFloat(digits) / 100;
        this.realValue = numeric;
        this.displayValue = this.format(numeric);
        
        // Ensure cursor stays at the end for consistent cents-first experience
        this.$nextTick(() => {
            e.target.setSelectionRange(e.target.value.length, e.target.value.length);
        });
    }
}" class="relative">
    <div class="relative mt-1">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
            <span class="text-gray-500 dark:text-gray-400 font-bold">R$</span>
        </div>
        <input type="text" x-model="displayValue" @input="handleInput($event)" placeholder="{{ $placeholder }}"
            class="block w-full rounded-xl border-gray-400 pl-11 text-gray-900 bg-white shadow-sm focus:border-[#4ECDC4] focus:ring-[#4ECDC4] dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 placeholder-gray-400 dark:placeholder-gray-600 font-bold" />
    </div>
</div>