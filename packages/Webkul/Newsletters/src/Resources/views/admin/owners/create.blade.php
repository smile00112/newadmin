<x-admin::layouts>
    <x-slot:title>
        {{ $context['type'] === 'super_admin' ? __('newsletters::app.admin.owners.create-title') : __('newsletters::app.admin.managers.create-title') }}
    </x-slot:title>

    <form method="POST" action="{{ route('admin.newsletters.owners.store') }}">
        @csrf

        <div class="flex items-center justify-between">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                {{ $context['type'] === 'super_admin' ? __('newsletters::app.admin.owners.create-title') : __('newsletters::app.admin.managers.create-title') }}
            </p>

            <div class="flex items-center gap-x-2.5">
                <a href="{{ route('admin.newsletters.owners.index') }}" class="secondary-button">
                    {{ __('newsletters::app.common.actions.cancel') }}
                </a>
                <button type="submit" class="primary-button">
                    {{ __('newsletters::app.common.actions.save') }}
                </button>
            </div>
        </div>

        <div class="p-3 mt-4 box-shadow rounded bg-white p-6 dark:bg-gray-900">
            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.owners.name') }}
                        <span class="text-red-600 dark:text-red-400">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
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
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.owners.email') }}
                        <span class="text-red-600 dark:text-red-400">*</span>
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="email"
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
                    <label for="role_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.owners.role') }}
                        <span class="text-red-600 dark:text-red-400">*</span>
                    </label>
                    <select
                        name="role_id"
                        id="role_id"
                        required
                        onchange="checkRoleAndToggleCompanyOption()"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors"
                    >
                        <option value="">{{ __('newsletters::app.admin.owners.select-role') }}</option>
                        @foreach($roles as $role)
                            @php
                                $isOwnerRole = $role->name === 'Владелец компании' || $role->permission_type === 'all';
                            @endphp
                            <option 
                                value="{{ $role->id }}" 
                                data-is-owner="{{ $isOwnerRole ? '1' : '0' }}"
                                {{ (old('role_id', $defaultRole?->id) == $role->id) ? 'selected' : '' }}
                            >
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
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.owners.password') }}
                        <span class="text-red-600 dark:text-red-400">*</span>
                    </label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.owners.password-confirmation') }}
                        <span class="text-red-600 dark:text-red-400">*</span>
                    </label>
                    <input
                        type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors"
                    >
                </div>

                @if($context['type'] === 'super_admin' && $context['can_create_companies'])
                <!-- Company Option -->
                <div id="company-option-group">
                    <label for="company_option" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.owners.company-option') }}
                        <span class="text-red-600 dark:text-red-400">*</span>
                    </label>
                    <select
                        name="company_option"
                        id="company_option"
                        required
                        onchange="toggleCompanyFields()"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors"
                    >
                        <option value="existing" {{ old('company_option') === 'existing' ? 'selected' : '' }}>
                            {{ __('newsletters::app.admin.owners.select-existing-company') }}
                        </option>
                        <option value="new" id="new-company-option" {{ old('company_option') === 'new' ? 'selected' : '' }}>
                            {{ __('newsletters::app.admin.owners.create-new-company') }}
                        </option>
                    </select>
                    @error('company_option')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Existing Company Selection -->
                <div id="existing-company-group">
                    <label for="company_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.owners.company') }}
                        <span class="text-red-600 dark:text-red-400">*</span>
                    </label>
                    <select
                        name="company_id"
                        id="company_id"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors"
                    >
                        <option value="">{{ __('newsletters::app.admin.owners.select-company') }}</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('company_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- New Company Fields -->
                <div class="hidden" id="new-company-group">
                    <label for="company_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.companies.name') }}
                        <span class="text-red-600 dark:text-red-400">*</span>
                    </label>
                    <input
                        type="text"
                        name="company_name"
                        id="company_name"
                        value="{{ old('company_name') }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors"
                    >
                    @error('company_name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="hidden" id="new-company-description-group">
                    <label for="company_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.companies.description') }}
                    </label>
                    <textarea
                        name="company_description"
                        id="company_description"
                        rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors resize-y"
                    >{{ old('company_description') }}</textarea>
                    @error('company_description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                @endif

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
        </div>
    </form>

    @if($context['type'] === 'super_admin' && $context['can_create_companies'])
    <script>
        function toggleCompanyFields() {
            const companyOption = document.getElementById('company_option').value;
            const existingGroup = document.getElementById('existing-company-group');
            const newCompanyGroup = document.getElementById('new-company-group');
            const newCompanyDescriptionGroup = document.getElementById('new-company-description-group');
            const companyIdField = document.getElementById('company_id');
            const companyNameField = document.getElementById('company_name');

            if (companyOption === 'existing') {
                existingGroup.classList.remove('hidden');
                newCompanyGroup.classList.add('hidden');
                newCompanyDescriptionGroup.classList.add('hidden');
                companyIdField.setAttribute('required', 'required');
                companyNameField.removeAttribute('required');
            } else {
                existingGroup.classList.add('hidden');
                newCompanyGroup.classList.remove('hidden');
                newCompanyDescriptionGroup.classList.remove('hidden');
                companyIdField.removeAttribute('required');
                companyNameField.setAttribute('required', 'required');
            }
        }

        function checkRoleAndToggleCompanyOption() {
            const roleSelect = document.getElementById('role_id');
            const companyOptionGroup = document.getElementById('company-option-group');
            const companyOption = document.getElementById('company_option');
            const newCompanyOption = document.getElementById('new-company-option');
            
            if (!roleSelect || !companyOptionGroup) return;
            
            const selectedRoleId = roleSelect.value;
            if (!selectedRoleId) {
                if (companyOptionGroup) companyOptionGroup.style.display = 'block';
                return;
            }
            
            // Получаем data-атрибут выбранной роли
            const selectedOption = roleSelect.options[roleSelect.selectedIndex];
            const isOwnerRole = selectedOption.getAttribute('data-is-owner') === '1';
            
            if (!isOwnerRole) {
                // Для менеджеров скрываем опцию создания новой компании
                if (newCompanyOption) {
                    newCompanyOption.style.display = 'none';
                }
                if (companyOption && companyOption.value === 'new') {
                    companyOption.value = 'existing';
                    toggleCompanyFields();
                }
            } else {
                // Для владельцев показываем опцию создания новой компании
                if (newCompanyOption) {
                    newCompanyOption.style.display = 'block';
                }
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleCompanyFields();
            checkRoleAndToggleCompanyOption();
            
            // Отслеживаем изменение роли
            const roleSelect = document.getElementById('role_id');
            if (roleSelect) {
                roleSelect.addEventListener('change', checkRoleAndToggleCompanyOption);
            }
        });
    </script>
    @endif
</x-admin::layouts>

