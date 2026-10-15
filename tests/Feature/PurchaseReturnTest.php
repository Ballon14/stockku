<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseReturnTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kasir;
    private User $karyawan;
    private Supplier $supplier;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'karyawan', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->kasir = User::factory()->create();
        $this->kasir->assignRole('kasir');

        $this->karyawan = User::factory()->create();
        $this->karyawan->assignRole('karyawan');

        $category = Category::create([
            'name' => 'Serum',
            'slug' => 'serum',
        ]);

        $this->supplier = Supplier::create([
            'code' => 'SUP-001',
            'name' => 'PT Kosmetika Sejahtera',
            'phone' => '08123456789',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Brightening Serum 30ml',
            'sku' => 'SRM-BRT-01',
            'harga_beli' => 50000,
            'harga_jual' => 85000,
            'stok' => 10,
            'min_stok' => 2,
            'satuan' => 'botol',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_access_purchase_returns_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('purchase-returns.index'));
        $response->assertOk();
    }

    public function test_kasir_and_karyawan_cannot_access_purchase_returns(): void
    {
        $this->actingAs($this->kasir)->get(route('purchase-returns.index'))->assertForbidden();
        $this->actingAs($this->karyawan)->get(route('purchase-returns.index'))->assertForbidden();
    }

    public function test_valid_purchase_return_decrements_stock_and_creates_movement(): void
    {
        $this->actingAs($this->admin);
        $purchaseService = app(PurchaseService::class);
        $purchase = $purchaseService->createPurchase(
            $this->supplier->id,
            '2026-10-16',
            [
                [
                    'product_id' => $this->product->id,
                    'qty' => 5,
                    'harga' => 50000,
                ],
            ]
        );

        // Stok awal 10 + beli 5 = 15
        $this->assertEquals(15, $this->product->fresh()->stok);

        // Retur 2 botol ke supplier
        $response = $this->post(route('purchase-returns.store', $purchase), [
            'alasan' => 'Kemasan botol retak',
            'tanggal' => '2026-10-16',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 2,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('purchase_returns', [
            'purchase_id' => $purchase->id,
            'supplier_id' => $this->supplier->id,
            'total_nilai' => 100000,
            'alasan' => 'Kemasan botol retak',
        ]);

        $this->assertDatabaseHas('purchase_return_items', [
            'product_id' => $this->product->id,
            'qty' => 2,
            'harga' => 50000,
            'subtotal' => 100000,
        ]);

        // Purchase item returned_qty diupdate
        $this->assertEquals(2, $purchase->fresh()->items->first()->returned_qty);

        // Status pembelian menjadi partial_return
        $this->assertEquals('partial_return', $purchase->fresh()->status);

        // Stok berkurang dari 15 menjadi 13
        $this->assertEquals(13, $this->product->fresh()->stok);

        // Catatan mutasi stok tercatat keluar (out)
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'out',
            'qty' => 2,
            'stok_sebelum' => 15,
            'stok_sesudah' => 13,
            'reference_type' => \App\Models\PurchaseReturn::class,
        ]);
    }

    public function test_full_return_sets_status_returned(): void
    {
        $this->actingAs($this->admin);
        $purchaseService = app(PurchaseService::class);
        $purchase = $purchaseService->createPurchase(
            $this->supplier->id,
            '2026-10-16',
            [
                [
                    'product_id' => $this->product->id,
                    'qty' => 4,
                    'harga' => 50000,
                ],
            ]
        );

        $this->post(route('purchase-returns.store', $purchase), [
            'alasan' => 'Semua barang salah kirim varian',
            'tanggal' => '2026-10-16',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 4,
                ],
            ],
        ]);

        $this->assertEquals('returned', $purchase->fresh()->status);
        $this->assertEquals(4, $purchase->fresh()->items->first()->returned_qty);
    }

    public function test_over_return_beyond_purchase_qty_is_rejected(): void
    {
        $this->actingAs($this->admin);
        $purchaseService = app(PurchaseService::class);
        $purchase = $purchaseService->createPurchase(
            $this->supplier->id,
            '2026-10-16',
            [
                [
                    'product_id' => $this->product->id,
                    'qty' => 3,
                    'harga' => 50000,
                ],
            ]
        );

        $initialStock = $this->product->fresh()->stok; // 10 + 3 = 13

        $response = $this->from(route('purchase-returns.create', $purchase))->post(route('purchase-returns.store', $purchase), [
            'alasan' => 'Mencoba meretur lebih dari yang dibeli',
            'tanggal' => '2026-10-16',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 5, // Beli hanya 3, retur 5 -> harus gagal
                ],
            ],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertEquals($initialStock, $this->product->fresh()->stok);
        $this->assertEquals(0, $purchase->fresh()->items->first()->returned_qty);
    }

    public function test_return_fails_when_store_stock_is_insufficient(): void
    {
        $this->actingAs($this->admin);
        $purchaseService = app(PurchaseService::class);
        $purchase = $purchaseService->createPurchase(
            $this->supplier->id,
            '2026-10-16',
            [
                [
                    'product_id' => $this->product->id,
                    'qty' => 5,
                    'harga' => 50000,
                ],
            ]
        );

        // Simulasi barang terjual habis (stok toko sisa 1 unit)
        $this->product->update(['stok' => 1]);

        // Coba retur 3 botol ke supplier padahal toko hanya punya 1 botol
        $response = $this->post(route('purchase-returns.store', $purchase), [
            'alasan' => 'Retur ke supplier tapi barang fisik tidak ada di toko',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 3,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertEquals(1, $this->product->fresh()->stok);
    }
}
