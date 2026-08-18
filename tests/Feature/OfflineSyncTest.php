<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OfflineSyncTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(int $stok = 10, float $hargaJual = 5000, string $name = 'Produk Tes'): Product
    {
        $category = Category::create(['name' => 'Kategori Tes', 'slug' => 'kategori-'.uniqid()]);

        return Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'sku' => 'TES-'.uniqid(),
            'harga_beli' => 3000,
            'harga_jual' => $hargaJual,
            'stok' => $stok,
            'min_stok' => 2,
            'satuan' => 'pcs',
            'is_active' => true,
        ]);
    }

    private function actingAsKasir(): User
    {
        $role = Role::findOrCreate('kasir');
        $user = User::create([
            'name' => 'Kasir Offline',
            'email' => 'kasir-offline-'.uniqid().'@stockku.com',
            'password' => 'password',
        ]);
        $user->assignRole($role);

        $this->actingAs($user);

        return $user;
    }

    public function test_catalog_requires_authentication(): void
    {
        $this->get(route('offline.catalog'))->assertRedirect(route('login', absolute: false));
        $this->postJson(route('offline.sync'), ['transactions' => []])->assertStatus(401);
    }

    public function test_catalog_returns_only_active_products(): void
    {
        $this->actingAsKasir();
        $active = $this->makeProduct(name: 'Aktif');
        $inactive = $this->makeProduct(name: 'Nonaktif');
        $inactive->update(['is_active' => false]);

        $response = $this->getJson(route('offline.catalog'))->assertOk();

        $data = collect($response->json());
        $this->assertTrue($data->contains('id', $active->id));
        $this->assertFalse($data->contains('id', $inactive->id));
        $this->assertArrayHasKey('harga_jual', $data->first());
    }

    public function test_karyawan_cannot_access_sync_endpoints(): void
    {
        $role = Role::findOrCreate('karyawan');
        $user = User::create([
            'name' => 'Karyawan',
            'email' => 'karyawan-offline-'.uniqid().'@stockku.com',
            'password' => 'password',
        ]);
        $user->assignRole($role);
        $this->actingAs($user);

        $this->get(route('offline.catalog'))->assertForbidden();
        $this->postJson(route('offline.sync'), ['transactions' => []])->assertForbidden();
    }

    public function test_sync_creates_sale_and_decrements_stock(): void
    {
        $this->actingAsKasir();
        $product = $this->makeProduct(stok: 10, hargaJual: 5000);

        $response = $this->postJson(route('offline.sync'), [
            'transactions' => [[
                'offline_id' => 'off-abc-123',
                'items' => [['product_id' => $product->id, 'qty' => 3]],
                'diskon' => 1000,
                'bayar' => 20000,
                'catatan' => 'Penjualan offline',
            ]],
        ])->assertOk();

        $this->assertSame('success', $response->json('results.0.status'));
        $this->assertNotNull($response->json('results.0.sale_id'));

        $this->assertDatabaseHas('sales', [
            'id' => $response->json('results.0.sale_id'),
            'subtotal' => 15000,
            'diskon' => 1000,
            'grand_total' => 14000,
            'sumber' => 'offline',
            'offline_id' => 'off-abc-123',
        ]);
        $this->assertSame(7, $product->fresh()->stok);
    }

    public function test_sync_is_idempotent_for_same_offline_id(): void
    {
        $this->actingAsKasir();
        $product = $this->makeProduct(stok: 100, hargaJual: 5000);

        $payload = [
            'transactions' => [[
                'offline_id' => 'off-duplicate',
                'items' => [['product_id' => $product->id, 'qty' => 2]],
                'diskon' => 0,
                'bayar' => 50000,
            ]],
        ];

        $first = $this->postJson(route('offline.sync'), $payload)->assertOk();
        $second = $this->postJson(route('offline.sync'), $payload)->assertOk();

        $this->assertSame($first->json('results.0.sale_id'), $second->json('results.0.sale_id'));
        $this->assertDatabaseCount('sales', 1);
        $this->assertSame(98, $product->fresh()->stok);
    }

    public function test_sync_reports_failure_when_stock_insufficient(): void
    {
        $this->actingAsKasir();
        $product = $this->makeProduct(stok: 2, hargaJual: 5000);

        $response = $this->postJson(route('offline.sync'), [
            'transactions' => [[
                'offline_id' => 'off-gagal',
                'items' => [['product_id' => $product->id, 'qty' => 5]],
                'diskon' => 0,
                'bayar' => 100000,
            ]],
        ])->assertOk();

        $this->assertSame('failed', $response->json('results.0.status'));
        $this->assertStringContainsString('tidak mencukupi', $response->json('results.0.message'));
        $this->assertDatabaseCount('sales', 0);
        $this->assertSame(2, $product->fresh()->stok);
    }

    public function test_sync_continues_when_one_transaction_fails(): void
    {
        $this->actingAsKasir();
        $product = $this->makeProduct(stok: 2, hargaJual: 5000);
        $product2 = $this->makeProduct(stok: 5, hargaJual: 10000, name: 'Produk Kedua');

        $response = $this->postJson(route('offline.sync'), [
            'transactions' => [
                [
                    'offline_id' => 'off-gagal-1',
                    'items' => [['product_id' => $product->id, 'qty' => 99]],
                    'diskon' => 0,
                    'bayar' => 100000,
                ],
                [
                    'offline_id' => 'off-berhasil-2',
                    'items' => [['product_id' => $product2->id, 'qty' => 1]],
                    'diskon' => 0,
                    'bayar' => 50000,
                ],
            ],
        ])->assertOk();

        $statuses = collect($response->json('results'))->pluck('status');
        $this->assertTrue($statuses->contains('failed'));
        $this->assertTrue($statuses->contains('success'));
        $this->assertDatabaseCount('sales', 1);
    }

    public function test_sync_rejects_invalid_payload(): void
    {
        $this->actingAsKasir();

        $this->postJson(route('offline.sync'), [
            'transactions' => [[
                'offline_id' => 'off-invalid',
                'items' => [['product_id' => 'bukan-angka', 'qty' => 0]],
                'diskon' => -5,
                'bayar' => 'x',
            ]],
        ])->assertStatus(422);
    }
}
