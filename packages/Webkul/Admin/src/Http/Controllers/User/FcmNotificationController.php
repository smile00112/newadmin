<?php

namespace Webkul\Admin\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Webkul\Admin\Services\FcmNotificationService;

class FcmNotificationController extends Controller
{
    protected $fcmService;

    public function __construct(FcmNotificationService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Отправка тестового уведомления текущему пользователю
     */
    public function sendTest(Request $request)
    {
        $user = Auth::guard('admin')->user();

        if (!$user->fcm_token) {
            return response()->json([
                'success' => false,
                'message' => 'FCM токен не найден. Разрешите уведомления в браузере.'
            ], 400);
        }

        $title = $request->input('title', 'Тестовое уведомление');
        $body = $request->input('body', 'Это тестовое push-уведомление от Dolinger Admin');
        $data = [
            'type' => 'test',
            'timestamp' => now()->toISOString(),
            'user_id' => $user->id,
        ];

        $result = $this->fcmService->sendToDevice($user->fcm_token, $title, $body, $data);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Тестовое уведомление успешно отправлено!',
                'user' => $user->name,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Не удалось отправить уведомление. Проверьте логи.'
        ], 500);
    }

    /**
     * Отправка уведомления всем администраторам
     */
    public function sendToAll(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:500',
        ]);

        $title = $request->input('title');
        $body = $request->input('body');
        $data = [
            'type' => 'broadcast',
            'timestamp' => now()->toISOString(),
            'sender_id' => Auth::guard('admin')->id(),
        ];

        $results = $this->fcmService->sendToAllAdmins($title, $body, $data);

        $successCount = count(array_filter($results));
        $totalCount = count($results);

        return response()->json([
            'success' => true,
            'message' => "Уведомление отправлено {$successCount} из {$totalCount} администраторов",
            'results' => $results,
        ]);
    }

    /**
     * Страница тестирования FCM
     */
    public function testPage()
    {
        $user = Auth::guard('admin')->user();
        
        return view('admin::fcm.test', [
            'user' => $user,
            'hasFcmToken' => !empty($user->fcm_token),
        ]);
    }
}

