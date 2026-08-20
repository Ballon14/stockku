<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\ProductService;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_today_summary_counts_present_employees(): void
    {
        $emp1 = $this->makeEmployee('Emp Satu', 'emp1@stockku.com');
        $emp2 = $this->makeEmployee('Emp Dua', 'emp2@stockku.com');
        $emp3 = $this->makeEmployee('Emp Tiga', 'emp3@stockku.com');

        Attendance::create(['employee_id' => $emp1->id, 'tanggal' => now()->toDateString(), 'clock_in' => now()->format('H:i:s'), 'status' => 'hadir']);
        Attendance::create(['employee_id' => $emp2->id, 'tanggal' => now()->toDateString(), 'clock_in' => now()->format('H:i:s'), 'status' => 'hadir']);

        $summary = app(AttendanceService::class)->getTodaySummary();

        $this->assertSame(3, $summary['total']);
        $this->assertSame(2, $summary['hadir']);
        $this->assertSame(1, $summary['tidak_hadir']);
    }

    public function test_product_with_sale_history_cannot_be_deleted(): void
    {
        $supplier = Supplier::create(['name' => 'Supplier Tes', 'code' => 'SUP-1', 'phone' => '081234567890']);
        $user = User::create(['name' => 'Kasir', 'email' => 'kasir-prod@stockku.com', 'password' => 'password']);
        $this->actingAs($user);
        $category = Category::create(['name' => 'Kategori', 'slug' => 'kategori-prod']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Produk Bertransaksi',
            'sku' => 'PRD-1',
            'barcode' => null,
            'harga_beli' => 1000,
            'harga_jual' => 2000,
            'stok' => 5,
            'min_stok' => 1,
            'satuan' => 'pcs',
            'is_active' => true,
        ]);

        app(SaleService::class)->createSale([['product_id' => $product->id, 'qty' => 1, 'diskon' => 0]], 0, 2000);

        $this->expectException(\RuntimeException::class);
        app(ProductService::class)->delete($product);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_product_without_history_can_be_deleted(): void
    {
        $category = Category::create(['name' => 'Kategori', 'slug' => 'kategori-del']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Produk Bersih',
            'sku' => 'PRD-2',
            'barcode' => null,
            'harga_beli' => 1000,
            'harga_jual' => 2000,
            'stok' => 5,
            'min_stok' => 1,
            'satuan' => 'pcs',
            'is_active' => true,
        ]);

        $this->assertTrue(app(ProductService::class)->delete($product));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    private function makeEmployee(string $name, string $email): Employee
    {
        $user = User::create(['name' => $name, 'email' => $email, 'password' => 'password']);

        return Employee::create([
            'user_id' => $user->id,
            'nama' => $name,
            'jabatan' => 'Karyawan',
            'no_kontak' => '081234567890',
            'tanggal_masuk' => now()->toDateString(),
            'is_active' => true,
        ]);
    }
}
