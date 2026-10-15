<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'karyawan', 'guard_name' => 'web']);
    }

    public function test_admin_can_update_employee_details_and_sync_user()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create([
            'name' => 'Budi Lama',
            'email' => 'karyawan@stockku.test',
            'password' => 'password123',
            'is_active' => true,
        ]);
        $user->assignRole('karyawan');

        $employee = Employee::create([
            'user_id' => $user->id,
            'nama' => 'Budi Lama',
            'jabatan' => 'Staff Gudang',
            'no_kontak' => '08123456789',
            'email' => 'budi@example.com',
            'alamat' => 'Jl. Merdeka No. 1',
            'tanggal_masuk' => '2024-01-01',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('employees.update', $employee), [
            'nama' => 'Budi Baru',
            'jabatan' => 'Kepala Gudang',
            'no_kontak' => '08129999999',
            'email' => 'budi_new@example.com',
            'alamat' => 'Jl. Baru No. 2',
            'tanggal_masuk' => '2024-02-01',
            'user_email' => 'karyawan@stockku.test',
            'user_role' => 'kasir',
            'user_password' => '', // kosongkan jika tidak diubah
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('employees.index'));

        $employee->refresh();
        $this->assertEquals('Budi Baru', $employee->nama);
        $this->assertEquals('Kepala Gudang', $employee->jabatan);
        $this->assertEquals('08129999999', $employee->no_kontak);
        $this->assertTrue($employee->is_active, 'is_active should remain true after update');

        // Check user sync
        $user->refresh();
        $this->assertEquals('Budi Baru', $user->name);
        $this->assertTrue($user->hasRole('kasir'));
        $this->assertTrue(Hash::check('password123', $user->password), 'Password should remain unchanged when left blank');
    }

    public function test_admin_can_update_employee_password()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create([
            'name' => 'Siti Kasir',
            'email' => 'siti@stockku.test',
            'password' => 'oldpassword123',
            'is_active' => true,
        ]);
        $user->assignRole('kasir');

        $employee = Employee::create([
            'user_id' => $user->id,
            'nama' => 'Siti Kasir',
            'jabatan' => 'Kasir',
            'tanggal_masuk' => '2024-01-01',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('employees.update', $employee), [
            'nama' => 'Siti Kasir',
            'jabatan' => 'Kasir Senior',
            'tanggal_masuk' => '2024-01-01',
            'user_email' => 'siti@stockku.test',
            'user_role' => 'kasir',
            'user_password' => 'newpassword123',
        ]);

        $response->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password), 'New password should be correctly hashed and usable');
    }

    public function test_validation_fails_when_user_email_is_taken_by_another_user()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user1 = User::factory()->create(['email' => 'user1@stockku.test']);
        $user2 = User::factory()->create(['email' => 'user2@stockku.test']);

        $employee = Employee::create([
            'user_id' => $user1->id,
            'nama' => 'Karyawan 1',
            'jabatan' => 'Staff',
            'tanggal_masuk' => '2024-01-01',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('employees.update', $employee), [
            'nama' => 'Karyawan 1',
            'jabatan' => 'Staff',
            'tanggal_masuk' => '2024-01-01',
            'user_email' => 'user2@stockku.test', // already taken by user2
            'user_role' => 'karyawan',
        ]);

        $response->assertSessionHasErrors(['user_email']);
    }
}
