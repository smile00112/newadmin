<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.admin-accounts.title') }}
    </x-slot:title>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.admin-accounts.title') }}
        </p>
    </div>

    <div class="table-responsive mt-4">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('newsletters::app.admin.companies.name') }}</th>
                    <th>{{ __('newsletters::app.admin.account.current-balance') }}</th>
                    <th>{{ __('newsletters::app.admin.account.last-topup') }}</th>
                    <th>{{ __('newsletters::app.common.actions.title') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $company)
                    <tr>
                        <td>{{ $company->name }}</td>
                        <td>
                            <span class="{{ $company->account->balance <= 0 ? 'text-red-600' : 'text-gray-800' }}">
                                {{ number_format($company->account->balance, 2) }}
                            </span>
                        </td>
                        <td>
                            @if($company->account->topups->count() > 0)
                                {{ $company->account->topups->first()->created_at->format('Y-m-d H:i') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <button
                                type="button"
                                class="primary-button"
                                onclick="openTopupModal({{ $company->id }}, '{{ $company->name }}')"
                            >
                                {{ __('newsletters::app.admin.account.topup-button') }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">
                            <p>{{ __('newsletters::app.common.messages.no_data') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Topup Modal -->
    <div id="topupModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-900 rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    {{ __('newsletters::app.admin.account.topup-title') }} - <span id="companyName"></span>
                </h3>
                <button onclick="closeTopupModal()" class="text-gray-500 hover:text-gray-700">
                    <span class="icon-close"></span>
                </button>
            </div>

            <form id="topupForm" method="POST">
                @csrf
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        {{ __('newsletters::app.admin.account.amount') }}
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
                        {{ __('newsletters::app.admin.account.notes') }}
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

                <div class="flex items-center gap-x-2.5 mt-4">
                    <button type="submit" class="primary-button">
                        {{ __('newsletters::app.admin.account.topup-button') }}
                    </button>
                    <button type="button" onclick="closeTopupModal()" class="secondary-button">
                        {{ __('newsletters::app.common.actions.cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openTopupModal(companyId, companyName) {
            document.getElementById('companyName').textContent = companyName;
            document.getElementById('topupForm').action = '{{ route("admin.newsletters.admin-accounts.topup", ":id") }}'.replace(':id', companyId);
            document.getElementById('topupModal').classList.remove('hidden');
            document.getElementById('topupModal').classList.add('flex');
        }

        function closeTopupModal() {
            document.getElementById('topupModal').classList.add('hidden');
            document.getElementById('topupModal').classList.remove('flex');
            document.getElementById('topupForm').reset();
        }
    </script>
</x-admin::layouts>

