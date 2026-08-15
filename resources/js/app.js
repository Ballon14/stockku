import './bootstrap';
import './pwa/offline';
import { connection, offlinePos, syncBanner } from './pwa/offlinePos';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('connection', connection);
Alpine.data('offlinePos', offlinePos);
Alpine.data('syncBanner', syncBanner);

Alpine.start();
