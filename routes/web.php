<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Route Khusus Hosting (InfinityFree) untuk Migrasi
Route::get('/migrations', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return '<pre>' . \Illuminate\Support\Facades\Artisan::output() . '</pre>';
    } catch (\Exception $e) {
        return '<pre>' . $e->getMessage() . '</pre>';
    }
});

// Forgot Password / OTP Flow
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendOtp'])->name('password.email');
Route::get('/verify-otp', [AuthController::class, 'showVerifyOtpForm'])->name('password.verify-otp-form');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('password.verify-otp');
Route::get('/reset-password', [AuthController::class, 'showResetPasswordForm'])->name('password.reset-form');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Dashboards
Route::middleware(['auth'])->group(function () {
    // Performance Evaluation (Accessible by multiple roles)
    Route::get('/performance-evaluation', [App\Http\Controllers\PerformanceEvaluationController::class, 'index'])->name('performance.index');
    Route::get('/performance-evaluation/{id}', [App\Http\Controllers\PerformanceEvaluationController::class, 'show'])->name('performance.show');
    
    // Birthday Greetings
    Route::post('/birthday-greetings', [App\Http\Controllers\Shared\BirthdayGreetingController::class, 'store'])->name('birthday-greetings.store');

    Route::prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');
        
        // Settings
        Route::get('/settings', [App\Http\Controllers\SuperAdmin\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [App\Http\Controllers\SuperAdmin\SettingController::class, 'update'])->name('settings.update');

        // User Management
        Route::get('users/generate-id/{role}', [App\Http\Controllers\SuperAdmin\UserController::class, 'generateNextId'])->name('users.generate-id');
        Route::post('users/{user}/toggle-status', [App\Http\Controllers\SuperAdmin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::get('users/bulk-email', [App\Http\Controllers\SuperAdmin\UserController::class, 'showBulkEmailForm'])->name('users.bulk-email');
        Route::post('users/bulk-email/parse', [App\Http\Controllers\SuperAdmin\UserController::class, 'parseBulkEmailInput'])->name('users.bulk-email.parse');
        Route::post('users/bulk-email/send', [App\Http\Controllers\SuperAdmin\UserController::class, 'sendBulkEmails'])->name('users.bulk-email.send');
        Route::resource('users', App\Http\Controllers\SuperAdmin\UserController::class);

        // Location Management
        Route::resource('locations', App\Http\Controllers\SuperAdmin\LocationController::class);
        Route::post('locations/{location}/delete', [App\Http\Controllers\SuperAdmin\LocationController::class, 'destroy'])->name('locations.delete');

        // Holiday Management
        Route::resource('holidays', App\Http\Controllers\SuperAdmin\HolidayController::class)->only(['index', 'store', 'destroy']);

        // Attendance Monitoring (hapus + backup)
        Route::get('/attendance/deleted-backups', [App\Http\Controllers\SuperAdmin\AttendanceMonitoringController::class, 'deletedBackups'])->name('attendance.deleted-backups');
        Route::post('/attendance/deleted-backups/{deletedBackup}/restore', [App\Http\Controllers\SuperAdmin\AttendanceMonitoringController::class, 'restore'])->name('attendance.restore');
        Route::get('/attendance/recap', fn () => redirect()->route('super-admin.attendance.index'))->name('attendance.recap');
        Route::get('/attendance', [App\Http\Controllers\SuperAdmin\AttendanceMonitoringController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/{attendance}/delete', [App\Http\Controllers\SuperAdmin\AttendanceMonitoringController::class, 'destroy'])
            ->whereNumber('attendance')
            ->name('attendance.destroy');
        Route::post('/attendance/manual-checkin', [App\Http\Controllers\SuperAdmin\AttendanceMonitoringController::class, 'manualCheckin'])->name('attendance.manual-checkin');
        Route::post('/attendance/manual-checkout', [App\Http\Controllers\SuperAdmin\AttendanceMonitoringController::class, 'manualCheckout'])->name('attendance.manual-checkout');
        Route::get('/attendance/payment-history', [App\Http\Controllers\HRD\AttendanceMonitoringController::class, 'paymentHistory'])->name('attendance.payment-history');

        // Leave Approvals untuk Super Admin (Bisa hapus dll)
        Route::get('/leave-approvals', [App\Http\Controllers\PIC\LeaveApprovalController::class, 'index'])->name('leave-approvals.index');
        Route::delete('/leave-approvals/{leaveRequest}', [App\Http\Controllers\PIC\LeaveApprovalController::class, 'destroy'])->name('leave-approvals.destroy');

        // Sales Reports
        Route::get('/sales-reports', [App\Http\Controllers\Shared\SalesReportController::class, 'index'])->name('sales-reports.index');

        // Ramayana Stocks
        Route::get('/top-products', [App\Http\Controllers\Shared\TopProductController::class, 'index'])->name('top-products.index');
        Route::get('/ramayana-stocks', [App\Http\Controllers\SuperAdmin\RamayanaStockController::class, 'index'])->name('ramayana-stocks.index');
        Route::get('/ramayana-stocks/download-template', [App\Http\Controllers\SuperAdmin\RamayanaStockController::class, 'downloadTemplate'])->name('ramayana-stocks.download-template');
        Route::get('/ramayana-stocks/{user}', [App\Http\Controllers\SuperAdmin\RamayanaStockController::class, 'show'])->name('ramayana-stocks.show');
        Route::post('/ramayana-stocks/import', [App\Http\Controllers\SuperAdmin\RamayanaStockController::class, 'import'])->name('ramayana-stocks.import');

        // Incoming Stocks (Barang Masuk)
        Route::get('/ramayana-stocks/{user}/incoming/create', [App\Http\Controllers\SuperAdmin\RamayanaStockController::class, 'createIncoming'])->name('ramayana-stocks.incoming.create');
        Route::post('/ramayana-stocks/{user}/incoming', [App\Http\Controllers\SuperAdmin\RamayanaStockController::class, 'storeIncoming'])->name('ramayana-stocks.incoming.store');
        Route::get('/ramayana-stocks/{user}/incoming/history', [App\Http\Controllers\SuperAdmin\RamayanaStockController::class, 'incomingHistory'])->name('ramayana-stocks.incoming.history');
        Route::get('/ramayana-stocks/{user}/incoming/{incomingStock}', [App\Http\Controllers\SuperAdmin\RamayanaStockController::class, 'incomingDetail'])->name('ramayana-stocks.incoming.show');
        Route::delete('/ramayana-stocks/{user}/incoming/{incomingStock}', [App\Http\Controllers\SuperAdmin\RamayanaStockController::class, 'destroyIncoming'])->name('ramayana-stocks.incoming.destroy');
    });

    Route::prefix('pic')->name('pic.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\PIC\DashboardController::class, 'index'])->name('dashboard');

        // Leave Approvals
        Route::get('/leave-approvals', [App\Http\Controllers\PIC\LeaveApprovalController::class, 'index'])->name('leave-approvals.index');
        Route::put('/leave-approvals/{leaveRequest}/approve', [App\Http\Controllers\PIC\LeaveApprovalController::class, 'approve'])->name('leave-approvals.approve');
        Route::put('/leave-approvals/{leaveRequest}/reject', [App\Http\Controllers\PIC\LeaveApprovalController::class, 'reject'])->name('leave-approvals.reject');
        Route::delete('/leave-approvals/{leaveRequest}', [App\Http\Controllers\PIC\LeaveApprovalController::class, 'destroy'])->name('leave-approvals.destroy');

        // Divisional Employees
        Route::get('/employees', [App\Http\Controllers\PIC\EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/employees/{user}', [App\Http\Controllers\PIC\EmployeeController::class, 'show'])->name('employees.show');
        Route::get('/reports', [App\Http\Controllers\PIC\ReportController::class, 'index'])->name('reports.index');
    });

    Route::prefix('pic-ramayana')->name('pic_ramayana.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\PIC\DashboardController::class, 'index'])->name('dashboard');

        // Leave Approvals
        Route::get('/leave-approvals', [App\Http\Controllers\PIC\LeaveApprovalController::class, 'index'])->name('leave-approvals.index');
        Route::put('/leave-approvals/{leaveRequest}/approve', [App\Http\Controllers\PIC\LeaveApprovalController::class, 'approve'])->name('leave-approvals.approve');
        Route::put('/leave-approvals/{leaveRequest}/reject', [App\Http\Controllers\PIC\LeaveApprovalController::class, 'reject'])->name('leave-approvals.reject');
        Route::delete('/leave-approvals/{leaveRequest}', [App\Http\Controllers\PIC\LeaveApprovalController::class, 'destroy'])->name('leave-approvals.destroy');

        // Divisional Employees
        Route::get('/employees', [App\Http\Controllers\PIC\EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/employees/{user}', [App\Http\Controllers\PIC\EmployeeController::class, 'show'])->name('employees.show');
        Route::get('/reports', [App\Http\Controllers\PIC\ReportController::class, 'index'])->name('reports.index');
        
        // Ramayana Stocks
        Route::get('/ramayana-stocks', [App\Http\Controllers\PIC\RamayanaStockController::class, 'index'])->name('ramayana-stocks.index');
        Route::get('/ramayana-stocks/{id}', [App\Http\Controllers\PIC\RamayanaStockController::class, 'show'])->name('ramayana-stocks.show');

        // Sales Reports
        Route::get('/top-products', [App\Http\Controllers\Shared\TopProductController::class, 'index'])->name('top-products.index');
        Route::get('/sales-reports', [App\Http\Controllers\Shared\SalesReportController::class, 'index'])->name('sales-reports.index');
    });

    Route::prefix('hrd')->name('hrd.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\HRD\DashboardController::class, 'index'])->name('dashboard');

        // Attendance Monitoring
        Route::get('/attendance', [App\Http\Controllers\HRD\AttendanceMonitoringController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/recap', [App\Http\Controllers\HRD\AttendanceMonitoringController::class, 'recap'])->name('attendance.recap');
        Route::post('/attendance/recap/pay', [App\Http\Controllers\HRD\AttendanceMonitoringController::class, 'payMealAllowance'])->name('attendance.pay-meal-allowance');
        Route::post('/attendance/recap/toggle-payment', [App\Http\Controllers\HRD\AttendanceMonitoringController::class, 'togglePayment'])->name('attendance.toggle-payment');
        Route::post('/attendance/recap/save-history', [App\Http\Controllers\HRD\AttendanceMonitoringController::class, 'saveHistory'])->name('attendance.save-history');
        Route::get('/attendance/payment-history', [App\Http\Controllers\HRD\AttendanceMonitoringController::class, 'paymentHistory'])->name('attendance.payment-history');
        Route::post('/attendance/payment-history/{payment}/update', [App\Http\Controllers\HRD\AttendanceMonitoringController::class, 'updatePaymentHistory'])->name('attendance.payment-history.update');
        Route::post('/attendance/payment-history/{payment}/delete', [App\Http\Controllers\HRD\AttendanceMonitoringController::class, 'deletePaymentHistory'])->name('attendance.payment-history.delete');
        Route::get('/scanner', [App\Http\Controllers\HRD\ScannerController::class, 'index'])->name('scanner.index');
    });

    Route::prefix('karyawan')->name('karyawan.')->group(function () {
        Route::get('/dashboard', function () {
            $settings = \App\Models\Setting::all()->pluck('value', 'key');
            $top3Sales = \App\Models\SalesInput::query()
                ->where('type', 'sale')
                ->whereYear('date', now()->year)
                ->whereMonth('date', now()->month)
                ->whereHas('user', fn($q) => $q->whereHas('role', fn($r) => $r->where('slug', 'karyawan_ramayana')))
                ->selectRaw('user_id, SUM(qty) as total_qty')
                ->groupBy('user_id')
                ->orderByDesc('total_qty')
                ->with('user.location')
                ->limit(3)
                ->get();
            return view('karyawan.dashboard', compact('settings', 'top3Sales'));
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

    Route::prefix('karyawan-ramayana')->name('karyawan_ramayana.')->group(function () {
        Route::get('/dashboard', function () {
            $settings = \App\Models\Setting::all()->pluck('value', 'key');
            $top3Sales = \App\Models\SalesInput::query()
                ->where('type', 'sale')
                ->whereYear('date', now()->year)
                ->whereMonth('date', now()->month)
                ->whereHas('user', fn($q) => $q->whereHas('role', fn($r) => $r->where('slug', 'karyawan_ramayana')))
                ->selectRaw('user_id, SUM(qty) as total_qty')
                ->groupBy('user_id')
                ->orderByDesc('total_qty')
                ->with('user.location')
                ->limit(3)
                ->get();
            return view('karyawan.dashboard', compact('settings', 'top3Sales'));
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

        // Sales & Stock Input
        Route::resource('sales', App\Http\Controllers\KaryawanRamayana\SalesController::class);
        
        Route::get('stocks', [App\Http\Controllers\KaryawanRamayana\StockController::class, 'index'])->name('stocks.index');
    });

    // Save FCM Token (untuk semua user yang login)
    Route::post('/save-fcm-token', function (\Illuminate\Http\Request $request) {
        $request->validate(['token' => 'required|string']);
        $user = \Illuminate\Support\Facades\Auth::user();
        $user->update(['fcm_token' => $request->token]);
        return response()->json(['success' => true]);
    })->name('save-fcm-token');
});

// API Route for Database Sync (Exempted from CSRF)
Route::post('/api/sync-users', [\App\Http\Controllers\Api\SyncController::class, 'syncUsers']);

// Temporary route to run migrations and seeders on live server
Route::get('/run-migrations', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'RamayanaRoleDivisionSeeder', '--force' => true]);
    return 'Migrations and Seeder completed successfully!';
});

// ✅ Cron Trigger untuk Smart Notification
// Dipanggil OTOMATIS setiap 5 menit oleh:
//   1. Laravel Scheduler (crontab di server) - via schedule:run setiap 1 menit
//   2. UptimeRobot/layanan eksternal - ping URL ini setiap 5 menit sebagai fallback
Route::get('/cron/smart-notification', function (\Illuminate\Http\Request $request) {
    // Proteksi dengan secret key
    if ($request->query('key') !== env('SYNC_SECRET_KEY')) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $startTime = microtime(true);

    $options = [];
    if ($request->query('test')) {
        $options['--test'] = true;
    }

    \Illuminate\Support\Facades\Artisan::call('notify:smart-attendance', $options);
    $output = \Illuminate\Support\Facades\Artisan::output();

    $elapsed = round((microtime(true) - $startTime) * 1000);

    // Log setiap kali cron dipanggil agar bisa dipantau
    \Illuminate\Support\Facades\Log::info('[CRON] smart-notification triggered', [
        'time'    => now()->toDateTimeString(),
        'source'  => $request->query('source', 'external'),
        'elapsed' => $elapsed . 'ms',
    ]);

    return response()->json([
        'success' => true,
        'output'  => trim($output) ?: 'No output.',
        'time'    => now()->toDateTimeString(),
        'elapsed' => $elapsed . 'ms',
        'server'  => gethostname(),
    ]);
});
