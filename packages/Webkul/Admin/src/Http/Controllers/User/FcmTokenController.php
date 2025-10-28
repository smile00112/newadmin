<?php

namespace Webkul\Admin\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FcmTokenController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string'
        ]);

        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $admin->fcm_token = $request->fcm_token;
        $admin->save();

        \Log::info('FCM token saved', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'token_preview' => substr($request->fcm_token, 0, 20) . '...'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FCM token успешно обновлен',
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email
            ]
        ]);
    }
}
