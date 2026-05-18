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
    public function index()
    {
        $users = User::with(['role', 'division'])->get();
        return view('super-admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        $divisions = Division::all();
        return view('super-admin.users.create', compact('roles', 'divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'username' => 'required|unique:users',
            'password' => 'required|min:8',
            'role_id' => 'required|exists:roles,id',
            'division_id' => 'nullable|exists:divisions,id',
            'employee_id' => 'nullable|unique:users',
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email, // Optional, can be null
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'division_id' => $request->division_id,
            'employee_id' => $request->employee_id,
            'phone' => $request->phone,
            'address' => $request->address,
            'position' => $request->position,
        ]);

        return redirect()->route('super-admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $divisions = Division::all();
        return view('super-admin.users.edit', compact('user', 'roles', 'divisions'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required',
            'username' => 'required|unique:users,username,' . $user->id,
            'role_id' => 'required|exists:roles,id',
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        $data = $request->only(['name', 'username', 'email', 'role_id', 'division_id', 'employee_id', 'phone', 'address', 'position']);
        
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

    public function generateNextId(Role $role)
    {
        $nextId = User::generateNextEmployeeId($role->id);
        return response()->json(['id' => $nextId]);
    }
}
