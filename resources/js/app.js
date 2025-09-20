// resources/js/app.js
import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Global Alpine components
Alpine.data('notification', () => ({
    show: false,
    message: '',
    type: 'success',

    notify(message, type = 'success') {
        this.message = message;
        this.type = type;
        this.show = true;
        setTimeout(() => {
            this.show = false;
        }, 5000);
    }
}));

Alpine.start();
