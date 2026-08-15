import { db } from './db';

let catalog = [];
let listeners = [];
let syncing = false;

function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

function notify() {
    window.dispatchEvent(new CustomEvent('offline-queue-change'));
    listeners.forEach((listener) => listener());
}

export function subscribe(listener) {
    listeners.push(listener);
}

export function isOnline() {
    return navigator.onLine;
}

export function getCatalog() {
    return catalog;
}

export async function refreshCatalog() {
    try {
        const response = await fetch('/offline/catalog', {
            headers: { 'Accept': 'application/json' },
        });

        if (!response.ok) {
            throw new Error('Gagal memuat katalog produk.');
        }

        const products = await response.json();
        catalog = products;
        await db.clearCatalog();
        await db.putCatalog(products);
    } catch (error) {
        catalog = await db.getCatalog();
    }

    return catalog;
}

export async function enqueueTransaction({ items, diskon, bayar, catatan }) {
    const offlineId = crypto.randomUUID();

    const transaction = {
        offline_id: offlineId,
        items,
        diskon,
        bayar,
        catatan,
        status: 'pending',
        created_at: new Date().toISOString(),
    };

    await db.enqueue(transaction);
    notify();

    return transaction;
}

export async function getQueue() {
    const queue = await db.getQueue();

    return queue.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
}

export async function processQueue() {
    if (syncing || !isOnline()) {
        return { synced: [], failed: [] };
    }

    syncing = true;

    try {
        const queue = await getQueue();
        const pending = queue.filter((item) => item.status !== 'failed');

        if (pending.length === 0) {
            return { synced: [], failed: [] };
        }

        const response = await fetch('/offline/sync', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({ transactions: pending }),
        });

        if (!response.ok) {
            throw new Error('Sinkronisasi gagal, coba lagi nanti.');
        }

        const { results } = await response.json();
        const synced = [];
        const failed = [];

        for (const result of results) {
            if (result.status === 'success') {
                await db.removeQueued(result.offline_id);
                synced.push(result);
            } else {
                await db.markFailed(result.offline_id, result.message);
                failed.push(result);
            }
        }

        return { synced, failed };
    } catch (error) {
        return { synced: [], failed: [], error: error.message };
    } finally {
        syncing = false;
        notify();
    }
}

window.addEventListener('online', () => {
    notify();
    refreshCatalog();
    processQueue();
});

window.addEventListener('offline', () => {
    notify();
});

window.OfflinePOS = {
    isOnline,
    getCatalog,
    refreshCatalog,
    enqueueTransaction,
    getQueue,
    processQueue,
    subscribe,
};