<?php

namespace Webkul\Shop\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Webkul\Customer\Models\Customer;
use Webkul\Shop\Http\Requests\Customer\LoginRequest;
use Webkul\Shop\Services\SmsService;
use Webkul\Shop\Services\InMemoryVerificationService;
use Webkul\Shop\Models\PhoneVerificationCode;

class CustomerController extends APIController
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Login Customer BY email and password
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        if (!auth()->guard('customer')->attempt($request->only(['email', 'password']))) {
            return response()->json([
                'message' => trans('shop::app.customers.login-form.invalid-credentials'),
            ], Response::HTTP_FORBIDDEN);
        }

        if (!auth()->guard('customer')->user()->status) {
            auth()->guard('customer')->logout();

            return response()->json([
                'message' => trans('shop::app.customers.login-form.not-activated'),
            ], Response::HTTP_FORBIDDEN);
        }

        if (!auth()->guard('customer')->user()->is_verified) {
            Cookie::queue(Cookie::make('enable-resend', 'true', 1));

            Cookie::queue(Cookie::make('email-for-resend', $request->get('email'), 1));

            auth()->guard('customer')->logout();

            return response()->json([
                'message' => trans('shop::app.customers.login-form.verify-first'),
            ], Response::HTTP_FORBIDDEN);
        }

        /**
         * Event passed to prepare cart after login.
         */
        Event::dispatch('customer.after.login', auth()->guard()->user());

        return response()->json([]);
    }

    /**
     * Send SMS verification code
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendSmsCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:15'
        ]);

        try {
            $result = $this->smsService->sendVerificationCode($request->phone);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'expires_in' => $result['expires_in'] ?? 300
            ], $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            \Log::error('SMS code sending failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify SMS code and login/register user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifySmsCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:15',
            'code' => 'required|string|size:6'
        ]);

        try {
            // Check if database table exists, otherwise use in-memory storage
            if (Schema::hasTable('phone_verification_codes')) {
                // Use database storage
                $isValid = PhoneVerificationCode::verifyCode($request->phone, $request->code);
            } else {
                // Use in-memory storage as fallback
                $isValid = InMemoryVerificationService::verifyCode($request->phone, $request->code);
            }

            if (!$isValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired verification code'
                ], 400);
            }
        } catch (\Exception $e) {
            \Log::error('SMS code verification failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage()
            ], 500);
        }

        // Find or create customer
        $customer = Customer::where('phone', $request->phone)->first();

        if (!$customer) {
            // Auto-register new customer
            $customer = Customer::create([
                'first_name' => 'Customer',
                'last_name' => 'User',
                'phone' => $request->phone,
                'email' => $request->phone . '@temp.com', // Temporary email
                'password' => Hash::make('temp_password_' . time()),
                'status' => 1,
                'is_verified' => 1,
                'customer_group_id' => 1, // Default customer group
                'channel_id' => 1, // Default channel
            ]);
        }

        // Check if customer is active
        if (!$customer->status) {
            return response()->json([
                'success' => false,
                'message' => 'Account is deactivated'
            ], 403);
        }

        // Create API token
        $token = $customer->createToken('api-token', ['*'], now()->addDays(30))->plainTextToken;

        // Dispatch login event
        Event::dispatch('customer.after.login', $customer);

        return response()->json([
            'success' => true,
            'message' => 'Authentication successful',
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 30 * 24 * 60 * 60, // 30 days in seconds
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email
            ]
        ]);
    }

    /**
     * Check token validity
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkToken(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid token'
            ], 401);
        }

        return response()->json([
            'valid' => true,
            'customer' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email
            ]
        ]);
    }

    /**
     * Refresh token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token'
            ], 401);
        }

        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        // Create new token
        $token = $user->createToken('api-token', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 30 * 24 * 60 * 60
        ]);
    }

    /**
     * Logout and revoke token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Login customer by phone and sms code (legacy method)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function auth_by_sms()
    {
        // This method is kept for backward compatibility
        // Use sendSmsCode and verifySmsCode instead
        return response()->json([
            'message' => 'This method is deprecated. Use sendSmsCode and verifySmsCode instead.'
        ], 400);
    }
}
