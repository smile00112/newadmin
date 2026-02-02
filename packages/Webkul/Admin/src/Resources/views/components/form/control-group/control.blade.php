@props([
    'type' => 'text',
    'name' => '',
])

@switch($type)
    @case('hidden')
    @case('text')
    @case('email')
    @case('password')
    @case('number')
        <v-field
            v-slot="{ field, errors }"
            {{ $attributes->only(['name', ':name', 'value', ':value', 'v-model', 'rules', ':rules', 'label', ':label']) }}
            name="{{ $name }}"
        >
            <input
                type="{{ $type }}"
                name="{{ $name }}"
                v-bind="field"
                :class="[errors.length ? 'ring-2 !ring-rose-500 border-rose-500' : '']"
                {{ $attributes->except(['value', ':value', 'v-model', 'rules', ':rules', 'label', ':label'])->merge(['class' => 'w-full rounded-xl border-0 bg-gray-50/50 px-4 py-3 text-sm text-gray-700 ring-1 ring-gray-200 transition-all duration-200 placeholder:text-gray-400 hover:ring-indigo-300 focus:bg-white focus:ring-2 focus:ring-indigo-500 dark:bg-gray-800/50 dark:text-gray-200 dark:ring-gray-700 dark:hover:ring-indigo-600 dark:focus:ring-indigo-500']) }}
            />
        </v-field>

        @break

    @case('price')
        <v-field
            v-slot="{ field, errors }"
            {{ $attributes->only(['name', ':name', 'value', ':value', 'v-model', 'rules', ':rules', 'label', ':label']) }}
            name="{{ $name }}"
        >
            <div
                class="flex w-full items-center overflow-hidden rounded-xl bg-gray-50/50 text-sm text-gray-600 ring-1 ring-gray-200 transition-all duration-200 focus-within:bg-white focus-within:ring-2 focus-within:ring-indigo-500 hover:ring-indigo-300 dark:bg-gray-800/50 dark:text-gray-300 dark:ring-gray-700 dark:hover:ring-indigo-600"
                :class="[errors.length ? 'ring-2 !ring-rose-500' : '']"
            >
                @if (isset($currency))
                    <span {{ $currency->attributes->merge(['class' => 'py-3 text-gray-500 ltr:pl-4 rtl:pr-4 bg-gray-100/50 dark:bg-gray-700/50']) }}>
                        {{ $currency }}
                    </span>
                @else
                    <span class="py-3 text-gray-500 ltr:pl-4 rtl:pr-4 bg-gray-100/50 dark:bg-gray-700/50">
                        {{ core()->currencySymbol(core()->getBaseCurrencyCode()) }}
                    </span>
                @endif

                <input
                    type="text"
                    name="{{ $name }}"
                    v-bind="field"
                    {{ $attributes->except(['value', ':value', 'v-model', 'rules', ':rules', 'label', ':label'])->merge(['class' => 'w-full px-4 py-3 text-sm text-gray-700 bg-transparent dark:text-gray-200']) }}
                />
            </div>
        </v-field>

        @break

    @case('file')
        <v-field
            v-slot="{ field, errors, handleChange, handleBlur }"
            {{ $attributes->only(['name', ':name', 'value', ':value', 'v-model', 'rules', ':rules', ':rules', 'label', ':label']) }}
            name="{{ $name }}"
        >
            <input
                type="{{ $type }}"
                v-bind="{ name: field.name }"
                :class="[errors.length ? 'ring-2 !ring-rose-500' : '']"
                {{ $attributes->except(['value', ':value', 'v-model', 'rules', ':rules', 'label', ':label'])->merge(['class' => 'w-full rounded-xl border-0 bg-gray-50/50 px-4 py-3 text-sm text-gray-700 ring-1 ring-gray-200 transition-all duration-200 hover:ring-indigo-300 focus:ring-2 focus:ring-indigo-500 dark:bg-gray-800/50 dark:text-gray-200 dark:ring-gray-700 dark:file:bg-indigo-600 dark:file:text-white dark:file:rounded-lg dark:file:border-0 dark:file:px-4 dark:file:py-2 dark:file:mr-3 file:bg-indigo-50 file:text-indigo-600 file:rounded-lg file:border-0 file:px-4 file:py-2 file:mr-3 file:font-medium file:cursor-pointer hover:file:bg-indigo-100']) }}
                @change="handleChange"
                @blur="handleBlur"
            />
        </v-field>

        @break

    @case('color')
        <v-field
            name="{{ $name }}"
            v-slot="{ field, errors }"
            {{ $attributes->except('class') }}
        >
            <input
                type="{{ $type }}"
                :class="[errors.length ? 'ring-2 ring-rose-500' : '']"
                v-bind="field"
                {{ $attributes->except(['value'])->merge(['class' => 'w-full h-12 appearance-none rounded-xl border-0 ring-1 ring-gray-200 text-sm text-gray-600 transition-all hover:ring-indigo-300 cursor-pointer dark:ring-gray-700 dark:text-gray-300']) }}
            >
        </v-field>
        @break

    @case('textarea')
        <v-field
            v-slot="{ field, errors }"
            {{ $attributes->only(['name', ':name', 'value', ':value', 'v-model', 'rules', ':rules', 'label', ':label']) }}
            name="{{ $name }}"
        >
            <textarea
                type="{{ $type }}"
                name="{{ $name }}"
                v-bind="field"
                :class="[errors.length ? 'ring-2 !ring-rose-500' : '']"
                {{ $attributes->except(['value', ':value', 'v-model', 'rules', ':rules', 'label', ':label'])->merge(['class' => 'w-full rounded-xl border-0 bg-gray-50/50 px-4 py-3 text-sm text-gray-700 ring-1 ring-gray-200 transition-all duration-200 placeholder:text-gray-400 hover:ring-indigo-300 focus:bg-white focus:ring-2 focus:ring-indigo-500 dark:bg-gray-800/50 dark:text-gray-200 dark:ring-gray-700 dark:hover:ring-indigo-600 resize-none']) }}
            >
            </textarea>

            @if ($attributes->get('tinymce', false) || $attributes->get(':tinymce', false))
                <x-admin::tinymce
                    :selector="'textarea#' . $attributes->get('id')"
                    :prompt="stripcslashes($attributes->get('prompt', ''))"
                    ::field="field"
                >
                </x-admin::tinymce>
            @endif
        </v-field>

        @break

    @case('date')
        <v-field
            v-slot="{ field, errors }"
            {{ $attributes->only(['name', ':name', 'value', ':value', 'v-model', 'rules', ':rules', 'label', ':label']) }}
            name="{{ $name }}"
        >
            <x-admin::flat-picker.date>
                <input
                    name="{{ $name }}"
                    v-bind="field"
                    :class="[errors.length ? 'ring-2 !ring-rose-500' : '']"
                    {{ $attributes->except(['value', ':value', 'v-model', 'rules', ':rules', 'label', ':label'])->merge(['class' => 'w-full rounded-xl border-0 bg-gray-50/50 px-4 py-3 text-sm text-gray-700 ring-1 ring-gray-200 transition-all duration-200 hover:ring-indigo-300 focus:ring-2 focus:ring-indigo-500 dark:bg-gray-800/50 dark:text-gray-200 dark:ring-gray-700']) }}
                    autocomplete="off"
                />
            </x-admin::flat-picker.date>
        </v-field>

        @break

    @case('datetime')
        <v-field
            v-slot="{ field, errors }"
            {{ $attributes->only(['name', ':name', 'value', ':value', 'v-model', 'rules', ':rules', 'label', ':label']) }}
            name="{{ $name }}"
        >
            <x-admin::flat-picker.datetime>
                <input
                    name="{{ $name }}"
                    v-bind="field"
                    :class="[errors.length ? 'border !border-red-600 hover:border-red-600' : '']"
                    {{ $attributes->except(['value', ':value', 'v-model', 'rules', ':rules', 'label', ':label'])->merge(['class' => 'w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400']) }}
                    autocomplete="off"
                >
            </x-admin::flat-picker.datetime>
        </v-field>
        @break

    @case('time')
        <v-field
            name="{{ $name }}"
            v-slot="{ field, errors }"
            {{ $attributes->only(['value', ':value', 'v-model', 'rules', ':rules', 'label', ':label']) }}
        >
            <x-admin::flat-picker.time>
                <input
                    type="time"
                    name="{{ $name }}"
                    v-bind="field"
                    :class="[errors.length ? 'border !border-red-600 hover:border-red-600' : '']"
                    {{ $attributes->except(['value', ':value', 'v-model', 'rules', ':rules', 'label', ':label'])->merge(['class' => 'flex w-full min-h-[39px] py-2.5 px-3 border rounded-md text-sm text-gray-600 dark:text-gray-300 transition-all hover:border-gray-400 dark:hover:border-gray-400 focus:border-gray-400 dark:focus:border-gray-400 dark:bg-gray-900 dark:border-gray-800']) }}
                    autocomplete="off"
                >
            </x-admin::flat-picker.time>
        </v-field>
        @break

    @case('select')
        <v-field
            v-slot="{ field, errors }"
            {{ $attributes->only(['name', ':name', 'value', ':value', 'v-model', 'rules', ':rules', 'label', ':label']) }}
            name="{{ $name }}"
        >
            <select
                name="{{ $name }}"
                v-bind="field"
                :class="[errors.length ? 'ring-2 !ring-rose-500' : '']"
                {{ $attributes->except(['value', ':value', 'v-model', 'rules', ':rules', 'label', ':label'])->merge(['class' => 'custom-select w-full rounded-xl border-0 bg-gray-50/50 px-4 py-3 text-sm font-normal text-gray-700 ring-1 ring-gray-200 transition-all duration-200 hover:ring-indigo-300 focus:ring-2 focus:ring-indigo-500 dark:bg-gray-800/50 dark:text-gray-200 dark:ring-gray-700 cursor-pointer appearance-none']) }}
            >
                {{ $slot }}
            </select>
        </v-field>

        @break

    @case('multiselect')
        <v-field
            as="select"
            v-slot="{ value }"
            :class="[errors && errors['{{ $name }}'] ? 'ring-2 !ring-rose-500' : '']"
            {{ $attributes->except([])->merge(['class' => 'flex w-full flex-col rounded-xl border-0 bg-gray-50/50 px-4 py-3 text-sm font-normal text-gray-700 ring-1 ring-gray-200 transition-all duration-200 hover:ring-indigo-300 focus:ring-2 focus:ring-indigo-500 dark:bg-gray-800/50 dark:text-gray-200 dark:ring-gray-700']) }}
            name="{{ $name }}"
            multiple
        >
            {{ $slot }}
        </v-field>

        @break

    @case('checkbox')
        <v-field
            v-slot="{ field }"
            type="checkbox"
            class="hidden"
            {{ $attributes->only(['name', ':name', 'value', ':value', 'v-model', 'rules', ':rules', 'label', ':label', 'key', ':key']) }}
            name="{{ $name }}"
        >
            <input
                type="checkbox"
                name="{{ $name }}"
                v-bind="field"
                class="peer sr-only"
                {{ $attributes->except(['rules', 'label', ':label', 'key', ':key']) }}
            />

            <v-checked-handler
                :field="field"
                checked="{{ $attributes->get('checked') }}"
            >
            </v-checked-handler>
        </v-field>

        <label
             {{
                $attributes
                    ->except(['value', ':value', 'v-model', 'rules', ':rules', 'label', ':label', 'key', ':key'])
                    ->merge(['class' => 'icon-uncheckbox peer-checked:icon-checked text-2xl peer-checked:text-blue-600'])
                    ->merge(['class' => $attributes->get('disabled') ? 'cursor-not-allowed opacity-70' : 'cursor-pointer'])
            }}
        >
        </label>

        @break

    @case('radio')
        <v-field
            type="radio"
            class="hidden"
            v-slot="{ field }"
            {{ $attributes->only(['name', ':name', 'value', ':value', 'v-model', 'rules', ':rules', 'label', ':label', 'key', ':key']) }}
            name="{{ $name }}"
        >
            <input
                type="radio"
                name="{{ $name }}"
                v-bind="field"
                class="peer sr-only"
                {{ $attributes->except(['rules', 'label', ':label', 'key', ':key']) }}
            />

            <v-checked-handler
                class="hidden"
                :field="field"
                checked="{{ $attributes->get('checked') }}"
            >
            </v-checked-handler>
        </v-field>

        <label
            class="icon-radio-normal peer-checked:icon-radio-selected cursor-pointer text-2xl peer-checked:text-blue-600"
            {{ $attributes->except(['value', ':value', 'v-model', 'rules', ':rules', 'label', ':label', 'key', ':key']) }}
        >
        </label>

        @break

    @case('switch')
        <label class="relative inline-flex cursor-pointer items-center">
            <v-field
                type="checkbox"
                class="hidden"
                v-slot="{ field }"
                {{ $attributes->only(['name', ':name', 'value', ':value', 'v-model', 'rules', ':rules', 'label', ':label', 'key', ':key']) }}
                name="{{ $name }}"
            >
                <input
                    type="checkbox"
                    name="{{ $name }}"
                    id="{{ $name }}"
                    class="peer sr-only"
                    v-bind="field"
                    {{ $attributes->except(['v-model', 'rules', ':rules', 'label', ':label', 'key', ':key']) }}
                />

                <v-checked-handler
                    class="hidden"
                    :field="field"
                    checked="{{ $attributes->get('checked') }}"
                >
                </v-checked-handler>
            </v-field>

            <label
                class="peer h-5 w-9 cursor-pointer rounded-full bg-gray-200 after:absolute after:top-0.5 after:h-4 after:w-4 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-blue-300 dark:bg-gray-800 dark:after:border-white dark:after:bg-white dark:peer-checked:bg-gray-950 after:ltr:left-0.5 peer-checked:after:ltr:translate-x-full after:rtl:right-0.5 peer-checked:after:rtl:-translate-x-full"
                for="{{ $name }}"
            ></label>
        </label>

        @break

    @case('image')
        <x-admin::media.images
            name="{{ $name }}"
            ::class="[errors && errors['{{ $name }}'] ? 'border !border-red-600 hover:border-red-600' : '']"
            {{ $attributes }}
        />

        @break

    @case('custom')
        <v-field {{ $attributes }}>
            {{ $slot }}
        </v-field>
@endswitch

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-checked-handler-template"
    >
    </script>

    <script type="module">
        app.component('v-checked-handler', {
            template: '#v-checked-handler-template',

            props: ['field', 'checked'],

            mounted() {
                if (this.checked == '') {
                    return;
                }

                this.field.checked = true;

                this.field.onChange();
            },
        });
    </script>
@endpushOnce
