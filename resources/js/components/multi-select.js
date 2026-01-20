document.addEventListener('livewire:init', () => {
    Alpine.data('multiSelect', (model, options, placeholder = 'Selecione') => ({
        options: options,
        selected: model,
        show: false,
        filter: '',

        init() {
            // Entangle handles sync automatically
        },

        get filteredOptions() {
            if (this.filter === '') return this.options;
            return this.options.filter(option =>
                option.name.toLowerCase().includes(this.filter.toLowerCase())
            );
        },

        toggle(id) {
            if (!Array.isArray(this.selected)) {
                this.selected = [];
            }
            if (this.selected.includes(id)) {
                this.selected = this.selected.filter(item => item != id);
            } else {
                this.selected.push(id);
            }
        },

        isSelected(id) {
            return Array.isArray(this.selected) && this.selected.includes(id);
        }
    }));
});
