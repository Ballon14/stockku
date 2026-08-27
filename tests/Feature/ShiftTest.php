<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Shift;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('admin'));
        Employee::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'jabatan' => 'Owner',
            'no_kontak' => '08123456789',
            'tanggal_masuk' => '2024-01-01',
        ]);
        return $user;
    }

    private function createEmployeeUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('karyawan'));
        Employee::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'jabatan' => 'Staff',
            'no_kontak' => '08123456789',
            'tanggal_masuk' => '2024-01-01',
        ]);
        return $user;
    }

    public function test_admin_can_view_shifts_page()
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin)
            ->get(route('shifts.index'))
            ->assertOk();
    }

    public function test_karyawan_cannot_view_shifts_page()
    {
        $user = $this->createEmployeeUser();

        $this->actingAs($user)
            ->get(route('shifts.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_shift()
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin)
            ->post(route('shifts.store'), [
                'name' => 'Shift Pagi',
                'start_time' => '08:00',
                'end_time' => '17:00',
            ])->assertRedirect(route('shifts.index'));

        $this->assertDatabaseHas('shifts', [
            'name' => 'Shift Pagi',
        ]);
    }

    public function test_clock_in_requires_shift()
    {
        $user = $this->createEmployeeUser();

        Shift::create([
            'name' => 'Shift Pagi',
            'start_time' => '08:00',
            'end_time' => '17:00',
        ]);

        // Missing shift_id
        $this->actingAs($user)
            ->post(route('attendance.clock-in'))
            ->assertSessionHasErrors('shift_id');
            
        // With shift_id
        $this->actingAs($user)
            ->post(route('attendance.clock-in'), ['shift_id' => 1])
            ->assertRedirect(route('attendance.clock'));

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $user->employee->id,
            'shift_id' => 1,
            'status' => 'hadir',
        ]);
    }
}
