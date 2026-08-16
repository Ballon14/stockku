import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        VitePWA({
            registerType: 'autoUpdate',
            injectRegister: 'auto',
            includeAssets: ['favicon.ico'],
            manifest: {
                name: 'StockKu',
                short_name: 'StockKu',
                description: 'Manajemen Toko & Kasir',
                lang: 'id',
                start_url: '/',
                scope: '/',
                display: 'standalone',
                background_color: '#0f172a',
                theme_color: '#6366f1',
                icons: [
                    { src: '/icons/icon-192.png', sizes: '192x192', type: 'image/png' },
                    { src: '/icons/icon-512.png', sizes: '512x512', type: 'image/png' },
                    { src: '/icons/icon-maskable-512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
                ],
            },
            workbox: {
                navigateFallback: null,
                globPatterns: ['**/*.{js,css,svg,png,ico,woff2}'],
                runtimeCaching: [
                    {
                        urlPattern: ({ request }) => request.mode === 'navigate',
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'stockku-pages',
                            networkTimeoutSeconds: 5,
                        },
                    },
                    {
                        urlPattern: ({ url, sameOrigin }) => sameOrigin && url.pathname.startsWith('/build/'),
                        handler: 'StaleWhileRevalidate',
                        options: {
                            cacheName: 'stockku-assets',
                        },
                    },
                    {
                        urlPattern: ({ url, sameOrigin }) => sameOrigin && url.pathname.startsWith('/icons/'),
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'stockku-icons',
                        },
                    },
                ],
            },
        }),
    ],
});
