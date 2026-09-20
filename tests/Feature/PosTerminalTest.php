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



    public function test_add_by_search_enter_exact_code_adds_to_cart(): void
    {
        $user = User::create(['name' => 'Kasir', 'email' => 'kasir-pos8@stockku.com', 'password' => 'password']);
        $this->actingAs($user);
        $product = $this->makeProduct(barcode: '8990001112223');

        Livewire::test(PosTerminal::class)
            ->set('search', '8990001112223')
            ->call('addBySearchEnter')
            ->assertSet('search', '')
            ->assertCount('cart', 1)
            ->assertSet('cart.p_'.$product->id.'.qty', 1);
    }

    public function test_add_by_search_enter_unknown_code_does_nothing(): void
    {
        $user = User::create(['name' => 'Kasir', 'email' => 'kasir-pos9@stockku.com', 'password' => 'password']);
        $this->actingAs($user);

        Livewire::test(PosTerminal::class)
            ->set('search', '9998887776665')
            ->call('addBySearchEnter')
            ->assertCount('cart', 0);
    }

    public function test_diskon_persen_converts_to_rupiah(): void
    {
        $user = User::create(['name' => 'Kasir', 'email' => 'kasir-pos4@stockku.com', 'password' => 'password']);
        $this->actingAs($user);
        $product = $this->makeProduct();

        Livewire::test(PosTerminal::class)
            ->call('addToCart', $product->id)
            ->set('diskonPersen', 10)
            ->assertSet('diskon', 500);
    }

    public function test_diskon_rupiah_syncs_persen(): void
    {
        $user = User::create(['name' => 'Kasir', 'email' => 'kasir-pos5@stockku.com', 'password' => 'password']);
        $this->actingAs($user);
        $product = $this->makeProduct();

        Livewire::test(PosTerminal::class)
            ->call('addToCart', $product->id)
            ->set('diskon', 1000)
            ->assertSet('diskonPersen', 20);
    }
}
