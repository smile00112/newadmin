<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.channel-instances.title') }}
    </x-slot:title>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ __('newsletters::app.admin.channel-instances.title') }}
        </h1>

        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.newsletters.channel-instances.create', ['type' => $type]) }}" class="primary-button">
                {{ __('newsletters::app.common.actions.create') }} {{ __('newsletters::app.admin.channel-instances.type.' . $type) }}
            </a>
        </div>
    </div>

    <!-- Tabs -->
    <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <a href="{{ route('admin.newsletters.channel-instances.index', ['type' => 'whatsapp']) }}"
               class="@if($type === 'whatsapp') border-blue-500 text-blue-600 dark:text-blue-400 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                {{ __('newsletters::app.admin.channel-instances.type.whatsapp') }}
            </a>
            <a href="{{ route('admin.newsletters.channel-instances.index', ['type' => 'email']) }}"
               class="@if($type === 'email') border-blue-500 text-blue-600 dark:text-blue-400 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                {{ __('newsletters::app.admin.channel-instances.type.email') }}
            </a>
            <a href="{{ route('admin.newsletters.channel-instances.index', ['type' => 'telegram']) }}"
               class="@if($type === 'telegram') border-blue-500 text-blue-600 dark:text-blue-400 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                {{ __('newsletters::app.admin.channel-instances.type.telegram') }}
            </a>
        </nav>
    </div>

    <!-- Instances Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        @if($type === 'whatsapp')
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('newsletters::app.admin.whatsapp-instances.link-name') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('newsletters::app.admin.whatsapp-instances.login') }}
                            </th>
                        @elseif($type === 'email')
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('newsletters::app.admin.mail-instances.name') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('newsletters::app.admin.mail-instances.host') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('newsletters::app.admin.mail-instances.from-email') }}
                            </th>
                        @elseif($type === 'telegram')
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('newsletters::app.admin.telegram-instances.bot-name') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('newsletters::app.admin.telegram-instances.bot-username') }}
                            </th>
                        @endif
{{--                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">--}}
{{--                            {{ __('newsletters::app.admin.channel-instances.mailing-list') }}--}}
{{--                        </th>--}}
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.common.fields.status') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.common.fields.created_at') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.common.fields.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($instances as $instance)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $instance->id }}
                            </td>
                            @if($type === 'whatsapp')
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $instance->link_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $instance->login }}
                                </td>
                            @elseif($type === 'email')
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $instance->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $instance->host }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $instance->from_email }}
                                </td>
                            @elseif($type === 'telegram')
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $instance->bot_name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $instance->bot_username ?? '-' }}
                                </td>
                            @endif
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                @if($instance->mailingList)
                                    <a href="{{ route('admin.newsletters.mailing-lists.edit', $instance->mailing_list_id) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                        {{ Str::limit($instance->mailingList->message_text, 30) }}
                                    </a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if(isset($instance->active) && $instance->active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                        {{ __('newsletters::app.common.fields.active') }}
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100">
                                        {{ __('newsletters::app.common.fields.inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $instance->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.newsletters.channel-instances.edit', ['type' => $type, 'id' => $instance->id]) }}"
                                       class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
                                        {{ __('newsletters::app.common.actions.edit') }}
                                    </a>
                                    <form action="{{ route('admin.newsletters.channel-instances.destroy', ['type' => $type, 'id' => $instance->id]) }}"
                                          method="POST"
                                          class="inline"
                                          onsubmit="return confirm('{{ __('newsletters::app.admin.channel-instances.delete-confirm') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400">
                                            {{ __('newsletters::app.common.actions.delete') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('newsletters::app.admin.channel-instances.no-instances', ['type' => $type]) }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin::layouts>



