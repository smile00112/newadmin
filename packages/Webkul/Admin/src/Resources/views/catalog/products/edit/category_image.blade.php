@php
    use Illuminate\Support\Facades\Storage;
@endphp

{!! view_render_event('bagisto.admin.catalog.product.edit.form.category_image.before', ['product' => $product]) !!}

<div class="box-shadow relative rounded bg-white p-4 dark:bg-gray-900">
    <!-- Panel Header -->
    <div class="mb-4 flex justify-between gap-5">
        <div class="flex flex-col gap-2">
            <p class="text-base font-semibold text-gray-800 dark:text-white">
                @lang('admin::app.catalog.products.edit.category_image.title')
            </p>

            <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                @lang('admin::app.catalog.products.edit.category_image.info')
            </p>
        </div>
    </div>

    <!-- Category Image Upload -->
    <div class="flex gap-2.5">
        @if ($product->category_image)
            <div class="relative">
                <img
                    src="{{ Storage::url($product->category_image) }}"
                    class="h-[120px] w-[120px] overflow-hidden rounded border object-cover hover:border-gray-400 dark:border-gray-800"
                    alt="Category Image"
                />
                
                <input
                    type="hidden"
                    name="category_image"
                    value="{{ $product->category_image }}"
                />
            </div>
        @endif

        <v-field
            type="file"
            class="w-full"
            name="category_image"
            v-slot="{ handleChange, handleBlur }"
            label="@lang('admin::app.catalog.products.edit.category_image.title')"
        >
            <input
                type="file"
                id="category_image"
                accept="image/*"
                :class="[errors['category_image'] ? 'border border-red-600 hover:border-red-600' : '']"
                class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:text-gray-300 dark:file:bg-gray-800 dark:file:dark:text-white dark:hover:border-gray-400 dark:focus:border-gray-400"
                name="category_image"
                @change="handleChange"
                @blur="handleBlur"
            >
        </v-field>
    </div>

    @if ($product->category_image)
        <div class="mt-2.5 flex items-center gap-2.5">
            <x-admin::form.control-group.control
                type="checkbox"
                id="category_image_delete"
                name="category_image_delete"
                value="1"
                for="category_image_delete"
            />

            <label
                for="category_image_delete"
                class="cursor-pointer select-none text-sm text-gray-600 dark:text-gray-300"
            >
                @lang('admin::app.catalog.products.edit.remove')
            </label>
        </div>
    @endif

    <x-admin::form.control-group.error control-name='category_image' />
</div>

{!! view_render_event('bagisto.admin.catalog.product.edit.form.category_image.after', ['product' => $product]) !!}

