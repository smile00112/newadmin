<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.registration-requests.edit-title') }}
    </x-slot:title>

    <div class="flex items-center justify-between mb-4">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.registration-requests.edit-title') }}
        </p>
        <a href="{{ route('admin.newsletters.registration-requests.index') }}" class="secondary-button">
            {{ __('newsletters::app.common.actions.back') }}
        </a>
    </div>

    <form method="POST" action="{{ route('admin.newsletters.registration-requests.update', $request->id) }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('newsletters::app.admin.registration-requests.name') }}
                </label>
                <input type="text"
                       value="{{ $request->name }}"
                       disabled
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('newsletters::app.admin.registration-requests.email') }}
                </label>
                <input type="email"
                       value="{{ $request->email }}"
                       disabled
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('newsletters::app.admin.registration-requests.phone') }}
                </label>
                <input type="text"
                       value="{{ $request->phone }}"
                       disabled
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
            </div>
{{--
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('newsletters::app.admin.registration-requests.plan') }}
                </label>
                <input type="text"
                       value="{{ $request->plan ? ucfirst($request->plan) : '-' }}"
                       disabled
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('newsletters::app.admin.registration-requests.status') }}
                </label>
                <select name="status"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    <option value="pending" {{ $request->status === 'pending' ? 'selected' : '' }}>
                        {{ __('newsletters::app.admin.registration-requests.status-pending') }}
                    </option>
                    <option value="processed" {{ $request->status === 'processed' ? 'selected' : '' }}>
                        {{ __('newsletters::app.admin.registration-requests.status-processed') }}
                    </option>
                    <option value="rejected" {{ $request->status === 'rejected' ? 'selected' : '' }}>
                        {{ __('newsletters::app.admin.registration-requests.status-rejected') }}
                    </option>
                </select>
            </div>
--}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('newsletters::app.admin.registration-requests.created_at') }}
                </label>
                <input type="text"
                       value="{{ $request->created_at->format('Y-m-d H:i:s') }}"
                       disabled
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
            </div>
        </div>

        <div class="mt-6 flex items-center gap-x-2.5">
            <button type="submit" class="primary-button">
                {{ __('newsletters::app.common.actions.save') }}
            </button>
            <a href="{{ route('admin.newsletters.registration-requests.index') }}" class="secondary-button">
                {{ __('newsletters::app.common.actions.cancel') }}
            </a>
        </div>
    </form>
</x-admin::layouts>

