<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Webkul\RestApi\Http\Controllers\V1\Shop\ShopController;
use Webkul\RestApi\Http\Requests\Auth\SmsAuthRequest;
use Webkul\RestApi\Http\Requests\Auth\WhatsAppAuthRequest;
use Webkul\RestApi\Http\Requests\Auth\TelegramAuthRequest;
use Webkul\RestApi\Http\Requests\Auth\VerificationRequest;
use Webkul\RestApi\Http\Requests\Auth\TokenResetRequest;
use Webkul\RestApi\Http\Resources\V1\Shop\Customer\CustomerResource;
use Webkul\RestApi\Services\Auth\VerificationService;
use Webkul\RestApi\Services\Auth\SmsService;
use Webkul\RestApi\Services\Auth\WhatsAppService;
use Webkul\RestApi\Services\Auth\TelegramService;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\Core\Repositories\SubscribersListRepository;
use Event;

class MultiChannelAuthController extends ShopController
{
    /**
     * Controller instance.
     *
     * @return void
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CustomerGroupRepository $customerGroupRepository,
        protected SubscribersListRepository $subscriptionRepository,
        protected VerificationService $verificationService,
        protected SmsService $smsService,
        protected WhatsAppService $whatsappService,
        protected TelegramService $telegramService
    ) {}

    /**
     * Initiate SMS authentication.
     */
    public function initiateSmsAuth(SmsAuthRequest $request): Response
    {

        $phoneNumber = $request->country_code . $request->phone_number;

        if (!$this->smsService->validatePhoneNumber($phoneNumber)) {
            return response([
                'message' => 'Invalid phone number format.',
            ], 400);
        }

        // Check if customer exists with this phone number
        $customer = $this->customerRepository->where('phone', $phoneNumber)->first();

        if (!$customer) {
            return response([
                'message' => 'No account found with this phone number.',
            ], 404);
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
     * Initiate WhatsApp authentication.
     */
    public function initiateWhatsAppAuth(WhatsAppAuthRequest $request): Response
    {
        $phoneNumber = $request->country_code . $request->phone_number;

        if (!$this->whatsappService->validatePhoneNumber($phoneNumber)) {
            return response([
                'message' => 'Invalid phone number format.',
            ], 400);
        }

        // Check if customer exists with this phone number
        $customer = $this->customerRepository->where('phone', $phoneNumber)->first();

        if (!$customer) {
            return response([
                'message' => 'No account found with this phone number.',
            ], 404);
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
     * Initiate Telegram authentication.
     */
    public function initiateTelegramAuth(TelegramAuthRequest $request): Response
    {
        if (!$this->telegramService->validateTelegramId($request->telegram_id)) {
            return response([
                'message' => 'Invalid Telegram ID format.',
            ], 400);
        }

        // Check if customer exists with this Telegram ID
        $customer = $this->customerRepository->where('telegram_id', $request->telegram_id)->first();

        if (!$customer) {
            return response([
                'message' => 'No account found with this Telegram ID.',
            ], 404);
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
     * Verify code and authenticate user.
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

        // Find customer based on channel
        $customer = null;
        switch ($verifiedData['channel']) {
            case 'sms':
            case 'whatsapp':
                $customer = $this->customerRepository->where('phone', $verifiedData['identifier'])->first();
                break;
            case 'telegram':
                $customer = $this->customerRepository->where('telegram_id', $verifiedData['identifier'])->first();
                break;
        }

        if (!$customer) {
            return response([
                'message' => 'Customer not found.',
            ], 404);
        }

        // Prevent multiple token creation
        $customer->tokens()->delete();

        // Create new token
        $token = $customer->createToken($request->device_name ?? 'mobile-app', ['role:customer'])->plainTextToken;

        // Dispatch login event
        Event::dispatch('customer.after.login', $customer);

        // Clean up verification data
        $this->verificationService->cleanupVerification($request->verification_token);

        return response([
            'data' => new CustomerResource($customer),
            'message' => 'Authentication successful.',
            'token' => $token,
        ]);
    }

    /**
     * Reset token via different channels.
     */
    public function resetToken(TokenResetRequest $request): Response
    {
        $customer = null;
        $channel = $request->reset_method;

        switch ($channel) {
            case 'sms':
            case 'whatsapp':
                if (!$request->phone_number) {
                    return response([
                        'message' => 'Phone number is required for SMS/WhatsApp reset.',
                    ], 400);
                }
                $customer = $this->customerRepository->where('phone', $request->phone_number)->first();
                break;
            case 'telegram':
                if (!$request->telegram_id) {
                    return response([
                        'message' => 'Telegram ID is required for Telegram reset.',
                    ], 400);
                }
                $customer = $this->customerRepository->where('telegram_id', $request->telegram_id)->first();
                break;
            case 'email':
                if (!$request->email) {
                    return response([
                        'message' => 'Email is required for email reset.',
                    ], 400);
                }
                $customer = $this->customerRepository->where('email', $request->email)->first();
                break;
        }

        if (!$customer) {
            return response([
                'message' => 'No account found with the provided information.',
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
     * Verify reset code and generate new token.
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

        // Find customer based on verified data
        $customer = null;
        switch ($verifiedData['channel']) {
            case 'sms':
            case 'whatsapp':
                $customer = $this->customerRepository->where('phone', $verifiedData['identifier'])->first();
                break;
            case 'telegram':
                $customer = $this->customerRepository->where('telegram_id', $verifiedData['identifier'])->first();
                break;
            case 'email':
                $customer = $this->customerRepository->where('email', $verifiedData['identifier'])->first();
                break;
        }

        if (!$customer) {
            return response([
                'message' => 'Customer not found.',
            ], 404);
        }

        // Revoke all existing tokens
        $customer->tokens()->delete();

        // Create new token
        $token = $customer->createToken('token-reset', ['role:customer'])->plainTextToken;

        // Clean up verification data
        $this->verificationService->cleanupVerification($request->verification_token);

        return response([
            'data' => new CustomerResource($customer),
            'message' => 'Token reset successful.',
            'token' => $token,
        ]);
    }
}
