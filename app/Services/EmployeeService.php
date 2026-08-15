<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmployeeService
{
    public function getAll($search = null)
    {
        $query = Employee::with('user');

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
        if ($userData) {
            $user = User::create([
                'name' => $data['nama'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
            ]);
            $user->assignRole($userData['role'] ?? 'karyawan');
            $data['user_id'] = $user->id;
        }

        return Employee::create($data);
    }

    public function update(Employee $employee, array $data, ?array $userData = null): Employee
    {
        $employee->update($data);

        if ($userData && $employee->user) {
            $updateData = ['email' => $userData['email']];
            if (! empty($userData['password'])) {
                $updateData['password'] = Hash::make($userData['password']);
            }
            $employee->user->update($updateData);

            if (! empty($userData['role'])) {
                $employee->user->syncRoles([$userData['role']]);
            }
        }

        return $employee;
    }

    public function delete(Employee $employee): bool
    {
        return $employee->delete();
    }
}
