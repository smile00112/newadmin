<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.companies.edit-title') }}
    </x-slot:title>

    <form method="POST" action="{{ route('admin.newsletters.companies.update', $company->id) }}">
        @csrf
        @method('PUT')

        <div class="flex items-center justify-between">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                {{ __('newsletters::app.admin.companies.edit-title') }}
            </p>

            <div class="flex items-center gap-x-2.5">
                <a href="{{ route('admin.newsletters.companies.index') }}" class="secondary-button">
                    {{ __('newsletters::app.common.actions.cancel') }}
                </a>
                <button type="submit" class="primary-button">
                    {{ __('newsletters::app.common.actions.save') }}
                </button>
            </div>
        </div>

        <div class="p-3 mt-4 box-shadow rounded bg-white p-6 dark:bg-gray-900">
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.companies.name') }}
                        <span class="text-red-600 dark:text-red-400">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name', $company->name) }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.companies.slug') }}
                    </label>
                    <input
                        type="text"
                        name="slug"
                        id="slug"
                        value="{{ old('slug', $company->slug) }}"
                        placeholder="{{ __('newsletters::app.admin.companies.slug-placeholder') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors"
                    >
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.companies.slug-help') }}</p>
                    @error('slug')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.companies.description') }}
                    </label>
                    <textarea
                        name="description"
                        id="description"
                        rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors resize-y"
                    >{{ old('description', $company->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            {{ old('is_active', $company->is_active) ? 'checked' : '' }}
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-400 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                        >
                        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('newsletters::app.admin.companies.is_active') }}
                        </span>
                    </label>
                </div>

                <!-- Balance (Read-only) -->
                @if($company->account)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.account.current-balance') }}
                        </label>
                        <div class="flex items-center gap-x-2.5">
                            <input
                                type="text"
                                value="{{ number_format($company->account->balance, 2) }}"
                                disabled
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                            >
                            @php
                                $admin = auth()->guard('admin')->user();
                                $isSuperAdmin = $admin && !$admin->company_id;
                            @endphp
                            @if($isSuperAdmin)
                                <button
                                    type="button"
                                    onclick="openTopupModal({{ $company->id }}, '{{ $company->name }}')"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors"
                                >
                                    {{ __('newsletters::app.admin.account.topup-button') }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </form>

    <!-- Topup Modal -->
    @php
        $admin = auth()->guard('admin')->user();
        $isSuperAdmin = $admin && !$admin->company_id;
    @endphp
    @if($isSuperAdmin)
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
    @endif

    <!-- Owners Section -->
    <div class="mt-6">
        <div class="flex items-center justify-between mb-4">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                {{ __('newsletters::app.admin.owners.title') }}
            </p>
            <button
                type="button"
                onclick="openCreateOwnerModal()"
                class="primary-button"
            >
                {{ __('newsletters::app.admin.owners.create-button') }}
            </button>
        </div>

        <div class="p-3 mt-4 box-shadow rounded bg-white p-6 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.owners.name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.owners.email') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.owners.role') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.owners.status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.owners.created_at') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.common.actions.title') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($owners as $owner)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $owner->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $owner->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $owner->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $owner->role ? $owner->role->name : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $owner->status ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                                        {{ $owner->status ? __('newsletters::app.admin.owners.active') : __('newsletters::app.admin.owners.inactive') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $owner->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center gap-x-2.5">
                                        <a href="{{ route('admin.newsletters.owners.edit', $owner->id) }}"
                                           class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                           title="{{ __('admin::app.datagrid.edit') }}">
                                            <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-edit"></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.owners.no-owners') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Owner Modal -->
    <div id="createOwnerModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-900 rounded-lg p-5 max-w-2xl lg:w-1/3 z-[10003] mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    {{ __('newsletters::app.admin.owners.create-title') }}
                </h3>
                <button onclick="closeCreateOwnerModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <span class="icon-close"></span>
                </button>
            </div>

            <form id="createOwnerForm" method="POST" action="{{ route('admin.newsletters.owners.store') }}">
                @csrf
                <input type="hidden" name="company_id" value="{{ $company->id }}">
                <input type="hidden" name="company_option" value="existing">
                <input type="hidden" name="redirect_to_company" value="1">

                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="modal_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.owners.name') }}
                            <span class="text-red-600 dark:text-red-400">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="modal_name"
                            value="{{ old('name') }}"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors"
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="modal_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.owners.email') }}
                            <span class="text-red-600 dark:text-red-400">*</span>
                        </label>
                        <input
                            type="email"
                            name="email"
                            id="modal_email"
                            value="{{ old('email') }}"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="modal_role_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.owners.role') }}
                            <span class="text-red-600 dark:text-red-400">*</span>
                        </label>
                        <select
                            name="role_id"
                            id="modal_role_id"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors"
                        >
                            <option value="">{{ __('newsletters::app.admin.owners.select-role') }}</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                    @if($role->description)
                                        - {{ $role->description }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="modal_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.owners.password') }}
                            <span class="text-red-600 dark:text-red-400">*</span>
                        </label>
                        <input
                            type="password"
                            name="password"
                            id="modal_password"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors"
                        >
                        @error('password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label for="modal_password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.owners.password-confirmation') }}
                            <span class="text-red-600 dark:text-red-400">*</span>
                        </label>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="modal_password_confirmation"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors"
                        >
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                name="status"
                                value="1"
                                {{ old('status', true) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-400 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                            >
                            <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('newsletters::app.admin.owners.status') }}
                            </span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5 mt-6">
                    <button type="submit" class="primary-button">
                        {{ __('newsletters::app.common.actions.save') }}
                    </button>
                    <button type="button" onclick="closeCreateOwnerModal()" class="secondary-button">
                        {{ __('newsletters::app.common.actions.cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateOwnerModal() {
            document.getElementById('createOwnerModal').classList.remove('hidden');
            document.getElementById('createOwnerModal').classList.add('flex');
        }

        function closeCreateOwnerModal() {
            document.getElementById('createOwnerModal').classList.add('hidden');
            document.getElementById('createOwnerModal').classList.remove('flex');
            document.getElementById('createOwnerForm').reset();
        }

        // Close modal on outside click
        document.getElementById('createOwnerModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeCreateOwnerModal();
            }
        });
    </script>
</x-admin::layouts>

