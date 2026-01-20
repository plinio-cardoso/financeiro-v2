export default function (Alpine) {
    Alpine.directive('money', (el) => {
        const formatMoney = (value) => {
            if (!value) return '';

            // Remove everything that is not a digit
            let number = value.replace(/\D/g, '');

            if (number === '') return '';

            // Divide by 100 to get decimal places
            number = parseFloat(number) / 100;

            return number.toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });
        };

        const handleInput = (e) => {
            const input = e.target;
            const position = input.selectionStart;
            const originalLength = input.value.length;

            // Format existing value
            const formatted = formatMoney(input.value);
            input.value = formatted;

            // Attempt to restore cursor position (approximate)
            // Ideally we'd calculate exact offset, but simple masks often jump to end.
            // For BRL currency (R$ at start), keeping cursor at end is often acceptable/default for simple implementations.
        };

        el.addEventListener('input', handleInput);

        // Initial format if value exists
        if (el.value) {
            // If value comes from backend as 1500.00 (float), we need to handle that initial load differently than 'input' event
            // Convert dot to nothing, ensure it's treated as cents? 
            // 1500.00 -> 150000 cents -> R$ 1.500,00
            let val = el.value;
            if (val.includes('.')) {
                // Assuming standard database float format: 123.456
                // Fix to 2 decimals first to avoid float precision issues
                val = parseFloat(val).toFixed(2);
                // Remove dot
                val = val.replace('.', '');
            }
            el.value = formatMoney(val);
        }
    });

    // Also register a magic or store if needed, but directive is sufficient for x-money
}
