<x-admin::layouts>
    <x-slot:title>
        @lang('bonus::app.admin.levels.create')
    </x-slot>

    <x-admin::form :action="route('admin.bonus.levels.store')" method="POST">
        <div class="flex items-center justify-between">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('bonus::app.admin.levels.create')
            </p>
            <div class="flex gap-x-2.5">
                <a href="{{ route('admin.bonus.levels.index') }}" class="transparent-button">
                    @lang('admin::app.datagrid.back')
                </a>
                <button type="submit" class="primary-button">
                    @lang('admin::app.datagrid.save')
                </button>
            </div>
        </div>

        <div class="mt-7 box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    @lang('bonus::app.admin.levels.name')
                </x-admin::form.control-group.label>
                <x-admin::form.control-group.control type="text" name="name" rules="required" />
            </x-admin::form.control-group>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    @lang('bonus::app.admin.levels.cashback-percent')
                </x-admin::form.control-group.label>
                <x-admin::form.control-group.control type="number" name="cashback_percent" rules="required|integer" />
            </x-admin::form.control-group>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    @lang('bonus::app.admin.levels.threshold')
                </x-admin::form.control-group.label>
                <x-admin::form.control-group.control type="number" name="threshold_value" rules="required|integer" />
            </x-admin::form.control-group>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('bonus::app.admin.levels.sort-order')
                </x-admin::form.control-group.label>
                <x-admin::form.control-group.control type="text" name="sort_order" />
            </x-admin::form.control-group>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('bonus::app.admin.levels.is-active')
                </x-admin::form.control-group.label>
                <x-admin::form.control-group.control type="switch" name="is_active" :value="1" :checked="true" />
            </x-admin::form.control-group>
        </div>
    </x-admin::form>
</x-admin::layouts>
