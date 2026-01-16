<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.account-warmings.title') }} - {{ __('newsletters::app.common.actions.create') }}
    </x-slot:title>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap mb-6">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.account-warmings.title') }} - {{ __('newsletters::app.common.actions.create') }}
        </p>

        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.newsletters.account-warmings.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                {{ __('newsletters::app.common.actions.back') }}
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ __('newsletters::app.common.actions.create') }} {{ __('newsletters::app.admin.account-warmings.title') }}
            </h2>
        </div>

        <form action="{{ route('admin.newsletters.account-warmings.store') }}" method="POST" class="p-6 space-y-6">
            @csrf
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.account-warmings.name') }}
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        required
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- WhatsApp Instances Selection -->
                <div>
                    <label for="whatsapp_instance_ids" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.account-warmings.select-accounts') }}
                        <span class="text-red-500">*</span>
                    </label>
                    <select
                        name="whatsapp_instance_ids[]"
                        id="whatsapp_instance_ids"
                        multiple
                        size="10"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        required
                    >
                        @foreach($whatsappInstances as $instance)
                            <option value="{{ $instance->id }}" {{ in_array($instance->id, old('whatsapp_instance_ids', [])) ? 'selected' : '' }}>
                                {{ $instance->phone ?? $instance->link_name }} ({{ $instance->active ? __('newsletters::app.admin.account-warmings.active') : __('newsletters::app.admin.account-warmings.inactive') }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('newsletters::app.admin.account-warmings.select-accounts-hint') }}
                    </p>
                    @error('whatsapp_instance_ids')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phrases -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.account-warmings.phrases') }}
                        <span class="text-red-500">*</span>
                    </label>
                    <div id="phrases-container" class="space-y-4">
                        <div class="phrase-pair border border-gray-300 dark:border-gray-600 rounded-md p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('newsletters::app.admin.account-warmings.question') }}
                                    </label>
                                    <textarea
                                        name="phrases[0][question]"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        required
                                    >{{ old('phrases.0.question') }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('newsletters::app.admin.account-warmings.answer') }}
                                    </label>
                                    <textarea
                                        name="phrases[0][answer]"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        required
                                    >{{ old('phrases.0.answer') }}</textarea>
                                </div>
                            </div>
                            <button type="button" onclick="removePhrasePair(this)" class="mt-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm">
                                {{ __('newsletters::app.common.actions.remove') }}
                            </button>
                        </div>
                    </div>
                    <button type="button" onclick="addPhrasePair()" class="mt-4 px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ __('newsletters::app.admin.account-warmings.add-phrase-pair') }}
                    </button>
                    @error('phrases')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Delays -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="delay_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.account-warmings.delay-from') }}
                        </label>
                        <input
                            type="number"
                            name="delay_from"
                            id="delay_from"
                            value="{{ old('delay_from', 5) }}"
                            min="1"
                            max="3600"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        >
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.account-warmings.delay-hint') }}</p>
                        @error('delay_from')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="delay_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.account-warmings.delay-to') }}
                        </label>
                        <input
                            type="number"
                            name="delay_to"
                            id="delay_to"
                            value="{{ old('delay_to', 5) }}"
                            min="1"
                            max="3600"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        >
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.account-warmings.delay-hint') }}</p>
                        @error('delay_to')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Start At -->
                <div>
                    <label for="start_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.account-warmings.start-at') }}
                    </label>
                    <input
                        type="datetime-local"
                        name="start_at"
                        id="start_at"
                        value="{{ old('start_at') }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                    >
                    @error('start_at')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end gap-x-2">
                <a href="{{ route('admin.newsletters.account-warmings.index') }}" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                    {{ __('newsletters::app.common.actions.cancel') }}
                </a>
                <button type="submit" 
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    {{ __('newsletters::app.common.actions.create') }}
                </button>
            </div>
        </form>
    </div>

    <script>
        let phraseIndex = 1;

        function addPhrasePair() {
            const container = document.getElementById('phrases-container');
            const newPair = document.createElement('div');
            newPair.className = 'phrase-pair border border-gray-300 dark:border-gray-600 rounded-md p-4';
            newPair.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.account-warmings.question') }}
                        </label>
                        <textarea
                            name="phrases[${phraseIndex}][question]"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required
                        ></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.account-warmings.answer') }}
                        </label>
                        <textarea
                            name="phrases[${phraseIndex}][answer]"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required
                        ></textarea>
                    </div>
                </div>
                <button type="button" onclick="removePhrasePair(this)" class="mt-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm">
                    {{ __('newsletters::app.common.actions.remove') }}
                </button>
            `;
            container.appendChild(newPair);
            phraseIndex++;
        }

        function removePhrasePair(button) {
            const container = document.getElementById('phrases-container');
            if (container.children.length > 1) {
                button.closest('.phrase-pair').remove();
            } else {
                alert('{{ __("newsletters::app.admin.account-warmings.min-phrases-required") }}');
            }
        }
    </script>
</x-admin::layouts>


