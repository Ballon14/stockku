<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Services\EmployeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecurityFixesTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $role = Role::findOrCreate('admin');
        $user = User::create([
            'name' => 'Admin Audit',
            'email' => 'admin-audit-'.uniqid().'@stockku.com',
            'password' => 'password',
        ]);
        $user->assignRole($role);
        $this->actingAs($user);

        return $user;
    }

    private function kasirUser(bool $withEmployee = true): User
    {
        $role = Role::findOrCreate('kasir');
        $user = User::create([
            'name' => 'Kasir Audit',
            'email' => 'kasir-audit-'.uniqid().'@stockku.com',
            'password' => 'password',
        ]);
        $user->assignRole($role);
        $this->actingAs($user);

        if ($withEmployee) {
            Employee::create([
                'user_id' => $user->id,
                'nama' => $user->name,
                'jabatan' => 'Kasir',
                'no_kontak' => '08123456789',
                'tanggal_masuk' => now()->toDateString(),
                'is_active' => true,
            ]);
        }

        return $user;
    }

    public function test_supplier_module_store_works_with_form_request(): void
    {
        $this->adminUser();

        $response = $this->post(route('suppliers.store'), [
            'name' => 'Supplier Baru',
            'code' => 'SUP-AUDIT-'.uniqid(),
            'phone' => '081234567890',
            'email' => 'supplier@example.com',
            'address' => 'Jl. Merdeka 1',
            'contact_person' => 'Budi',
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', ['name' => 'Supplier Baru']);
    }

    public function test_supplier_code_must_be_unique(): void
    {
        $this->adminUser();
        Supplier::create(['name' => 'Lama', 'code' => 'SUP-DUP', 'phone' => '0812']);

        $this->post(route('suppliers.store'), [
            'name' => 'Baru',
            'code' => 'SUP-DUP',
        ])->assertSessionHasErrors('code');
    }

    public function test_kasir_cannot_view_other_kasir_receipt(): void
    {
        $this->kasirUser();

        $other = User::create([
            'name' => 'Kasir Lain',
            'email' => 'kasir-lain-'.uniqid().'@stockku.com',
            'password' => 'password',
        ]);
        $sale = Sale::create([
            'user_id' => $other->id,
            'invoice_number' => 'INV-TEST-'.uniqid(),
            'subtotal' => 10000,
            'diskon' => 0,
            'grand_total' => 10000,
            'bayar' => 10000,
            'kembalian' => 0,
            'status' => 'completed',
        ]);

        $this->get(route('sales.receipt', $sale))->assertForbidden();
    }

    public function test_delete_employee_deactivates_user_and_sessions(): void
    {
        $this->adminUser();
        $user = User::create([
            'name' => 'Karyawan Dihapus',
            'email' => 'karyawan-hapus-'.uniqid().'@stockku.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $employee = Employee::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'jabatan' => 'Staff',
            'no_kontak' => '08123456789',
            'tanggal_masuk' => now()->toDateString(),
            'is_active' => true,
        ]);
        \DB::table('sessions')->insert([
            'id' => 'session-'.$user->id,
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'payload' => base64_encode('x'),
            'last_activity' => now()->timestamp,
        ]);

        app(EmployeeService::class)->delete($employee);

        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
        $this->assertSame(false, (bool) $user->fresh()->is_active);
        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
    }


    public function test_product_update_cannot_change_stok_directly(): void
    {
        $this->adminUser();
        $category = Category::create(['name' => 'Kat', 'slug' => 'kat-audit3-'.uniqid()]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Produk Stok',
            'sku' => 'STK-'.uniqid(),
            'harga_beli' => 1000,
            'harga_jual' => 2000,
            'stok' => 7,
            'min_stok' => 1,
            'satuan' => 'pcs',
            'is_active' => true,
        ]);

        $response = $this->put(route('products.update', $product), [
            'category_id' => $category->id,
            'name' => 'Produk Stok',
            'sku' => $product->sku,
            'harga_beli' => 1200,
            'harga_jual' => 2500,
            'stok' => 999,
            'min_stok' => 1,
            'satuan' => 'pcs',
        ]);

        $response->assertSessionHasErrors('stok');
        $this->assertSame(7, $product->fresh()->stok);
        $this->assertSame(2000, (int) $product->fresh()->harga_jual);
    }

    public function test_product_update_without_stok_field_works(): void
    {
        $this->adminUser();
        $category = Category::create(['name' => 'Kat', 'slug' => 'kat-audit4-'.uniqid()]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Produk Stok 2',
            'sku' => 'STK2-'.uniqid(),
            'harga_beli' => 1000,
            'harga_jual' => 2000,
            'stok' => 7,
            'min_stok' => 1,
            'satuan' => 'pcs',
            'is_active' => true,
        ]);

        $this->put(route('products.update', $product), [
            'category_id' => $category->id,
            'name' => 'Produk Stok 2',
            'sku' => $product->sku,
            'harga_beli' => 1200,
            'harga_jual' => 2500,
            'min_stok' => 1,
            'satuan' => 'pcs',
        ])->assertSessionHasNoErrors();

        $this->assertSame(7, $product->fresh()->stok);
        $this->assertSame(2500, (int) $product->fresh()->harga_jual);
        $this->assertDatabaseMissing('stock_movements', ['product_id' => $product->id]);
    }
}
