<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\PrtgAlert;
use Illuminate\Support\Facades\Log;

class PrtgWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Verify the security token
        $configuredSecret = (string) config('services.prtg.webhook_secret');
        $providedToken = (string) $request->query('token');

        if ($configuredSecret === '' || ! hash_equals($configuredSecret, $providedToken)) {
            abort(401, 'Unauthorized');
        }
        
        // 2. Extract the JSON payload sent by PRTG
        $alertData = $request->validate([
            'sensor' => 'required|string',
            'device' => 'required|string',
            'status' => 'required|string',
            'message' => 'nullable|string',
            'date' => 'nullable|string',
        ]);

        $alertData = array_map(function ($value) {
            if (! is_string($value)) {
                return $value;
            }

            return iconv('UTF-8', 'UTF-8//IGNORE', $value) ?: '';
        }, $alertData);
        
        Log::info('PRTG Alert Received', $alertData);
        
        // 3. Notify every admin-level user so the alert is visible to the signed-in administrator.
        $admins = User::role(['admin', 'super-admin'])->get();
        foreach ($admins as $admin) {
            $admin->notify(new PrtgAlert($alertData));
        }
        
        return response()->json(['status' => 'success']);
    }
}