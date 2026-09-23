<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\EmploymentContract;
use App\Models\User;
use Illuminate\Http\Request;

class EmploymentContractController extends Controller
{
    public function index(Request $request)
    {
        $query = EmploymentContract::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('employee_name', 'like', "%{$search}%")
                  ->orWhere('employee_position', 'like', "%{$search}%")
                  ->orWhere('contract_number', 'like', "%{$search}%");
            });
        }

        $contracts = $query->latest()->paginate(10)->withQueryString();
        return view('super-admin.contracts.index', compact('contracts'));
    }

    public function create()
    {
        $users = User::whereHas('role', fn($q) => $q->where('slug', '!=', 'super-admin'))
                     ->orderBy('name')->get();

        $nextNumber = 'KK-' . date('Ymd') . '-' . str_pad(EmploymentContract::count() + 1, 3, '0', STR_PAD_LEFT);

        return view('super-admin.contracts.form', compact('users', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'contract_number'                 => 'required|string|unique:employment_contracts,contract_number',
            'user_id'                         => 'nullable|exists:users,id',
            'employee_name'                   => 'required|string|max:255',
            'employee_nik'                    => 'nullable|string|max:20',
            'employee_birth_place'            => 'nullable|string|max:100',
            'employee_birth_date'             => 'nullable|date',
            'employee_address'                => 'nullable|string',
            'employee_phone'                  => 'nullable|string|max:20',
            'employee_position'               => 'required|string|max:255',
            'contract_start'                  => 'required|date',
            'contract_end'                    => 'required|date|after:contract_start',
            'basic_salary'                    => 'required|integer|min:0',
            'meal_allowance'                  => 'nullable|integer|min:0',
            'transport_allowance'             => 'nullable|integer|min:0',
            'other_allowance'                 => 'nullable|integer|min:0',
            'other_allowance_note'            => 'nullable|string|max:255',
            'company_representative'          => 'required|string|max:255',
            'company_representative_position' => 'required|string|max:255',
            'signed_city'                     => 'required|string|max:100',
            'signed_date'                     => 'required|date',
        ]);

        $data['basic_salary']        = $data['basic_salary'] ?? 0;
        $data['meal_allowance']      = $data['meal_allowance'] ?? 0;
        $data['transport_allowance'] = $data['transport_allowance'] ?? 0;
        $data['other_allowance']     = $data['other_allowance'] ?? 0;

        EmploymentContract::create($data);

        return redirect()->route('super-admin.contracts.index')
                         ->with('success', 'Kontrak kerja berhasil dibuat.');
    }

    public function show(EmploymentContract $contract)
    {
        return view('super-admin.contracts.show', compact('contract'));
    }

    public function edit(EmploymentContract $contract)
    {
        $users = User::whereHas('role', fn($q) => $q->where('slug', '!=', 'super-admin'))
                     ->orderBy('name')->get();

        return view('super-admin.contracts.form', compact('contract', 'users'));
    }

    public function update(Request $request, EmploymentContract $contract)
    {
        $data = $request->validate([
            'contract_number'                 => 'required|string|unique:employment_contracts,contract_number,' . $contract->id,
            'user_id'                         => 'nullable|exists:users,id',
            'employee_name'                   => 'required|string|max:255',
            'employee_nik'                    => 'nullable|string|max:20',
            'employee_birth_place'            => 'nullable|string|max:100',
            'employee_birth_date'             => 'nullable|date',
            'employee_address'                => 'nullable|string',
            'employee_phone'                  => 'nullable|string|max:20',
            'employee_position'               => 'required|string|max:255',
            'contract_start'                  => 'required|date',
            'contract_end'                    => 'required|date|after:contract_start',
            'basic_salary'                    => 'required|integer|min:0',
            'meal_allowance'                  => 'nullable|integer|min:0',
            'transport_allowance'             => 'nullable|integer|min:0',
            'other_allowance'                 => 'nullable|integer|min:0',
            'other_allowance_note'            => 'nullable|string|max:255',
            'company_representative'          => 'required|string|max:255',
            'company_representative_position' => 'required|string|max:255',
            'signed_city'                     => 'required|string|max:100',
            'signed_date'                     => 'required|date',
        ]);

        $contract->update($data);

        return redirect()->route('super-admin.contracts.index')
                         ->with('success', 'Kontrak kerja berhasil diperbarui.');
    }

    public function destroy(EmploymentContract $contract)
    {
        $contract->delete();
        return redirect()->route('super-admin.contracts.index')
                         ->with('success', 'Kontrak kerja berhasil dihapus.');
    }

    public function print(EmploymentContract $contract)
    {
        return view('super-admin.contracts.print', compact('contract'));
    }
}
