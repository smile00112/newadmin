<?php

use Illuminate\Support\Str;
use Webkul\ExternalPayments\Contracts\PaymentProviderAdapterInterface;
use Webkul\ExternalPayments\Models\ExternalSystem;
use Webkul\ExternalPayments\Models\ExternalSystemPaymentProvider;
use Webkul\ExternalPayments\Services\PaymentProviderRegistry;
use Webkul\Newsletters\Models\AccountTopup;
use Webkul\Newsletters\Models\Company;
use Webkul\Newsletters\Models\CompanyAccount;
use Webkul\TochkaPayment\Events\PaymentFailed;
use Webkul\TochkaPayment\Models\TochkaPaymentHistory;
use Webkul\User\Models\Admin;

use function Pest\Laravel\post;

it('creates pending owner topup and redirects to provider payment page', function (): void {
    $company = Company::query()->create([
        'name' => 'Topup Company',
        'slug' => 'topup-company-'.Str::lower(Str::random(8)),
        'is_active' => true,
    ]);

    CompanyAccount::query()->firstOrCreate(
        ['company_id' => $company->id],
        ['balance' => 0]
    );

    ExternalSystem::query()->create([
        'name' => 'ExtPay',
        'api_token' => Str::random(64),
        'is_active' => true,
        'company_id' => $company->id,
    ]);

    $externalSystem = ExternalSystem::query()->where('company_id', $company->id)->firstOrFail();

    ExternalSystemPaymentProvider::query()->create([
        'external_system_id' => $externalSystem->id,
        'payment_provider' => 'tochka',
        'is_default' => true,
    ]);

    $adapter = new class implements PaymentProviderAdapterInterface
    {
        public function createPayment(array $data): array
        {
            return [
                'payment_id' => '5551',
                'order_id' => 'order-1',
                'payment_url' => 'https://pay.test/redirect/5551',
            ];
        }

        public function getMinAmount(): float
        {
            return 1.0;
        }
    };

    $registry = \Mockery::mock(PaymentProviderRegistry::class);
    $registry->shouldReceive('has')->with('tochka')->once()->andReturnTrue();
    $registry->shouldReceive('get')->with('tochka')->once()->andReturn($adapter);
    $this->app->instance(PaymentProviderRegistry::class, $registry);

    $owner = Admin::factory()->create([
        'company_id' => $company->id,
        'role_id' => 1,
    ]);

    $this->loginAsAdmin($owner);

    post(route('admin.newsletters.account.topup'), [
        'amount' => 120.50,
        'notes' => 'test topup',
    ])->assertRedirect('https://pay.test/redirect/5551');

    $this->assertDatabaseHas('account_topups', [
        'account_id' => CompanyAccount::query()->where('company_id', $company->id)->value('id'),
        'amount' => '120.50',
        'status' => AccountTopup::STATUS_PENDING,
        'provider_key' => 'tochka',
        'provider_payment_id' => '5551',
    ]);

    $this->assertDatabaseHas('company_accounts', [
        'company_id' => $company->id,
        'balance' => '0.00',
    ]);
});

it('fails owner topup when company payment provider is not configured', function (): void {
    $company = Company::query()->create([
        'name' => 'No Provider Company',
        'slug' => 'no-provider-company-'.Str::lower(Str::random(8)),
        'is_active' => true,
    ]);

    CompanyAccount::query()->firstOrCreate(
        ['company_id' => $company->id],
        ['balance' => 0]
    );

    $owner = Admin::factory()->create([
        'company_id' => $company->id,
        'role_id' => 1,
    ]);

    $this->loginAsAdmin($owner);

    post(route('admin.newsletters.account.topup'), [
        'amount' => 100,
    ])->assertRedirect(route('admin.newsletters.account.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('account_topups', [
        'status' => AccountTopup::STATUS_FAILED,
        'account_id' => CompanyAccount::query()->where('company_id', $company->id)->value('id'),
    ]);

    $this->assertDatabaseHas('company_accounts', [
        'company_id' => $company->id,
        'balance' => '0.00',
    ]);
});

it('credits owner balance only once on duplicated successful webhook events', function (): void {
    $company = Company::query()->create([
        'name' => 'Webhook Company',
        'slug' => 'webhook-company-'.Str::lower(Str::random(8)),
        'is_active' => true,
    ]);

    $account = CompanyAccount::query()->firstOrCreate(
        ['company_id' => $company->id],
        ['balance' => 0]
    );

    $topup = AccountTopup::query()->create([
        'account_id' => $account->id,
        'type' => AccountTopup::TYPE_TOPUP,
        'amount' => 200.00,
        'status' => AccountTopup::STATUS_PENDING,
        'provider_key' => 'tochka',
        'provider_payment_id' => '9001',
    ]);

    event('external_payments.payment.success', [(object) ['id' => 9001]]);
    event('external_payments.payment.success', [(object) ['id' => 9001]]);

    $account->refresh();
    $topup->refresh();

    expect((float) $account->balance)->toBe(200.0);
    expect($topup->status)->toBe(AccountTopup::STATUS_PAID);
    expect($topup->paid_at)->not->toBeNull();
});

it('marks pending owner topup as failed and does not credit balance on failed payment event', function (): void {
    $company = Company::query()->create([
        'name' => 'Failed Event Company',
        'slug' => 'failed-event-company-'.Str::lower(Str::random(8)),
        'is_active' => true,
    ]);

    $account = CompanyAccount::query()->firstOrCreate(
        ['company_id' => $company->id],
        ['balance' => 0]
    );

    $payment = TochkaPaymentHistory::query()->create([
        'company_id' => $company->id,
        'amount' => 300.00,
        'status' => TochkaPaymentHistory::STATUS_FAILED,
    ]);

    $topup = AccountTopup::query()->create([
        'account_id' => $account->id,
        'type' => AccountTopup::TYPE_TOPUP,
        'amount' => 300.00,
        'status' => AccountTopup::STATUS_PENDING,
        'provider_key' => 'tochka',
        'provider_payment_id' => (string) $payment->id,
    ]);

    event(new PaymentFailed($payment));

    $account->refresh();
    $topup->refresh();

    expect($topup->status)->toBe(AccountTopup::STATUS_FAILED);
    expect((float) $account->balance)->toBe(0.0);
});
