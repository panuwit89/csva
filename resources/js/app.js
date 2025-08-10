import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.store('modalController', {
    isOpen: false,

    open() {
        this.isOpen = true;
    },

    close() {
        this.isOpen = false;
    }
});

Alpine.start();
