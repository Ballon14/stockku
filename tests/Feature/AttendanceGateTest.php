<?php

namespace Tests\Feature;

use App\Livewire\PosTerminal;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use App\Support\AttendanceGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
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

    public function test_admin_is_never_gated(): void
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

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewMissing('attendanceReadOnly');

        $this->post(route('leave-requests.store'), [
            'jenis' => 'izin',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
            'keterangan' => 'Tes admin bebas',
        ])->assertRedirect(route('leave-requests.index'));
    }

    public function test_employee_without_today_attendance_gets_read_only_access(): void
    {
        $user = $this->createEmployeeUser();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('attendanceReadOnly', true);
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
            ->assertOk()
            ->assertViewMissing('attendanceReadOnly');
    }

    public function test_employee_after_clock_out_gets_read_only_access(): void
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
            ->assertOk()
            ->assertViewHas('attendanceReadOnly', true);
    }

    public function test_employee_without_attendance_cannot_perform_write_actions(): void
    {
        $user = $this->createEmployeeUser();

        $this->actingAs($user)
            ->post(route('leave-requests.store'), [
                'jenis' => 'izin',
                'tanggal_mulai' => now()->toDateString(),
                'tanggal_selesai' => now()->toDateString(),
                'keterangan' => 'Harus diblokir',
            ])
            ->assertRedirect(route('attendance.clock'))
            ->assertSessionHas('warning');

        $this->assertDatabaseMissing('leave_requests', ['keterangan' => 'Harus diblokir']);
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
            ->assertOk()
            ->assertViewMissing('attendanceReadOnly');
    }

    public function test_attendance_routes_are_always_accessible(): void
    {
        $user = $this->createEmployeeUser();

        $this->actingAs($user)
            ->get(route('attendance.clock'))
            ->assertOk();
    }

    public function test_attendance_gate_helper_for_read_only_state(): void
    {
        $user = $this->createEmployeeUser();

        $this->assertTrue(AttendanceGate::isReadOnly($user));

        Attendance::create([
            'employee_id' => $user->employee->id,
            'tanggal' => now()->toDateString(),
            'status' => 'hadir',
            'clock_in' => '08:00:00',
        ]);

        $this->assertFalse(AttendanceGate::isReadOnly($user->fresh()));
    }

public function test_pos_payment_is_blocked_for_unattended_kasir(): void
    {
        $user = $this->createEmployeeUser();
        $user->assignRole(Role::findOrCreate('kasir'));

        $this->actingAs($user);

        Livewire::test(\App\Livewire\PosTerminal::class)
            ->set('cart', ['p1' => [
                'product_id' => 1,
                'name' => 'Produk Tes',
                'harga' => 10000,
                'qty' => 1,
                'stok' => 10,
                'diskon' => 0,
                'subtotal' => 10000,
            ]])
            ->set('bayar', 100000)
            ->call('processPayment')
            ->assertNotSet('lastSaleId', 1);

        $this->assertDatabaseCount('sales', 0);
    }
}
