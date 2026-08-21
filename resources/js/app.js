import './bootstrap';
import { confirmDialog, initConfirmHelpers } from './confirm';
import Alpine from 'alpinejs';

initConfirmHelpers();

const livewireAlpine = window.Alpine;

if (livewireAlpine) {
    window.Alpine = livewireAlpine;
    livewireAlpine.data('confirmDialog', confirmDialog);
} else {
    window.Alpine = Alpine;
    Alpine.data('confirmDialog', confirmDialog);
    Alpine.start();
}