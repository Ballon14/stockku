<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'is_active' => 'boolean',
            'create_account' => 'boolean',
        ];

        if ($this->input('create_account')) {
            $userId = $this->route('employee')?->user_id;
            $rules['user_email'] = 'required|email|unique:users,email,'.$userId;
            $rules['user_role'] = 'required|in:admin,kasir,karyawan';

            if (! $this->route('employee')) {
                $rules['user_password'] = 'required|string|min:8';
            } else {
                $rules['user_password'] = 'nullable|string|min:8';
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama karyawan wajib diisi.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'tanggal_masuk.required' => 'Tanggal masuk wajib diisi.',
            'user_email.required' => 'Email akun wajib diisi.',
            'user_email.unique' => 'Email sudah digunakan.',
            'user_password.required' => 'Password wajib diisi.',
            'user_password.min' => 'Password minimal 8 karakter.',
        ];
    }
}
