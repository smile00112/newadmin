<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.common.actions.edit') }} - {{ __('newsletters::app.admin.mailing-lists.title_sklon') }}
    </x-slot:title>

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.newsletters.mailing-lists.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                {{ __('newsletters::app.common.actions.back') }}
            </a>
{{--            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">--}}
{{--                {{ __('newsletters::app.admin.mailing-lists.title') }} - {{ __('newsletters::app.common.actions.edit') }}--}}
{{--            </h1>--}}
        </div>
    </div>

    @if(!$hasBalance)
        <div class="mb-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        {{ __('newsletters::app.admin.account.insufficient-balance-warning') }}
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                        <p>
                            {{ __('newsletters::app.admin.account.insufficient-balance') }}
                            <a href="{{ route('admin.newsletters.account.index') }}" class="font-medium underline">
                                {{ __('newsletters::app.admin.account.topup-title') }}
                            </a>
                        </p>
                        <p class="mt-1">
                            {{ __('newsletters::app.admin.mailing-lists.cannot-start-without-balance') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.newsletters.mailing-lists.update', $mailingList->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Mailing List Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 my-5 p5">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('newsletters::app.common.actions.edit') }} {{ __('newsletters::app.admin.mailing-lists.title_sklon') }}
                        </h2>
                        <div class="flex gap-2 space-x-2">
                            <a href="{{ route('admin.newsletters.mailing-lists.index') }}"
                               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                                {{ __('newsletters::app.common.actions.cancel') }}
                            </a>
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ __('newsletters::app.common.actions.update') }}
                            </button>
                        </div>
                    </div>
                </div>
            <div class="p-4 space-y-4">
                    <!-- Message Text -->
                    <div>
                        <label for="message_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mailing-lists.message-text') }}
                            <span class="text-red-500">*</span>
                        </label>

                        <!-- TinyMCE Editor for Email -->
                        <div id="email_editor_wrapper" style="display: none;">
                            <textarea
                                name="message_text"
                                id="message_text_editor"
                                rows="6"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                placeholder="{{ __('newsletters::app.admin.mailing-lists.message-text') }}"
                            >{{ old('message_text', $mailingList->message_text) }}</textarea>
                            <x-admin::tinymce
                                selector="textarea#message_text_editor"
                            />
                        </div>

                        <!-- Regular Textarea for WhatsApp/Telegram -->
                        <div id="regular_textarea_wrapper">
                            <textarea
                                name="message_text"
                                id="message_text"
                                rows="6"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                placeholder="{{ __('newsletters::app.admin.mailing-lists.message-text') }}"
                            >{{ old('message_text', $mailingList->message_text) }}</textarea>
                        </div>

                        @error('message_text')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Media File Upload -->
                    <div>
                        <label for="media_file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mailing-lists.media-file') }} ({{ __('newsletters::app.admin.mailing-lists.photo-or-video') }})
                        </label>
                        <input
                            type="file"
                            name="media_file"
                            id="media_file"
                            accept="image/*,video/*"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        >
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('newsletters::app.admin.mailing-lists.media-file-hint') }}
                        </p>
                        @error('media_file')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        
                        <!-- Existing Media Display -->
                        @if($mailingList->message_links && isset($mailingList->message_links[0]))
                            @php
                                $media = $mailingList->message_links[0];
                            @endphp
                            <div class="mt-2">
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    {{ __('newsletters::app.admin.mailing-lists.current-media') }}:
                                </p>
                                <div class="relative inline-block">
                                    @if($media['type'] === 'image')
                                        <img src="{{ $media['url'] }}" alt="Current media" class="max-w-xs max-h-48 rounded-lg">
                                    @else
                                        <video src="{{ $media['url'] }}" controls class="max-w-xs max-h-48 rounded-lg"></video>
                                    @endif
                                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        {{ $media['original_name'] ?? '' }} ({{ number_format($media['size'] / 1024, 2) }} KB)
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- New Media Preview -->
                        <div id="media_preview" class="mt-2 hidden">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {{ __('newsletters::app.admin.mailing-lists.new-media-preview') }}:
                            </p>
                            <div class="relative inline-block">
                                <img id="media_preview_image" src="" alt="Preview" class="max-w-xs max-h-48 rounded-lg hidden">
                                <video id="media_preview_video" src="" controls class="max-w-xs max-h-48 rounded-lg hidden"></video>
                                <button type="button" onclick="removeMediaPreview()" class="absolute top-0 right-0 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600">
                                    ×
                                </button>
                            </div>
                        </div>
                    </div>

                <!-- Mailing Schedule Settings -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label for="mailing_hours_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mailing-lists.mailing-hours-from') }}
                        </label>
                        <input
                            type="time"
                            name="mailing_hours_from"
                            id="mailing_hours_from"
                            value="{{ old('mailing_hours_from', $mailingList->mailing_hours_from) }}"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            placeholder="09:00"
                        >
                        @error('mailing_hours_from')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="mailing_hours_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mailing-lists.mailing-hours-to') }}
                        </label>
                        <input
                            type="time"
                            name="mailing_hours_to"
                            id="mailing_hours_to"
                            value="{{ old('mailing_hours_to', $mailingList->mailing_hours_to) }}"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            placeholder="18:00"
                        >
                        @error('mailing_hours_to')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="message_delay_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mailing-lists.message-delay-from') }}
                        </label>
                        <input
                            type="number"
                            name="message_delay_from"
                            id="message_delay_from"
                            value="{{ old('message_delay_from', $mailingList->message_delay_from ?? 5) }}"
                            min="1"
                            max="3600"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            placeholder="5"
                        >
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.mailing-lists.message-delay-from-hint') }}</p>
                        @error('message_delay_from')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="message_delay_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mailing-lists.message-delay-to') }}
                        </label>
                        <input
                            type="number"
                            name="message_delay_to"
                            id="message_delay_to"
                            value="{{ old('message_delay_to', $mailingList->message_delay_to ?? 5) }}"
                            min="1"
                            max="3600"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            placeholder="5"
                        >
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.mailing-lists.message-delay-to-hint') }}</p>
                        @error('message_delay_to')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="max_messages_per_instance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mailing-lists.max-messages-per-instance') }}
                        </label>
                        <input
                            type="number"
                            name="max_messages_per_instance"
                            id="max_messages_per_instance"
                            value="{{ old('max_messages_per_instance', $mailingList->max_messages_per_instance ?? 500) }}"
                            min="1"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            placeholder=""
                        >
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.mailing-lists.max-messages-per-instance-hint') }}</p>
                        @error('max_messages_per_instance')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Active Status -->
{{--                    <div class="flex items-center">--}}
{{--                        <label class="flex items-center space-x-3">--}}
{{--                            <!-- Hidden input to ensure 0 is sent when checkbox is unchecked -->--}}
{{--                            <input type="hidden" name="active" value="0">--}}
{{--                            <input--}}
{{--                                type="checkbox"--}}
{{--                                name="active"--}}
{{--                                value="1"--}}
{{--                                {{ old('active', $mailingList->active) ? 'checked' : '' }}--}}
{{--                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"--}}
{{--                            >--}}
{{--                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">--}}
{{--                                {{ __('newsletters::app.admin.mailing-lists.active') }}--}}
{{--                            </span>--}}
{{--                        </label>--}}
{{--                        @error('active')--}}
{{--                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>--}}
{{--                        @enderror--}}
{{--                    </div>--}}

                    <!-- Start At -->
{{--                    <div>--}}
{{--                        <label for="start_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">--}}
{{--                            {{ __('newsletters::app.admin.mailing-lists.start-at') }}--}}
{{--                        </label>--}}
{{--                        <input--}}
{{--                            type="datetime-local"--}}
{{--                            name="start_at"--}}
{{--                            id="start_at"--}}
{{--                            value="{{ old('start_at', $mailingList->start_at ? $mailingList->start_at->format('Y-m-d\TH:i') : '') }}"--}}
{{--                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"--}}
{{--                        >--}}
{{--                        @error('start_at')--}}
{{--                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>--}}
{{--                        @enderror--}}
{{--                    </div>--}}
{{--                    </div>--}}
            </div>
        </div>

        <!-- WhatsApp Instances, Customer Numbers, and User Numbers Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Customer Numbers Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('newsletters::app.admin.customer-numbers.title') }}
                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                                ({{ count($customerNumbers) }}/{{ $totalCustomerNumbers }})
                            </span>
                    </h2>
                    <div class="flex space-x-2 gap-2">
                        <button type="button" onclick="addCustomerNumberRow()"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('newsletters::app.common.actions.add') }} {{ __('newsletters::app.admin.customer-numbers.title') }}
                        </button>
{{--                        <button type="button" onclick="openCSVImportModal('customers')"--}}
{{--                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">--}}
{{--                            {{ __('newsletters::app.common.actions.import') }} CSV--}}
{{--                        </button>--}}
                    </div>
                </div>
                </div>

                <!-- Search and Load More Section -->
                <div class="px-6 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <input type="text" id="phoneSearchInput" placeholder="{{ __('newsletters::app.admin.customer-numbers.search-placeholder') }}"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:text-white">
                        </div>
                        @if($totalCustomerNumbers > 50)
                            <button type="button" onclick="loadMoreCustomerNumbers()"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-600 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-500">
                                {{ __('newsletters::app.admin.customer-numbers.load-more') }}
                            </button>
                        @endif
                    </div>
                </div>

                <div class="px-6 py-2">
                <div id="customerNumbersContainer">
                    @forelse($customerNumbers as $index => $customer)
                            <!-- Hidden fields for existing customer data -->
                            <input type="hidden" name="customer_numbers[{{ $index }}][id]" value="{{ $customer->id }}">
                            <input type="hidden" name="customer_numbers[{{ $index }}][phone_number]" value="{{ $customer->phone_number }}">
                            <input type="hidden" name="customer_numbers[{{ $index }}][name]" value="{{ $customer->name }}">
                            <input type="hidden" name="customer_numbers[{{ $index }}][delivered]" value="{{ $customer->delivered ? '1' : '0' }}">
                            <input type="hidden" name="customer_numbers[{{ $index }}][viewed]" value="{{ $customer->viewed ? '1' : '0' }}">
                            <input type="hidden" name="customer_numbers[{{ $index }}][incoming_message]" value="{{ $customer->incoming_message ? '1' : '0' }}">
                            @if($customer->whatsapp_instance_id)
                                <input type="hidden" name="customer_numbers[{{ $index }}][whatsapp_instance_id]" value="{{ $customer->whatsapp_instance_id }}">
                            @endif
                            <div data-customer-id="{{ $customer->id }}" class="customer-number-row flex items-center justify-between p-3 mb-2 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 {{ $customer->incoming_message ? 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-300 dark:border-yellow-700' : '' }}">
                                    <div class="flex items-center space-x-4 flex-1">
                                    <div class="flex-shrink-0">
                                        @if($customer->incoming_message)
                                            @php
                                                $chatPayload = [
                                                    'id' => $customer->id,
                                                    'phone_number' => $customer->phone_number,
                                                    'name' => $customer->name,
                                                    'instance_phone' => $customer->whatsAppInstance ? ($customer->whatsAppInstance->phone ?: $customer->whatsAppInstance->login) : '',
                                                ];
                                            @endphp
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-400 dark:bg-yellow-900 dark:text-yellow-200 dark:focus:ring-yellow-600"
                                                data-chat-trigger
                                                data-chat-payload='@json($chatPayload, JSON_UNESCAPED_UNICODE)'
                                                title="{{ __('newsletters::app.admin.customer-numbers.chat-with-client') }}"
                                            >
                                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                                </svg>
                                                {{ __('newsletters::app.admin.messages.new-message') }}
                                            </button>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="grid grid-cols-4 gap-4 text-sm">
                                        <div>
                                                <span class="text-gray-500 dark:text-gray-400">ID: {{ $customer->id }}</span>
                                        </div>
                                <div>
                                                <span class="font-medium text-gray-900 dark:text-white phone-number">{{ $customer->phone_number }}</span>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.customer-numbers.phone-number') }}</div>
                                                @if($customer->whatsAppInstance)
                                                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                                        {{ __('newsletters::app.admin.whatsapp-instances.instance-phone') }}: <span class="font-medium">{{ $customer->whatsAppInstance->phone ?: $customer->whatsAppInstance->login }}</span>
                                                    </div>
                                                @endif
                                </div>
                                            <div>
                                                <span class="font-medium {{ $customer->delivered ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                    {{ $customer->delivered ? __('newsletters::app.admin.customer-numbers.delivered') : __('newsletters::app.admin.customer-numbers.not-delivered') }}
                                                </span>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.customer-numbers.delivered_title') }}</div>
                            </div>
                                            <div>
                                                <span class="font-medium {{ $customer->viewed ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                    {{ $customer->viewed ? __('newsletters::app.admin.customer-numbers.viewed') : __('newsletters::app.admin.customer-numbers.not-viewed') }}
                                                </span>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.customer-numbers.viewed_title') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button type="button" onclick="openCustomerEditModal({{ $customer->id }}, '{{ $customer->phone_number }}', '{{ $customer->name }}', {{ $customer->delivered ? 'true' : 'false' }}, {{ $customer->viewed ? 'true' : 'false' }}, '{{ $customer->whatsAppInstance ? ($customer->whatsAppInstance->phone ?: $customer->whatsAppInstance->login) : '' }}')"
                                        class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500"
                                        title="{{ __('newsletters::app.admin.customer-numbers.edit-button-caption') }}">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M3 17.25V21H6.75L17.81 9.94L14.06 6.19L3 17.25ZM20.71 7.04C21.1 6.65 21.1 6.02 20.71 5.63L18.37 3.29C17.98 2.9 17.35 2.9 16.96 3.29L15.13 5.12L18.88 8.87L20.71 7.04Z" fill="currentColor"/>
                                    </svg>
                                    </button>
                                    <button type="button" onclick="deleteCustomerNumber({{ $customer->id }})"
                                        class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-red-500"
                                        title="{{ __('newsletters::app.admin.customer-numbers.delete-button-caption') }}">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                </button>
                                </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.customer-numbers.no-numbers') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
            </div>

            <!-- WhatsApp Instances Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('newsletters::app.admin.whatsapp-instances.title') }}
                        </h2>
                        <div class="flex space-x-2 gap-2">
                            <button type="button" onclick="addWhatsAppInstanceRow()"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ __('newsletters::app.common.actions.add') }} {{ __('newsletters::app.admin.whatsapp-instances.title') }}
                            </button>
{{--                            <button type="button" onclick="openCSVImportModal('whatsapp')"--}}
{{--                                    class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">--}}
{{--                                {{ __('newsletters::app.common.actions.import') }} CSV--}}
{{--                            </button>--}}
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div id="whatsappInstancesContainer">
                        @forelse($whatsappInstances as $index => $instance)
                            <div class="whatsapp-instance-row grid grid-cols-1 gap-4 mb-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                                <!-- Hidden field for existing instance ID -->
                                <input type="hidden" name="whatsapp_instances[{{ $index }}][id]" value="{{ $instance->id }}">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {{ __('newsletters::app.admin.whatsapp-instances.link-name') }}
                                            <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            name="whatsapp_instances[{{ $index }}][link_name]"
                                            value="{{ old('whatsapp_instances.' . $index . '.link_name', $instance->link_name) }}"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                            required
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {{ __('newsletters::app.admin.whatsapp-instances.login') }}
                                            <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            name="whatsapp_instances[{{ $index }}][login]"
                                            value="{{ old('whatsapp_instances.' . $index . '.login', $instance->login) }}"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                            required
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {{ __('newsletters::app.admin.whatsapp-instances.password') }}
                                            <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            name="whatsapp_instances[{{ $index }}][password]"
                                            value="{{ old('whatsapp_instances.' . $index . '.password', $instance->password) }}"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                            required
                                        >
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <button type="button" onclick="removeWhatsAppInstanceRow(this)"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        {{--                                    {{ __('newsletters::app.common.actions.delete') }}--}}
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <p class="text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.whatsapp-instances.no-instances') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
        </div>
        <!-- Submit Buttons -->
        <div class="flex justify-end space-x-3 mt-4">
            <a href="{{ route('admin.newsletters.mailing-lists.index') }}"
               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                {{ __('newsletters::app.common.actions.cancel') }}
            </a>
            <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                {{ __('newsletters::app.common.actions.update') }}
            </button>
        </div>
    </form>

    <!-- CSV Import Modals -->
    <div id="csvImportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" style="z-index: 9999;" onclick="closeCSVImportModal()">
        <div class="modal-content relative top-20 sm:top-32 mx-auto p-4 sm:p-6 border w-80 max-w-md shadow-xl rounded-lg bg-white dark:bg-gray-800" style="z-index: 10000;" onclick="event.stopPropagation()">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white" id="modalTitle">
                        {{ __('newsletters::app.common.actions.import') }} CSV
                    </h3>
                    <button onclick="closeCSVImportModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.common.fields.csv_file') }}
                    </label>
                    <input type="file" id="csvFile" accept=".csv"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.common.fields.csv_format') }}
                    </label>
                    <div id="formatInfo" class="text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-3 rounded border">
                        <!-- Format info will be populated by JavaScript -->
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button onclick="closeCSVImportModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        {{ __('newsletters::app.common.actions.cancel') }}
                    </button>
                    <button onclick="processCSVImport()"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ __('newsletters::app.common.actions.import') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- User Numbers Modal -->
    <div id="userNumbersModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" style="z-index: 9999;" onclick="closeUserNumbersModal()">
        <div class="modal-content relative top-10 mx-auto p-4 sm:p-6 border w-11/12 max-w-4xl shadow-xl rounded-lg bg-white dark:bg-gray-800" style="z-index: 10000;" onclick="event.stopPropagation()">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('newsletters::app.admin.user-numbers.manage-users') }}
                    </h3>
                    <button onclick="closeUserNumbersModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Search and Filter -->
                <div class="mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('newsletters::app.admin.user-numbers.search') }}
                            </label>
                            <input type="text" id="userSearchInput" placeholder="{{ __('newsletters::app.admin.user-numbers.search-placeholder') }}"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('newsletters::app.admin.user-numbers.filter') }}
                            </label>
                            <select id="userFilterSelect" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="all">{{ __('newsletters::app.admin.user-numbers.all-users') }}</option>
                                <option value="selected">{{ __('newsletters::app.admin.user-numbers.selected-users') }}</option>
                                <option value="unselected">{{ __('newsletters::app.admin.user-numbers.unselected-users') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Users List -->
                <div class="mb-4 max-h-96 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg">
                    <div id="usersList" class="p-4">
                        <!-- Users will be loaded here via JavaScript -->
                        <div class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.user-numbers.loading-users') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Selected Users Summary -->
                <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900 rounded-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-blue-800 dark:text-blue-200">
                            {{ __('newsletters::app.admin.user-numbers.selected-count') }}: <span id="selectedUsersCount">0</span>
                        </span>
                        <button type="button" onclick="clearAllSelections()" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                            {{ __('newsletters::app.admin.user-numbers.clear-all') }}
                        </button>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button onclick="closeUserNumbersModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        {{ __('newsletters::app.common.actions.cancel') }}
                    </button>
                    <button onclick="applyUserSelections()"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ __('newsletters::app.common.actions.apply') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('newsletters::admin.components.chat-modal')

    <!-- Customer Edit Modal -->
    <div id="customerEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" style="z-index: 9999;" onclick="closeCustomerEditModal()">
        <div class="modal-content relative top-20 mx-auto p-4 sm:p-6 border w-80 max-w-md shadow-xl rounded-lg bg-white dark:bg-gray-800" style="z-index: 10000;" onclick="event.stopPropagation()">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('newsletters::app.admin.customer-numbers.edit-customer') }}
                    </h3>
                    <button onclick="closeCustomerEditModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="customerEditForm">
                    <input type="hidden" id="editCustomerId" name="customer_id">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.customer-numbers.phone-number') }}
                        </label>
                        <input type="text" id="editPhoneNumber" name="phone_number"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.whatsapp-instances.instance-phone') }}
                        </label>
                        <input type="text" id="editInstancePhone" name="instance_phone"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-gray-100 dark:bg-gray-600 dark:text-gray-300"
                            readonly>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.customer-numbers.name') }}
                        </label>
                        <input type="text" id="editCustomerName" name="name"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.customer-numbers.delivered') }}
                        </label>
                        <select id="editDelivered" name="delivered"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="0">{{ __('newsletters::app.admin.customer-numbers.not-delivered') }}</option>
                            <option value="1">{{ __('newsletters::app.admin.customer-numbers.delivered') }}</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.customer-numbers.viewed') }}
                        </label>
                        <select id="editViewed" name="viewed"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="0">{{ __('newsletters::app.admin.customer-numbers.not-viewed') }}</option>
                            <option value="1">{{ __('newsletters::app.admin.customer-numbers.viewed') }}</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.customer-numbers.chat-with-client') }}
                        </label>
                        <textarea id="editChatHistory" name="chat_history" rows="8"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            placeholder="{{ __('newsletters::app.admin.customer-numbers.loading-chat') }}"
                            readonly></textarea>
                    </div>

                    <!-- Reply Message Section -->
                    <div id="replyMessageSection" class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.customer-numbers.reply-message') }}
                        </label>
                        <div class="flex gap-2">
                            <textarea id="replyMessageText" rows="3"
                                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:text-white"
                                placeholder="{{ __('newsletters::app.admin.customer-numbers.type-your-message') }}"></textarea>
                            <button type="button" onclick="sendReplyMessage()"
                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 self-end">
                                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                                {{ __('newsletters::app.common.actions.send') }}
                            </button>
                        </div>
                        <div id="replyMessageStatus" class="mt-2 text-sm hidden"></div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeCustomerEditModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                            {{ __('newsletters::app.common.actions.cancel') }}
                        </button>
                        <button type="button" onclick="saveCustomerChanges()"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('newsletters::app.common.actions.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        #csvImportModal, #userNumbersModal, #customerEditModal {
            z-index: 9999 !important;
        }
        #csvImportModal .modal-content, #userNumbersModal .modal-content, #customerEditModal .modal-content {
            z-index: 10000 !important;
        }
        .user-item {
            transition: background-color 0.2s ease;
        }
        .user-item:hover {
            background-color: #f3f4f6;
        }
        .user-item.selected {
            background-color: #dbeafe;
        }
        .user-item.selected:hover {
            background-color: #bfdbfe;
        }
        .dark .user-item:hover {
            background-color: #374151;
        }
        .dark .user-item.selected {
            background-color: #1e3a8a;
        }
        .dark .user-item.selected:hover {
            background-color: #1e40af;
        }
    </style>


    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        // Initialize Pusher with Reverb configuration
        // For local development, always use localhost for WebSocket connections
        const wsHost = '{{ config('broadcasting.connections.reverb.options.host', 'localhost') }}';
        const isLocal = window.location.hostname === 'localhost' ||
                       window.location.hostname === '127.0.0.1' ||
                       window.location.hostname.includes('.test') ||
                       window.location.hostname.includes('.local');

        // Для локальной разработки используем localhost, для продакшена - текущий домен
        const finalWsHost = isLocal ? 'localhost' : (wsHost || window.location.hostname);

        // Для продакшена в Coolify порты должны быть стандартными (80/443), не 8080
        // Traefik проксирует WebSocket на внутренний порт 8080 автоматически
        const wsPort = isLocal ? {{ config('broadcasting.connections.reverb.options.port', 8080) }} : 80;
        const wssPort = isLocal ? {{ config('broadcasting.connections.reverb.options.port', 8080) }} : 443;
        // Для локальной разработки всегда используем ws:// (forceTLS: false)
        // Для продакшена используем настройку из конфига
        const useTLS = isLocal ? false : ({{ config('broadcasting.connections.reverb.options.useTLS', false) ? 'true' : 'false' }});

        const pusher = new Pusher('{{ config('broadcasting.connections.reverb.key') }}', {
            cluster: '{{ config('broadcasting.connections.reverb.options.cluster', 'mt1') }}',
            wsHost: finalWsHost,
            wsPort: wsPort,
            wssPort: wssPort,
            forceTLS: useTLS,
            enabledTransports: ['ws', 'wss'],
        });
        const channel = pusher.subscribe('mailing-lists-stats');

        channel.bind('message-read', function(data) {
            console.log('Message read:', data);
            //updateCustomerNumberRow(event);
        });

        channel.bind('mailing-list-{{ $mailingList->id }}', function(data) {
            console.log('Message mailing-list', data);
            //updateCustomerNumberRow(event);
        });


        let customerNumberIndex = {{ count($customerNumbers) }};
        let whatsappInstanceIndex = {{ count($whatsappInstances) }};
        let userNumberIndex = {{ count($userNumbers ?? []) }};
        let currentImportType = '';
        let allUsers = [];
        let selectedUsers = new Set();

        function addCustomerNumberRow() {
            const container = document.getElementById('customerNumbersContainer');
            const newRow = document.createElement('div');
            newRow.className = 'customer-number-row flex items-center justify-between p-3 mb-2 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700';
            newRow.innerHTML = `
                <div class="flex items-center space-x-4 flex-1">
                    <div class="flex-shrink-0">
                        <!-- No incoming message badge for new rows -->
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center space-x-3">
                            <input type="text" name="customer_numbers[${customerNumberIndex}][phone_number]" placeholder="{{ __('newsletters::app.admin.customer-numbers.phone-number') }}"
                                class="flex-1 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:text-white"
                                required>
                            <input type="text" name="customer_numbers[${customerNumberIndex}][name]" placeholder="{{ __('newsletters::app.admin.customer-numbers.name') }}"
                                class="flex-1 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:text-white"
                            required>
                    </div>
                </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400">New</span>
                    <button type="button" onclick="removeCustomerNumberRow(this)"
                        class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-red-500">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            customerNumberIndex++;
        }

        function removeCustomerNumberRow(button) {
            if (confirm('{{ __("newsletters::app.common.messages.confirm_delete") }}')) {
                button.closest('.customer-number-row').remove();
            }
        }

        // Phone number search functionality with debounce and API
        let searchTimeout;
        let isSearching = false;

        function searchCustomerNumbers() {
            const searchTerm = document.getElementById('phoneSearchInput').value.trim();
            const container = document.getElementById('customerNumbersContainer');

            // Check if container exists
            if (!container) {
                console.error('Customer numbers container not found');
                return;
            }

            if (searchTerm.length < 1) {
                // If search is empty, reload the original 50 numbers
                location.reload();
                return;
            }

            if (isSearching) return;
            isSearching = true;

            // Show loading state
            container.innerHTML = `
                <div class="text-center py-8">
                    <div class="inline-flex items-center px-4 py-2 text-sm text-gray-600 dark:text-gray-400">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-600 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('newsletters::app.admin.customer-numbers.searching') }}...
                    </div>
                </div>
            `;

            // Get CSRF token safely
            const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfTokenElement ? csrfTokenElement.getAttribute('content') : '{{ csrf_token() }}';

            // Make API request
            fetch('{{ route("admin.newsletters.customer-numbers.search") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    query: searchTerm,
                    mailing_list_id: {{ $mailingList->id }}
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                isSearching = false;
                if (data.success) {
                    displaySearchResults(data.results, data.count);
                } else {
                    showSearchError(data.message || 'Search failed');
                }
            })
            .catch(error => {
                isSearching = false;
                console.error('Search error:', error);
                showSearchError('{{ __("newsletters::app.admin.customer-numbers.search-error") }}');
            });
        }

        function displaySearchResults(results, count) {
            const container = document.getElementById('customerNumbersContainer');

            if (results.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <p class="text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.customer-numbers.no-search-results') }}</p>
                    </div>
                `;
                return;
            }

            let html = '';
            results.forEach((customer, index) => {
                const chatPayload = {
                    id: customer.id,
                    phone_number: customer.phone_number,
                    name: customer.name || '',
                    instance_phone: (customer.whatsAppInstance?.phone || customer.whatsapp_instance?.phone || customer.whatsAppInstance?.login || customer.whatsapp_instance?.login || ''),
                };

                const payloadAttr = JSON.stringify(chatPayload).replace(/'/g, '&#39;');

                html += `
                    <div class="customer-number-row flex items-center justify-between p-3 mb-2 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 ${customer.incoming_message ? 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-300 dark:border-yellow-700' : ''}">
                        <div class="flex items-center space-x-4 flex-1">
                            <div class="flex-shrink-0">
                                ${customer.incoming_message ? `
                                    <button
                                        type="button"
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-400 dark:bg-yellow-900 dark:text-yellow-200 dark:focus:ring-yellow-600"
                                        data-chat-trigger
                                        data-chat-payload='${payloadAttr}'
                                        title="{{ __('newsletters::app.admin.customer-numbers.chat-with-client') }}"
                                    >
                                        <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                        </svg>
                                        {{ __('newsletters::app.admin.messages.new-message') }}
                                    </button>
                                ` : ''}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="grid grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <span class="font-medium text-gray-900 dark:text-white phone-number">${customer.phone_number}</span>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.customer-numbers.phone-number') }}</div>
                                        ${(customer.whatsAppInstance || customer.whatsapp_instance) ? `
                                            <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                                {{ __('newsletters::app.admin.whatsapp-instances.instance-phone') }}: <span class="font-medium">${(customer.whatsAppInstance?.phone || customer.whatsapp_instance?.phone) || (customer.whatsAppInstance?.login || customer.whatsapp_instance?.login)}</span>
                                            </div>
                                        ` : ''}
                                    </div>
                                    <div>
                                        <span class="font-medium ${customer.delivered ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'}">
                                            ${customer.delivered ? '{{ __("newsletters::app.admin.customer-numbers.delivered") }}' : '{{ __("newsletters::app.admin.customer-numbers.not-delivered") }}'}
                                        </span>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.customer-numbers.delivered') }}</div>
                                    </div>
                                    <div>
                                        <span class="font-medium ${customer.viewed ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'}">
                                            ${customer.viewed ? '{{ __("newsletters::app.admin.customer-numbers.viewed") }}' : '{{ __("newsletters::app.admin.customer-numbers.not-viewed") }}'}
                                        </span>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.customer-numbers.viewed') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">ID: ${customer.id}</span>
                            <button type="button" onclick="openCustomerEditModal(${customer.id}, '${customer.phone_number}', '${customer.name}', ${customer.delivered}, ${customer.viewed}, '${(customer.whatsAppInstance || customer.whatsapp_instance) ? ((customer.whatsAppInstance?.phone || customer.whatsapp_instance?.phone) || (customer.whatsAppInstance?.login || customer.whatsapp_instance?.login)) : ''}')"
                                class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500"
                                title="{{ __('newsletters::app.admin.customer-numbers.edit-button-caption') }}">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 17.25V21H6.75L17.81 9.94L14.06 6.19L3 17.25ZM20.71 7.04C21.1 6.65 21.1 6.02 20.71 5.63L18.37 3.29C17.98 2.9 17.35 2.9 16.96 3.29L15.13 5.12L18.88 8.87L20.71 7.04Z" fill="currentColor"/>
                                </svg>
                            </button>
                            <button type="button" onclick="deleteCustomerNumber(${customer.id})"
                                class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-red-500"
                                title="{{ __('newsletters::app.admin.customer-numbers.delete-button-caption') }}">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;

            // Update the header count
            const header = document.querySelector('h2');
            if (header) {
                const countSpan = header.querySelector('span');
                if (countSpan) {
                    countSpan.textContent = `(${count}/{{ $totalCustomerNumbers }})`;
                }
            }
        }

        function showSearchError(message) {
            const container = document.getElementById('customerNumbersContainer');
            container.innerHTML = `
                <div class="text-center py-8">
                    <p class="text-red-600 dark:text-red-400">${message}</p>
                </div>
            `;
        }

        // Debounced search function
        function debouncedSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(searchCustomerNumbers, 1000);
        }

        // Load more customer numbers functionality
        function loadMoreCustomerNumbers() {
            // This would typically make an AJAX call to load more numbers
            // For now, we'll show an alert indicating the feature
            alert('{{ __("newsletters::app.admin.customer-numbers.load-more-notice") }}');
        }

        // Event listener for phone search with debounce
        const phoneSearchInput = document.getElementById('phoneSearchInput');
        if (phoneSearchInput) {
            phoneSearchInput.addEventListener('input', debouncedSearch);
        } else {
            console.error('Phone search input not found');
        }

        // Customer Edit Modal Functions
        function openCustomerEditModal(id, phoneNumber, name, delivered, viewed, instancePhone = '') {
            // Check if modal elements exist
            const editCustomerId = document.getElementById('editCustomerId');
            const editPhoneNumber = document.getElementById('editPhoneNumber');
            const editInstancePhone = document.getElementById('editInstancePhone');
            const editCustomerName = document.getElementById('editCustomerName');
            const editDelivered = document.getElementById('editDelivered');
            const editViewed = document.getElementById('editViewed');
            const chatTextarea = document.getElementById('editChatHistory');
            const modal = document.getElementById('customerEditModal');

            if (!editCustomerId || !editPhoneNumber || !editCustomerName || !editDelivered || !editViewed || !chatTextarea || !modal) {
                console.error('Modal elements not found');
                return;
            }

            editCustomerId.value = id;
            editPhoneNumber.value = phoneNumber;
            if (editInstancePhone) {
                editInstancePhone.value = instancePhone || '-';
            }
            editCustomerName.value = name;
            editDelivered.value = delivered ? '1' : '0';
            editViewed.value = viewed ? '1' : '0';

            // Clear and set loading placeholder for chat history
            chatTextarea.value = '';
            chatTextarea.placeholder = '{{ __("newsletters::app.admin.customer-numbers.loading-chat") }}';

            modal.classList.remove('hidden');

            // Load chat history via API
            loadChatHistory(id, phoneNumber);
        }

        function closeCustomerEditModal() {
            const modal = document.getElementById('customerEditModal');
            if (modal) {
                modal.classList.add('hidden');
            } else {
                console.error('Customer edit modal not found');
            }
        }

        // Load chat history via API
        function loadChatHistory(customerId, phoneNumber) {
            const chatTextarea = document.getElementById('editChatHistory');
            const replySection = document.getElementById('replyMessageSection');

            // Check if textarea exists
            if (!chatTextarea) {
                console.error('Chat textarea not found');
                return;
            }

            // Show loading state
            chatTextarea.value = '';
            chatTextarea.placeholder = '{{ __("newsletters::app.admin.customer-numbers.loading-chat") }}';

            // Show reply section initially
            if (replySection) {
                replySection.style.display = 'block';
            }

            // Get CSRF token safely
            const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfTokenElement ? csrfTokenElement.getAttribute('content') : '{{ csrf_token() }}';

            // Make API request
            fetch('{{ route("admin.newsletters.customer-numbers.chat-history") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    customer_id: customerId,
                    phone_number: phoneNumber
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.chat_history) {
                    // Check if chat history is an error message or empty
                    const isError = data.chat_history.includes('{{ __("newsletters::app.admin.customer-numbers.no-whatsapp-instance") }}') ||
                                   data.chat_history.includes('{{ __("newsletters::app.admin.customer-numbers.chat-history-unavailable") }}') ||
                                   data.chat_history.includes('недоступна') ||
                                   data.chat_history.includes('не найдена');

                    chatTextarea.value = data.chat_history;
                    chatTextarea.placeholder = '{{ __("newsletters::app.admin.customer-numbers.chat-with-client") }}';

                    // Hide reply section if chat loading failed
                    if (replySection && isError) {
                        replySection.style.display = 'none';
                    }
                } else {
                    chatTextarea.value = data.message || '{{ __("newsletters::app.admin.customer-numbers.chat-history-error") }}';

                    // Hide reply section on error
                    if (replySection) {
                        replySection.style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.error('Error loading chat history:', error);
                chatTextarea.value = '{{ __("newsletters::app.admin.customer-numbers.chat-history-error") }}\n\n' +
                                    '{{ __("newsletters::app.admin.customer-numbers.chat-history-unavailable") }}';

                // Hide reply section on error
                if (replySection) {
                    replySection.style.display = 'none';
                }
            });
        }

        function saveCustomerChanges() {
            const customerId = document.getElementById('editCustomerId').value;
            const formData = {
                phone_number: document.getElementById('editPhoneNumber').value,
                name: document.getElementById('editCustomerName').value,
                delivered: document.getElementById('editDelivered').value === 'true',
                viewed: document.getElementById('editViewed').value === 'true'
            };

            console.log('Saving customer changes:', formData);

            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                alert('Security token not found. Please refresh the page.');
                return;
            }

            // Make AJAX call to save changes
            fetch(`{{ url('admin/newsletters/customer-numbers/edit') }}/${customerId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                },
                body: JSON.stringify(formData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.message || '{{ __("newsletters::app.admin.customer-numbers.update-success") }}');

                    // Update the display in the list
                    const customerCard = document.querySelector(`[data-customer-id="${customerId}"]`);
                    if (customerCard) {
                        // Update phone number display
                        const phoneElement = customerCard.querySelector('.text-lg.font-semibold');
                        if (phoneElement) {
                            phoneElement.textContent = formData.phone_number;
                        }

                        // Update name display
                        const nameElement = customerCard.querySelector('.text-sm.text-gray-600');
                        if (nameElement) {
                            nameElement.textContent = formData.name;
                        }
                    }

                    closeCustomerEditModal();

                    // Reload page to refresh all data
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    alert(data.message || '{{ __("newsletters::app.admin.customer-numbers.update-failed") }}');
                }
            })
            .catch(error => {
                console.error('Error saving customer changes:', error);
                alert('{{ __("newsletters::app.admin.customer-numbers.update-failed") }}: ' + error.message);
            });
        }

        /**
         * Send reply message to customer
         */
        function sendReplyMessage() {
            const customerId = document.getElementById('editCustomerId').value;
            const messageText = document.getElementById('replyMessageText').value.trim();
            const statusDiv = document.getElementById('replyMessageStatus');

            if (!messageText) {
                showReplyStatus('{{ __("newsletters::app.admin.customer-numbers.message-empty-error") }}', 'error');
                return;
            }

            // Disable send button and show loading
            const sendButton = event.target;
            const originalButtonContent = sendButton.innerHTML;
            sendButton.disabled = true;
            sendButton.innerHTML = '<svg class="w-5 h-5 inline-block animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> {{ __("newsletters::app.common.actions.sending") }}...';

            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                showReplyStatus('Security token not found', 'error');
                sendButton.disabled = false;
                sendButton.innerHTML = originalButtonContent;
                return;
            }

            // Send message via API
            fetch('{{ route("admin.newsletters.customer-numbers.send-reply") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                },
                body: JSON.stringify({
                    customer_number_id: customerId,
                    message: messageText
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showReplyStatus(data.message, 'success');
                    // Clear message field
                    document.getElementById('replyMessageText').value = '';

                    // Reload chat history to show sent message
                    const phoneNumber = document.getElementById('editPhoneNumber').value;
                    loadChatHistory(customerId, phoneNumber);
                } else {
                    showReplyStatus(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                showReplyStatus('{{ __("newsletters::app.admin.customer-numbers.message-sent-failed") }}: ' + error.message, 'error');
            })
            .finally(() => {
                // Re-enable send button
                sendButton.disabled = false;
                sendButton.innerHTML = originalButtonContent;
            });
        }

        /**
         * Show reply message status
         */
        function showReplyStatus(message, type) {
            const statusDiv = document.getElementById('replyMessageStatus');
            statusDiv.textContent = message;
            statusDiv.className = 'mt-2 text-sm ';

            if (type === 'success') {
                statusDiv.className += 'text-green-600 dark:text-green-400';
            } else {
                statusDiv.className += 'text-red-600 dark:text-red-400';
            }

            statusDiv.classList.remove('hidden');

            // Hide after 5 seconds
            setTimeout(() => {
                statusDiv.classList.add('hidden');
            }, 5000);
        }

        function deleteCustomerNumber(customerId) {
            if (confirm('{{ __("newsletters::app.admin.customer-numbers.delete-confirm") }}')) {
                console.log('Deleting customer:', customerId);

                // Get CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    alert('Security token not found. Please refresh the page.');
                    return;
                }

                // Make AJAX call to delete the customer
                fetch(`{{ url('admin/newsletters/customer-numbers') }}/${customerId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.message) {
                        alert(data.message);

                        // Remove the row from display
                        const customerRow = document.querySelector(`[data-customer-id="${customerId}"]`);
                        if (customerRow) {
                            customerRow.remove();
                        }

                        // Reload page to refresh all data
                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    }
                })
                .catch(error => {
                    console.error('Error deleting customer:', error);
                    alert('{{ __("newsletters::app.admin.customer-numbers.delete-failed") }}: ' + error.message);
                });
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeCustomerEditModal();
            }
        });

        function addWhatsAppInstanceRow() {
            const container = document.getElementById('whatsappInstancesContainer');
            const newRow = document.createElement('div');
            newRow.className = 'whatsapp-instance-row grid grid-cols-1 gap-4 mb-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg';
            newRow.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.whatsapp-instances.link-name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="whatsapp_instances[${whatsappInstanceIndex}][link_name]"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.whatsapp-instances.login') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="whatsapp_instances[${whatsappInstanceIndex}][login]"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.whatsapp-instances.password') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="whatsapp_instances[${whatsappInstanceIndex}][password]"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="removeWhatsAppInstanceRow(this)"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
{{--                        {{ __('newsletters::app.common.actions.delete') }}--}}
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            whatsappInstanceIndex++;
        }

        function removeWhatsAppInstanceRow(button) {
            if (confirm('{{ __("newsletters::app.common.messages.confirm_delete") }}')) {
                button.closest('.whatsapp-instance-row').remove();
            }
        }

        function openCSVImportModal(type) {
            currentImportType = type;
            const modal = document.getElementById('csvImportModal');
            const title = document.getElementById('modalTitle');
            const formatInfo = document.getElementById('formatInfo');

            if (type === 'whatsapp') {
                title.textContent = '{{ __("newsletters::app.common.actions.import") }} WhatsApp CSV';
                formatInfo.innerHTML = `
                    <p class="mb-1 text-xs font-medium">{{ __('newsletters::app.common.csv_format_whatsapp') }}:</p>
                    <code class="block text-xs font-mono">link_name,login,password</code>
                `;
            } else if (type === 'customers') {
                title.textContent = '{{ __("newsletters::app.common.actions.import") }} Customers CSV';
                formatInfo.innerHTML = `
                    <p class="mb-1 text-xs font-medium">{{ __('newsletters::app.common.csv_format_customers') }}:</p>
                    <code class="block text-xs font-mono">phone_number,name,email</code>
                `;
            }

            modal.classList.remove('hidden');
        }

        function closeCSVImportModal() {
            document.getElementById('csvImportModal').classList.add('hidden');
            document.getElementById('csvFile').value = '';
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeCSVImportModal();
            }
        });

        function processCSVImport() {
            const fileInput = document.getElementById('csvFile');
            const file = fileInput.files[0];

            if (!file) {
                alert('{{ __("newsletters::app.common.messages.please_select_file") }}');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const csv = e.target.result;
                    const lines = csv.split('\n').filter(line => line.trim());

                    console.log('CSV lines:', lines); // Debug log

                    if (lines.length < 2) {
                        alert('CSV file must contain at least a header row and one data row.');
                        return;
                    }

                    if (currentImportType === 'whatsapp') {
                        importWhatsAppInstances(lines);
                    } else if (currentImportType === 'customers') {
                        importCustomerNumbers(lines);
                    }

                    closeCSVImportModal();
                } catch (error) {
                    console.error('Error processing CSV:', error);
                    alert('Error processing CSV file: ' + error.message);
                }
            };
            reader.readAsText(file);
        }

        function importWhatsAppInstances(lines) {
            const container = document.getElementById('whatsappInstancesContainer');
            let importedCount = 0;

            console.log('Importing WhatsApp instances from CSV...');

            lines.forEach((line, index) => {
                if (index === 0) {
                    console.log('Header row:', line);
                    return; // Skip header
                }

                console.log('Processing line', index, ':', line);

                // Parse CSV line properly - handle commas within quoted fields
                const fields = parseCSVLine(line);
                console.log('Parsed fields:', fields);

                if (fields.length >= 3) {
                    const link_name = fields[0] ? fields[0].trim() : '';
                    const login = fields[1] ? fields[1].trim() : '';
                    const password = fields[2] ? fields[2].trim() : '';

                    console.log('Extracted data:', { link_name, login, password });

                    if (link_name && login && password) {
                        const newRow = document.createElement('div');
                        newRow.className = 'whatsapp-instance-row grid grid-cols-1 gap-4 mb-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg';
                        newRow.innerHTML = `
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('newsletters::app.admin.whatsapp-instances.link-name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="whatsapp_instances[${whatsappInstanceIndex}][link_name]"
                                        value="${link_name.replace(/"/g, '&quot;')}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('newsletters::app.admin.whatsapp-instances.login') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="whatsapp_instances[${whatsappInstanceIndex}][login]"
                                        value="${login.replace(/"/g, '&quot;')}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('newsletters::app.admin.whatsapp-instances.password') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" name="whatsapp_instances[${whatsappInstanceIndex}][password]"
                                        value="${password.replace(/"/g, '&quot;')}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        required>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" onclick="removeWhatsAppInstanceRow(this)"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
{{--                                    {{ __('newsletters::app.common.actions.delete') }}--}}
                                </button>
                            </div>
                        `;
                        container.appendChild(newRow);
                        whatsappInstanceIndex++;
                        importedCount++;
                        console.log('Added row for:', link_name, login);
                    } else {
                        console.log('Skipping row - missing required fields:', { link_name, login, password });
                    }
                } else {
                    console.log('Skipping row - not enough fields:', fields);
                }
            });

            console.log('Imported', importedCount, 'WhatsApp instances');
            if (importedCount === 0) {
                alert('No valid WhatsApp instances found in CSV. Please check the format.');
            } else {
                alert(`Successfully imported ${importedCount} WhatsApp instances.`);
            }
        }

        function importCustomerNumbers(lines) {
            const container = document.getElementById('customerNumbersContainer');
            let importedCount = 0;

            console.log('Importing customer numbers from CSV...');

            lines.forEach((line, index) => {
                if (index === 0) {
                    console.log('Header row:', line);
                    return; // Skip header
                }

                console.log('Processing line', index, ':', line);

                // Parse CSV line properly - handle commas within quoted fields
                const fields = parseCSVLine(line);
                console.log('Parsed fields:', fields);

                if (fields.length >= 2) {
                    const phone_number_raw = fields[0] ? fields[0].trim() : '';
                    const phone_number = sanitizePhoneNumber(phone_number_raw);
                    const name = fields[1] ? fields[1].trim() : '';

                    console.log('Extracted data:', { phone_number, name });

                    if (phone_number && name) {
                        const newRow = document.createElement('div');
                        newRow.className = 'customer-number-row grid grid-cols-1 gap-4 mb-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg';
                        newRow.innerHTML = `
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('newsletters::app.admin.customer-numbers.phone-number') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="customer_numbers[${customerNumberIndex}][phone_number]"
                                        value="${phone_number.replace(/"/g, '&quot;')}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('newsletters::app.admin.customer-numbers.name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="customer_numbers[${customerNumberIndex}][name]"
                                        value="${name.replace(/"/g, '&quot;')}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        required>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" onclick="removeCustomerNumberRow(this)"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    {{ __('newsletters::app.common.actions.delete') }}
                                </button>
                            </div>
                        `;
                        container.appendChild(newRow);
                        customerNumberIndex++;
                        importedCount++;
                        console.log('Added row for:', name, phone_number);
                    } else {
                        console.log('Skipping row - missing required fields:', { phone_number, name });
                    }
                } else {
                    console.log('Skipping row - not enough fields:', fields);
                }
            });

            console.log('Imported', importedCount, 'customer numbers');
            if (importedCount === 0) {
                alert('No valid customer numbers found in CSV. Please check the format.');
            } else {
                alert(`Successfully imported ${importedCount} customer numbers.`);
            }
        }

        function parseCSVLine(line) {
            const result = [];
            let current = '';
            let inQuotes = false;

            for (let i = 0; i < line.length; i++) {
                const char = line[i];

                if (char === '"') {
                    inQuotes = !inQuotes;
                } else if (char === ',' && !inQuotes) {
                    result.push(current);
                    current = '';
                } else {
                    current += char;
                }
            }

            result.push(current);
            return result;
        }

        // Remove + - ( ) spaces and any non-digit characters from phone
        function sanitizePhoneNumber(phone) {
            if (!phone) return '';
            return phone.replace(/[^\d]/g, '');
        }

        // User Numbers Management Functions
        function openUserNumbersModal() {
            const modal = document.getElementById('userNumbersModal');
            modal.classList.remove('hidden');
            loadUsers();
        }

        function closeUserNumbersModal() {
            document.getElementById('userNumbersModal').classList.add('hidden');
        }

        function loadUsers() {
            // Use the actual user data passed from the controller
            allUsers = @json($userNumbers ?? []);

            renderUsers();
            updateSelectedCount();
        }

        function renderUsers() {
            const container = document.getElementById('usersList');
            const searchTerm = document.getElementById('userSearchInput').value.toLowerCase();
            const filterType = document.getElementById('userFilterSelect').value;

            let filteredUsers = allUsers.filter(user => {
                const matchesSearch = user.name.toLowerCase().includes(searchTerm) ||
                                    user.email.toLowerCase().includes(searchTerm);
                const matchesFilter = filterType === 'all' ||
                                    (filterType === 'selected' && selectedUsers.has(user.id)) ||
                                    (filterType === 'unselected' && !selectedUsers.has(user.id));
                return matchesSearch && matchesFilter;
            });

            if (filteredUsers.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <p class="text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.user-numbers.no-users-found') }}</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = filteredUsers.map(user => `
                <div class="user-item p-3 border border-gray-200 dark:border-gray-600 rounded-lg mb-2 cursor-pointer ${selectedUsers.has(user.id) ? 'selected' : ''}"
                     onclick="toggleUserSelection('${user.id}')">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <input type="checkbox" ${selectedUsers.has(user.id) ? 'checked' : ''}
                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                   onclick="event.stopPropagation(); toggleUserSelection('${user.id}')">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">${user.name}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">${user.email}</div>
                                ${user.phone ? `<div class="text-xs text-gray-400">Phone: ${user.phone}</div>` : ''}
                            </div>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${user.type === 'admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'}">
                                ${user.type === 'admin' ? 'Admin' : 'Customer'}
                            </span>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function toggleUserSelection(userId) {
            if (selectedUsers.has(userId)) {
                selectedUsers.delete(userId);
            } else {
                selectedUsers.add(userId);
            }
            renderUsers();
            updateSelectedCount();
        }

        function updateSelectedCount() {
            document.getElementById('selectedUsersCount').textContent = selectedUsers.size;
        }

        function clearAllSelections() {
            selectedUsers.clear();
            renderUsers();
            updateSelectedCount();
        }

        function applyUserSelections() {
            const container = document.getElementById('userNumbersContainer');

            // Clear existing user number rows
            container.innerHTML = '';

            if (selectedUsers.size === 0) {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <p class="text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.user-numbers.no-users') }}</p>
                    </div>
                `;
                closeUserNumbersModal();
                return;
            }

            // Add selected users as rows
            selectedUsers.forEach(userId => {
                const user = allUsers.find(u => u.id === userId);
                if (user) {
                    const newRow = document.createElement('div');
                    newRow.className = 'user-number-row grid grid-cols-1 gap-4 mb-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg';
                    newRow.innerHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('newsletters::app.admin.user-numbers.name') }}
                                </label>
                                <input type="text" name="user_numbers[${userNumberIndex}][name]" value="${user.name}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    readonly>
                                <input type="hidden" name="user_numbers[${userNumberIndex}][id]" value="${user.id}">
                                <input type="hidden" name="user_numbers[${userNumberIndex}][type]" value="${user.type}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('newsletters::app.admin.user-numbers.email') }}
                                </label>
                                <input type="email" name="user_numbers[${userNumberIndex}][email]" value="${user.email}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('newsletters::app.admin.user-numbers.type') }}
                                </label>
                                <div class="flex items-center h-10 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-50 dark:bg-gray-700">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${user.type === 'admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'}">
                                        ${user.type === 'admin' ? 'Admin' : 'Customer'}
                                    </span>
                                </div>
                            </div>
                        </div>
                        ${user.phone ? `
                        <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('newsletters::app.admin.user-numbers.phone') }}
                                </label>
                                <input type="text" name="user_numbers[${userNumberIndex}][phone]" value="${user.phone}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    readonly>
                            </div>
                        </div>
                        ` : ''}
                        <div class="flex justify-end">
                            <button type="button" onclick="removeUserNumberRow(this)"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                {{ __('newsletters::app.common.actions.delete') }}
                            </button>
                        </div>
                    `;
                    container.appendChild(newRow);
                    userNumberIndex++;
                }
            });

            closeUserNumbersModal();
        }

        function removeUserNumberRow(button) {
            if (confirm('{{ __("newsletters::app.common.messages.confirm_delete") }}')) {
                button.closest('.user-number-row').remove();
            }
        }

        // Event listeners for search and filter
        document.getElementById('userSearchInput').addEventListener('input', renderUsers);
        document.getElementById('userFilterSelect').addEventListener('change', renderUsers);

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeUserNumbersModal();
            }
        });

        // WebSocket / Broadcast connection for real-time updates
        @if(config('broadcasting.default') !== 'null')
        {{--(function() {--}}
        {{--    // Initialize broadcast connection if available--}}
        {{--    if (typeof window.Echo !== 'undefined') {--}}
        {{--        // Subscribe to mailing list channel--}}
        {{--        window.Echo.channel('mailing-list.{{ $mailingList->id }}')--}}
        {{--            .listen('.customer-number.message-read', (event) => {--}}
        {{--                console.log('Customer number message read event received:', event);--}}
        {{--                updateCustomerNumberRow(event);--}}
        {{--            });--}}

        {{--        console.log('Subscribed to mailing-list.{{ $mailingList->id }} channel');--}}
        {{--    } else {--}}
        {{--        console.warn('Laravel Echo is not initialized. Real-time updates are disabled.');--}}
        {{--    }--}}
        {{--})();--}}
        @endif
        /**
         * Update customer number row when incoming_message is cleared
         */
        function updateCustomerNumberRow(event) {
            const customerId = event.customer_number_id;
            const container = document.getElementById('customerNumbersContainer');

            if (!container) return;

            // Find all customer number rows
            const rows = container.querySelectorAll('.customer-number-row');

            rows.forEach(row => {
                // Find the hidden input with customer ID
                const idInput = row.querySelector('input[name*="[id]"]');

                if (idInput && idInput.value == customerId) {
                    // Remove yellow background highlighting
                    row.classList.remove('bg-yellow-50', 'dark:bg-yellow-900/20', 'border-yellow-300', 'dark:border-yellow-700');

                    // Remove the "incoming" badge
                    const badgeContainer = row.querySelector('.flex-shrink-0');
                    if (badgeContainer) {
                        badgeContainer.innerHTML = '';
                    }

                    // Update hidden input for incoming_message
                    const incomingMessageInput = row.querySelector('input[name*="[incoming_message]"]');
                    if (incomingMessageInput) {
                        incomingMessageInput.value = '0';
                    }

                    console.log('Updated customer number row for ID:', customerId);

                    // Optional: Show a brief notification
                    showNotification('Message status updated for ' + event.phone_number);
                }
            });
        }

        /**
         * Show brief notification
         */
        function showNotification(message) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-opacity duration-300';
            notification.textContent = message;
            notification.style.opacity = '0';

            document.body.appendChild(notification);

            // Fade in
            setTimeout(() => {
                notification.style.opacity = '1';
            }, 10);

            // Fade out and remove
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        // Media file preview
        const mediaFileInput = document.getElementById('media_file');
        if (mediaFileInput) {
            mediaFileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const preview = document.getElementById('media_preview');
                    const previewImage = document.getElementById('media_preview_image');
                    const previewVideo = document.getElementById('media_preview_video');
                    
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImage.src = e.target.result;
                            previewImage.classList.remove('hidden');
                            previewVideo.classList.add('hidden');
                            preview.classList.remove('hidden');
                        };
                        reader.readAsDataURL(file);
                    } else if (file.type.startsWith('video/')) {
                        const url = URL.createObjectURL(file);
                        previewVideo.src = url;
                        previewVideo.classList.remove('hidden');
                        previewImage.classList.add('hidden');
                        preview.classList.remove('hidden');
                    }
                }
            });
        }

        function removeMediaPreview() {
            const mediaFileInput = document.getElementById('media_file');
            const preview = document.getElementById('media_preview');
            const previewImage = document.getElementById('media_preview_image');
            const previewVideo = document.getElementById('media_preview_video');
            
            if (mediaFileInput) {
                mediaFileInput.value = '';
            }
            if (preview) {
                preview.classList.add('hidden');
            }
            if (previewImage) {
                previewImage.src = '';
            }
            if (previewVideo) {
                previewVideo.src = '';
            }
        }

        // Инициализация TinyMCE и синхронизация для edit.blade.php
        document.addEventListener('DOMContentLoaded', function() {
            const channelType = '{{ $mailingList->channel_type ?? 'whatsapp' }}';
            const messageTextValue = document.getElementById('message_text').value;

            // Если канал - email, инициализируем TinyMCE
            if (channelType === 'email') {
                // Показываем TinyMCE, скрываем обычный textarea
                document.getElementById('email_editor_wrapper').style.display = 'block';
                document.getElementById('regular_textarea_wrapper').style.display = 'none';

                // Инициализируем TinyMCE
                setTimeout(function() {
                    if (typeof tinymce !== 'undefined') {
                        // Удаляем существующий редактор, если есть
                        let existingEditor = tinymce.get('message_text_editor');
                        if (existingEditor) {
                            existingEditor.remove();
                        }

                        setTimeout(function() {
                            const imageUploadHandler = (blobInfo, progress) => new Promise((resolve, reject) => {
                                const xhr = new XMLHttpRequest();
                                xhr.withCredentials = false;
                                xhr.open('POST', '{{ route('admin.tinymce.upload') }}');

                                xhr.upload.onprogress = (e) => progress((e.loaded / e.total) * 100);

                                xhr.onload = function() {
                                    if (xhr.status === 403) {
                                        reject('HTTP Error', { remove: true });
                                        return;
                                    }

                                    if (xhr.status < 200 || xhr.status >= 300) {
                                        reject('HTTP Error');
                                        return;
                                    }

                                    const json = JSON.parse(xhr.responseText);
                                    if (!json || typeof json.location != 'string') {
                                        reject('Invalid JSON: ' + xhr.responseText);
                                        return;
                                    }

                                    resolve(json.location);
                                };

                                xhr.onerror = () => reject('Upload failed');

                                const formData = new FormData();
                                formData.append('_token', '{{ csrf_token() }}');
                                formData.append('file', blobInfo.blob(), blobInfo.filename());

                                xhr.send(formData);
                            });

                            tinymce.init({
                                selector: '#message_text_editor',
                                plugins: 'image media wordcount save fullscreen code table lists link',
                                toolbar1: 'formatselect | bold italic strikethrough forecolor backcolor image alignleft aligncenter alignright alignjustify | link hr |numlist bullist outdent indent  | removeformat | code | table',
                                image_advtab: true,
                                height: 400,
                                relative_urls: false,
                                menubar: false,
                                remove_script_host: false,
                                document_base_url: '{{ asset('/') }}',
                                skin: document.documentElement.classList.contains('dark') ? 'oxide-dark' : 'oxide',
                                content_css: document.documentElement.classList.contains('dark') ? 'dark' : 'default',
                                images_upload_handler: imageUploadHandler,
                                setup: function(editor) {
                                    editor.on('init', function() {
                                        editor.setContent(messageTextValue);
                                    });
                                    
                                    // Синхронизация при изменении содержимого TinyMCE
                                    editor.on('change keyup input', function() {
                                        const content = editor.getContent();
                                        const messageText = document.getElementById('message_text');
                                        if (messageText) {
                                            messageText.value = content;
                                        }
                                    });
                                    
                                    // Синхронизация при программном изменении
                                    editor.on('SetContent', function() {
                                        const content = editor.getContent();
                                        const messageText = document.getElementById('message_text');
                                        if (messageText) {
                                            messageText.value = content;
                                        }
                                    });
                                }
                            });
                        }, 100);
                    }
                }, 200);
            } else {
                // Для WhatsApp/Telegram показываем обычный textarea
                document.getElementById('email_editor_wrapper').style.display = 'none';
                document.getElementById('regular_textarea_wrapper').style.display = 'block';
            }

            // Синхронизация при изменении обычного textarea
            const messageText = document.getElementById('message_text');
            if (messageText) {
                messageText.addEventListener('input', function() {
                    // Если TinyMCE активен, обновляем его содержимое
                    if (typeof tinymce !== 'undefined') {
                        const editor = tinymce.get('message_text_editor');
                        if (editor && !editor.isHidden()) {
                            const currentContent = editor.getContent();
                            const newContent = this.value;
                            // Обновляем только если содержимое действительно изменилось
                            if (currentContent !== newContent) {
                                editor.setContent(newContent);
                            }
                        }
                    }
                });
            }
        });

        // Копирование содержимого TinyMCE в textarea перед отправкой формы
        document.querySelector('form').addEventListener('submit', function(e) {
            const channelType = '{{ $mailingList->channel_type ?? 'whatsapp' }}';
            if (channelType === 'email' && typeof tinymce !== 'undefined') {
                const editor = tinymce.get('message_text_editor');
                if (editor) {
                    const content = editor.getContent();

                    // Проверяем, что контент не пустой
                    const textContent = editor.getContent({format: 'text'}).trim();
                    if (!textContent) {
                        e.preventDefault();
                        alert('Пожалуйста, заполните поле "Текст сообщения"');
                        editor.focus();
                        return false;
                    }

                    // Обновляем значение в textarea, который используется TinyMCE
                    const editorTextarea = document.getElementById('message_text_editor');
                    if (editorTextarea) {
                        editorTextarea.value = content;
                    }

                    // Копируем значение в скрытое поле
                    const hiddenMessageText = document.getElementById('message_text');
                    if (hiddenMessageText) {
                        hiddenMessageText.removeAttribute('required');
                        hiddenMessageText.value = content;
                    }
                }
            }
        });

    </script>

</x-admin::layouts>
