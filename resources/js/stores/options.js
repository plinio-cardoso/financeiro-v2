document.addEventListener('livewire:init', () => {
    Alpine.store('options', {
        statuses: [
            { value: '', label: 'Todos os Status' },
            { value: 'pending', label: 'Pendente' },
            { value: 'paid', label: 'Pago' }
        ],

        types: [
            { value: '', label: 'Todos os Tipos' },
            { value: 'debit', label: 'Débito' },
            { value: 'credit', label: 'Crédito' }
        ],

        frequencies: [
            { value: '', label: 'Todas Frequências' },
            { value: 'weekly', label: 'Semanal' },
            { value: 'monthly', label: 'Mensal' },
            { value: 'custom', label: 'Personalizada' }
        ],

        recurringStatuses: [
            { value: '', label: 'Todos os Status' },
            { value: 'active', label: 'Ativas' },
            { value: 'inactive', label: 'Inativas' }
        ]
    });
});
