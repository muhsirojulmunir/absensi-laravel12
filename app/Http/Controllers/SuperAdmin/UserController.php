<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['role', 'division'])
                     ->where('username', '!=', 'superadmin')
                     ->orderBy('role_id', 'asc')
                     ->orderByRaw("
                        CASE 
                            WHEN division_id = 3 THEN 1 /* Staff Kantor */
                            WHEN division_id = 1 THEN 2 /* Live Streaming */
                            WHEN division_id = 4 THEN 3 /* Gudang */
                            ELSE 4
                        END ASC
                     ");

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(10)->withQueryString();
        return view('super-admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        $divisions = Division::all();
        $locations = \App\Models\Location::all();
        return view('super-admin.users.create', compact('roles', 'divisions', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'username' => 'required|unique:users',
            'password' => 'required|min:8',
            'role_id' => 'required|exists:roles,id',
            'division_id' => 'nullable|exists:divisions,id',
            'location_id' => 'nullable|exists:locations,id',
            'employee_id' => 'nullable|unique:users',
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email, // Optional, can be null
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'division_id' => $request->division_id,
            'location_id' => $request->location_id,
            'employee_id' => $request->employee_id,
            'phone' => $request->phone,
            'address' => $request->address,
            'position' => $request->position,
            'birth_place' => $request->birth_place,
            'birth_date' => $request->birth_date,
        ]);

        return redirect()->route('super-admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $divisions = Division::all();
        $locations = \App\Models\Location::all();
        return view('super-admin.users.edit', compact('user', 'roles', 'divisions', 'locations'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required',
            'username' => 'required|unique:users,username,' . $user->id,
            'role_id' => 'required|exists:roles,id',
            'division_id' => 'nullable|exists:divisions,id',
            'location_id' => 'nullable|exists:locations,id',
        ]);

        $data = $request->only(['name', 'username', 'email', 'role_id', 'division_id', 'location_id', 'employee_id', 'phone', 'address', 'position', 'birth_place', 'birth_date']);
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('super-admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('super-admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        // Prevent super-admin from deactivating themselves
        if (auth()->id() === $user->id) {
            return redirect()->route('super-admin.users.index')->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return redirect()->route('super-admin.users.index')->with('success', "Akun pengguna berhasil $status.");
    }

    public function generateNextId(Role $role)
    {
        $nextId = User::generateNextEmployeeId($role->id);
        return response()->json(['id' => $nextId]);
    }
}
