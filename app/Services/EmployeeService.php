<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    public function getAll($search = null)
    {
        $query = Employee::with('user.roles');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('jabatan', 'like', "%{$search}%")
                    ->orWhere('no_kontak', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate(15);
    }

    public function store(array $data, ?array $userData = null): Employee
    {
        return DB::transaction(function () use ($data, $userData) {
            if ($userData) {
                $user = User::create([
                    'name' => $data['nama'],
                    'email' => $userData['email'],
                    'password' => $userData['password'],
                ]);
                $user->assignRole($userData['role'] ?? 'karyawan');
                $data['user_id'] = $user->id;
            }

            return Employee::create($data);
        });
    }

    public function update(Employee $employee, array $data, ?array $userData = null): Employee
    {
        return DB::transaction(function () use ($employee, $data, $userData) {
            $employee->update($data);

            if ($userData && $employee->user) {
                $updateData = [
                    'email' => $userData['email'],
                    'name' => $data['nama'] ?? $employee->nama,
                ];
                if (! empty($userData['password'])) {
                    $updateData['password'] = $userData['password'];
                }
                $employee->user->update($updateData);

                if (! empty($userData['role'])) {
                    $employee->user->syncRoles([$userData['role']]);
                }
            } elseif ($userData && ! $employee->user) {
                // Buat akun user baru untuk karyawan yang belum punya akun
                if (empty($userData['password'])) {
                    throw new \RuntimeException('Password wajib diisi untuk membuat akun baru.');
                }

                $user = User::create([
                    'name' => $data['nama'] ?? $employee->nama,
                    'email' => $userData['email'],
                    'password' => $userData['password'],
                ]);
                $user->assignRole($userData['role'] ?? 'karyawan');
                $employee->update(['user_id' => $user->id]);
            }

            return $employee;
        });
    }

    public function delete(Employee $employee): bool
    {
        return DB::transaction(function () use ($employee) {
            if ($employee->user) {
                $employee->user->update(['is_active' => false]);
                DB::table('sessions')->where('user_id', $employee->user->id)->delete();
            }

            return $employee->delete();
        });
    }
}
