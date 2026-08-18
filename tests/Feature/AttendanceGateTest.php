<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceGateTest extends TestCase
{
    use RefreshDatabase;

    private function createEmployeeUser(): User
    {
        $user = User::factory()->create();
        Employee::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'jabatan' => 'Staff',
            'no_kontak' => '08123456789',
            'tanggal_masuk' => '2024-01-01',
        ]);

        return $user;
    }

    public function test_user_without_employee_record_is_not_gated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_employee_without_today_attendance_is_redirected_to_clock(): void
    {
        $user = $this->createEmployeeUser();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('attendance.clock'))
            ->assertSessionHas('warning');
    }

    public function test_employee_after_clock_in_can_access_activities(): void
    {
        $user = $this->createEmployeeUser();
        Attendance::create([
            'employee_id' => $user->employee->id,
            'tanggal' => now()->toDateString(),
            'status' => 'hadir',
            'clock_in' => '08:00:00',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_employee_after_clock_out_is_redirected_to_clock(): void
    {
        $user = $this->createEmployeeUser();
        Attendance::create([
            'employee_id' => $user->employee->id,
            'tanggal' => now()->toDateString(),
            'status' => 'hadir',
            'clock_in' => '08:00:00',
            'clock_out' => '17:00:00',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('attendance.clock'))
            ->assertSessionHas('warning');
    }

    public function test_employee_on_approved_leave_is_not_gated(): void
    {
        $user = $this->createEmployeeUser();
        Attendance::create([
            'employee_id' => $user->employee->id,
            'tanggal' => now()->toDateString(),
            'status' => 'izin',
            'keterangan' => 'Cuti keluarga',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_attendance_routes_are_always_accessible(): void
    {
        $user = $this->createEmployeeUser();

        $this->actingAs($user)
            ->get(route('attendance.clock'))
            ->assertOk();
    }
}
