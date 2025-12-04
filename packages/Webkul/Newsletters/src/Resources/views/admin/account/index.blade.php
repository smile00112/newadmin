<x-admin::layouts>
    <x-slot:title>
        @lang('newsletters::app.admin.account.title')
    </x-slot:title>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('newsletters::app.admin.account.title')
        </p>
    </div>

    <!-- Balance Card -->
    <div class="mt-4 box-shadow rounded bg-white p-6 dark:bg-gray-900">
        <div class="mb-4">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                @lang('newsletters::app.admin.account.current-balance')
            </p>
            <p class="text-3xl font-bold text-gray-800 dark:text-white">
                {{ number_format($account->balance, 2) }}
            </p>
            @if($account->balance <= 0)
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                    @lang('newsletters::app.admin.account.insufficient-balance-warning')
                </p>
            @endif
        </div>
    </div>

    <!-- Topup Form -->
    <div class="mt-4 box-shadow rounded bg-white p-6 dark:bg-gray-900">
        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
            @lang('newsletters::app.admin.account.topup-title')
        </p>

        <x-admin::form
            :action="route('admin.newsletters.account.topup')"
            method="POST"
        >
            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    @lang('newsletters::app.admin.account.amount')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="number"
                    name="amount"
                    step="0.01"
                    min="0.01"
                    rules="required|numeric|min:0.01"
                    :label="trans('newsletters::app.admin.account.amount')"
                    :placeholder="trans('newsletters::app.admin.account.amount-placeholder')"
                />

                <x-admin::form.control-group.error control-name="amount" />
            </x-admin::form.control-group>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('newsletters::app.admin.account.notes')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="textarea"
                    name="notes"
                    rules="max:500"
                    :label="trans('newsletters::app.admin.account.notes')"
                    :placeholder="trans('newsletters::app.admin.account.notes-placeholder')"
                />

                <x-admin::form.control-group.error control-name="notes" />
            </x-admin::form.control-group>

            <div class="flex items-center gap-x-2.5">
                <button type="submit" class="primary-button">
                    @lang('newsletters::app.admin.account.topup-button')
                </button>
            </div>
        </x-admin::form>
    </div>

    <!-- Topup History -->
    <div class="mt-4 box-shadow rounded bg-white p-6 dark:bg-gray-900">
        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
            @lang('newsletters::app.admin.account.topup-history')
        </p>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>@lang('newsletters::app.admin.account.topup-date')</th>
                        <th>@lang('newsletters::app.admin.account.amount')</th>
                        <th>@lang('newsletters::app.admin.account.admin')</th>
                        <th>@lang('newsletters::app.admin.account.notes')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topups as $topup)
                        <tr>
                            <td>{{ $topup->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ number_format($topup->amount, 2) }}</td>
                            <td>{{ $topup->admin ? $topup->admin->name : '-' }}</td>
                            <td>{{ $topup->notes ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">
                                <p>@lang('newsletters::app.common.messages.no_data')</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($topups->hasPages())
            <div class="mt-4">
                {{ $topups->links() }}
            </div>
        @endif
    </div>
</x-admin::layouts>

