<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'no_kontak' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'alamat' => 'nullable|string|max:1000',
            'tanggal_masuk' => 'required|date',
            'is_active' => 'sometimes|boolean',
        ];

        $employee = $this->route('employee');
        if (is_numeric($employee) || is_string($employee)) {
            $employee = Employee::find($employee);
        }
        $userId = $employee?->user_id;

        $rules['user_email'] = [
            'required',
            'email',
            'max:255',
            Rule::unique('users', 'email')->ignore($userId),
        ];
        $rules['user_role'] = 'required|in:admin,kasir,karyawan';

        if (! $employee || ! $employee->user_id) {
            $rules['user_password'] = 'required|string|min:8';
        } else {
            $rules['user_password'] = 'nullable|string|min:8';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama karyawan wajib diisi.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'tanggal_masuk.required' => 'Tanggal masuk wajib diisi.',
            'tanggal_masuk.date' => 'Format tanggal masuk tidak valid.',
            'user_email.required' => 'Email akun wajib diisi.',
            'user_email.email' => 'Format email akun tidak valid.',
            'user_email.unique' => 'Email akun sudah digunakan.',
            'user_password.required' => 'Password wajib diisi.',
            'user_password.min' => 'Password minimal 8 karakter.',
            'user_role.required' => 'Role akun wajib dipilih.',
            'user_role.in' => 'Role akun tidak valid.',
        ];
    }
}
