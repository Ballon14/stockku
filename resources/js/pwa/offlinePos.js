import {
    getCatalog,
    getQueue,
    enqueueTransaction,
    processQueue,
    refreshCatalog,
    subscribe,
} from './offline';
import { db } from './db';

const rupiah = (value) => 'Rp ' + Number(value || 0).toLocaleString('id-ID');

export function offlinePos() {
    return {
        search: '',
        barcode: '',
        barcodeError: '',
        products: [],
        cart: {},
        diskon: 0,
        bayar: 0,
        paymentMethod: 'cash',
        catatan: '',
        queueItems: [],
        syncing: false,
        message: null,
        messageType: 'success',
        loaded: false,

        async init() {
            subscribe(() => this.updateQueue());
            this.updateQueue();
            await refreshCatalog();
            this.products = getCatalog();
            this.loaded = true;
            this.restoreCart();
        },

        restoreCart() {
            try {
                const raw = localStorage.getItem('stokcku_offline_cart');

                if (!raw) {
                    return;
                }

                const data = JSON.parse(raw);
                const cart = {};

                for (const [key, item] of Object.entries(data.cart || {})) {
                    const product = this.products.find((p) => p.id === item.product_id);

                    if (!product || product.stok <= 0) {
                        continue;
                    }

                    const qty = Math.min(Math.max(1, parseInt(item.qty, 10) || 1), product.stok);

                    cart[key] = {
                        product_id: product.id,
                        name: product.name,
                        harga: Number(product.harga_jual),
                        qty,
                        stok: product.stok,
                        subtotal: Number(product.harga_jual) * qty,
                    };
                }

                this.cart = cart;
                this.diskon = Math.max(0, Number(data.diskon || 0));
                this.bayar = Math.max(0, Number(data.bayar || 0));
                this.paymentMethod = data.paymentMethod === 'qris' ? 'qris' : 'cash';
                this.catatan = data.catatan || '';
            } catch (error) {
                localStorage.removeItem('stokcku_offline_cart');
            }
        },

        saveCart() {
            try {
                localStorage.setItem('stokcku_offline_cart', JSON.stringify({
                    cart: this.cart,
                    diskon: this.diskon,
                    bayar: this.bayar,
                    paymentMethod: this.paymentMethod,
                    catatan: this.catatan,
                }));
            } catch (error) {
                // localStorage penuh atau tidak tersedia — abaikan
            }
        },

        async updateQueue() {
            this.queueItems = await getQueue();
        },

        get queueCount() {
            return this.queueItems.length;
        },

        get failedItems() {
            return this.queueItems.filter((item) => item.status === 'failed');
        },

        filteredProducts() {
            const q = this.search.trim().toLowerCase();

            if (!q) {
                return this.products;
            }

            return this.products.filter((product) =>
                product.name.toLowerCase().includes(q) ||
                (product.sku && product.sku.toLowerCase().includes(q))
            );
        },

        addToCart(productId) {
            const product = this.products.find((item) => item.id === productId);

            if (!product) {
                return;
            }

            const key = 'p_' + product.id;

            if (this.cart[key]) {
                if (this.cart[key].qty < product.stok) {
                    this.cart[key].qty++;
                    this.cart[key].subtotal = this.cart[key].qty * this.cart[key].harga;
                }
            } else {
                this.cart[key] = {
                    product_id: product.id,
                    name: product.name,
                    harga: Number(product.harga_jual),
                    qty: 1,
                    stok: product.stok,
                    subtotal: Number(product.harga_jual),
                };
            }

            this.search = '';
        },

        addByBarcode() {
            const code = String(this.barcode || '').trim();
            this.barcode = '';

            if (!code) {
                return;
            }

            const product = this.products.find((item) =>
                (item.barcode && String(item.barcode) === code) ||
                (item.sku && String(item.sku).toLowerCase() === code.toLowerCase())
            );

            if (!product) {
                this.barcodeError = 'Barcode/SKU "' + code + '" tidak ditemukan.';
                return;
            }

            this.barcodeError = '';
            this.addToCart(product.id);
        },

        updateQty(key, qty) {
            qty = parseInt(qty, 10);

            if (!this.cart[key]) {
                return;
            }

            if (qty <= 0) {
                delete this.cart[key];
            } else if (qty <= this.cart[key].stok) {
                this.cart[key].qty = qty;
                this.cart[key].subtotal = qty * this.cart[key].harga;
            }
        },

        removeItem(key) {
            delete this.cart[key];
        },

        clearCart() {
            this.cart = {};
            this.diskon = 0;
            this.bayar = 0;
            this.paymentMethod = 'cash';
            this.catatan = '';
        },

        selectPayment(method) {
            if (method !== 'cash' && method !== 'qris') {
                return;
            }

            this.paymentMethod = method;

            if (method === 'qris' && this.bayar < this.grandTotal) {
                this.bayar = this.grandTotal;
            }
        },

        get cartItems() {
            return Object.values(this.cart);
        },

        get subtotal() {
            return this.cartItems.reduce((sum, item) => sum + item.subtotal, 0);
        },

        get grandTotal() {
            return Math.max(0, this.subtotal - Number(this.diskon || 0));
        },

        get kembalian() {
            return Math.max(0, Number(this.bayar || 0) - this.grandTotal);
        },

        get canCheckout() {
            return this.cartItems.length > 0 && Number(this.bayar || 0) >= this.grandTotal;
        },

        async checkout() {
            if (!this.canCheckout) {
                return;
            }

            const items = this.cartItems.map((item) => ({
                product_id: item.product_id,
                qty: item.qty,
            }));

            await enqueueTransaction({
                items,
                diskon: Number(this.diskon || 0),
                bayar: Number(this.bayar || 0),
                catatan: this.catatan,
                paymentMethod: this.paymentMethod,
            });

            this.clearCart();
            this.showMessage('Transaksi tersimpan offline dan akan disinkronkan otomatis saat koneksi kembali.', 'success');
        },

        async syncNow() {
            if (this.syncing) {
                return;
            }

            this.syncing = true;
            this.message = null;

            const result = await processQueue();

            this.syncing = false;

            if (result.synced.length > 0) {
                this.showMessage(`${result.synced.length} transaksi berhasil disinkronkan. Memuat ulang...`, 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else if (result.error) {
                this.showMessage(result.error, 'error');
            } else if (result.failed.length > 0) {
                this.showMessage(`${result.failed.length} transaksi gagal disinkronkan, periksa stok produk.`, 'error');
            } else {
                this.showMessage('Tidak ada antrian yang perlu disinkronkan.', 'error');
            }
        },

        async removeFailed(offlineId) {
            await db.removeQueued(offlineId);
            this.updateQueue();
        },

        showMessage(message, type) {
            this.message = message;
            this.messageType = type;

            setTimeout(() => {
                this.message = null;
            }, 6000);
        },

        rupiah,
    };
}

export function connection() {
    return {
        online: navigator.onLine,
        checking: false,

        async init() {
            this.checkConnection();

            window.addEventListener('online', () => this.checkConnection());
            window.addEventListener('offline', () => this.checkConnection());
            setInterval(() => this.checkConnection(), 15000);
        },

        async checkConnection() {
            if (this.checking) {
                return;
            }

            this.checking = true;

            try {
                const response = await fetch('/offline/catalog', {
                    cache: 'no-store',
                    headers: { 'Accept': 'application/json' },
                });
                this.online = response.ok;
            } catch (error) {
                this.online = false;
            } finally {
                this.checking = false;
            }
        },
    };
}

export function syncBanner() {
    return {
        count: 0,
        syncing: false,

        init() {
            this.updateCount();
            subscribe(() => this.updateCount());
        },

        async updateCount() {
            const queue = await getQueue();
            this.count = queue.length;
        },

        async sync() {
            if (this.syncing) {
                return;
            }

            this.syncing = true;

            const result = await processQueue();

            this.syncing = false;

            if (result.synced.length > 0) {
                window.location.reload();
            }
        },
    };
}