import './bootstrap';
import Mask from '@alpinejs/mask';

document.addEventListener('livewire:init', () => {
    window.Alpine.plugin(Mask);
});

import './alpine-init';
