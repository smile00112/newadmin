<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.owners.edit-title') }}
    </x-slot:title>

    <div class="flex items-center justify-between mb-6">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.owners.edit-title') }}
        </p>
        <a href="{{ route('admin.newsletters.owners.index') }}" class="secondary-button">
            {{ __('newsletters::app.common.actions.back') }}
        </a>
    </div>

    <form method="POST" action="{{ route('admin.newsletters.owners.update', $owner->id) }}" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-6">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('newsletters::app.admin.owners.name') }}
                    <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name', $owner->name) }}"
                    required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('name') border-red-500 dark:border-red-500 @enderror"
                >
                @error('name')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('newsletters::app.admin.owners.email') }}
                    <span class="text-red-500">*</span>
                </label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email', $owner->email) }}"
                    required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('email') border-red-500 dark:border-red-500 @enderror"
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div>
                <input type="hidden" name="status" value="0">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <div class="relative">
                        <input
                            type="checkbox"
                            name="status"
                            value="1"
                            {{ old('status', $owner->status) ? 'checked' : '' }}
                            class="sr-only peer"
                        >
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('newsletters::app.admin.owners.status') }}
                        <span class="text-gray-500 dark:text-gray-400">({{ __('newsletters::app.admin.owners.active') }})</span>
                    </span>
                </label>
            </div>

            <!-- Company (Read-only) -->
            @if($owner->company)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.owners.company') }}
                    </label>
                    <input
                        type="text"
                        value="{{ $owner->company->name }}"
                        disabled
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                    >
                </div>
            @endif

            <!-- Balance (Read-only) -->
            @if($owner->company && $owner->company->account)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.owners.balance') }}
                    </label>
                    <input
                        type="text"
                        value="{{ number_format($owner->company->account->balance, 2) }}"
                        disabled
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                    >
                </div>
            @endif
        </div>

        <div class="mt-6 flex items-center gap-x-2.5">
            <button type="submit" class="primary-button">
                {{ __('newsletters::app.common.actions.save') }}
            </button>
            @if($owner->company && $owner->company->account)
                <button type="button" onclick="openTopupModal()" class="secondary-button">
                    {{ __('newsletters::app.admin.owners.topup-button') }}
                </button>
            @endif
            <a href="{{ route('admin.newsletters.owners.index') }}" class="secondary-button">
                {{ __('newsletters::app.common.actions.cancel') }}
            </a>
        </div>
    </form>

    <!-- Transaction History -->
    @if($owner->company && $owner->company->account && isset($transactions))
        <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('newsletters::app.admin.owners.topup-history') }}
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('newsletters::app.admin.account.topup-date') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('newsletters::app.admin.owners.transaction-type') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('newsletters::app.admin.account.amount') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('newsletters::app.admin.account.admin') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('newsletters::app.admin.account.notes') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($transactions as $transaction)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d H:i') : $transaction->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    @if($transaction->type === 'topup')
                                        <span class="text-green-600 dark:text-green-400">{{ __('newsletters::app.admin.owners.transaction-type-topup') }}</span>
                                    @else
                                        <span class="text-red-600 dark:text-red-400">{{ __('newsletters::app.admin.owners.transaction-type-deduction') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ $transaction->type === 'topup' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $transaction->type === 'topup' ? '+' : '-' }}{{ number_format($transaction->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $transaction->admin ? $transaction->admin->name : '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                    {{ $transaction->notes ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('newsletters::app.common.messages.no_data') }}
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Topup Modal -->
    @if($owner->company && $owner->company->account)
        <div id="topupModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[10001]">
            <div class="flex min-h-screen items-end justify-center p-4 sm:items-center sm:p-0">
                <div class="relative top-10 mx-auto p-5 border border-gray-200 dark:border-gray-700 w-11/12 shadow-lg rounded-md bg-white dark:bg-gray-800 max-h-[90vh] overflow-y-auto md:w-3/4 lg:w-2/3">

                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ __('newsletters::app.admin.owners.topup-title') }} - {{ $owner->name }} ({{ $owner->company->name }})
                        </h3>
                        <button onclick="closeTopupModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <span class="icon-cross text-2xl"></span>
                        </button>
                    </div>

                    <div class="mt-3">
                        <form id="topupForm" method="POST" action="{{ route('admin.newsletters.owners.topup', $owner->id) }}" class="space-y-4">
                            @csrf
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                {{ __('newsletters::app.admin.owners.topup-amount') }}
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="number"
                                name="amount"
                                step="0.01"
                                min="0.01"
                                rules="required|numeric|min:0.01"
                                :label="trans('newsletters::app.admin.owners.topup-amount')"
                                :placeholder="trans('newsletters::app.admin.account.amount-placeholder')"
                            />

                            <x-admin::form.control-group.error control-name="amount" />
                        </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            {{ __('newsletters::app.admin.owners.transaction-date') }}
                        </x-admin::form.control-group.label>

                        <input
                            type="datetime-local"
                            name="transaction_date"
                            id="transaction_date"
                            value="{{ old('transaction_date', date('Y-m-d\TH:i')) }}"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('transaction_date') border-red-500 dark:border-red-500 @enderror"
                        />

                        <x-admin::form.control-group.error control-name="transaction_date" />
                    </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <div class="relative">
                                    <input
                                        type="checkbox"
                                        id="create_deductions"
                                        name="create_deductions"
                                        value="1"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    >
                                    <label for="create_deductions" class="text-sm font-medium text-gray-700 dark:text-gray-300"> {{ __('newsletters::app.admin.owners.create-deductions') }} </label>
                                </div>

                            </label>
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                {{ __('newsletters::app.admin.owners.topup-notes') }}
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="textarea"
                                name="notes"
                                rules="max:500"
                                :label="trans('newsletters::app.admin.owners.topup-notes')"
                                :placeholder="trans('newsletters::app.admin.account.notes-placeholder')"
                            />

                            <x-admin::form.control-group.error control-name="notes" />
                        </x-admin::form.control-group>
                        </form>
                    </div>

                    <div class="flex justify-end gap-x-2 pt-4 mt-6 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" onclick="closeTopupModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                            {{ __('newsletters::app.common.actions.cancel') }}
                        </button>
                        <button type="submit" form="topupForm" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('newsletters::app.common.actions.save') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        function openTopupModal() {
            const modal = document.getElementById('topupModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function closeTopupModal() {
            const modal = document.getElementById('topupModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        // Close modal on outside click
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('topupModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeTopupModal();
                    }
                });
            }
        });
    </script>
</x-admin::layouts>

