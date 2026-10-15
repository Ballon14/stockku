<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        Role::findOrCreate('admin');

        $user = User::create([
            'name' => 'Admin Test',
            'email' => 'admin-test-'.uniqid().'@stockku.com',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
        $user->assignRole('admin');

        return $user;
    }

    public function test_admin_can_search_products_via_normal_request()
    {
        $admin = $this->createAdmin();
        $cat = Category::create(['name' => 'Minuman', 'is_active' => true]);

        Product::create([
            'category_id' => $cat->id,
            'name' => 'Kopi Arabika Premium',
            'sku' => 'KOP-001',
            'harga_beli' => 15000,
            'harga_jual' => 25000,
            'stok' => 50,
            'satuan' => 'pcs',
            'min_stok' => 5,
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $cat->id,
            'name' => 'Teh Melati Wangi',
            'sku' => 'TEH-002',
            'harga_beli' => 5000,
            'harga_jual' => 10000,
            'stok' => 30,
            'satuan' => 'pcs',
            'min_stok' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('products.index', ['search' => 'Arabika']));

        $response->assertOk();
        $response->assertSee('Kopi Arabika Premium');
        $response->assertDontSee('Teh Melati Wangi');
    }

    public function test_admin_live_search_via_ajax_returns_partial_table()
    {
        $admin = $this->createAdmin();
        $cat = Category::create(['name' => 'Snack', 'is_active' => true]);

        Product::create([
            'category_id' => $cat->id,
            'name' => 'Keripik Singkong Balado',
            'sku' => 'SNK-001',
            'harga_beli' => 8000,
            'harga_jual' => 12000,
            'stok' => 40,
            'satuan' => 'pcs',
            'min_stok' => 5,
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $cat->id,
            'name' => 'Biskuit Cokelat',
            'sku' => 'SNK-002',
            'harga_beli' => 6000,
            'harga_jual' => 9000,
            'stok' => 20,
            'satuan' => 'pcs',
            'min_stok' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(
            route('products.index', ['search' => 'Singkong']),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();
        $response->assertSee('Keripik Singkong Balado');
        $response->assertDontSee('Biskuit Cokelat');
        // Partial table should not include the outer HTML header/title tags
        $response->assertDontSee('Export CSV');
        $response->assertDontSee('Import CSV');
    }

    public function test_search_by_category_name_or_sku_works()
    {
        $admin = $this->createAdmin();
        $cat = Category::create(['name' => 'Elektronik', 'is_active' => true]);

        Product::create([
            'category_id' => $cat->id,
            'name' => 'Kabel Type-C',
            'sku' => 'KBL-TC-99',
            'harga_beli' => 15000,
            'harga_jual' => 30000,
            'stok' => 15,
            'satuan' => 'pcs',
            'min_stok' => 2,
            'is_active' => true,
        ]);

        // Search by SKU
        $responseBySku = $this->actingAs($admin)->get(route('products.index', ['search' => 'TC-99']));
        $responseBySku->assertOk();
        $responseBySku->assertSee('Kabel Type-C');

        // Search by Category Name
        $responseByCatName = $this->actingAs($admin)->get(route('products.index', ['search' => 'Elektronik']));
        $responseByCatName->assertOk();
        $responseByCatName->assertSee('Kabel Type-C');
    }
}
