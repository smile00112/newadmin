<?php

namespace Webkul\Newsletters\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Webkul\Newsletters\Models\RegistrationRequest;
use Illuminate\Support\Facades\Validator;

class LandingPageController
{
    /**
     * Display the landing page.
     */
    public function index()
    {
        return view('newsletters::landing.index');
    }

    /**
     * Store registration request.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:255',
            'plan' => 'nullable|string|in:start,pro,corporate',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Пожалуйста, заполните все обязательные поля корректно.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            RegistrationRequest::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'plan' => $request->plan,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Спасибо! Ваша заявка успешно отправлена. Мы свяжемся с вами в ближайшее время.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при отправке заявки. Пожалуйста, попробуйте позже.'
            ], 500);
        }
    }
}

