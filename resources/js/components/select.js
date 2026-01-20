document.addEventListener('livewire:init', () => {
    Alpine.data('customSelect', (model, options, placeholder = 'Selecione') => ({
        options: options,
        selected: model,
        show: false,

        init() {
            // Entangle handles sync automatically
        },

        get selectedLabel() {
            if (!this.selected) return placeholder;
            const option = this.options.find(o => o.value == this.selected);
            return option ? option.label : placeholder;
        },

        select(value) {
            this.selected = value;
            this.show = false;
        }
    }));
});
