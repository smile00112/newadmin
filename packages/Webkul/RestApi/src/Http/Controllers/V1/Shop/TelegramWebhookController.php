<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\RestApi\Http\Controllers\V1\Shop\ShopController;
use Webkul\RestApi\Services\Auth\TelegramService;
use Webkul\RestApi\Services\Auth\VerificationService;
use Event;

class TelegramWebhookController extends ShopController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CustomerGroupRepository $customerGroupRepository,
        protected TelegramService $telegramService,
        protected VerificationService $verificationService
    ) {}

    /**
     * Handle incoming Telegram webhook.
     */
    public function handleWebhook(Request $request): Response
    {
        $update = $request->all();

        Log::info('Telegram webhook received', ['update' => $update]);

        // Verify this is a valid Telegram update
        if (!isset($update['message'])) {
            return response(['status' => 'ok']);
        }

        $message = $update['message'];
        $telegramId = (string) ($message['from']['id'] ?? null);

        if (!$telegramId) {
            Log::warning('Telegram webhook: no user ID in message');
            return response(['status' => 'ok']);
        }

        // Handle /start command
        if (isset($message['text']) && str_starts_with($message['text'], '/start')) {
            return $this->handleStartCommand($telegramId, $message);
        }

        // Handle shared contact
        if (isset($message['contact'])) {
            return $this->handleSharedContact($telegramId, $message['contact']);
        }

        return response(['status' => 'ok']);
    }

    /**
     * Handle /start command - show contact request button.
     */
    protected function handleStartCommand(string $telegramId, array $message): Response
    {
        Log::info("Telegram /start command from: {$telegramId}");

        // Check if user already has telegram_id linked
        $existingCustomer = $this->customerRepository->where('telegram_id', $telegramId)->first();

        if ($existingCustomer) {
            // User already registered, send verification code
            $this->sendVerificationCodeToUser($existingCustomer, $telegramId);

            return response(['status' => 'ok']);
        }

        // New user - show contact request keyboard
        $this->telegramService->sendContactRequestKeyboard($telegramId);

        return response(['status' => 'ok']);
    }

    /**
     * Handle shared contact - save telegram_id and send verification code.
     */
    protected function handleSharedContact(string $telegramId, array $contact): Response
    {
        Log::info("Telegram contact shared", ['telegram_id' => $telegramId, 'contact' => $contact]);

        // Get phone number from contact
        $phoneNumber = $contact['phone_number'] ?? null;

        if (!$phoneNumber) {
            Log::warning('Telegram webhook: no phone number in contact');
            $this->telegramService->sendMessage($telegramId, 'Не удалось получить номер телефона. Попробуйте еще раз.');
            return response(['status' => 'ok']);
        }

        // Normalize phone number (remove + and leading zeros if needed)
        $phoneNumber = $this->normalizePhoneNumber($phoneNumber);

        // Find customer by phone
        $customer = $this->customerRepository->where('phone', $phoneNumber)->first();

        if (!$customer) {
            // Try to find with different phone formats
            $customer = $this->findCustomerByPhone($phoneNumber);
        }

        if (!$customer) {
            // Create new customer with this phone and telegram_id
            $customer = $this->createCustomerFromTelegram($phoneNumber, $telegramId, $contact);
        } else {
            // Update existing customer with telegram_id
            $customer->telegram_id = $telegramId;
            $customer->save();

            Log::info("Updated customer {$customer->id} with telegram_id: {$telegramId}");
        }

        // Send verification code
        $this->sendVerificationCodeToUser($customer, $telegramId);

        return response(['status' => 'ok']);
    }

    /**
     * Normalize phone number to standard format.
     */
    protected function normalizePhoneNumber(string $phoneNumber): string
    {
        // Remove all non-digit characters
        $digits = preg_replace('/[^0-9]/', '', $phoneNumber);

        // If starts with 8, replace with 7 (Russian format)
        if (strlen($digits) === 11 && str_starts_with($digits, '8')) {
            $digits = '7' . substr($digits, 1);
        }

        return $digits;
    }

    /**
     * Try to find customer by various phone formats.
     */
    protected function findCustomerByPhone(string $phoneNumber): ?object
    {
        // Try exact match
        $customer = $this->customerRepository->where('phone', $phoneNumber)->first();

        if ($customer) {
            return $customer;
        }

        // Try with + prefix
        $customer = $this->customerRepository->where('phone', '+' . $phoneNumber)->first();

        if ($customer) {
            return $customer;
        }

        // Try without country code (last 10 digits)
        if (strlen($phoneNumber) > 10) {
            $shortPhone = substr($phoneNumber, -10);
            $customer = $this->customerRepository->where('phone', 'LIKE', '%' . $shortPhone)->first();
        }

        return $customer;
    }

    /**
     * Create new customer from Telegram contact.
     */
    protected function createCustomerFromTelegram(string $phoneNumber, string $telegramId, array $contact): object
    {
        $firstName = $contact['first_name'] ?? 'Аноним';
        $lastName = $contact['last_name'] ?? '';
        $email = $phoneNumber . '@telegram.user';

        Event::dispatch('customer.registration.before');

        $customer = $this->customerRepository->create([
            'first_name'        => $firstName,
            'last_name'         => $lastName ?: 'Аноним',
            'email'             => $email,
            'phone'             => $phoneNumber,
            'telegram_id'       => $telegramId,
            'password'          => bcrypt(Str::random(16)),
            'is_verified'       => 1,
            'channel_id'        => core()->getCurrentChannel()->id,
            'customer_group_id' => $this->customerGroupRepository->findOneWhere(['code' => 'general'])->id ?? 1,
        ]);

        Event::dispatch('customer.registration.after', $customer);

        Log::info("Created new customer {$customer->id} from Telegram", [
            'phone'       => $phoneNumber,
            'telegram_id' => $telegramId,
        ]);

        return $customer;
    }

    /**
     * Send verification code to user via Telegram.
     */
    protected function sendVerificationCodeToUser(object $customer, string $telegramId): void
    {
        // Generate verification code
        $verificationData = $this->verificationService->generateVerificationCode($telegramId, 'telegram');

        // Send code via Telegram
        $sent = $this->telegramService->sendVerificationCode(
            $telegramId,
            $verificationData['verification_code']
        );

        if ($sent) {
            Log::info("Verification code sent to telegram_id: {$telegramId}");

            // Also send info message
            $this->telegramService->sendMessage(
                $telegramId,
                "Код действителен {$verificationData['expires_in']} секунд. Введите его в приложении для завершения авторизации."
            );
        } else {
            Log::error("Failed to send verification code to telegram_id: {$telegramId}");
            $this->telegramService->sendMessage($telegramId, 'Произошла ошибка при отправке кода. Попробуйте позже.');
        }
    }
}
