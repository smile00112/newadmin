<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.channel-instances.title') }} - {{ __('newsletters::app.common.actions.create') }} {{ __('newsletters::app.admin.channel-instances.type.' . $type) }}
    </x-slot:title>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ __('newsletters::app.admin.channel-instances.title') }} - {{ __('newsletters::app.common.actions.create') }} {{ __('newsletters::app.admin.channel-instances.type.' . $type) }}
        </h1>

        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.newsletters.channel-instances.index', ['type' => $type]) }}" class="secondary-button">
                Назад
            </a>
        </div>
    </div>

    <form action="{{ route('admin.newsletters.channel-instances.store', ['type' => $type]) }}" method="POST">
        @csrf

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            @if($type === 'whatsapp')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="link_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.whatsapp-instances.link-name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="link_name"
                               id="link_name"
                               value="{{ old('link_name') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                               required>
                        @error('link_name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="login" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.whatsapp-instances.login') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="login"
                               id="login"
                               value="{{ old('login') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                               required>
                        @error('login')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.whatsapp-instances.password') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="password"
                               name="password"
                               id="password"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                               required>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

            @elseif($type === 'email')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mail-instances.name') }}
                        </label>
                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="host" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mail-instances.host') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="host"
                               id="host"
                               value="{{ old('host') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                               required>
                        @error('host')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="port" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mail-instances.port') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               name="port"
                               id="port"
                               value="{{ old('port', 587) }}"
                               min="1"
                               max="65535"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                               required>
                        @error('port')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mail-instances.username') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="username"
                               id="username"
                               value="{{ old('username') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                               required>
                        @error('username')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mail-instances.password') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="password"
                               name="password"
                               id="password"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                               required>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="encryption" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mail-instances.encryption') }}
                        </label>
                        <select name="encryption"
                                id="encryption"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">{{ __('newsletters::app.common.actions.select') }}</option>
                            <option value="tls" {{ old('encryption') === 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl" {{ old('encryption') === 'ssl' ? 'selected' : '' }}>SSL</option>
                        </select>
                        @error('encryption')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="from_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mail-instances.from-email') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="email"
                               name="from_email"
                               id="from_email"
                               value="{{ old('from_email') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                               required>
                        @error('from_email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="from_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mail-instances.from-name') }}
                        </label>
                        <input type="text"
                               name="from_name"
                               id="from_name"
                               value="{{ old('from_name') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        @error('from_name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if($type === 'email' && $companies->isNotEmpty())
                    <div class="mt-6">
                        <label for="company_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.companies.company') }}
                            @if($isAdmin)
                                <span class="text-red-500">*</span>
                            @endif
                        </label>
                        <select name="company_id"
                                id="company_id"
                                @if($isOwner) disabled @endif
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @if($isOwner) bg-gray-100 dark:bg-gray-800 cursor-not-allowed @endif"
                                @if($isOwner) style="pointer-events: none;" @endif>
                            <option value="">{{ __('newsletters::app.common.actions.select') }}</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ old('company_id', $isOwner ? $company->id : '') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($isOwner)
                            <input type="hidden" name="company_id" value="{{ $companies->first()->id }}">
                        @endif
                        @error('company_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

            @elseif($type === 'telegram')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="bot_token" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.telegram-instances.bot-token') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="bot_token"
                               id="bot_token"
                               value="{{ old('bot_token') }}"
                               placeholder="123456789:ABCdefGHIjklMNOpqrsTUVwxyz"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                               required>
                        @error('bot_token')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="bot_username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.telegram-instances.bot-username') }}
                        </label>
                        <input type="text"
                               name="bot_username"
                               id="bot_username"
                               value="{{ old('bot_username') }}"
                               placeholder="@your_bot"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        @error('bot_username')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="bot_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.telegram-instances.bot-name') }}
                        </label>
                        <input type="text"
                               name="bot_name"
                               id="bot_name"
                               value="{{ old('bot_name') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        @error('bot_name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @endif

            <!-- Common fields -->
            <div class="mt-6">
                <label for="mailing_list_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('newsletters::app.admin.channel-instances.mailing-list') }}
                </label>
                <select name="mailing_list_id"
                        id="mailing_list_id"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">{{ __('newsletters::app.common.actions.select') }}</option>
                    @foreach($mailingLists as $mailingList)
                        <option value="{{ $mailingList->id }}" {{ old('mailing_list_id') == $mailingList->id ? 'selected' : '' }}>
                            {{ Str::limit($mailingList->message_text, 50) }}
                        </option>
                    @endforeach
                </select>
                @error('mailing_list_id')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            @if($type === 'email' || $type === 'telegram')
                <div class="mt-6">
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="active"
                               value="1"
                               {{ old('active', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            {{ __('newsletters::app.common.fields.active') }}
                        </span>
                    </label>
                </div>
            @endif
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="primary-button">
                {{ __('newsletters::app.common.actions.create') }}
            </button>
        </div>
    </form>
</x-admin::layouts>

