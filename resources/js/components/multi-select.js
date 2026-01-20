document.addEventListener('livewire:init', () => {
    Alpine.data('multiSelect', (wireProperty, options, placeholder = 'Selecione') => ({
        options: options,
        selected: [],
        show: false,
        filter: '',

        init() {
            this.selected = this.$wire.get(wireProperty) || [];
            this.$watch('selected', value => {
                this.$wire.set(wireProperty, value);
            });
        },

        get filteredOptions() {
            if (this.filter === '') return this.options;
            return this.options.filter(option =>
                option.name.toLowerCase().includes(this.filter.toLowerCase())
            );
        },

        toggle(id) {
            if (this.selected.includes(id)) {
                this.selected = this.selected.filter(item => item != id);
            } else {
                this.selected.push(id);
            }
        },

        isSelected(id) {
            return this.selected.includes(id);
        }
    }));
});
