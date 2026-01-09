<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.mailing-lists.title') }} - {{ __('newsletters::app.common.actions.create') }}
    </x-slot:title>

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.newsletters.mailing-lists.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                {{ __('newsletters::app.common.actions.back') }}
            </a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('newsletters::app.admin.mailing-lists.title') }} - {{ __('newsletters::app.common.actions.create') }}
            </h1>
        </div>
    </div>

    <form action="{{ route('admin.newsletters.mailing-lists.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Mailing List Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 my-5 p5">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('newsletters::app.common.actions.create') }} {{ __('newsletters::app.admin.mailing-lists.title_sklon') }}
                    </h2>
                </div>
            <div class="p-6 space-y-6">
                    <!-- Channel Type -->
                    <div>
                        <label for="channel_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mailing-lists.channel-type') }}
                            <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="channel_type"
                            id="channel_type"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            onchange="toggleChannelInstances(this.value)"
                            required
                        >
                            <option value="whatsapp" {{ old('channel_type', 'whatsapp') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                            <option value="email" {{ old('channel_type') == 'email' ? 'selected' : '' }}>Email</option>
                            <option value="telegram" {{ old('channel_type') == 'telegram' ? 'selected' : '' }}>Telegram</option>
                        </select>
                        @error('channel_type')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

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
                            >{{ old('message_text') }}</textarea>
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
                                required
                            >{{ old('message_text') }}</textarea>
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
                        <div id="media_preview" class="mt-2 hidden">
                            <div class="relative inline-block">
                                <img id="media_preview_image" src="" alt="Preview" class="max-w-xs max-h-48 rounded-lg hidden">
                                <video id="media_preview_video" src="" controls class="max-w-xs max-h-48 rounded-lg hidden"></video>
                                <button type="button" onclick="removeMediaPreview()" class="absolute top-0 right-0 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600">
                                    ×
                                </button>
                            </div>
                        </div>
                    </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Active Status -->
{{--                    <div>--}}
{{--                        <label class="flex items-center space-x-3">--}}
{{--                            <input--}}
{{--                                type="checkbox"--}}
{{--                                name="active"--}}
{{--                                value="1"--}}
{{--                                {{ old('active', true) ? 'checked' : '' }}--}}
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
                    <div>
                        <label for="start_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mailing-lists.start-at') }}
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
                            value="{{ old('mailing_hours_from') }}"
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
                            value="{{ old('mailing_hours_to') }}"
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
                            value="{{ old('message_delay_from', 5) }}"
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
                            value="{{ old('message_delay_to', 5) }}"
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
                            value="{{ old('max_messages_per_instance') }}"
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
            </div>
        </div>

        <!-- Channel Instances and Customer Numbers Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- WhatsApp Instances Section -->
            <div id="whatsappInstancesSection" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('newsletters::app.admin.whatsapp-instances.title') }}
                        </h2>
                        <div class="flex space-x-2 gap-2">
                            <button type="button" onclick="addWhatsAppInstanceRow()"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ __('newsletters::app.common.actions.add') }}
                            </button>
                            <button type="button" onclick="openCSVImportModal('whatsapp')"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ __('newsletters::app.common.actions.import') }} CSV
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <!-- Select existing WhatsApp instances -->
                    @if(isset($whatsappInstances) && $whatsappInstances->count() > 0)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.whatsapp-instances.select-existing') }}
                        </label>
                        <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 dark:border-gray-600 rounded-md p-3 dark:bg-gray-700">
                            @foreach($whatsappInstances as $whatsappInstance)
                                <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-600 p-2 rounded">
                                    <input
                                        type="checkbox"
                                        name="whatsapp_instance_ids[]"
                                        value="{{ $whatsappInstance->id }}"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    >
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ $whatsappInstance->link_name }} ({{ $whatsappInstance->login }})
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('newsletters::app.admin.whatsapp-instances.select-existing-hint') }}
                        </p>
                    </div>
                    @endif
                    <div id="whatsappInstancesContainer">
{{--                        <div class="whatsapp-instance-row grid grid-cols-1 gap-4 mb-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg">--}}
{{--                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">--}}
{{--                                <div>--}}
{{--                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">--}}
{{--                                        {{ __('newsletters::app.admin.whatsapp-instances.link-name') }}--}}
{{--                                        <span class="text-red-500">*</span>--}}
{{--                                    </label>--}}
{{--                                    <input--}}
{{--                                        type="text"--}}
{{--                                        name="whatsapp_instances[0][link_name]"--}}
{{--                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"--}}
{{--                                        required--}}
{{--                                    >--}}
{{--                                </div>--}}
{{--                                <div>--}}
{{--                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">--}}
{{--                                        {{ __('newsletters::app.admin.whatsapp-instances.login') }}--}}
{{--                                        <span class="text-red-500">*</span>--}}
{{--                                    </label>--}}
{{--                                    <input--}}
{{--                                        type="text"--}}
{{--                                        name="whatsapp_instances[0][login]"--}}
{{--                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"--}}
{{--                                        required--}}
{{--                                    >--}}
{{--                                </div>--}}
{{--                                <div>--}}
{{--                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">--}}
{{--                                        {{ __('newsletters::app.admin.whatsapp-instances.password') }}--}}
{{--                                        <span class="text-red-500">*</span>--}}
{{--                                    </label>--}}
{{--                                    <input--}}
{{--                                        type="text"--}}
{{--                                        name="whatsapp_instances[0][password]"--}}
{{--                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue1-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"--}}
{{--                                        required--}}
{{--                                    >--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="flex justify-end">--}}
{{--                                <button type="button" onclick="removeWhatsAppInstanceRow(this)"--}}
{{--                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">--}}
{{--                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">--}}
{{--                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>--}}
{{--                                    </svg>--}}
{{--                                    {{ __('newsletters::app.common.actions.delete') }}--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                        </div>--}}
                    </div>
                </div>
            </div>

            <!-- Email Instances Section (hidden by default) -->
            <div id="emailInstancesSection" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('newsletters::app.admin.mail-instances.title') }}
                        </h2>
                        <button type="button" onclick="addEmailInstanceRow()"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('newsletters::app.common.actions.add') }}
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <!-- Select existing mail instances -->
                    @if(isset($mailInstances) && $mailInstances->count() > 0)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mail-instances.select-existing') }}
                        </label>
                        <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 dark:border-gray-600 rounded-md p-3 dark:bg-gray-700">
                            @foreach($mailInstances as $mailInstance)
                                <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-600 p-2 rounded">
                                    <input
                                        type="checkbox"
                                        name="mail_instance_ids[]"
                                        value="{{ $mailInstance->id }}"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    >
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ $mailInstance->name ?: $mailInstance->from_email }} ({{ $mailInstance->host }})
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('newsletters::app.admin.mail-instances.select-existing-hint') }}
                        </p>
                    </div>
                    <div id="newEmailInstancesWrapper">
                    @endif
                    <div id="emailInstancesContainer"></div>
                    @if(isset($mailInstances) && $mailInstances->count() > 0)
                    </div>
                    @endif
                </div>
            </div>

            <!-- Telegram Instances Section (hidden by default) -->
            <div id="telegramInstancesSection" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('newsletters::app.admin.telegram-instances.title') }}
                        </h2>
                        <button type="button" onclick="addTelegramInstanceRow()"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('newsletters::app.common.actions.add') }}
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <!-- Select existing Telegram instances -->
                    @if(isset($telegramInstances) && $telegramInstances->count() > 0)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.telegram-instances.select-existing') }}
                        </label>
                        <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 dark:border-gray-600 rounded-md p-3 dark:bg-gray-700">
                            @foreach($telegramInstances as $telegramInstance)
                                <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-600 p-2 rounded">
                                    <input
                                        type="checkbox"
                                        name="telegram_instance_ids[]"
                                        value="{{ $telegramInstance->id }}"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    >
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ $telegramInstance->bot_name ?: $telegramInstance->bot_username ?: 'Telegram Bot' }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('newsletters::app.admin.telegram-instances.select-existing-hint') }}
                        </p>
                    </div>
                    @endif
                    <div id="telegramInstancesContainer"></div>
                </div>
            </div>

            <!-- Customer Numbers Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('newsletters::app.admin.customer-numbers.title') }}
                        </h2>
                        <div class="flex space-x-2 gap-2">
                            {{-- Закомментировано: ручное добавление контактов заменено на выбор фильтра --}}
                            {{-- <button type="button" onclick="addCustomerNumberRow()"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ __('newsletters::app.common.actions.add') }}
                            </button>
                            <button type="button" onclick="openCSVImportModal('customers')"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ __('newsletters::app.common.actions.import') }} CSV
                            </button> --}}
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <!-- Contact Group and Filter Selection -->
                    <div class="mb-6 space-y-4">
                        <div>
                            <label for="contact_group_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('newsletters::app.admin.contact-groups.title') }}
                            </label>
                            <select
                                name="contact_group_id"
                                id="contact_group_id"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                onchange="loadFiltersForGroup(this.value)"
                            >
                                <option value="">{{ __('newsletters::app.common.actions.select') }} {{ __('newsletters::app.admin.contact-groups.title') }}</option>
                                @if(isset($contactGroups) && $contactGroups->count() > 0)
                                    @foreach($contactGroups as $group)
                                        <option value="{{ $group->id }}" {{ old('contact_group_id') == $group->id ? 'selected' : '' }}>
                                            {{ $group->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('contact_group_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="filter_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('newsletters::app.admin.contact-filters.title') }}
                            </label>
                            <select
                                name="filter_id"
                                id="filter_id"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">{{ __('newsletters::app.common.actions.select') }} {{ __('newsletters::app.admin.contact-filters.title') }}</option>
                            </select>
                            @error('filter_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Закомментировано: ручное добавление контактов заменено на выбор фильтра --}}
                    <div id="customerNumbersContainer" style="display: none;">
{{--                        <div class="customer-number-row grid grid-cols-1 gap-4 mb-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg">--}}
{{--                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">--}}
{{--                                <div>--}}
{{--                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">--}}
{{--                                        {{ __('newsletters::app.admin.customer-numbers.phone-number') }}--}}
{{--                                        <span class="text-red-500">*</span>--}}
{{--                                    </label>--}}
{{--                                    <input--}}
{{--                                        type="text"--}}
{{--                                        name="customer_numbers[0][phone_number]"--}}
{{--                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"--}}
{{--                                        required--}}
{{--                                    >--}}
{{--                                </div>--}}
{{--                                <div>--}}
{{--                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">--}}
{{--                                        {{ __('newsletters::app.admin.customer-numbers.name') }}--}}
{{--                                        <span class="text-red-500">*</span>--}}
{{--                                    </label>--}}
{{--                                    <input--}}
{{--                                        type="text"--}}
{{--                                        name="customer_numbers[0][name]"--}}
{{--                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"--}}
{{--                                        required--}}
{{--                                    >--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="flex justify-end">--}}
{{--                                <button type="button" onclick="removeCustomerNumberRow(this)"--}}
{{--                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">--}}
{{--                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">--}}
{{--                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>--}}
{{--                                    </svg>--}}
{{--                                    {{ __('newsletters::app.common.actions.delete') }}--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                        </div>--}}
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
                            {{ __('newsletters::app.common.actions.create') }}
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

    <style>
        #csvImportModal {
            z-index: 9999 !important;
        }
        #csvImportModal .modal-content {
            z-index: 10000 !important;
        }
    </style>

    <script>
        let customerNumberIndex = 1;
        let whatsappInstanceIndex = 1;
        let emailInstanceIndex = 0;
        let telegramInstanceIndex = 0;
        let currentImportType = '';

        function toggleChannelInstances(channelType) {
            document.getElementById('whatsappInstancesSection').classList.add('hidden');
            document.getElementById('emailInstancesSection').classList.add('hidden');
            document.getElementById('telegramInstancesSection').classList.add('hidden');

            if (channelType === 'whatsapp') {
                document.getElementById('whatsappInstancesSection').classList.remove('hidden');
                // Copy value from TinyMCE to regular textarea before switching
                if (typeof tinymce !== 'undefined' && tinymce.get('message_text_editor')) {
                    const editorContent = tinymce.get('message_text_editor').getContent();
                    document.getElementById('message_text').value = editorContent;
                    tinymce.get('message_text_editor').remove();
                }
                // Hide TinyMCE, show regular textarea
                document.getElementById('email_editor_wrapper').style.display = 'none';
                document.getElementById('regular_textarea_wrapper').style.display = 'block';
                // Управление атрибутом required
                const messageTextEditor = document.getElementById('message_text_editor');
                const messageText = document.getElementById('message_text');
                if (messageTextEditor) {
                    messageTextEditor.removeAttribute('required');
                }
                if (messageText) {
                    messageText.setAttribute('required', 'required');
                }
            } else if (channelType === 'email') {
                document.getElementById('emailInstancesSection').classList.remove('hidden');
                // Copy value from regular textarea to TinyMCE textarea before switching
                const regularValue = document.getElementById('message_text').value;
                
                // Destroy existing TinyMCE editor if it exists
                if (typeof tinymce !== 'undefined' && tinymce.get('message_text_editor')) {
                    tinymce.get('message_text_editor').remove();
                }
                
                // Show TinyMCE, hide regular textarea
                document.getElementById('email_editor_wrapper').style.display = 'block';
                document.getElementById('regular_textarea_wrapper').style.display = 'none';
                
                // Управление атрибутом required
                const messageTextEditor = document.getElementById('message_text_editor');
                const messageText = document.getElementById('message_text');
                if (messageTextEditor) {
                    messageTextEditor.setAttribute('required', 'required');
                }
                if (messageText) {
                    messageText.removeAttribute('required');
                }
                
                // Set value in textarea
                const editorTextarea = document.getElementById('message_text_editor');
                if (editorTextarea) {
                    editorTextarea.value = regularValue;
                }
                
                // Force reinitialize TinyMCE - always reinitialize to ensure it works
                // Wait a bit for DOM to update and Vue component to potentially mount
                setTimeout(function() {
                    if (typeof tinymce !== 'undefined') {
                        // Always remove existing editor first to ensure clean state
                        let existingEditor = tinymce.get('message_text_editor');
                        if (existingEditor) {
                            existingEditor.remove();
                        }
                        
                        // Wait a bit more before initializing
                        setTimeout(function() {
                            // Initialize TinyMCE with same config as Vue component
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
                                        editor.setContent(regularValue);
                                    });
                                }
                            });
                        }, 100);
                    }
                }, 200);
            } else if (channelType === 'telegram') {
                document.getElementById('telegramInstancesSection').classList.remove('hidden');
                // Copy value from TinyMCE to regular textarea before switching
                if (typeof tinymce !== 'undefined' && tinymce.get('message_text_editor')) {
                    const editorContent = tinymce.get('message_text_editor').getContent();
                    document.getElementById('message_text').value = editorContent;
                    tinymce.get('message_text_editor').remove();
                }
                // Hide TinyMCE, show regular textarea
                document.getElementById('email_editor_wrapper').style.display = 'none';
                document.getElementById('regular_textarea_wrapper').style.display = 'block';
                // Управление атрибутом required
                const messageTextEditor = document.getElementById('message_text_editor');
                const messageText = document.getElementById('message_text');
                if (messageTextEditor) {
                    messageTextEditor.removeAttribute('required');
                }
                if (messageText) {
                    messageText.setAttribute('required', 'required');
                }
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            const channelType = document.getElementById('channel_type').value;
            if (channelType) {
                toggleChannelInstances(channelType);
            }
        });
        
        // Copy TinyMCE content to textarea before form submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const channelType = document.getElementById('channel_type').value;
            if (channelType === 'email' && typeof tinymce !== 'undefined') {
                const editor = tinymce.get('message_text_editor');
                if (editor) {
                    const content = editor.getContent();
                    // Обновляем значение в textarea, который используется TinyMCE
                    const editorTextarea = document.getElementById('message_text_editor');
                    if (editorTextarea) {
                        editorTextarea.value = content;
                    }
                    
                    // Убираем required и копируем значение в скрытое поле
                    const hiddenMessageText = document.getElementById('message_text');
                    if (hiddenMessageText) {
                        hiddenMessageText.removeAttribute('required');
                        hiddenMessageText.value = content;
                    }
                }
            }
        });

        function toggleNewEmailInstanceForm(selectedValue) {
            const wrapper = document.getElementById('newEmailInstancesWrapper');
            if (wrapper) {
                if (selectedValue) {
                    // Hide new instance form when existing instance is selected
                    wrapper.style.display = 'none';
                } else {
                    // Show new instance form when "Create new" is selected
                    wrapper.style.display = 'block';
                }
            }
        }

        function addEmailInstanceRow() {
            const container = document.getElementById('emailInstancesContainer');
            const newRow = document.createElement('div');
            newRow.className = 'email-instance-row grid grid-cols-1 gap-4 mb-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg';
            newRow.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mail-instances.name') }}
                        </label>
                        <input type="text" name="mail_instances[${emailInstanceIndex}][name]"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mail-instances.host') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="mail_instances[${emailInstanceIndex}][host]"
                            placeholder="smtp.gmail.com"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mail-instances.port') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="mail_instances[${emailInstanceIndex}][port]"
                            value="587"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mail-instances.encryption') }}
                        </label>
                        <select name="mail_instances[${emailInstanceIndex}][encryption]"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="tls">TLS</option>
                            <option value="ssl">SSL</option>
                            <option value="none">None</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mail-instances.username') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="mail_instances[${emailInstanceIndex}][username]"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mail-instances.password') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="mail_instances[${emailInstanceIndex}][password]"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mail-instances.from-email') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="mail_instances[${emailInstanceIndex}][from_email]"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mail-instances.from-name') }}
                        </label>
                        <input type="text" name="mail_instances[${emailInstanceIndex}][from_name]"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="removeEmailInstanceRow(this)"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        {{ __('newsletters::app.common.actions.delete') }}
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            emailInstanceIndex++;
        }

        function removeEmailInstanceRow(button) {
            if (confirm('{{ __("newsletters::app.common.messages.confirm_delete") }}')) {
                button.closest('.email-instance-row').remove();
            }
        }

        function addTelegramInstanceRow() {
            const container = document.getElementById('telegramInstancesContainer');
            const newRow = document.createElement('div');
            newRow.className = 'telegram-instance-row grid grid-cols-1 gap-4 mb-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg';
            newRow.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.telegram-instances.bot-name') }}
                        </label>
                        <input type="text" name="telegram_instances[${telegramInstanceIndex}][bot_name]"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.telegram-instances.bot-username') }}
                        </label>
                        <input type="text" name="telegram_instances[${telegramInstanceIndex}][bot_username]"
                            placeholder="@your_bot"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.telegram-instances.bot-token') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="telegram_instances[${telegramInstanceIndex}][bot_token]"
                            placeholder="123456789:ABCdefGHIjklMNOpqrsTUVwxyz"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="removeTelegramInstanceRow(this)"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        {{ __('newsletters::app.common.actions.delete') }}
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            telegramInstanceIndex++;
        }

        function removeTelegramInstanceRow(button) {
            if (confirm('{{ __("newsletters::app.common.messages.confirm_delete") }}')) {
                button.closest('.telegram-instance-row').remove();
            }
        }

        function addCustomerNumberRow() {
            const container = document.getElementById('customerNumbersContainer');
            const newRow = document.createElement('div');
            newRow.className = 'customer-number-row grid grid-cols-1 gap-4 mb-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg';
            newRow.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.customer-numbers.phone-number') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="customer_numbers[${customerNumberIndex}][phone_number]"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                        </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.customer-numbers.name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="customer_numbers[${customerNumberIndex}][name]"
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
        }

        function removeCustomerNumberRow(button) {
            if (confirm('{{ __("newsletters::app.common.messages.confirm_delete") }}')) {
                button.closest('.customer-number-row').remove();
            }
        }

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
                        <input type="text" name="whatsapp_instances[${whatsappInstanceIndex}][password]"
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
                        {{ __('newsletters::app.common.actions.delete') }}
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
                                    <input type="text" name="whatsapp_instances[${whatsappInstanceIndex}][password]"
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
                                    {{ __('newsletters::app.common.actions.delete') }}
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

                if (fields.length >= 1) {
                    const phone_number_raw = fields[0] ? fields[0].trim() : '';
                    const phone_number = sanitizePhoneNumber(phone_number_raw);
                    const name = fields[1] ? fields[1].trim() : 'Нет';

                    console.log('Extracted data:', { phone_number, name });

                    if (phone_number) {
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
        // If phone starts with 89, replace 8 with 7 (89 -> 79)
        function sanitizePhoneNumber(phone) {
            if (!phone) return '';
            // Оставляем только цифры
            let cleaned = phone.replace(/[^\d]/g, '');
            // Если номер начинается с 89, заменяем первую цифру на 7
            if (cleaned.startsWith('89')) {
                cleaned = '7' + cleaned.substring(1);
            }
            return cleaned;
        }

        // Media file preview
        document.getElementById('media_file').addEventListener('change', function(e) {
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

        function removeMediaPreview() {
            document.getElementById('media_file').value = '';
            document.getElementById('media_preview').classList.add('hidden');
            document.getElementById('media_preview_image').src = '';
            document.getElementById('media_preview_video').src = '';
        }

        // Load filters for selected contact group
        function loadFiltersForGroup(groupId) {
            const filterSelect = document.getElementById('filter_id');
            if (!filterSelect) return;

            if (!groupId) {
                filterSelect.innerHTML = '<option value="">{{ __("newsletters::app.common.actions.select") }} {{ __("newsletters::app.admin.contact-filters.title") }}</option>';
                return;
            }

            filterSelect.disabled = true;
            filterSelect.innerHTML = '<option value="">{{ __("newsletters::app.common.messages.loading") }}...</option>';

            fetch(`/admin/newsletters/contact-groups/${groupId}/filters`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    filterSelect.innerHTML = '<option value="">{{ __("newsletters::app.common.actions.select") }} {{ __("newsletters::app.admin.contact-filters.title") }}</option>';
                    if (data.filters && data.filters.length > 0) {
                        data.filters.forEach(filter => {
                            const option = document.createElement('option');
                            option.value = filter.id;
                            option.textContent = filter.name;
                            filterSelect.appendChild(option);
                        });
                    }
                    filterSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error loading filters:', error);
                    filterSelect.innerHTML = '<option value="">{{ __("newsletters::app.common.messages.error-loading") }}</option>';
                    filterSelect.disabled = false;
                });
        }

        // Initialize filter loading on page load if contact_group_id is preselected
        document.addEventListener('DOMContentLoaded', function() {
            const contactGroupSelect = document.getElementById('contact_group_id');
            if (contactGroupSelect && contactGroupSelect.value) {
                loadFiltersForGroup(contactGroupSelect.value);
            }
        });
    </script>
</x-admin::layouts>
