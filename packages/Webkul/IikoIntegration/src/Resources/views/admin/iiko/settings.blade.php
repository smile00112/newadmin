<x-admin::layouts>
    <x-slot:title>
        @lang('iiko-integration::app.settings.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('iiko-integration::app.settings.title')
        </p>
    </div>

    <div class="mt-7">
        <x-admin::form
            :action="route('admin.iiko.settings.store')"
            method="POST"
        >
            <input type="hidden" name="channel_code" value="{{ $channelCode }}">

            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                {{-- Tabs navigation --}}
                <div class="mb-4 border-b border-gray-200 dark:border-gray-800">
                    <nav class="flex space-x-2" aria-label="Tabs">
                        @foreach ($tabs as $tabKey => $tabName)
                            <a
                                href="{{ route('admin.iiko.settings.index', ['tab' => $tabKey, 'channel' => $channelCode]) }}"
                                class="px-4 py-2 text-sm font-medium rounded-t-lg transition-colors {{ $activeTab === $tabKey ? 'bg-blue-50 text-blue-600 border-b-2 border-blue-600 dark:bg-gray-800 dark:text-blue-400' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-800' }}"
                            >
                                {{ $tabName }}
                            </a>
                        @endforeach
                    </nav>
                </div>

                <div class="mt-4">
                    @foreach ($fields as $field)
                        <div class="mb-4">
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    {{ trans($field['title']) }}
                                </x-admin::form.control-group.label>

                                @if ($field['type'] === 'text')
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="settings[{{ $field['key'] }}]"
                                        :value="$field['value'] ?? ''"
                                        :placeholder="trans($field['title'])"
                                    />
                                @elseif ($field['type'] === 'password')
                                    <x-admin::form.control-group.control
                                        type="password"
                                        name="settings[{{ $field['key'] }}]"
                                        :value="$field['value'] ?? ''"
                                        :placeholder="trans($field['title'])"
                                    />
                                @elseif ($field['type'] === 'boolean')
                                    <x-admin::form.control-group.control
                                        type="switch"
                                        name="settings[{{ $field['key'] }}]"
                                        :value="1"
                                        :checked="(bool) ($field['value'] ?? false)"
                                    />
                                @endif

                                @if (isset($field['description']))
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ trans($field['description']) }}
                                    </p>
                                @endif
                            </x-admin::form.control-group>
                        </div>
                    @endforeach

                    <div class="flex items-center justify-between gap-4 mt-6">
                        @if ($activeTab === 'configuration')
                            <button
                                type="button"
                                id="test-connection-btn"
                                class="secondary-button"
                                onclick="testConnection()"
                            >
                                @lang('iiko-integration::app.settings.test-connection')
                            </button>
                        @else
                            <div></div>
                        @endif

                        <button type="submit" class="primary-button">
                            @lang('admin::app.save')
                        </button>
                    </div>
                </div>
            </div>
        </x-admin::form>
    </div>

    @push('scripts')
        <script>
            function testConnection() {
                const btn = document.getElementById('test-connection-btn');
                const originalText = btn.textContent;
                btn.disabled = true;
                btn.textContent = '@lang('iiko-integration::app.settings.testing')';

                fetch('{{ route('admin.iiko.settings.test') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    alert('@lang('iiko-integration::app.settings.connection-error')');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.textContent = originalText;
                });
            }
        </script>
    @endpush
</x-admin::layouts>
