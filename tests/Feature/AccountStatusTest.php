<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccountStatusTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole(string $role): User
    {
        $user = User::create([
            'name' => ucfirst($role).' Tes',
            'email' => $role.'-akun-'.uniqid().'@stockku.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole(Role::findOrCreate($role));

        return $user;
    }

    private function createEmployeeFor(User $user): Employee
    {
        return Employee::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'jabatan' => 'Staff',
            'no_kontak' => '08123456789',
            'tanggal_masuk' => '2024-01-01',
        ]);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = $this->createUserWithRole('karyawan');
        $user->update(['is_active' => false]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_active_user_can_login(): void
    {
        $user = $this->createUserWithRole('karyawan');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_toggle_employee_account_status(): void
    {
        $admin = $this->createUserWithRole('admin');
        $adminEmployee = $this->createEmployeeFor($admin);
        $target = $this->createUserWithRole('kasir');
        $employee = $this->createEmployeeFor($target);

        \App\Models\Attendance::create([
            'employee_id' => $adminEmployee->id,
            'tanggal' => now()->toDateString(),
            'clock_in' => now()->toTimeString(),
            'status' => 'hadir',
        ]);

        $this->actingAs($admin)
            ->post(route('employees.toggle-active', $employee))
            ->assertRedirect();

        $this->assertFalse($employee->fresh()->is_active);
        $this->assertFalse($target->fresh()->is_active);
    }

    public function test_toggle_reactivates_account(): void
    {
        $admin = $this->createUserWithRole('admin');
        $adminEmployee = $this->createEmployeeFor($admin);
        $target = $this->createUserWithRole('kasir');
        $employee = $this->createEmployeeFor($target);

        \App\Models\Attendance::create([
            'employee_id' => $adminEmployee->id,
            'tanggal' => now()->toDateString(),
            'clock_in' => now()->toTimeString(),
            'status' => 'hadir',
        ]);

        $this->actingAs($admin)->post(route('employees.toggle-active', $employee));
        $this->actingAs($admin)->post(route('employees.toggle-active', $employee));

        $this->assertTrue($employee->fresh()->is_active);
        $this->assertTrue($target->fresh()->is_active);
    }

    public function test_admin_account_cannot_be_deactivated(): void
    {
        $admin = $this->createUserWithRole('admin');
        $adminEmployee = $this->createEmployeeFor($admin);

        \App\Models\Attendance::create([
            'employee_id' => $adminEmployee->id,
            'tanggal' => now()->toDateString(),
            'clock_in' => now()->toTimeString(),
            'status' => 'hadir',
        ]);

        $this->actingAs($admin)
            ->post(route('employees.toggle-active', $adminEmployee))
            ->assertSessionHas('error');

        $this->assertTrue($adminEmployee->fresh()->is_active);
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_deactivating_account_kills_sessions(): void
    {
        if (config('session.driver') !== 'database') {
            $this->markTestSkipped('Session driver bukan database.');
        }

        $admin = $this->createUserWithRole('admin');
        $adminEmployee = $this->createEmployeeFor($admin);
        $target = $this->createUserWithRole('kasir');
        $employee = $this->createEmployeeFor($target);

        \App\Models\Attendance::create([
            'employee_id' => $adminEmployee->id,
            'tanggal' => now()->toDateString(),
            'clock_in' => now()->toTimeString(),
            'status' => 'hadir',
        ]);

        $this->actingAs($target)->get(route('dashboard'));

        $this->assertDatabaseHas('sessions', ['user_id' => $target->id]);

        $this->actingAs($admin)->post(route('employees.toggle-active', $employee));

        $this->assertDatabaseMissing('sessions', ['user_id' => $target->id]);
    }
}
