<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\SaleService;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class PosTerminal extends Component
{
    public string $search = '';

    public array $cart = [];

    public $diskon = 0;

    public $bayar = 0;

    public string $catatan = '';

    public bool $showCheckout = false;

    public ?int $lastSaleId = null;

    public function searchProducts()
    {
        // Triggered reactively
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
    }

    public function updateQty(string $key, int $qty)
    {
        if (isset($this->cart[$key])) {
            if ($qty <= 0) {
                unset($this->cart[$key]);
            } elseif ($qty <= $this->cart[$key]['stok']) {
                $this->cart[$key]['qty'] = $qty;
                $this->cart[$key]['subtotal'] = ($qty * $this->cart[$key]['harga']) - $this->cart[$key]['diskon'];
            }
        }
    }

    public function updateItemDiskon(string $key, float $diskon)
    {
        if (isset($this->cart[$key])) {
            $this->cart[$key]['diskon'] = max(0, $diskon);
            $this->cart[$key]['subtotal'] = ($this->cart[$key]['qty'] * $this->cart[$key]['harga']) - $this->cart[$key]['diskon'];
        }
    }

    public function removeItem(string $key)
    {
        unset($this->cart[$key]);
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->diskon = 0;
        $this->bayar = 0;
        $this->catatan = '';
        $this->showCheckout = false;
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

    public function openCheckout()
    {
        // Removed, form always visible
    }

    public function processPayment()
    {
        if (empty($this->cart)) {
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
            $sale = $saleService->createSale($items, (float) $this->diskon, (float) $this->bayar, $this->catatan);
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
