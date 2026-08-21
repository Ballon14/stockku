// Hapus cache halaman HTML lama (stockku-pages) dari versi service worker sebelumnya.
// Cache ini menyimpan HTML ter-autentikasi yang membawa popup flash basi & CSRF token kadaluarsa.
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((names) => Promise.all(names.filter((name) => name === 'stockku-pages').map((name) => caches.delete(name))))
            .then(() => self.clients.claim())
    );
});
