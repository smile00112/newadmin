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

        $user = Auth::user();
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json([
            'message' => 'FCM token успешно обновлен'
        ]);
    }
}
