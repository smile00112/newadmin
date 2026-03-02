<?php

namespace Webkul\Admin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webkul\Admin\Services\AIAssistantService;

class AIAssistantController extends Controller
{
    /**
     * Handle chat message from user.
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array',
            'history.*.role' => 'required_with:history|string|in:user,assistant',
            'history.*.content' => 'required_with:history|string',
        ]);

        try {
            $aiAssistantService = app(AIAssistantService::class);
            
            // Prepare context with conversation history
            $context = [
                'history' => $request->input('history', []),
            ];
            
            $response = $aiAssistantService->processMessage(
                $request->input('message'),
                $context
            );

            return response()->json([
                'success' => true,
                'response' => $response['message'],
                'actions' => $response['actions'] ?? [],
            ]);
        } catch (\Exception $e) {
            \Log::error('AI Assistant Error: ' . $e->getMessage());
            
            // Check if it's an API key error
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'API') || str_contains($errorMessage, 'ключ') || str_contains($errorMessage, 'key')) {
                $errorMessage = $e->getMessage();
            } elseif (str_contains($errorMessage, 'insufficient_quota') || str_contains($errorMessage, 'credits') || str_contains($errorMessage, 'billing')) {
                $errorMessage = 'Недостаточно средств на аккаунте OpenAI. Пожалуйста, пополните баланс на platform.openai.com/account/billing';
            } else {
                $errorMessage = config('app.debug') 
                    ? $e->getMessage() 
                    : 'Произошла ошибка при обработке запроса. Проверьте настройки AI в Конфигурация → Magic AI.';
            }
            
            return response()->json([
                'success' => false,
                'response' => $errorMessage,
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
