<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use App\Services\ActivityLogger;
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

        $employee = $this->employeeService->store($request->only([
            'nama', 'jabatan', 'no_kontak', 'email', 'alamat', 'tanggal_masuk', 'is_active',
        ]), $userData);

        app(ActivityLogger::class)->log('employee.create', 'Karyawan "'.$employee->nama.'" ('.$employee->jabatan.') ditambahkan.');

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
        if ($request->input('create_account')) {
            $userData = [
                'email' => $request->input('user_email'),
                'password' => $request->input('user_password'),
                'role' => $request->input('user_role'),
            ];
        }

        $this->employeeService->update($employee, $request->only([
            'nama', 'jabatan', 'no_kontak', 'email', 'alamat', 'tanggal_masuk', 'is_active',
        ]), $userData);

        app(ActivityLogger::class)->log('employee.update', 'Karyawan "'.$employee->nama.'" diperbarui.');

        return redirect()->route('employees.index')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function toggleActive(Employee $employee)
    {
        if ($employee->user && $employee->user->hasRole('admin')) {
            return back()->with('error', 'Akun admin (owner) tidak dapat dinonaktifkan.');
        }

        $newStatus = ! $employee->is_active;

        $employee->update(['is_active' => $newStatus]);

        if ($employee->user) {
            $employee->user->update(['is_active' => $newStatus]);

            if (! $newStatus) {
                \DB::table('sessions')->where('user_id', $employee->user->id)->delete();
            }
        }

        $status = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
        app(ActivityLogger::class)->log('employee.toggle_active', 'Karyawan "'.$employee->nama.'" '.$status.'.');

        return back()->with('success', 'Karyawan "'.$employee->nama.'" berhasil '.$status.'.');
    }

    public function destroy(Employee $employee)
    {
        if ($employee->user && $employee->user->hasRole('admin')) {
            return back()->with('error', 'Akun admin (owner) tidak dapat dihapus.');
        }

        $this->employeeService->delete($employee);
        app(ActivityLogger::class)->log('employee.delete', 'Karyawan "'.$employee->nama.'" dihapus.');

        return redirect()->route('employees.index')->with('success', 'Data karyawan berhasil dihapus.');
    }
}
