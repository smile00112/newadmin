<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Support\Facades\Cache;
use Webkul\RestApi\Http\Controllers\V1\Shop\ShopController;
use Webkul\RestApi\Http\Requests\Auth\SmsAuthRequest;
use Webkul\RestApi\Http\Requests\Auth\WhatsAppAuthRequest;
use Webkul\RestApi\Http\Requests\Auth\TelegramAuthRequest;
use Webkul\RestApi\Http\Requests\Auth\TelegramInitiateRequest;
use Webkul\RestApi\Http\Requests\Auth\VerificationRequest;
use Webkul\RestApi\Http\Requests\Auth\TokenResetRequest;
use Webkul\RestApi\Http\Resources\V1\Shop\Customer\CustomerResource;
use Webkul\RestApi\Services\Auth\VerificationService;
use Webkul\RestApi\Services\Auth\SmsService;
use Webkul\RestApi\Services\Auth\WhatsAppService;
use Webkul\RestApi\Services\Auth\TelegramService;
use Webkul\RestApi\Services\Auth\CustomerTokenLogService;
use Webkul\RestApi\Repositories\AuthChannelSettingRepository;
use Webkul\RestApi\Jobs\SendVerificationCodeJob;
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
        protected TelegramService $telegramService,
        protected AuthChannelSettingRepository $authChannelSettingRepository,
        protected CustomerTokenLogService $customerTokenLogService
    ) {
        $this->authChannelSettingRepository->preloadAllAuthChannels();
    }

    /**
     * Get cached default customer group ID.
     */
    protected function getDefaultCustomerGroupId(): int
    {
        return Cache::remember('customer_group_general_id', 3600, function () {
            return $this->customerGroupRepository->findOneWhere(['code' => 'general'])->id ?? 1;
        });
    }

    /**
     * Initiate SMS authentication.
     */
    public function initiateSmsAuth(SmsAuthRequest $request): Response
    {
        // Check if SMS channel is enabled
        if (!$this->authChannelSettingRepository->isChannelEnabled('sms')) {
            return response([
                'message' => trans('rest-api::app.auth_channels.errors.channel-disabled', ['channel' => 'SMS']),
            ], 403);
        }

        $phoneNumber = $request->country_code . $request->phone_number;

        if (!$this->smsService->validatePhoneNumber($phoneNumber)) {
            return response([
                'message' => 'Invalid phone number format.',
            ], 400);
        }

        // Use firstOrCreate to prevent duplicate entries
        $email = $phoneNumber . '@test.com';

        $customer = $this->customerRepository->firstOrCreate(
            ['phone' => $phoneNumber],
            [
                'first_name' => 'Аноним',
                'last_name' => 'Аноним',
                'email' => $email,
                'password' => bcrypt(Str::random(16)),
                'is_verified' => 1,
                'channel_id' => core()->getCurrentChannel()->id,
                'customer_group_id' => $this->getDefaultCustomerGroupId(),
            ]
        );

        // Если клиент был найден, но email отличается и email не занят другим клиентом, обновить email
        if ($customer->email !== $email && !$this->customerRepository->where('email', $email)->where('id', '!=', $customer->id)->exists()) {
            $customer->email = $email;
            $customer->save();
        }

        if ($customer->wasRecentlyCreated) {
            Event::dispatch('customer.registration.after', $customer);
        }

        // Generate verification code
        $verificationData = $this->verificationService->generateVerificationCode($phoneNumber, 'sms');

        // Send SMS asynchronously
        SendVerificationCodeJob::dispatch('sms', $phoneNumber, $verificationData['verification_code'], core()->getCurrentChannelCode());

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
        // Check if WhatsApp channel is enabled
        if (!$this->authChannelSettingRepository->isChannelEnabled('whatsapp')) {
            return response([
                'message' => trans('rest-api::app.auth_channels.errors.channel-disabled', ['channel' => 'WhatsApp']),
            ], 403);
        }

        $phoneNumber = $request->country_code . $request->phone_number;

        if (!$this->whatsappService->validatePhoneNumber($phoneNumber)) {
            return response([
                'message' => 'Invalid phone number format.',
            ], 400);
        }

        // Use firstOrCreate to prevent duplicate entries
        $email = $phoneNumber . '@test.com';

        $customer = $this->customerRepository->firstOrCreate(
            ['phone' => $phoneNumber],
            [
                'first_name' => 'Аноним',
                'last_name' => 'Аноним',
                'email' => $email,
                'password' => bcrypt(Str::random(16)),
                'is_verified' => 1,
                'channel_id' => core()->getCurrentChannel()->id,
                'customer_group_id' => $this->getDefaultCustomerGroupId(),
            ]
        );

        // Если клиент был найден, но email отличается и email не занят другим клиентом, обновить email
        if ($customer->email !== $email && !$this->customerRepository->where('email', $email)->where('id', '!=', $customer->id)->exists()) {
            $customer->email = $email;
            $customer->save();
        }

        if ($customer->wasRecentlyCreated) {
            Event::dispatch('customer.registration.after', $customer);
        }

        // Generate verification code
        $verificationData = $this->verificationService->generateVerificationCode($phoneNumber, 'whatsapp');

        // Send WhatsApp message asynchronously
        SendVerificationCodeJob::dispatch('whatsapp', $phoneNumber, $verificationData['verification_code'], core()->getCurrentChannelCode());

        return response([
            'message' => 'Verification code sent to your WhatsApp.',
            'verification_token' => $verificationData['verification_token'],
            'expires_in' => $verificationData['expires_in'],
        ]);
    }

    /**
     * Initiate Telegram authentication (new flow with phone number).
     */
    public function initiateTelegramAuth(TelegramInitiateRequest $request): Response
    {
        // Check if Telegram channel is enabled
        if (!$this->authChannelSettingRepository->isChannelEnabled('telegram')) {
            return response([
                'message' => trans('rest-api::app.auth_channels.errors.channel-disabled', ['channel' => 'Telegram']),
            ], 403);
        }

        $phoneNumber = $request->getFullPhoneNumber();

        // Find customer by phone number
        $customer = $this->customerRepository->where('phone', $phoneNumber)->first();

        // If customer doesn't exist, create one
        if (!$customer) {
            $email = $phoneNumber . '@telegram.user';

            $customer = $this->customerRepository->create([
                'first_name'        => 'Аноним',
                'last_name'         => 'Аноним',
                'email'             => $email,
                'phone'             => $phoneNumber,
                'password'          => bcrypt(Str::random(16)),
                'is_verified'       => 1,
                'channel_id'        => core()->getCurrentChannel()->id,
                'customer_group_id' => $this->getDefaultCustomerGroupId(),
            ]);

            Event::dispatch('customer.registration.after', $customer);
        }

        // Check if customer has telegram_id
        if (!$customer->telegram_id) {
            // User needs to register in Telegram bot first
            $botUrl = $this->telegramService->getBotUrl();

            if (!$botUrl) {
                return response([
                    'message' => 'Telegram bot is not configured.',
                ], 500);
            }

            // Add start parameter with phone hash for tracking
            $startParam = base64_encode($phoneNumber);
            $botUrlWithParam = $botUrl . '?start=' . $startParam;

            return response([
                'need_telegram_registration' => true,
                'bot_url'                    => $botUrlWithParam,
                'message'                    => trans('rest-api::app.auth_channels.telegram.need-registration'),
            ]);
        }

        // Customer has telegram_id - send verification code
        $verificationData = $this->verificationService->generateVerificationCode($customer->telegram_id, 'telegram');

        // Send Telegram message asynchronously
        SendVerificationCodeJob::dispatch('telegram', $customer->telegram_id, $verificationData['verification_code'], core()->getCurrentChannelCode());

        return response([
            'need_telegram_registration' => false,
            'bot_url'                    => '',
            'message'                    => trans('rest-api::app.auth_channels.telegram.code-sent'),
            'verification_token'         => $verificationData['verification_token'],
            'expires_in'                 => $verificationData['expires_in'],
        ]);
    }

    /**
     * Initiate Telegram authentication (legacy - by telegram_id).
     */
    public function initiateTelegramAuthLegacy(TelegramAuthRequest $request): Response
    {
        // Check if Telegram channel is enabled
        if (!$this->authChannelSettingRepository->isChannelEnabled('telegram')) {
            return response([
                'message' => trans('rest-api::app.auth_channels.errors.channel-disabled', ['channel' => 'Telegram']),
            ], 403);
        }

        if (!$this->telegramService->validateTelegramId($request->telegram_id)) {
            return response([
                'message' => 'Invalid Telegram ID format.',
            ], 400);
        }

        // Use firstOrCreate to prevent duplicate entries
        $email = $request->telegram_id . '@test.com';

        $customer = $this->customerRepository->firstOrCreate(
            ['telegram_id' => $request->telegram_id],
            [
                'first_name'        => 'Аноним',
                'last_name'         => 'Аноним',
                'email'             => $email,
                'password'          => bcrypt(Str::random(16)),
                'is_verified'       => 1,
                'channel_id'        => core()->getCurrentChannel()->id,
                'customer_group_id' => $this->getDefaultCustomerGroupId(),
            ]
        );

        // Если клиент был найден, но email отличается и email не занят другим клиентом, обновить email
        if ($customer->email !== $email && !$this->customerRepository->where('email', $email)->where('id', '!=', $customer->id)->exists()) {
            $customer->email = $email;
            $customer->save();
        }

        if ($customer->wasRecentlyCreated) {
            Event::dispatch('customer.registration.after', $customer);
        }

        // Generate verification code
        $verificationData = $this->verificationService->generateVerificationCode($request->telegram_id, 'telegram');

        // Send Telegram message asynchronously
        SendVerificationCodeJob::dispatch('telegram', $request->telegram_id, $verificationData['verification_code'], core()->getCurrentChannelCode());

        return response([
            'message'            => 'Verification code sent to your Telegram.',
            'verification_token' => $verificationData['verification_token'],
            'expires_in'         => $verificationData['expires_in'],
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
        $query = match ($verifiedData['channel']) {
            'sms', 'whatsapp' => $this->customerRepository->where('phone', $verifiedData['identifier']),
            'telegram'        => $this->customerRepository->where('telegram_id', $verifiedData['identifier']),
            default           => null,
        };
        $customer = $query?->with('group')->first();

        if (!$customer) {
            return response([
                'message' => 'Customer not found.',
            ], 404);
        }

        // Prevent multiple token creation
        $customer->tokens()->delete();

        $tokenName = $request->device_name ?? 'mobile-app';
        $abilities = ['role:customer'];

        // Create new token
        $token = $customer->createToken($tokenName, $abilities)->plainTextToken;

        $this->customerTokenLogService->logToken(
            $customer,
            $tokenName,
            $abilities,
            null,
            $token,
            $request
        );

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

        // Check if channel is enabled (except email which doesn't have settings yet)
        if (in_array($channel, ['sms', 'whatsapp', 'telegram']) && !$this->authChannelSettingRepository->isChannelEnabled($channel)) {
            $channelNames = ['sms' => 'SMS', 'whatsapp' => 'WhatsApp', 'telegram' => 'Telegram'];
            return response([
                'message' => trans('rest-api::app.auth_channels.errors.channel-disabled', ['channel' => $channelNames[$channel]]),
            ], 403);
        }

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

        // Send verification code asynchronously
        if ($channel !== 'email') {
            SendVerificationCodeJob::dispatch($channel, $identifier, $verificationData['verification_code'], core()->getCurrentChannelCode());
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
        $query = match ($verifiedData['channel']) {
            'sms', 'whatsapp' => $this->customerRepository->where('phone', $verifiedData['identifier']),
            'telegram'        => $this->customerRepository->where('telegram_id', $verifiedData['identifier']),
            'email'           => $this->customerRepository->where('email', $verifiedData['identifier']),
            default           => null,
        };
        $customer = $query?->with('group')->first();

        if (!$customer) {
            return response([
                'message' => 'Customer not found.',
            ], 404);
        }

        // Revoke all existing tokens
        $customer->tokens()->delete();

        $tokenName = 'token-reset';
        $abilities = ['role:customer'];

        // Create new token
        $token = $customer->createToken($tokenName, $abilities)->plainTextToken;

        $this->customerTokenLogService->logToken(
            $customer,
            $tokenName,
            $abilities,
            null,
            $token,
            $request
        );

        // Clean up verification data
        $this->verificationService->cleanupVerification($request->verification_token);

        return response([
            'data' => new CustomerResource($customer),
            'message' => 'Token reset successful.',
            'token' => $token,
        ]);
    }
}
