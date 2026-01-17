@props(['placeholder' => 'R$ 0,00'])

<div x-data="{
    value: @entangle($attributes->wire('model')),
    format(value) {
        if (!value) return '';
        
        // Remove everything that is not a number
        let number = value.replace(/\D/g, '');
        
        // Convert to decimal
        number = (number / 100).toFixed(2);
        
        // Split decimal and integer parts
        let parts = number.split('.');
        
        // Add thousands separator
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        
        return 'R$ ' + parts.join(',');
    },
    input(event) {
        let val = event.target.value;
        
        // Remove non-digits
        let number = val.replace(/\D/g, '');
        
        if (number === '') {
            this.value = '';
            event.target.value = '';
            return;
        }
        
        // Update the displayed value
        event.target.value = this.format(number);
        
        // Update the Livewire model with the actual float value (1234.56)
        this.value = (parseFloat(number) / 100).toFixed(2);
    },
    init() {
        if (this.value) {
            // If value comes as float (e.g. 10.50), convert to string '1050' then format
            let strVal = parseFloat(this.value).toFixed(2).replace('.', '');
            this.$refs.input.value = this.format(strVal);
        }
    }
}" class="relative">
    <input x-ref="input" type="text" @input="input($event)" placeholder="{{ $placeholder }}"
        class="block w-full mt-1 border-gray-300 text-gray-900 rounded-md shadow-sm focus:border-[#4ECDC4] focus:ring-[#4ECDC4] dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 placeholder-gray-400 dark:placeholder-gray-600" />
</div>