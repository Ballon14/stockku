<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = \App\Models\Shift::orderBy('start_time')->get();
        return view('shifts.index', compact('shifts'));
    }

    public function create()
    {
        return view('shifts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:shifts',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
        ]);

        \App\Models\Shift::create($validated);

        return redirect()->route('shifts.index')->with('success', 'Shift berhasil ditambahkan.');
    }

    public function edit(\App\Models\Shift $shift)
    {
        return view('shifts.edit', compact('shift'));
    }

    public function update(Request $request, \App\Models\Shift $shift)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:shifts,name,'.$shift->id,
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
        ]);

        $shift->update($validated);

        return redirect()->route('shifts.index')->with('success', 'Shift berhasil diperbarui.');
    }

    public function destroy(\App\Models\Shift $shift)
    {
        $shift->delete();

        return redirect()->route('shifts.index')->with('success', 'Shift berhasil dihapus.');
    }
}
