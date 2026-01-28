<x-admin::layouts>
    <x-slot:title>
        @lang('bonus::app.admin.levels.edit')
    </x-slot>

    <x-admin::form :action="route('admin.bonus.levels.update', $level->id)" method="PUT">
        <div class="flex items-center justify-between">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('bonus::app.admin.levels.edit')
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
                <x-admin::form.control-group.control type="text" name="name" :value="old('name', $level->name)" rules="required" />
            </x-admin::form.control-group>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    @lang('bonus::app.admin.levels.cashback-percent')
                </x-admin::form.control-group.label>
                <x-admin::form.control-group.control type="text" name="cashback_percent" :value="old('cashback_percent', $level->cashback_percent)" rules="required|numeric" />
            </x-admin::form.control-group>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    @lang('bonus::app.admin.levels.calculation-type')
                </x-admin::form.control-group.label>
                <x-admin::form.control-group.control type="select" name="calculation_type" rules="required">
                    <option value="orders_count" {{ $level->calculation_type === 'orders_count' ? 'selected' : '' }}>@lang('bonus::app.admin.levels.orders-count')</option>
                    <option value="total_spent" {{ $level->calculation_type === 'total_spent' ? 'selected' : '' }}>@lang('bonus::app.admin.levels.total-spent')</option>
                    <option value="cart_value" {{ $level->calculation_type === 'cart_value' ? 'selected' : '' }}>@lang('bonus::app.admin.levels.cart-value')</option>
                </x-admin::form.control-group.control>
            </x-admin::form.control-group>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    @lang('bonus::app.admin.levels.threshold')
                </x-admin::form.control-group.label>
                <x-admin::form.control-group.control type="text" name="threshold_value" :value="old('threshold_value', $level->threshold_value)" rules="required|numeric" />
            </x-admin::form.control-group>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('bonus::app.admin.levels.sort-order')
                </x-admin::form.control-group.label>
                <x-admin::form.control-group.control type="text" name="sort_order" :value="old('sort_order', $level->sort_order)" />
            </x-admin::form.control-group>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('bonus::app.admin.levels.is-active')
                </x-admin::form.control-group.label>
                <x-admin::form.control-group.control type="switch" name="is_active" :value="1" :checked="old('is_active', $level->is_active)" />
            </x-admin::form.control-group>
        </div>
    </x-admin::form>
</x-admin::layouts>
