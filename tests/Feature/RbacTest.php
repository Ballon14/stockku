<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole(string $role): User
    {
        foreach (['admin', 'manager', 'kasir', 'karyawan'] as $roleName) {
            Role::findOrCreate($roleName);
        }

        $user = User::create([
            'name' => ucfirst($role).' Tes',
            'email' => $role.'-tes-'.uniqid().'@stokcku.com',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
        $user->assignRole($role);

        return $user;
    }

    public function test_kasir_cannot_access_admin_routes(): void
    {
        $this->actingAs($this->createUserWithRole('kasir'));

        $this->get(route('categories.index'))->assertForbidden();
        $this->get(route('products.index'))->assertForbidden();
        $this->get(route('suppliers.index'))->assertForbidden();
        $this->get(route('employees.index'))->assertForbidden();
        $this->get(route('purchases.index'))->assertForbidden();
        $this->get(route('stock.index'))->assertForbidden();
        $this->get(route('reports.sales'))->assertForbidden();
    }

    public function test_kasir_redirected_to_pos_after_login(): void
    {
        $user = $this->createUserWithRole('kasir');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('pos', absolute: false));

        $this->actingAs($user);
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_kasir_can_access_pos(): void
    {
        $this->actingAs($this->createUserWithRole('kasir'));

        $this->get(route('pos'))->assertOk();
        $this->get(route('sales.index'))->assertOk();
    }

    public function test_manager_cannot_access_pos(): void
    {
        $this->actingAs($this->createUserWithRole('manager'));

        $this->get(route('pos'))->assertForbidden();
    }

    public function test_manager_can_access_reports(): void
    {
        $this->actingAs($this->createUserWithRole('manager'));

        $this->get(route('reports.sales'))->assertOk();
        $this->get(route('reports.stock'))->assertOk();
    }

    public function test_karyawan_can_access_attendance_only(): void
    {
        $this->actingAs($this->createUserWithRole('karyawan'));

        $this->get(route('pos'))->assertForbidden();
        $this->get(route('reports.sales'))->assertForbidden();
        $this->get(route('categories.index'))->assertForbidden();
        $this->get(route('attendance.index'))->assertRedirect();
        $this->get(route('attendance.clock'))->assertRedirect();
    }

    public function test_admin_has_full_access(): void
    {
        $this->actingAs($this->createUserWithRole('admin'));

        $this->get(route('categories.index'))->assertOk();
        $this->get(route('pos'))->assertOk();
        $this->get(route('reports.sales'))->assertOk();
        $this->get(route('stock.index'))->assertOk();
    }

    public function test_employee_can_take_attendance(): void
    {
        $user = $this->createUserWithRole('karyawan');
        Employee::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'jabatan' => 'Staff',
            'no_kontak' => '08123456789',
            'tanggal_masuk' => '2024-01-01',
        ]);

        $this->actingAs($user);

        $this->get(route('attendance.index'))->assertOk();
        $this->get(route('attendance.clock'))->assertOk();
    }
}
