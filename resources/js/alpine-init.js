// Import stores and components
import './stores/options';
import './stores/tags';
import './components/select';
import './components/multi-select';
import money from './directives/money';

document.addEventListener('alpine:init', () => {
    money(Alpine);
});

