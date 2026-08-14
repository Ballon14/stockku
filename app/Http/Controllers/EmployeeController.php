<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use App\Services\EmployeeService;

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeService $employeeService
    ) {}

    public function index()
    {
        $search = request('search');
        $employees = $this->employeeService->getAll($search);
        return view('employees.index', compact('employees', 'search'));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(EmployeeRequest $request)
    {
        $userData = null;
        if ($request->input('create_account')) {
            $userData = [
                'email' => $request->input('user_email'),
                'password' => $request->input('user_password'),
                'role' => $request->input('user_role'),
            ];
        }

        $this->employeeService->store($request->only([
            'nama', 'jabatan', 'no_kontak', 'email', 'alamat', 'tanggal_masuk', 'is_active'
        ]), $userData);

        return redirect()->route('employees.index')->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function edit(Employee $employee)
    {
        $employee->load('user');
        return view('employees.edit', compact('employee'));
    }

    public function update(EmployeeRequest $request, Employee $employee)
    {
        $userData = null;
        if ($request->input('create_account') && $employee->user) {
            $userData = [
                'email' => $request->input('user_email'),
                'password' => $request->input('user_password'),
                'role' => $request->input('user_role'),
            ];
        }

        $this->employeeService->update($employee, $request->only([
            'nama', 'jabatan', 'no_kontak', 'email', 'alamat', 'tanggal_masuk', 'is_active'
        ]), $userData);

        return redirect()->route('employees.index')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        $this->employeeService->delete($employee);
        return redirect()->route('employees.index')->with('success', 'Data karyawan berhasil dihapus.');
    }
}
