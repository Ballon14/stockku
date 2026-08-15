<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(int $stok = 10): Product
    {
        $category = Category::create(['name' => 'Kategori Tes', 'slug' => 'kategori-'.uniqid()]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Produk Tes',
            'sku' => 'TES-'.uniqid(),
            'harga_beli' => 3000,
            'harga_jual' => 5000,
            'stok' => $stok,
            'min_stok' => 2,
            'satuan' => 'pcs',
            'is_active' => true,
        ]);
    }

    private function actingAsUser(): void
    {
        $user = User::create([
            'name' => 'Karyawan Tes',
            'email' => 'karyawan-tes-'.uniqid().'@stokcku.com',
            'password' => 'password',
        ]);

        $this->actingAs($user);
    }

    public function test_in_movement_increments_stock(): void
    {
        $this->actingAsUser();
        $product = $this->makeProduct(stok: 5);

        app(StockService::class)->recordMovement($product, 'in', 4, null, null, 'Pembelian');

        $this->assertSame(9, $product->fresh()->stok);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'in',
            'qty' => 4,
            'stok_sebelum' => 5,
            'stok_sesudah' => 9,
        ]);
    }

    public function test_out_movement_decrements_stock(): void
    {
        $this->actingAsUser();
        $product = $this->makeProduct(stok: 5);

        app(StockService::class)->recordMovement($product, 'out', 3, null, null, 'Penjualan');

        $this->assertSame(2, $product->fresh()->stok);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'out',
            'qty' => 3,
            'stok_sebelum' => 5,
            'stok_sesudah' => 2,
        ]);
    }

    public function test_return_movement_increments_stock(): void
    {
        $this->actingAsUser();
        $product = $this->makeProduct(stok: 5);

        app(StockService::class)->recordMovement($product, 'return', 2, null, null, 'Retur');

        $this->assertSame(7, $product->fresh()->stok);
    }

    public function test_out_movement_with_insufficient_stock_throws(): void
    {
        $this->actingAsUser();
        $product = $this->makeProduct(stok: 2);

        $this->expectException(\RuntimeException::class);

        app(StockService::class)->recordMovement($product, 'out', 3, null, null, 'Penjualan');
    }

    public function test_out_movement_cannot_make_stock_negative(): void
    {
        $this->actingAsUser();
        $product = $this->makeProduct(stok: 2);

        try {
            app(StockService::class)->recordMovement($product, 'out', 3, null, null, 'Penjualan');
            $this->fail('RuntimeException seharusnya dilempar.');
        } catch (\RuntimeException) {
            // expected
        }

        $this->assertSame(2, $product->fresh()->stok);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_unknown_movement_type_throws(): void
    {
        $this->actingAsUser();
        $product = $this->makeProduct();

        $this->expectException(\InvalidArgumentException::class);

        app(StockService::class)->recordMovement($product, 'transfer', 1, null, null, 'Mutasi');
    }

    public function test_movement_records_user_id(): void
    {
        $this->actingAsUser();
        $product = $this->makeProduct();

        $movement = app(StockService::class)->recordMovement($product, 'in', 1, null, null, 'Pembelian');

        $this->assertNotNull($movement->user_id);
        $this->assertDatabaseHas('stock_movements', ['id' => $movement->id, 'user_id' => auth()->id()]);
    }
}
