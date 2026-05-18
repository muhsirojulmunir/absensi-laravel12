<?php

use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboards
Route::middleware(['auth'])->group(function () {
    Route::prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');
        
        // Settings
        Route::get('/settings', [App\Http\Controllers\SuperAdmin\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [App\Http\Controllers\SuperAdmin\SettingController::class, 'update'])->name('settings.update');

        // User Management
        Route::get('users/generate-id/{role}', [App\Http\Controllers\SuperAdmin\UserController::class, 'generateNextId'])->name('users.generate-id');
        Route::resource('users', App\Http\Controllers\SuperAdmin\UserController::class);
    });

    Route::prefix('pic')->name('pic.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\PIC\DashboardController::class, 'index'])->name('dashboard');

        // Leave Approvals
        Route::get('/leave-approvals', [App\Http\Controllers\PIC\LeaveApprovalController::class, 'index'])->name('leave-approvals.index');
        Route::put('/leave-approvals/{leaveRequest}/approve', [App\Http\Controllers\PIC\LeaveApprovalController::class, 'approve'])->name('leave-approvals.approve');
        Route::put('/leave-approvals/{leaveRequest}/reject', [App\Http\Controllers\PIC\LeaveApprovalController::class, 'reject'])->name('leave-approvals.reject');

        // Divisional Employees
        Route::get('/employees', [App\Http\Controllers\PIC\EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/employees/{user}', [App\Http\Controllers\PIC\EmployeeController::class, 'show'])->name('employees.show');
    });

    Route::prefix('hrd')->name('hrd.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\HRD\DashboardController::class, 'index'])->name('dashboard');

        // Attendance Monitoring
        Route::get('/attendance', [App\Http\Controllers\HRD\AttendanceMonitoringController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/recap', [App\Http\Controllers\HRD\AttendanceMonitoringController::class, 'recap'])->name('attendance.recap');
    });

    Route::prefix('karyawan')->name('karyawan.')->group(function () {
        Route::get('/dashboard', function () {
            $settings = \App\Models\Setting::all()->pluck('value', 'key');
            return view('karyawan.dashboard', compact('settings'));
        })->name('dashboard');

        // Leave Requests
        Route::resource('leave-requests', App\Http\Controllers\Karyawan\LeaveRequestController::class)->only(['index', 'store']);
        
        // Attendance History / Action
        Route::get('/attendance', [App\Http\Controllers\Karyawan\AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/store', [App\Http\Controllers\Karyawan\AttendanceController::class, 'store'])->name('attendance.store');
        Route::post('/attendance/checkout', [App\Http\Controllers\Karyawan\AttendanceController::class, 'checkout'])->name('attendance.checkout');

        // Profile / Data Diri
        Route::get('/profile', [App\Http\Controllers\Karyawan\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [App\Http\Controllers\Karyawan\ProfileController::class, 'update'])->name('profile.update');
    });
});
