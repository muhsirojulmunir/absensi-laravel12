<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\Division;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index()
    {
        $holidays = Holiday::with('division')->orderBy('date', 'desc')->get();
        $divisions = Division::all();
        
        return view('super-admin.holidays.index', compact('holidays', 'divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        Holiday::create($request->all());

        return redirect()->route('super-admin.holidays.index')->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()->route('super-admin.holidays.index')->with('success', 'Hari libur berhasil dihapus.');
    }
}
