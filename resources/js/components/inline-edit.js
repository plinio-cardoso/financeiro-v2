document.addEventListener('alpine:init', () => {
    Alpine.data('inlineEdit', (id, field, initialValue, options = {}) => ({
        editing: false,
        value: initialValue,
        original: initialValue,
        saving: false,

        save() {
            if (options.required && (!this.value || (typeof this.value === 'string' && this.value.trim() === ''))) {
                this.value = this.original;
                this.editing = false;
                return;
            }
            if (this.value === this.original) {
                this.editing = false;
                return;
            }
            this.saving = true;
            this.$wire.updateField(field, this.value)
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
            if (options.type === 'amount') {
                return 'R$ ' + this.value;
            }
            if (options.type === 'date') {
                if (!this.value) return '';
                const [year, month, day] = this.value.split('-');
                return day + '/' + month + '/' + year;
            }
            return this.value;
        },

        handleInput(e) {
            if (options.type === 'amount') {
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
        }
    }));
});
