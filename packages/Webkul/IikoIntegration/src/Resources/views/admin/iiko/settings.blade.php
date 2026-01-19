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
                <div class="mb-4">
                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                        @lang('iiko-integration::app.settings.configuration')
                    </p>
                </div>

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
                    <button
                        type="button"
                        id="test-connection-btn"
                        class="secondary-button"
                        onclick="testConnection()"
                    >
                        @lang('iiko-integration::app.settings.test-connection')
                    </button>

                    <button type="submit" class="primary-button">
                        @lang('admin::app.save')
                    </button>
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
