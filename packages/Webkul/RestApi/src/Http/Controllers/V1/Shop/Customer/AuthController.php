<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Customer;

use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Webkul\Core\Repositories\SubscribersListRepository;
use Webkul\Core\Rules\PhoneNumber;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\RestApi\Http\Resources\V1\Shop\Customer\CustomerResource;
use Webkul\RestApi\Services\Auth\CustomerTokenLogService;
use Webkul\Shop\Http\Requests\Customer\RegistrationRequest;

class AuthController extends CustomerController
{
    use SendsPasswordResetEmails;

    /**
     * Controller instance.
     *
     * @return void
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CustomerGroupRepository $customerGroupRepository,
        protected SubscribersListRepository $subscriptionRepository,
        protected CustomerTokenLogService $customerTokenLogService
    ) {}

    /**
     * Register the customer.
     */
    public function register(RegistrationRequest $registrationRequest): Response
    {
        Event::dispatch('customer.registration.before');

        $customer = $this->customerRepository->create([
            'first_name'        => $registrationRequest->first_name,
            'last_name'         => $registrationRequest->last_name,
            'email'             => $registrationRequest->email,
            'password'          => bcrypt($registrationRequest->password),
            'is_verified'       => 1,
            'channel_id'        => core()->getCurrentChannel()->id,
            'customer_group_id' => $this->customerGroupRepository->findOneWhere(['code' => 'general'])->id,
        ]);

        Event::dispatch('customer.registration.after', $customer);

        return response([
            'message' => trans('rest-api::app.shop.customer.accounts.create-success'),
        ]);
    }

    /**
     * Login the customer.
     */
    public function login(Request $request): Response
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (! EnsureFrontendRequestsAreStateful::fromFrontend($request)) {
            $request->validate([
                'device_name' => 'required',
            ]);

            $customer = $this->customerRepository->where('email', $request->email)->first();

            if (! $customer || ! Hash::check($request->password, $customer->password)) {
                throw ValidationException::withMessages([
                    'email' => trans('rest-api::app.shop.customer.accounts.error.credential-error'),
                ]);
            }

            /**
             * Preventing multiple token creation.
             */
            $customer->tokens()->delete();

            $tokenName = $request->device_name;
            $abilities = ['role:customer'];

            $plainTextToken = $customer->createToken($tokenName, $abilities)->plainTextToken;

            $this->customerTokenLogService->logToken(
                $customer,
                $tokenName,
                $abilities,
                null,
                $request
            );

            /**
             * Event passed to prepare cart after login.
             */
            Event::dispatch('customer.after.login', $customer);

            return response([
                'data'    => new CustomerResource($customer),
                'message' => trans('rest-api::app.shop.customer.accounts.logged-in-success'),
                'token'   => $plainTextToken,
            ]);

        }

        if (Auth::attempt($request->only(['email', 'password']))) {
            $request->session()->regenerate();

            return response([
                'data'    => new CustomerResource($this->resolveShopUser($request)),
                'message' => trans('rest-api::app.shop.customer.accounts.logged-in-success'),
            ]);
        }

        return response([
            'message' => trans('rest-api::app.shop.customer.accounts.error.invalid'),
        ], 401);
    }

    /**
     * Get details for current logged in customer.
     */
    public function get(Request $request): Response
    {
        $customer = $this->resolveShopUser($request);

        return response([
            'data' => new CustomerResource($customer),
        ]);
    }

    /**
     * Update the customer.
     */
    public function update(Request $request): Response
    {
        $customer = $this->resolveShopUser($request);

        // Получаем только переданные поля (исключаем служебные)
        $inputData = $request->except(['_method', '_token']);
        
        // Проверяем, что передано хотя бы одно поле для обновления
        if (empty($inputData)) {
            return response([
                'message' => trans('rest-api::app.shop.customer.accounts.error.no-fields-provided'),
                'errors' => ['general' => ['At least one field must be provided for update.']],
            ], 422);
        }

        $isPasswordChanged = false;

        // Валидируем только те поля, которые переданы
        $validationRules = [];
        
        if ($request->has('first_name')) {
            $validationRules['first_name'] = ['required'];
        }
        
        if ($request->has('last_name')) {
            $validationRules['last_name'] = ['required'];
        }
        
        if ($request->has('gender')) {
            $validationRules['gender'] = 'required|in:Other,Male,Female';
        }
        
        if ($request->has('date_of_birth')) {
            $validationRules['date_of_birth'] = 'date|before:today';
        }
        
        if ($request->has('email')) {
            $validationRules['email'] = 'email|unique:customers,email,'.$customer->id;
        }
        
        if ($request->has('phone')) {
            $validationRules['phone'] = ['required', new PhoneNumber, 'unique:customers,phone,'.$customer->id];
        }
        
        if ($request->has('current_password') || $request->has('new_password')) {
            $validationRules['new_password'] = 'confirmed|min:6|required_with:current_password';
            $validationRules['new_password_confirmation'] = 'required_with:new_password';
            $validationRules['current_password'] = 'required_with:new_password';
        }
        
        if ($request->hasFile('image')) {
            $validationRules['image'] = 'array';
            $validationRules['image.*'] = 'mimes:bmp,jpeg,jpg,png,webp';
        } elseif ($request->has('image')) {
            // Если изображение передано как массив данных (для удаления)
            $validationRules['image'] = 'array';
        }
        
        if ($request->has('subscribed_to_news_letter')) {
            $validationRules['subscribed_to_news_letter'] = 'nullable';
        }

        $request->validate($validationRules);

        // Используем только переданные поля
        $data = $inputData;

        // Обрабатываем подписку на рассылку только если поле передано
        if ($request->has('subscribed_to_news_letter')) {
            $data['subscribed_to_news_letter'] = $request->boolean('subscribed_to_news_letter');
        }

        // Обрабатываем изображение только если оно передано
        // Если изображение передано через multipart/form-data, оно будет обработано отдельно
        if (
            core()->getCurrentChannel()->theme === 'default'
            && ! $request->hasFile('image')
            && isset($data['image'])
            && empty($data['image'])
        ) {
            $data['image']['image_0'] = '';
        }

        // Обрабатываем пароль только если переданы все необходимые поля
        if (! empty($data['current_password']) && ! empty($data['new_password'])) {
            if (Hash::check($data['current_password'], $customer->password)) {
                $isPasswordChanged = true;
                $data['password'] = bcrypt($data['new_password']);
            } else {
                return response(['message' => trans('rest-api::app.shop.customer.accounts.error.password-mismatch')], 422);
            }
        }
        
        // Удаляем поля пароля из данных, если они не были использованы
        unset($data['current_password'], $data['new_password'], $data['new_password_confirmation']);

        Event::dispatch('customer.update.before');

        if ($customer = $this->customerRepository->update($data, $customer->id)) {
            if ($isPasswordChanged) {
                Event::dispatch('customer.password.update.after', $customer);
            }

            Event::dispatch('customer.update.after', $customer);

            // Обрабатываем подписку на рассылку только если поле передано
            if ($request->has('subscribed_to_news_letter')) {
                $email = $data['email'] ?? $customer->email;
                
                if ($request->boolean('subscribed_to_news_letter')) {
                    $subscription = $this->subscriptionRepository->firstOrNew(['email' => $email]);

                    if ($subscription->id) {
                        $this->subscriptionRepository->update([
                            'customer_id'   => $customer->id,
                            'is_subscribed' => 1,
                        ], $subscription->id);
                    } else {
                        $this->subscriptionRepository->create([
                            'email'         => $email,
                            'customer_id'   => $customer->id,
                            'channel_id'    => core()->getCurrentChannel()->id,
                            'is_subscribed' => 1,
                            'token'         => uniqid(),
                        ]);
                    }
                } else {
                    $subscription = $this->subscriptionRepository->findOneWhere(['email' => $email]);

                    if ($subscription) {
                        $this->subscriptionRepository->update([
                            'customer_id'   => $customer->id,
                            'is_subscribed' => 0,
                        ], $subscription->id);
                    }
                }
            }

            if ($request->hasFile('image')) {
                $this->customerRepository->uploadImages($data, $customer);
            } elseif (isset($data['image'])) {
                if (! empty($data['image'])) {
                    Storage::delete((string) $customer->image);
                }

                $customer->image = null;

                $customer->save();
            }

            return response([
                'data'    => new CustomerResource($customer),
                'message' => trans('rest-api::app.shop.customer.accounts.update-success'),
            ]);
        }

        return response(['message' => trans('rest-api::app.shop.customer.accounts.error.update-failed')]);
    }

    /**
     * Logout the customer.
     */
    public function logout(Request $request): Response
    {
        $customer = $this->resolveShopUser($request);

        ! EnsureFrontendRequestsAreStateful::fromFrontend($request)
            ? $customer->tokens()->delete()
            : auth()->guard('customer')->logout();

        Event::dispatch('customer.after.logout', $customer->id);

        return response([
            'message' => trans('rest-api::app.shop.customer.accounts.logged-out-success'),
        ]);
    }

    /**
     * Send Reset Password Link.
     */
    public function forgotPassword(Request $request): Response
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $response = Password::broker('customers')->sendResetLink($request->only(['email']));

        return response(
            ['message' => __($response)],
            $response == Password::RESET_LINK_SENT ? 200 : 400
        );
    }
}
