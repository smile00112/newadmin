@php
    $constructors = $product->constructor()->with([
        'groups',
        'groups.products.inventory_indices',
        'groups.products.images',
    ])->get();

    // Transform the data to include pivot data in products
    $constructors->each(function ($constructor) {
        $constructor->groups->each(function ($group) {
            $group->products->each(function ($product) {

                // Add pivot data to the product object
                $product->default = $product->pivot->default === 1;
                $product->sort = $product->pivot->sort ?? 0;

                //dump($product);
            });
        });
    });
@endphp

{!! view_render_event('bagisto.admin.catalog.product.edit.form.types.constructor.before', ['product' => $product]) !!}

<v-constructor-options :errors="errors"></v-constructor-options>

{!! view_render_event('bagisto.admin.catalog.product.edit.form.types.constructor.after', ['product' => $product]) !!}

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-constructor-options-template"
    >
        <div class="box-shadow relative rounded bg-white dark:bg-gray-900">
            <!-- Panel Header -->
            <div class="mb-2.5 flex justify-between gap-5 p-4">
                <div class="flex flex-col gap-2">
                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.catalog.products.edit.types.constructor.title')
                    </p>

                    <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                        @lang('admin::app.catalog.products.edit.types.constructor.info')
                    </p>
                </div>

                <!-- Add Button -->
                <div class="flex items-center gap-x-1">
                    <button
                        type="button"
                        class="secondary-button"
                        @click="addConstructor()"
                    >
                        @lang('admin::app.catalog.products.edit.types.constructor.add-btn')
                    </button>
                </div>
            </div>

            <!-- Panel Content -->
            <div
                class="grid"
                v-if="constructors.length"
            >
                <!-- Constructor Options -->
                <div
                    v-for="(constructor, constructorIndex) in constructors"
                    :key="constructor.id || constructorIndex"
                    class="border-b border-slate-300 p-4 dark:border-gray-800"
                >
                    <!-- Constructor Header -->
                    <div class="mb-4 flex justify-between items-center">
                        <div class="flex flex-col gap-1">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                                @lang('admin::app.catalog.products.edit.types.constructor.constructor-title')
                            </h3>
                            <div class="flex gap-4 text-sm text-gray-600 dark:text-gray-300">
                                <span v-if="constructor.visible">
                                    @lang('admin::app.catalog.products.edit.types.constructor.visible')
                                </span>
                                <span v-if="constructor.required">
                                    @lang('admin::app.catalog.products.edit.types.constructor.required')
                                </span>
                                <span v-if="constructor.combo">
                                    @lang('admin::app.catalog.products.edit.types.constructor.combo')
                                </span>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="secondary-button text-sm"
                                @click="editConstructor(constructor, constructorIndex)"
                            >
                                @lang('admin::app.catalog.products.edit.types.constructor.edit-btn')
                            </button>

                            <button
                                type="button"
                                class="danger-button text-sm"
                                @click="removeConstructor(constructorIndex)"
                            >
                                @lang('admin::app.catalog.products.edit.types.constructor.delete-btn')
                            </button>
                        </div>
                    </div>

                    <!-- Constructor Groups -->
                    <div v-if="constructor.groups && constructor.groups.length" class="space-y-3">
                        <div
                            v-for="(group, groupIndex) in constructor.groups"
                            :key="group.id || groupIndex"
                            class="border border-gray-200 rounded-lg p-3 dark:border-gray-700"
                        >
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="font-medium text-gray-800 dark:text-white">
                                    @{{ group.name || '@lang('admin::app.catalog.products.edit.types.constructor.group')' + (groupIndex + 1) }}
                                </h4>
                                <div class="flex gap-2">
{{--                                    <button--}}
{{--                                        type="button"--}}
{{--                                        class="secondary-button text-xs"--}}
{{--                                        @click="editGroup(group, constructorIndex, groupIndex)"--}}
{{--                                    >--}}
{{--                                        @lang('admin::app.catalog.products.edit.types.constructor.edit-group-btn')--}}
{{--                                    </button>--}}
{{--                                    <button--}}
{{--                                        type="button"--}}
{{--                                        class="danger-button text-xs"--}}
{{--                                        @click="removeGroup(constructorIndex, groupIndex)"--}}
{{--                                    >--}}
{{--                                        @lang('admin::app.catalog.products.edit.types.constructor.delete-group-btn')--}}
{{--                                    </button>--}}
                                </div>
                            </div>

                            <div class="text-sm text-gray-600 dark:text-gray-300 mb-2">
                                <span class="inline-block mr-4">
                                    <strong>@lang('admin::app.catalog.products.edit.types.constructor.field-type'):</strong>
                                    @{{ getFieldTypeLabel(group.field_type) }}
                                </span>
                                <span class="inline-block mr-4">
                                    <strong>@lang('admin::app.catalog.products.edit.types.constructor.checked-type'):</strong>
                                    @{{ getCheckedTypeLabel(group.checked_type) }}
                                </span>
                                <span class="inline-block">
                                    <strong>@lang('admin::app.catalog.products.edit.types.constructor.products-count'):</strong>
                                    @{{ group.products ? group.products.length : 0 }}
                                </span>
                            </div>

                            <!-- Group Products -->
                            <div v-if="group.products && group.products.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                <div
                                    v-for="product in group.products"
                                    :key="product.id"
                                    class="flex items-center gap-2 p-2 bg-gray-50 rounded dark:bg-gray-800"
                                >
                                    <div class="w-8 h-8 rounded overflow-hidden">
                                        <img
                                            v-if="product.images && product.images.length"
                                            :src="product.images[0].url"
                                            :alt="product.name"
                                            class="w-full h-full object-cover"
                                        >
                                        <div v-else class="w-full h-full bg-gray-200 flex items-center justify-center">
                                            <i class="icon-package text-gray-400"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-800 dark:text-white truncate">
                                            @{{ product.name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            @{{ product.sku }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Inputs -->
                    <input
                        type="hidden"
                        :name="'constructor[' + constructorIndex + '][id]'"
                        :value="constructor.id || ''"
                    />
                    <input
                        type="hidden"
                        :name="'constructor[' + constructorIndex + '][visible]'"
                        :value="constructor.visible ? 1 : 0"
                    />
                    <input
                        type="hidden"
                        :name="'constructor[' + constructorIndex + '][required]'"
                        :value="constructor.required ? 1 : 0"
                    />
                    <input
                        type="hidden"
                        :name="'constructor[' + constructorIndex + '][combo]'"
                        :value="constructor.combo ? 1 : 0"
                    />
                    <input
                        type="hidden"
                        :name="'constructor[' + constructorIndex + '][discount]'"
                        :value="constructor.discount ? 1 : 0"
                    />
                    <input
                        type="hidden"
                        :name="'constructor[' + constructorIndex + '][design]'"
                        :value="constructor.design || 'category'"
                    />
                    <input
                        type="hidden"
                        :name="'constructor[' + constructorIndex + '][discount_type]'"
                        :value="constructor.discount_type || ''"
                    />
                    <input
                        type="hidden"
                        :name="'constructor[' + constructorIndex + '][discount_value]'"
                        :value="constructor.discount_value || 0"
                    />
                    <input
                        type="hidden"
                        :name="'constructor[' + constructorIndex + '][min_selected_sum]'"
                        :value="constructor.min_selected_sum || 0"
                    />
                    <input
                        type="hidden"
                        :name="'constructor[' + constructorIndex + '][groups]'"
                        :value="JSON.stringify(constructor.groups || [])"
                    />
                </div>
            </div>

            <!-- Empty State -->
            <div
                class="grid justify-center justify-items-center gap-3.5 px-2.5 py-10"
                v-else
            >
{{--                <img--}}
{{--                    src="{{ bagisto_asset('images/icon-add-product.svg') }}"--}}
{{--                    class="h-20 w-20 dark:mix-blend-exclusion dark:invert"--}}
{{--                />--}}

{{--                <div class="flex flex-col items-center gap-1.5">--}}
{{--                    <p class="text-base font-semibold text-gray-400">--}}
{{--                        @lang('admin::app.catalog.products.edit.types.constructor.empty-title')--}}
{{--                    </p>--}}

{{--                    <p class="text-gray-400">--}}
{{--                        @lang('admin::app.catalog.products.edit.types.constructor.empty-info')--}}
{{--                    </p>--}}
{{--                </div>--}}

{{--                <div--}}
{{--                    class="secondary-button text-sm"--}}
{{--                    @click="addConstructor()"--}}
{{--                >--}}
{{--                    @lang('admin::app.catalog.products.edit.types.constructor.add-btn')--}}
{{--                </div>--}}
            </div>

            <!-- Constructor Form (Inline) -->
            <div v-if="showForm" class="border border-gray-200 rounded-lg p-4 mb-4 dark:border-gray-700">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                        @{{ isEditMode ? '@lang('admin::app.catalog.products.edit.types.constructor.edit-constructor')' : '@lang('admin::app.catalog.products.edit.types.constructor.create-constructor')' }}
                    </h3>
                </div>

                <div class="space-y-2 md:space-y-4">
                    <!-- Basic Settings -->
                    <div class="grid md:grid-cols-2 md:gap-4">
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                id="visible"
                                v-model="form.visible"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label for="visible" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                @lang('admin::app.catalog.products.edit.types.constructor.visible')
                            </label>
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                id="required"
                                v-model="form.required"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label for="required" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                @lang('admin::app.catalog.products.edit.types.constructor.required')
                            </label>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 md:gap-4">
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                id="combo"
                                v-model="form.combo"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label for="combo" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                @lang('admin::app.catalog.products.edit.types.constructor.combo')
                            </label>
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                id="discount"
                                v-model="form.discount"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label for="discount" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                @lang('admin::app.catalog.products.edit.types.constructor.discount')
                            </label>
                        </div>
                    </div>

                    <!-- Discount Settings -->
                    <div v-if="form.discount" class="grid grid-cols-2 gap-4 pt-2 pb-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                @lang('admin::app.catalog.products.edit.types.constructor.discount-type')
                            </label>
                            <select
                                v-model="form.discount_type"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">@lang('admin::app.catalog.products.edit.types.constructor.discount-none')</option>
                                <option value="percent">@lang('admin::app.catalog.products.edit.types.constructor.discount-percent')</option>
                                <option value="fixed">@lang('admin::app.catalog.products.edit.types.constructor.discount-fixed')</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                @lang('admin::app.catalog.products.edit.types.constructor.discount-value')
                            </label>
                            <input
                                type="number"
                                v-model="form.discount_value"
                                min="0"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 md:gap-4">
                        <!-- Design Selection -->
                        <div class="flex items-center gap-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                @lang('admin::app.catalog.products.edit.types.constructor.design')
                            </label>
                            <select
                                v-model="form.design"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="line">@lang('admin::app.catalog.products.edit.types.constructor.design-line')</option>
                                <option value="category">@lang('admin::app.catalog.products.edit.types.constructor.design-category')</option>
                                <option value="table">@lang('admin::app.catalog.products.edit.types.constructor.design-table')</option>
                            </select>
                        </div>

                        <!-- Min Selected Sum -->
                        <div class="flex items-center gap-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                @lang('admin::app.catalog.products.edit.types.constructor.min-selected-sum')
                            </label>
                            <input
                                type="number"
                                v-model="form.min_selected_sum"
                                min="0"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                    </div>

                    <!-- Groups Section -->
                    <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-md font-semibold text-gray-800 dark:text-white">
                                @lang('admin::app.catalog.products.edit.types.constructor.groups')
                            </h4>
                            <button
                                type="button"
                                class="px-3 py-1 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-blue-900 dark:text-blue-300 dark:border-blue-700 dark:hover:bg-blue-800"
                                @click.prevent="addGroup()"
                            >
                                @lang('admin::app.catalog.products.edit.types.constructor.add-group')
                            </button>
                        </div>

                        <!-- Groups List -->
                        <div v-if="form.groups && form.groups.length" class="space-y-3">
                            <div
                                v-for="(group, groupIndex) in form.groups"
                                :key="groupIndex"
                                class="border border-gray-200 rounded-lg p-3 dark:border-gray-700"
                            >
                                <div class="flex justify-between items-start mb-3">
                                    <h5 class="text-sm font-medium text-gray-800 dark:text-white">
                                        @{{ group.name || '@lang('admin::app.catalog.products.edit.types.constructor.group')' + ' ' + (groupIndex + 1) }}
                                    </h5>
                                    <div class="flex gap-2">
                                        <button
                                            type="button"
                                            class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                            @click="editGroup(groupIndex)"
                                        >
                                            @lang('admin::app.catalog.products.edit.types.constructor.edit-group')
                                        </button>
                                        <button
                                            type="button"
                                            class="text-xs text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                            @click="removeGroup(groupIndex)"
                                        >
                                            @lang('admin::app.catalog.products.edit.types.constructor.delete-group')
                                        </button>
                                    </div>
                                </div>

                                <!-- Group Settings Display -->
                                <div class="grid grid-cols-2 gap-4 text-xs text-gray-600 dark:text-gray-400">
                                    <div>
                                        <strong>@lang('admin::app.catalog.products.edit.types.constructor.field-type'):</strong>
                                        @{{ getFieldTypeLabel(group.field_type) }}
                                    </div>
                                    <div>
                                        <strong>@lang('admin::app.catalog.products.edit.types.constructor.checked-type'):</strong>
                                        @{{ getCheckedTypeLabel(group.checked_type) }}
                                    </div>
                                    <div>
                                        <strong>@lang('admin::app.catalog.products.edit.types.constructor.quantity-min'):</strong>
                                        @{{ group.quantity_min || 0 }}
                                    </div>
                                    <div>
                                        <strong>@lang('admin::app.catalog.products.edit.types.constructor.quantity-max'):</strong>
                                        @{{ group.quantity_max || 0 }}
                                    </div>
                                </div>

                                <!-- Group Products Count -->
                                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    <strong>@lang('admin::app.catalog.products.edit.types.constructor.products-count'):</strong>
                                    @{{ group.products ? group.products.length : 0 }}
                                </div>
                            </div>
                        </div>

                        <!-- Empty Groups State -->
                        <div v-else class="text-center py-4 text-gray-500 dark:text-gray-400">
                            <p class="text-sm">@lang('admin::app.catalog.products.edit.types.constructor.no-groups')</p>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end gap-2 mt-6">
                    <button
                        type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                        @click.prevent="cancelForm()"
                    >
                        @lang('admin::app.catalog.products.edit.types.constructor.cancel-btn')
                    </button>

                    <button
                        type="button"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        @click.prevent="saveConstructor()"
                    >
                        @lang('admin::app.catalog.products.edit.types.constructor.save-btn')
                    </button>
                </div>
            </div>

            <!-- Group Form Modal -->
            <x-admin::modal ref="groupFormModal">
                <!-- Modal Header -->
                <x-slot:header>
                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                        @{{ isEditGroupMode ? '@lang('admin::app.catalog.products.edit.types.constructor.edit-group')' : '@lang('admin::app.catalog.products.edit.types.constructor.add-group')' }}
                    </p>
                </x-slot:header>

                <!-- Modal Content -->
                <x-slot:content>
                    <div class="space-y-4">
                        <!-- Group Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                @lang('admin::app.catalog.products.edit.types.constructor.group-name')
                            </label>
                            <input
                                type="text"
                                v-model="groupForm.name"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                :placeholder="'@lang('admin::app.catalog.products.edit.types.constructor.group-name-placeholder')'"
                            />
                        </div>

                        <!-- Field Type and Checked Type -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    @lang('admin::app.catalog.products.edit.types.constructor.field-type')
                                </label>
                                <select
                                    v-model="groupForm.field_type"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="checkbox">@lang('admin::app.catalog.products.edit.types.constructor.field-type-checkbox')</option>
                                    <option value="radio">@lang('admin::app.catalog.products.edit.types.constructor.field-type-radio')</option>
                                    <option value="list">@lang('admin::app.catalog.products.edit.types.constructor.field-type-list')</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    @lang('admin::app.catalog.products.edit.types.constructor.checked-type')
                                </label>
                                <select
                                    v-model="groupForm.checked_type"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="once">@lang('admin::app.catalog.products.edit.types.constructor.checked-type-once')</option>
                                    <option value="multiple">@lang('admin::app.catalog.products.edit.types.constructor.checked-type-multiple')</option>
                                </select>
                            </div>
                        </div>

                        <!-- Quantity Min and Max -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    @lang('admin::app.catalog.products.edit.types.constructor.quantity-min')
                                </label>
                                <input
                                    type="number"
                                    v-model="groupForm.quantity_min"
                                    min="0"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    @lang('admin::app.catalog.products.edit.types.constructor.quantity-max')
                                </label>
                                <input
                                    type="number"
                                    v-model="groupForm.quantity_max"
                                    min="0"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                            </div>
                        </div>

                        <!-- Group Options -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="show_title"
                                    v-model="groupForm.show_title"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <label for="show_title" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    @lang('admin::app.catalog.products.edit.types.constructor.show-title')
                                </label>
                            </div>

                            <div class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="opened_by_default"
                                    v-model="groupForm.opened_by_default"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <label for="opened_by_default" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    @lang('admin::app.catalog.products.edit.types.constructor.opened-by-default')
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="zero_price"
                                    v-model="groupForm.zero_price"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <label for="zero_price" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    @lang('admin::app.catalog.products.edit.types.constructor.zero-price')
                                </label>
                            </div>

                            <div class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="required"
                                    v-model="groupForm.required"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <label for="required" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    @lang('admin::app.catalog.products.edit.types.constructor.required')
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                id="hidden"
                                v-model="groupForm.hidden"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label for="hidden" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                @lang('admin::app.catalog.products.edit.types.constructor.hidden')
                            </label>
                        </div>

                        <!-- Sort Order -->
{{--                        <div>--}}
{{--                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">--}}
{{--                                @lang('admin::app.catalog.products.edit.types.constructor.sort')--}}
{{--                            </label>--}}
{{--                            <input--}}
{{--                                type="number"--}}
{{--                                v-model="groupForm.sort"--}}
{{--                                min="0"--}}
{{--                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"--}}
{{--                            />--}}
{{--                        </div>--}}

                        <!-- Products Selection -->
                        <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
                            <div class="flex justify-between items-center mb-4">
                                <h5 class="text-md font-semibold text-gray-800 dark:text-white">
                                    @lang('admin::app.catalog.products.edit.types.constructor.group-products')
                                </h5>
                                <button
                                    type="button"
                                    class="px-3 py-1 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-blue-900 dark:text-blue-300 dark:border-blue-700 dark:hover:bg-blue-800"
                                    @click.prevent="openProductSearch()"
                                >
                                    @lang('admin::app.catalog.products.edit.types.constructor.add-products')
                                </button>
                            </div>

                            <!-- Selected Products -->
                            <div v-if="groupForm.products && groupForm.products.length" class="space-y-2">
                                <div
                                    v-for="(product, productIndex) in groupForm.products"
                                    :key="product.id"
                                    class="flex items-center justify-between p-3 border border-gray-200 rounded-lg dark:border-gray-700"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded overflow-hidden">
                                            <img
                                                v-if="product.images && product.images.length"
                                                :src="product.images[0].url"
                                                :alt="product.name"
                                                class="w-full h-full object-cover"
                                            >
                                            <div v-else class="w-full h-full bg-gray-200 flex items-center justify-center">
                                                <i class="icon-package text-gray-400"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-800 dark:text-white">
                                                @{{ product.name }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                @{{ product.sku }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center gap-2">
                                            <input
                                                type="checkbox"
                                                :id="'default_' + product.id + '__' + product.default"
                                                v-model="product.default"
                                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            />
                                            <label :for="'default_' + product.id" class="text-xs text-gray-600 dark:text-gray-400">
                                                @lang('admin::app.catalog.products.edit.types.constructor.default')
                                            </label>
                                        </div>

{{--                                        <div class="flex items-center gap-2">--}}
{{--                                            <label class="text-xs text-gray-600 dark:text-gray-400">--}}
{{--                                                @lang('admin::app.catalog.products.edit.types.constructor.sort'):--}}
{{--                                            </label>--}}
{{--                                            <input--}}
{{--                                                type="number"--}}
{{--                                                v-model="product.sort"--}}
{{--                                                min="0"--}}
{{--                                                class="w-16 rounded border border-gray-300 px-2 py-1 text-xs focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"--}}
{{--                                            />--}}
{{--                                        </div>--}}

                                        <button
                                            type="button"
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                            @click="removeProductFromGroup(productIndex)"
                                        >
                                            <i class="icon-trash text-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty Products State -->
                            <div v-else class="text-center py-4 text-gray-500 dark:text-gray-400">
                                <p class="text-sm">@lang('admin::app.catalog.products.edit.types.constructor.no-products')</p>
                            </div>
                        </div>
                    </div>
                </x-slot:content>

                <!-- Modal Footer -->
                <x-slot:footer>
                    <div class="flex items-center gap-x-2.5">
                        <x-admin::button
                            button-type="button"
                            class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                            :title="trans('admin::app.catalog.products.edit.types.constructor.cancel-btn')"
                            @click.prevent="$refs.groupFormModal.close()"
                        />

                        <x-admin::button
                            button-type="button"
                            class="primary-button"
                            :title="trans('admin::app.catalog.products.edit.types.constructor.save-group-btn')"
                            @click.prevent="saveGroup()"
                        />
                    </div>
                </x-slot:footer>
            </x-admin::modal>

            <!-- Product Search Modal -->
            <x-admin::products.search
                ref="productSearchModal"
                ::added-product-ids="addedProductIds"
                {{--                ::query-params="{type: 'simple', exclude_customizable_products: 1}"--}}
                ::search-ingredients="true"
                @onProductAdded="addProductToGroup($event)"
            />
        </div>
    </script>

    <script type="module">
        app.component('v-constructor-options', {
            template: '#v-constructor-options-template',

            props: ['errors'],

            data() {
                return {
                    constructors: @json($constructors),
                    showForm: false,
                    isEditMode: false,
                    editingIndex: null,
                    isEditGroupMode: false,
                    editingGroupIndex: null,
                    form: {
                        visible: true,
                        required: false,
                        combo: false,
                        discount: false,
                        design: 'category',
                        discount_type: '',
                        discount_value: 0,
                        min_selected_sum: 0,
                        groups: []
                    },
                    groupForm: {
                        name: '',
                        field_type: 'checkbox',
                        checked_type: 'once',
                        quantity_min: 0,
                        quantity_max: 0,
                        show_title: true,
                        opened_by_default: true,
                        zero_price: true,
                        required: false,
                        hidden: false,
                        sort: 0,
                        products: []
                    }
                }
            },

            computed: {
                addedProductIds() {
                    return this.groupForm.products.map(product => product.id);
                }
            },

            methods: {
                addConstructor() {
                    this.resetForm();
                    this.showForm = true;
                    this.isEditMode = false;
                },

                editConstructor(constructor, index) {
                    this.form = { ...constructor };

                    // Ensure groups and their products have proper pivot data
                    if (this.form.groups && Array.isArray(this.form.groups)) {
                        this.form.groups = this.form.groups.map(group => ({
                            ...group,
                            products: group.products ? group.products.map(product => ({
                                ...product,
                                default: product.default || false,
                                sort: product.sort || 0
                            })) : []
                        }));
                    }

                    this.isEditMode = true;
                    this.editingIndex = index;
                    this.showForm = true;
                },

                saveConstructor() {
                    if (this.isEditMode) {
                        this.constructors[this.editingIndex] = { ...this.form };
                    } else {
                        this.constructors.push({ ...this.form });
                    }

                    this.cancelForm();
                },

                cancelForm() {
                    this.showForm = false;
                    this.resetForm();
                },

                resetForm() {
                    this.form = {
                        visible: true,
                        required: false,
                        combo: false,
                        discount: false,
                        design: 'category',
                        discount_type: '',
                        discount_value: 0,
                        min_selected_sum: 0,
                        groups: []
                    };
                    this.isEditMode = false;
                    this.editingIndex = null;
                },

                removeConstructor(index) {
                    this.$emitter.emit('open-confirm-modal', {
                        agree: () => {
                            this.constructors.splice(index, 1);
                        }
                    });
                },

                addGroup() {
                    this.resetGroupForm();
                    this.isEditGroupMode = false;
                    this.editingGroupIndex = null;
                    this.$refs.groupFormModal.open();
                },

                editGroup(groupIndex) {
                    const group = this.form.groups[groupIndex];
                    this.groupForm = { ...group };

                    // Ensure products have proper default and sort values
                    if (this.groupForm.products && Array.isArray(this.groupForm.products)) {
                        this.groupForm.products = this.groupForm.products.map(product => ({
                            ...product,
                            default: product.default || false,
                            sort: product.sort || 0
                        }));
                    }

                    this.isEditGroupMode = true;
                    this.editingGroupIndex = groupIndex;
                    this.$refs.groupFormModal.open();
                },

                saveGroup() {
                    // Ensure products array is properly formatted
                    const groupData = { ...this.groupForm };

                    // Filter out any products with invalid IDs
                    if (groupData.products && Array.isArray(groupData.products)) {
                        groupData.products = groupData.products.filter(product =>
                            product && product.id && product.id > 0
                        );
                    }

                    if (this.isEditGroupMode) {
                        this.form.groups[this.editingGroupIndex] = groupData;
                    } else {
                        this.form.groups.push(groupData);
                    }

                    this.$refs.groupFormModal.close();
                    this.resetGroupForm();
                },

                removeGroup(groupIndex) {
                    this.$emitter.emit('open-confirm-modal', {
                        agree: () => {
                            this.form.groups.splice(groupIndex, 1);
                        }
                    });
                },

                resetGroupForm() {
                    this.groupForm = {
                        name: '',
                        field_type: 'checkbox',
                        checked_type: 'once',
                        quantity_min: 0,
                        quantity_max: 0,
                        show_title: true,
                        opened_by_default: true,
                        zero_price: true,
                        required: false,
                        hidden: false,
                        sort: 0,
                        products: []
                    };
                    this.isEditGroupMode = false;
                    this.editingGroupIndex = null;
                },

                openProductSearch() {
                    this.$refs.productSearchModal.openDrawer();
                },

                addProductToGroup(selectedProducts) {
                    selectedProducts.forEach(product => {
                        // Only add products with valid IDs
                        if (product && product.id && product.id > 0) {
                            // Check if product is already added
                            const exists = this.groupForm.products.some(p => p.id === product.id);
                            if (!exists) {
                                this.groupForm.products.push({
                                    ...product,
                                    default: false,
                                    sort: 0
                                });
                            }
                        }
                    });
                },

                removeProductFromGroup(productIndex) {
                    this.groupForm.products.splice(productIndex, 1);
                },

                getFieldTypeLabel(type) {
                    const types = {
                        'checkbox': '@lang('admin::app.catalog.products.edit.types.constructor.field-type-checkbox')',
                        'radio': '@lang('admin::app.catalog.products.edit.types.constructor.field-type-radio')',
                        'list': '@lang('admin::app.catalog.products.edit.types.constructor.field-type-list')'
                    };
                    return types[type] || type;
                },

                getCheckedTypeLabel(type) {
                    const types = {
                        'once': '@lang('admin::app.catalog.products.edit.types.constructor.checked-type-once')',
                        'multiple': '@lang('admin::app.catalog.products.edit.types.constructor.checked-type-multiple')'
                    };
                    return types[type] || type;
                }
            }
        });
    </script>
@endPushOnce
