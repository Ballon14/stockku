<?php

namespace Tests\Feature;

use App\Livewire\PosTerminal;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosTerminalTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(?string $barcode = null): Product
    {
        $category = Category::create(['name' => 'Kategori Tes', 'slug' => 'kategori-'.uniqid()]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Produk Barcode',
            'sku' => 'BC-'.uniqid(),
            'barcode' => $barcode,
            'harga_beli' => 3000,
            'harga_jual' => 5000,
            'stok' => 10,
            'min_stok' => 2,
            'satuan' => 'pcs',
            'is_active' => true,
        ]);
    }

    public function test_add_by_barcode_adds_product_to_cart(): void
    {
        $user = User::create(['name' => 'Kasir', 'email' => 'kasir-pos@stockku.com', 'password' => 'password']);
        $this->actingAs($user);
        $product = $this->makeProduct(barcode: '8991234567890');

        Livewire::test(PosTerminal::class)
            ->set('barcode', '8991234567890')
            ->call('addByBarcode')
            ->assertSet('barcode', '')
            ->assertSet('barcodeError', null)
            ->assertSet('cart', [
                'p_'.$product->id => [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'harga' => 5000,
                    'qty' => 1,
                    'diskon' => 0,
                    'subtotal' => 5000,
                    'stok' => 10,
                ],
            ]);
    }

    public function test_add_by_sku_as_fallback(): void
    {
        $user = User::create(['name' => 'Kasir', 'email' => 'kasir-pos2@stockku.com', 'password' => 'password']);
        $this->actingAs($user);
        $product = $this->makeProduct();

        Livewire::test(PosTerminal::class)
            ->set('barcode', $product->sku)
            ->call('addByBarcode')
            ->assertSet('barcodeError', null)
            ->assertCount('cart', 1);
    }

    public function test_add_by_barcode_unknown_code_sets_error(): void
    {
        $user = User::create(['name' => 'Kasir', 'email' => 'kasir-pos3@stockku.com', 'password' => 'password']);
        $this->actingAs($user);

        Livewire::test(PosTerminal::class)
            ->set('barcode', '9999999999999')
            ->call('addByBarcode')
            ->assertSet('barcode', '')
            ->assertSet('barcodeError', 'Barcode/SKU "9999999999999" tidak ditemukan.')
            ->assertCount('cart', 0);
    }
}
