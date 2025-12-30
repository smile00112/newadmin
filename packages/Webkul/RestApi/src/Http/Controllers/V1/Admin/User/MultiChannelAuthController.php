<?php

namespace Webkul\RestApi\Http\Controllers\V1\Admin\User;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Webkul\RestApi\Http\Controllers\V1\Admin\AdminController;
use Webkul\RestApi\Http\Requests\Auth\SmsAuthRequest;
use Webkul\RestApi\Http\Requests\Auth\WhatsAppAuthRequest;
use Webkul\RestApi\Http\Requests\Auth\TelegramAuthRequest;
use Webkul\RestApi\Http\Requests\Auth\VerificationRequest;
use Webkul\RestApi\Http\Requests\Auth\TokenResetRequest;
use Webkul\RestApi\Http\Resources\V1\Admin\Settings\UserResource;
use Webkul\RestApi\Services\Auth\VerificationService;
use Webkul\RestApi\Services\Auth\SmsService;
use Webkul\RestApi\Services\Auth\WhatsAppService;
use Webkul\RestApi\Services\Auth\TelegramService;
use Webkul\User\Repositories\AdminRepository;

class MultiChannelAuthController extends AdminController
{
    /**
     * Controller instance.
     *
     * @return void
     */
    public function __construct(
        protected AdminRepository $adminRepository,
        protected VerificationService $verificationService,
        protected SmsService $smsService,
        protected WhatsAppService $whatsappService,
        protected TelegramService $telegramService
    ) {}

    /**
     * Initiate SMS authentication for admin.
     */
    public function initiateSmsAuth(SmsAuthRequest $request): Response
    {
        $phoneNumber = $request->country_code . $request->phone_number;
        
        if (!$this->smsService->validatePhoneNumber($phoneNumber)) {
            return response([
                'message' => 'Invalid phone number format.',
            ], 400);
        }

        // Use firstOrCreate to prevent duplicate entries
        $email = $phoneNumber . '@test.com';
        
        $admin = $this->adminRepository->firstOrCreate(
            ['phone' => $phoneNumber],
            [
                'name' => 'Аноним',
                'email' => $email,
                'password' => bcrypt(Str::random(16)),
                'role_id' => 1, // Default role
                'status' => 1,
            ]
        );

        // Если админ был найден, но email отличается и email не занят другим админом, обновить email
        if ($admin->email !== $email && !$this->adminRepository->where('email', $email)->where('id', '!=', $admin->id)->exists()) {
            $admin->email = $email;
            $admin->save();
        }

        // Generate verification code
        $verificationData = $this->verificationService->generateVerificationCode($phoneNumber, 'sms');
        
        // Send SMS
        if (!$this->smsService->sendVerificationCode($phoneNumber, $verificationData['verification_code'])) {
            return response([
                'message' => 'Failed to send verification code.',
            ], 500);
        }

        return response([
            'message' => 'Verification code sent to your phone.',
            'verification_token' => $verificationData['verification_token'],
            'expires_in' => $verificationData['expires_in'],
        ]);
    }

    /**
     * Initiate WhatsApp authentication for admin.
     */
    public function initiateWhatsAppAuth(WhatsAppAuthRequest $request): Response
    {
        $phoneNumber = $request->country_code . $request->phone_number;
        
        if (!$this->whatsappService->validatePhoneNumber($phoneNumber)) {
            return response([
                'message' => 'Invalid phone number format.',
            ], 400);
        }

        // Use firstOrCreate to prevent duplicate entries
        $email = $phoneNumber . '@test.com';
        
        $admin = $this->adminRepository->firstOrCreate(
            ['phone' => $phoneNumber],
            [
                'name' => 'Аноним',
                'email' => $email,
                'password' => bcrypt(Str::random(16)),
                'role_id' => 1, // Default role
                'status' => 1,
            ]
        );

        // Если админ был найден, но email отличается и email не занят другим админом, обновить email
        if ($admin->email !== $email && !$this->adminRepository->where('email', $email)->where('id', '!=', $admin->id)->exists()) {
            $admin->email = $email;
            $admin->save();
        }

        // Generate verification code
        $verificationData = $this->verificationService->generateVerificationCode($phoneNumber, 'whatsapp');
        
        // Send WhatsApp message
        if (!$this->whatsappService->sendVerificationCode($phoneNumber, $verificationData['verification_code'])) {
            return response([
                'message' => 'Failed to send verification code.',
            ], 500);
        }

        return response([
            'message' => 'Verification code sent to your WhatsApp.',
            'verification_token' => $verificationData['verification_token'],
            'expires_in' => $verificationData['expires_in'],
        ]);
    }

    /**
     * Initiate Telegram authentication for admin.
     */
    public function initiateTelegramAuth(TelegramAuthRequest $request): Response
    {
        if (!$this->telegramService->validateTelegramId($request->telegram_id)) {
            return response([
                'message' => 'Invalid Telegram ID format.',
            ], 400);
        }

        // Use firstOrCreate to prevent duplicate entries
        $email = $request->telegram_id . '@test.com';
        
        $admin = $this->adminRepository->firstOrCreate(
            ['telegram_id' => $request->telegram_id],
            [
                'name' => 'Аноним',
                'email' => $email,
                'password' => bcrypt(Str::random(16)),
                'role_id' => 1, // Default role
                'status' => 1,
            ]
        );

        // Если админ был найден, но email отличается и email не занят другим админом, обновить email
        if ($admin->email !== $email && !$this->adminRepository->where('email', $email)->where('id', '!=', $admin->id)->exists()) {
            $admin->email = $email;
            $admin->save();
        }

        // Generate verification code
        $verificationData = $this->verificationService->generateVerificationCode($request->telegram_id, 'telegram');
        
        // Send Telegram message
        if (!$this->telegramService->sendVerificationCode($request->telegram_id, $verificationData['verification_code'])) {
            return response([
                'message' => 'Failed to send verification code.',
            ], 500);
        }

        return response([
            'message' => 'Verification code sent to your Telegram.',
            'verification_token' => $verificationData['verification_token'],
            'expires_in' => $verificationData['expires_in'],
        ]);
    }

    /**
     * Verify code and authenticate admin.
     */
    public function verifyAndAuthenticate(VerificationRequest $request): Response
    {
        if (!$this->verificationService->verifyCode($request->verification_token, $request->verification_code)) {
            return response([
                'message' => 'Invalid or expired verification code.',
            ], 400);
        }

        $verifiedData = $this->verificationService->getVerifiedData($request->verification_token);
        
        if (!$verifiedData) {
            return response([
                'message' => 'Verification session expired.',
            ], 400);
        }

        // Find admin based on channel
        $admin = null;
        switch ($verifiedData['channel']) {
            case 'sms':
            case 'whatsapp':
                $admin = $this->adminRepository->where('phone', $verifiedData['identifier'])->first();
                break;
            case 'telegram':
                $admin = $this->adminRepository->where('telegram_id', $verifiedData['identifier'])->first();
                break;
        }

        if (!$admin) {
            return response([
                'message' => 'Admin not found.',
            ], 404);
        }

        // Prevent multiple token creation
        $admin->tokens()->delete();

        // Create new token
        $token = $admin->createToken($request->device_name ?? 'admin-app', ['role:admin'])->plainTextToken;

        // Clean up verification data
        $this->verificationService->cleanupVerification($request->verification_token);

        return response([
            'data' => new UserResource($admin),
            'message' => 'Admin authentication successful.',
            'token' => $token,
        ]);
    }

    /**
     * Reset token via different channels for admin.
     */
    public function resetToken(TokenResetRequest $request): Response
    {
        $admin = null;
        $channel = $request->reset_method;

        switch ($channel) {
            case 'sms':
            case 'whatsapp':
                if (!$request->phone_number) {
                    return response([
                        'message' => 'Phone number is required for SMS/WhatsApp reset.',
                    ], 400);
                }
                $admin = $this->adminRepository->where('phone', $request->phone_number)->first();
                break;
            case 'telegram':
                if (!$request->telegram_id) {
                    return response([
                        'message' => 'Telegram ID is required for Telegram reset.',
                    ], 400);
                }
                $admin = $this->adminRepository->where('telegram_id', $request->telegram_id)->first();
                break;
            case 'email':
                if (!$request->email) {
                    return response([
                        'message' => 'Email is required for email reset.',
                    ], 400);
                }
                $admin = $this->adminRepository->where('email', $request->email)->first();
                break;
        }

        if (!$admin) {
            return response([
                'message' => 'No admin account found with the provided information.',
            ], 404);
        }

        // Generate verification code
        $identifier = $request->phone_number ?? $request->telegram_id ?? $request->email;
        $verificationData = $this->verificationService->generateVerificationCode($identifier, $channel);

        // Send verification code based on channel
        $sent = false;
        switch ($channel) {
            case 'sms':
                $sent = $this->smsService->sendVerificationCode($request->phone_number, $verificationData['verification_code']);
                break;
            case 'whatsapp':
                $sent = $this->whatsappService->sendVerificationCode($request->phone_number, $verificationData['verification_code']);
                break;
            case 'telegram':
                $sent = $this->telegramService->sendVerificationCode($request->telegram_id, $verificationData['verification_code']);
                break;
            case 'email':
                // TODO: Implement email verification
                $sent = true; // Placeholder
                break;
        }

        if (!$sent) {
            return response([
                'message' => 'Failed to send verification code.',
            ], 500);
        }

        return response([
            'message' => "Verification code sent via {$channel}.",
            'verification_token' => $verificationData['verification_token'],
            'expires_in' => $verificationData['expires_in'],
        ]);
    }

    /**
     * Verify reset code and generate new token for admin.
     */
    public function verifyResetAndGenerateToken(VerificationRequest $request): Response
    {
        if (!$this->verificationService->verifyCode($request->verification_token, $request->verification_code)) {
            return response([
                'message' => 'Invalid or expired verification code.',
            ], 400);
        }

        $verifiedData = $this->verificationService->getVerifiedData($request->verification_token);
        
        if (!$verifiedData) {
            return response([
                'message' => 'Verification session expired.',
            ], 400);
        }

        // Find admin based on verified data
        $admin = null;
        switch ($verifiedData['channel']) {
            case 'sms':
            case 'whatsapp':
                $admin = $this->adminRepository->where('phone', $verifiedData['identifier'])->first();
                break;
            case 'telegram':
                $admin = $this->adminRepository->where('telegram_id', $verifiedData['identifier'])->first();
                break;
            case 'email':
                $admin = $this->adminRepository->where('email', $verifiedData['identifier'])->first();
                break;
        }

        if (!$admin) {
            return response([
                'message' => 'Admin not found.',
            ], 404);
        }

        // Revoke all existing tokens
        $admin->tokens()->delete();

        // Create new token
        $token = $admin->createToken('admin-token-reset', ['role:admin'])->plainTextToken;

        // Clean up verification data
        $this->verificationService->cleanupVerification($request->verification_token);

        return response([
            'data' => new UserResource($admin),
            'message' => 'Admin token reset successful.',
            'token' => $token,
        ]);
    }
}
