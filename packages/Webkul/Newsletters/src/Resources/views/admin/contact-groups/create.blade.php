<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.contact-groups.create-title') }}
    </x-slot:title>

    <div class="flex items-center justify-between mb-6">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.contact-groups.create-title') }}
        </p>

        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.newsletters.contact-groups.index') }}" class="secondary-button">
                {{ __('newsletters::app.common.actions.back') }}
            </a>
        </div>
    </div>

    <form action="{{ route('admin.newsletters.contact-groups.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.contact-groups.name') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                           required
                           value="{{ old('name') }}">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.contact-groups.description') }}
                    </label>
                    <textarea name="description" 
                              id="description" 
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        {{ __('newsletters::app.admin.contact-groups.has-external-integration') }}
                    </h3>
                    
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="has_external_integration" 
                                   id="has_external_integration"
                                   value="1"
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                   {{ old('has_external_integration') ? 'checked' : '' }}
                                   onchange="toggleExternalIntegrationFields()">
                            <span class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                                {{ __('newsletters::app.admin.contact-groups.has-external-integration') }}
                            </span>
                        </label>
                        @error('has_external_integration')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="external-integration-fields" class="space-y-4 {{ old('has_external_integration') ? '' : 'hidden' }}">
                        <div>
                            <label for="request_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('newsletters::app.admin.contact-groups.request-url') }}
                            </label>
                            <input type="url" 
                                   name="request_url" 
                                   id="request_url" 
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                   value="{{ old('request_url') }}"
                                   placeholder="https://example.com/api/contacts">
                            @error('request_url')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="request_token" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('newsletters::app.admin.contact-groups.request-token') }}
                            </label>
                            <input type="text" 
                                   name="request_token" 
                                   id="request_token" 
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                   value="{{ old('request_token') }}"
                                   placeholder="Введите токен для авторизации">
                            @error('request_token')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="auto_request_frequency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('newsletters::app.admin.contact-groups.auto-request-frequency') }}
                            </label>
                            <select name="auto_request_frequency" 
                                    id="auto_request_frequency" 
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                                <option value="">{{ __('newsletters::app.common.actions.select') }}</option>
                                <option value="86400" {{ old('auto_request_frequency') == '86400' || old('auto_request_frequency') == 86400 ? 'selected' : '' }}>
                                    {{ __('newsletters::app.admin.contact-groups.frequency-daily') }}
                                </option>
                                <option value="172800" {{ old('auto_request_frequency') == '172800' || old('auto_request_frequency') == 172800 ? 'selected' : '' }}>
                                    {{ __('newsletters::app.admin.contact-groups.frequency-every-2-days') }}
                                </option>
                                <option value="259200" {{ old('auto_request_frequency') == '259200' || old('auto_request_frequency') == 259200 ? 'selected' : '' }}>
                                    {{ __('newsletters::app.admin.contact-groups.frequency-every-3-days') }}
                                </option>
                                <option value="604800" {{ old('auto_request_frequency') == '604800' || old('auto_request_frequency') == 604800 ? 'selected' : '' }}>
                                    {{ __('newsletters::app.admin.contact-groups.frequency-weekly') }}
                                </option>
                            </select>
                            @error('auto_request_frequency')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
        <script>
            function toggleExternalIntegrationFields() {
                const checkbox = document.getElementById('has_external_integration');
                const fields = document.getElementById('external-integration-fields');
                
                if (checkbox.checked) {
                    fields.classList.remove('hidden');
                } else {
                    fields.classList.add('hidden');
                }
            }

            // Initialize on page load
            document.addEventListener('DOMContentLoaded', function() {
                toggleExternalIntegrationFields();
            });
        </script>
        @endpush

        <div class="flex items-center justify-end gap-x-2.5">
            <a href="{{ route('admin.newsletters.contact-groups.index') }}" class="secondary-button">
                {{ __('newsletters::app.common.actions.cancel') }}
            </a>
            <button type="submit" class="primary-button">
                {{ __('newsletters::app.common.actions.save') }}
            </button>
        </div>
    </form>
</x-admin::layouts>

