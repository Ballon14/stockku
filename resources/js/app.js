import './bootstrap';
import './pwa/offline';
import { connection, offlinePos, syncBanner } from './pwa/offlinePos';
import { confirmDialog, initConfirmHelpers } from './confirm';
import { registerSW } from 'virtual:pwa-register';
import Alpine from 'alpinejs';

registerSW({ immediate: true });

initConfirmHelpers();

const livewireAlpine = window.Alpine;

if (livewireAlpine) {
    window.Alpine = livewireAlpine;
    livewireAlpine.data('connection', connection);
    livewireAlpine.data('offlinePos', offlinePos);
    livewireAlpine.data('syncBanner', syncBanner);
    livewireAlpine.data('confirmDialog', confirmDialog);
} else {
    window.Alpine = Alpine;
    Alpine.data('connection', connection);
    Alpine.data('offlinePos', offlinePos);
    Alpine.data('syncBanner', syncBanner);
    Alpine.data('confirmDialog', confirmDialog);
    Alpine.start();
}