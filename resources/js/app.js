import './bootstrap';
import './pwa/offline';
import { connection, offlinePos, syncBanner } from './pwa/offlinePos';
import { registerSW } from 'virtual:pwa-register';
import Alpine from 'alpinejs';

registerSW({ immediate: true });

const livewireAlpine = window.Alpine;

if (livewireAlpine) {
    window.Alpine = livewireAlpine;
    livewireAlpine.data('connection', connection);
    livewireAlpine.data('offlinePos', offlinePos);
    livewireAlpine.data('syncBanner', syncBanner);
} else {
    window.Alpine = Alpine;
    Alpine.data('connection', connection);
    Alpine.data('offlinePos', offlinePos);
    Alpine.data('syncBanner', syncBanner);
    Alpine.start();
}