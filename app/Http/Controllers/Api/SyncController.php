<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class SyncController extends Controller
{
    public function syncUsers(Request $request)
    {
        $secretKey = config('app.sync_secret_key', env('SYNC_SECRET_KEY'));
        
        if (!$secretKey || $request->header('X-Sync-Key') !== $secretKey) {
            return response()->json(['message' => 'Unauthorized. Invalid Sync Key.'], 401);
        }

        $users = $request->input('users');
        if (!is_array($users)) {
            return response()->json(['message' => 'Invalid payload format.'], 400);
        }

        $syncedCount = 0;

        foreach ($users as $userData) {
            if (!isset($userData['employee_id'])) {
                continue;
            }

            // Exclude ID to prevent primary key conflicts, let Eloquent handle update by employee_id
            $employeeId = $userData['employee_id'];
            unset($userData['id']);
            unset($userData['created_at']);
            unset($userData['updated_at']);

            User::updateOrCreate(
                ['employee_id' => $employeeId],
                $userData
            );

            $syncedCount++;
        }

        return response()->json([
            'message' => 'Sync successful',
            'synced_count' => $syncedCount
        ]);
    }
}
