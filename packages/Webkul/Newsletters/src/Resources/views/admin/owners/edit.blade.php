<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.owners.edit-title') }}
    </x-slot:title>

    <div class="flex items-center justify-between mb-4">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.owners.edit-title') }}
        </p>
        <a href="{{ route('admin.newsletters.owners.index') }}" class="secondary-button">
            {{ __('newsletters::app.common.actions.back') }}
        </a>
    </div>

    <form method="POST" action="{{ route('admin.newsletters.owners.update', $owner->id) }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-6">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('newsletters::app.admin.owners.name') }}
                    <span class="text-red-600">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    value="{{ old('name', $owner->name) }}" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('name') border-red-500 dark:border-red-500 @enderror"
                >
                @error('name')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('newsletters::app.admin.owners.email') }}
                    <span class="text-red-600">*</span>
                </label>
                <input 
                    type="email" 
                    name="email" 
                    id="email" 
                    value="{{ old('email', $owner->email) }}" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('email') border-red-500 dark:border-red-500 @enderror"
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div>
                <input type="hidden" name="status" value="0">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <div class="relative">
                        <input 
                            type="checkbox" 
                            name="status" 
                            value="1" 
                            {{ old('status', $owner->status) ? 'checked' : '' }}
                            class="sr-only peer"
                        >
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('newsletters::app.admin.owners.status') }} 
                        <span class="text-gray-500 dark:text-gray-400">({{ __('newsletters::app.admin.owners.active') }})</span>
                    </span>
                </label>
            </div>

            <!-- Company (Read-only) -->
            @if($owner->company)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.owners.company') }}
                    </label>
                    <input 
                        type="text" 
                        value="{{ $owner->company->name }}" 
                        disabled
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                    >
                </div>
            @endif

            <!-- Balance (Read-only) -->
            @if($owner->company && $owner->company->account)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.owners.balance') }}
                    </label>
                    <input 
                        type="text" 
                        value="{{ number_format($owner->company->account->balance, 2) }}" 
                        disabled
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                    >
                </div>
            @endif
        </div>

        <div class="mt-6 flex items-center gap-x-2.5">
            <button type="submit" class="primary-button">
                {{ __('newsletters::app.common.actions.save') }}
            </button>
            <a href="{{ route('admin.newsletters.owners.index') }}" class="secondary-button">
                {{ __('newsletters::app.common.actions.cancel') }}
            </a>
        </div>
    </form>
</x-admin::layouts>

