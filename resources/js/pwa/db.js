const DB_NAME = 'stokcku';
const DB_VERSION = 1;

const stores = {
    catalog: 'catalog',
    queue: 'queue',
};

function openDb() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains(stores.catalog)) {
                db.createObjectStore(stores.catalog, { keyPath: 'id' });
            }
            if (!db.objectStoreNames.contains(stores.queue)) {
                db.createObjectStore(stores.queue, { keyPath: 'offline_id' });
            }
        };

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

async function withStore(storeName, mode, callback) {
    const db = await openDb();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(storeName, mode);
        const store = tx.objectStore(storeName);
        const result = callback(store);
        tx.oncomplete = () => resolve(result && result.result !== undefined ? result.result : result);
        tx.onerror = () => reject(tx.error);
        tx.onabort = () => reject(tx.error);
    });
}

export const db = {
    putCatalog(items) {
        return withStore(stores.catalog, 'readwrite', (store) => {
            items.forEach((item) => store.put(item));
        });
    },
    getCatalog() {
        return withStore(stores.catalog, 'readonly', (store) => store.getAll());
    },
    clearCatalog() {
        return withStore(stores.catalog, 'readwrite', (store) => store.clear());
    },
    enqueue(transaction) {
        return withStore(stores.queue, 'readwrite', (store) => store.put(transaction));
    },
    getQueue() {
        return withStore(stores.queue, 'readonly', (store) => store.getAll());
    },
    countQueue() {
        return withStore(stores.queue, 'readonly', (store) => store.count());
    },
    removeQueued(offlineId) {
        return withStore(stores.queue, 'readwrite', (store) => store.delete(offlineId));
    },
    markFailed(offlineId, message) {
        return withStore(stores.queue, 'readwrite', (store) => {
            const request = store.get(offlineId);
            request.onsuccess = () => {
                const item = request.result;
                if (item) {
                    item.status = 'failed';
                    item.error = message;
                    store.put(item);
                }
            };
        });
    },
};