document.addEventListener('livewire:init', () => {
    Alpine.data('customSelect', (wireProperty, options, placeholder = 'Selecione') => ({
        options: options,
        selected: null,
        show: false,

        init() {
            this.selected = this.$wire.get(wireProperty);
            this.$watch('selected', value => {
                this.$wire.set(wireProperty, value);
            });
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
