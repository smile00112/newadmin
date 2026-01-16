<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.registration-requests.edit-title') }}
    </x-slot:title>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap mb-6">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.registration-requests.edit-title') }}
        </p>

        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.newsletters.registration-requests.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                {{ __('newsletters::app.common.actions.back') }}
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ __('newsletters::app.admin.registration-requests.edit-title') }}
            </h2>
        </div>

        <form method="POST" action="{{ route('admin.newsletters.registration-requests.update', $request->id) }}" class="p-6 space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('newsletters::app.admin.registration-requests.name') }}
                </label>
                <input type="text"
                       value="{{ $request->name }}"
                       disabled
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('newsletters::app.admin.registration-requests.email') }}
                </label>
                <input type="email"
                       value="{{ $request->email }}"
                       disabled
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('newsletters::app.admin.registration-requests.phone') }}
                </label>
                <input type="text"
                       value="{{ $request->phone }}"
                       disabled
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed">
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
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed">
            </div>

            <div class="flex justify-end gap-x-2">
                <a href="{{ route('admin.newsletters.registration-requests.index') }}" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                    {{ __('newsletters::app.common.actions.cancel') }}
                </a>
                <button type="submit" 
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    {{ __('newsletters::app.common.actions.save') }}
                </button>
            </div>
        </form>
    </div>
</x-admin::layouts>




