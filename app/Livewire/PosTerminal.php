<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\SaleService;
use App\Support\AttendanceGate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class PosTerminal extends Component
{
    public string $search = '';

    public array $cart = [];

    public $diskon = 0;

    public $diskonPersen = 0;

    public $bayar = 0;

    public string $paymentMethod = 'cash';

    public string $catatan = '';

    public bool $showCheckout = false;

    public ?int $lastSaleId = null;

    public string $barcode = '';

    public ?string $barcodeError = null;

    public function mount(): void
    {
        $this->restoreCartFromSession();
    }

    public function updated($name): void
    {
        if (in_array($name, ['cart', 'diskon', 'diskonPersen', 'bayar', 'paymentMethod', 'catatan'], true)) {
            $this->saveCartToSession();
        }
    }

    public function updatedDiskon($value): void
    {
        $this->diskonPersen = $this->subtotal > 0
            ? round(max(0, (float) $value) / $this->subtotal * 100)
            : 0;
    }

    public function updatedDiskonPersen($value): void
    {
        $this->diskon = round($this->subtotal * max(0, (float) $value) / 100);
    }

    public function searchProducts()
    {
        // Triggered reactively
    }

    public function addByBarcode(?string $code = null): void
    {
        $code = trim((string) ($code ?? $this->barcode));
        $this->barcode = '';

        if ($code === '') {
            return;
        }

        $product = $this->findByCode($code);

        if (! $product) {
            $this->barcodeError = "Barcode/SKU \"{$code}\" tidak ditemukan.";

            return;
        }

        $this->barcodeError = null;
        $this->addToCart($product->id);
    }

    public function addBySearchEnter(): void
    {
        $code = trim((string) $this->search);

        if ($code === '') {
            return;
        }

        $product = $this->findByCode($code);

        if (! $product) {
            return;
        }

        $this->search = '';
        $this->addToCart($product->id);
    }

    private function findByCode(string $code): ?Product
    {
        return Product::where('is_active', true)
            ->where(function ($query) use ($code) {
                $query->where('barcode', $code)
                    ->orWhereRaw('LOWER(sku) = ?', [mb_strtolower($code)]);
            })
            ->first();
    }

    public function addToCart(int $productId)
    {
        $product = Product::find($productId);
        if (! $product || $product->stok <= 0) {
            return;
        }

        $key = 'p_'.$productId;

        if (isset($this->cart[$key])) {
            if ($this->cart[$key]['qty'] < $product->stok) {
                $this->cart[$key]['qty']++;
                $this->cart[$key]['subtotal'] = $this->cart[$key]['qty'] * $this->cart[$key]['harga'];
            }
        } else {
            $this->cart[$key] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'harga' => (float) $product->harga_jual,
                'qty' => 1,
                'stok' => $product->stok,
                'diskon' => 0,
                'subtotal' => (float) $product->harga_jual,
            ];
        }

        $this->search = '';
        $this->syncDiskonFromPersen();
        $this->saveCartToSession();
    }

    public function updateQty(string $key, int $qty)
    {
        if (isset($this->cart[$key])) {
            if ($qty <= 0) {
                unset($this->cart[$key]);
            } elseif ($qty <= $this->cart[$key]['stok']) {
                $this->cart[$key]['qty'] = $qty;
                $this->cart[$key]['subtotal'] = max(0, ($qty * $this->cart[$key]['harga']) - $this->cart[$key]['diskon']);
            }
        }
        $this->syncDiskonFromPersen();
        $this->saveCartToSession();
    }

    public function updateItemDiskon(string $key, float $diskon)
    {
        if (isset($this->cart[$key])) {
            $this->cart[$key]['diskon'] = max(0, $diskon);
            $this->cart[$key]['subtotal'] = max(0, ($this->cart[$key]['qty'] * $this->cart[$key]['harga']) - $this->cart[$key]['diskon']);
        }
        $this->syncDiskonFromPersen();
        $this->saveCartToSession();
    }

    public function removeItem(string $key)
    {
        unset($this->cart[$key]);
        $this->syncDiskonFromPersen();
        $this->saveCartToSession();
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->diskon = 0;
        $this->diskonPersen = 0;
        $this->bayar = 0;
        $this->paymentMethod = 'cash';
        $this->catatan = '';
        $this->showCheckout = false;
        $this->saveCartToSession();
    }

    public function setPaymentMethod(string $method)
    {
        if (! in_array($method, ['cash', 'qris'], true)) {
            return;
        }

        $this->paymentMethod = $method;

        if ($method === 'qris' && (float) $this->bayar < $this->grandTotal) {
            $this->bayar = $this->grandTotal;
        }

        $this->saveCartToSession();
    }

    public function getSubtotalProperty(): float
    {
        return array_sum(array_column($this->cart, 'subtotal'));
    }

    public function getGrandTotalProperty(): float
    {
        return max(0, $this->subtotal - (float) $this->diskon);
    }

    public function getKembalianProperty(): float
    {
        return max(0, (float) $this->bayar - $this->grandTotal);
    }

    public function getQrisCodeProperty(): string
    {
        return (string) config('stockku.qris_code', '');
    }

    private function syncDiskonFromPersen(): void
    {
        if ((float) $this->diskonPersen > 0) {
            $this->diskon = round($this->subtotal * (float) $this->diskonPersen / 100);
        }
    }

    private function saveCartToSession(): void
    {
        if (! auth()->check()) {
            return;
        }

        session()->put('pos_cart_'.auth()->id(), [
            'cart' => $this->cart,
            'diskon' => $this->diskon,
            'diskonPersen' => $this->diskonPersen,
            'bayar' => $this->bayar,
            'catatan' => $this->catatan,
            'paymentMethod' => $this->paymentMethod,
        ]);
    }

    private function restoreCartFromSession(): void
    {
        if (! auth()->check()) {
            return;
        }

        $data = session()->get('pos_cart_'.auth()->id());

        if (! is_array($data) || empty($data['cart'])) {
            return;
        }

        $validated = [];

        foreach ($data['cart'] as $key => $item) {
            $product = Product::find($item['product_id'] ?? null);

            if (! $product || ! $product->is_active || $product->stok <= 0) {
                continue;
            }

            $itemDiskon = max(0, (float) ($item['diskon'] ?? 0));
            $qty = min(max(1, (int) ($item['qty'] ?? 1)), $product->stok);

            $validated[$key] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'harga' => (float) $product->harga_jual,
                'qty' => $qty,
                'stok' => $product->stok,
                'diskon' => $itemDiskon,
                'subtotal' => max(0, ($product->harga_jual * $qty) - $itemDiskon),
            ];
        }

        $this->cart = $validated;
        $this->diskon = max(0, (float) ($data['diskon'] ?? 0));
        $this->diskonPersen = max(0, (float) ($data['diskonPersen'] ?? 0));
        $this->bayar = max(0, (float) ($data['bayar'] ?? 0));
        $this->catatan = (string) ($data['catatan'] ?? '');
        $this->paymentMethod = in_array($data['paymentMethod'] ?? null, ['cash', 'qris'], true) ? $data['paymentMethod'] : 'cash';

        if (! empty($this->cart)) {
            $this->saveCartToSession();
        }
    }

    public function openCheckout()
    {
        // Removed, form always visible
    }

    public function processPayment()
    {
        if (empty($this->cart)) {
            return;
        }
        if (! AttendanceGate::isAttended(auth()->user())) {
            session()->flash('pos-error', 'Anda wajib clock-in terlebih dahulu untuk melakukan transaksi.');

            return;
        }
        if ((float) $this->bayar < $this->grandTotal) {
            session()->flash('pos-error', 'Jumlah bayar kurang dari total.');

            return;
        }

        $items = [];
        foreach ($this->cart as $item) {
            $items[] = [
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
                'diskon' => $item['diskon'],
            ];
        }

        try {
            $saleService = app(SaleService::class);
            $sale = $saleService->createSale($items, (float) $this->diskon, (float) $this->bayar, $this->catatan, $this->paymentMethod);
            $this->lastSaleId = $sale->id;
            $this->clearCart();
            session()->flash('pos-success', 'Transaksi berhasil! Invoice: '.$sale->invoice_number);
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first() ?? 'Data transaksi tidak valid.';
            session()->flash('pos-error', $message);
        } catch (\Exception $e) {
            session()->flash('pos-error', 'Gagal memproses transaksi: '.$e->getMessage());
        }
    }

    public function render()
    {
        $query = Product::where('is_active', true)->where('stok', '>', 0);

        if (strlen($this->search) > 0) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('sku', 'like', '%'.$this->search.'%')
                    ->orWhere('barcode', $this->search);
            });
        }

        $products = $query->limit(50)->get();

        return view('livewire.pos-terminal', [
            'products' => $products,
        ]);
    }
}
