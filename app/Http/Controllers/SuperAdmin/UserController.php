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
            'additional_location_ids' => 'nullable|array',
            'additional_location_ids.*' => 'exists:locations,id',
            'employee_id' => 'nullable|unique:users',
        ]);

        $additionalLocations = array_values(array_filter($request->additional_location_ids ?? []));

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email, // Optional, can be null
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'division_id' => $request->division_id,
            'location_id' => $request->location_id,
            'additional_location_ids' => $additionalLocations,
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
            'additional_location_ids' => 'nullable|array',
            'additional_location_ids.*' => 'exists:locations,id',
        ]);

        $data = $request->only(['name', 'username', 'email', 'role_id', 'division_id', 'location_id', 'employee_id', 'phone', 'address', 'position', 'birth_place', 'birth_date']);
        
        $data['additional_location_ids'] = array_values(array_filter($request->additional_location_ids ?? []));

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

    public function showBulkEmailForm()
    {
        $employees = User::whereHas('role', function ($q) {
            $q->whereIn('slug', ['karyawan', 'karyawan_ramayana']);
        })
        ->where('is_active', true)
        ->with(['latestBulkEmailLog.sender', 'role'])
        ->orderBy('name')
        ->get();

        $recentLogs = \App\Models\BulkEmailLog::with(['user', 'sender'])
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return view('super-admin.users.bulk-email', compact('employees', 'recentLogs'));
    }

    public function parseBulkEmailInput(Request $request)
    {
        $request->validate([
            'raw_text' => 'required|string',
        ]);

        $rawText = $request->raw_text;
        $lines = explode("\n", str_replace("\r", "", $rawText));
        $parsed = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $data = [];
            if (str_contains($line, ',')) {
                $data = explode(',', $line, 2);
            } elseif (str_contains($line, ';')) {
                $data = explode(';', $line, 2);
            } elseif (str_contains($line, ':')) {
                $data = explode(':', $line, 2);
            } elseif (str_contains($line, '|')) {
                $data = explode('|', $line, 2);
            } else {
                $data = preg_split('/\s+/', $line, 2);
            }

            if (count($data) >= 2) {
                $inputVal = trim($data[0]);
                $passwordVal = trim($data[1]);
                $parsed[] = [
                    'input' => $inputVal,
                    'password' => $passwordVal,
                ];
            } else {
                $inputVal = trim($line);
                $parsed[] = [
                    'input' => $inputVal,
                    'password' => '',
                ];
            }
        }

        $results = [];
        foreach ($parsed as $item) {
            $input = $item['input'];
            $password = $item['password'];

            $user = User::with('role')
                ->where('email', $input)
                ->orWhere('username', $input)
                ->first();

            if ($user) {
                $results[] = [
                    'found' => true,
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role->name ?? '-',
                    'password' => $password,
                ];
            } else {
                $results[] = [
                    'found' => false,
                    'input' => $input,
                    'password' => $password,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    public function sendBulkEmails(Request $request)
    {
        $request->validate([
            'users' => 'required|array',
            'users.*.user_id' => 'required|exists:users,id',
            'users.*.password' => 'required|string|min:4',
            'users.*.update_password' => 'nullable|boolean',
            'users.*.send_email' => 'nullable|boolean',
        ]);

        $usersData = $request->users;
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($usersData as $userData) {
            $user = User::find($userData['user_id']);
            if (!$user) {
                $errorCount++;
                continue;
            }

            $updatePassword = filter_var($userData['update_password'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $sendEmail = filter_var($userData['send_email'] ?? false, FILTER_VALIDATE_BOOLEAN);

            try {
                if ($updatePassword) {
                    $user->update([
                        'password' => Hash::make($userData['password']),
                    ]);
                }

                if ($sendEmail) {
                    if (empty($user->email)) {
                        throw new \Exception("User {$user->name} tidak memiliki alamat email.");
                    }

                    \Illuminate\Support\Facades\Mail::send(
                        'emails.credentials',
                        [
                            'name' => $user->name,
                            'username' => $user->username,
                            'email' => $user->email,
                            'password' => $userData['password'],
                        ],
                        function ($message) use ($user) {
                            $message->to($user->email)
                                    ->subject('Detail Kredensial Akun JMN Matrix');
                        }
                    );

                    // Log success
                    \App\Models\BulkEmailLog::create([
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'status' => 'success',
                        'sent_by' => auth()->id(),
                    ]);
                }

                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = "User: {$user->name} ({$user->email}) - Error: " . $e->getMessage();

                // Log failure if email sending was attempted
                if ($sendEmail) {
                    \App\Models\BulkEmailLog::create([
                        'user_id' => $user->id,
                        'email' => $user->email ?? '',
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'sent_by' => auth()->id(),
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'errors' => $errors,
        ]);
    }
}
