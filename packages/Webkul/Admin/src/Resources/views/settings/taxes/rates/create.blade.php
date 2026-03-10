<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.taxes.rates.create.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.settings.taxes.rates.create.before') !!}

    <x-admin::form :action="route('admin.settings.taxes.rates.store')">
<div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); box-shadow: 0 4px 15px rgba(239,68,68,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.settings.taxes.rates.create.title')
                </p>
                <p class="text-xs text-gray-400">Новая налоговая ставка</p>
            </div>
        </div>

            <!-- Back Button -->
            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ route('admin.settings.taxes.rates.index') }}"
                    style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; color: #6b7280; background: #f3f4f6; transition: all 0.15s; text-decoration: none;"
                    onmouseenter="this.style.background='#e5e7eb'"
                    onmouseleave="this.style.background='#f3f4f6'"
                >
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    @lang('admin::app.settings.taxes.rates.create.back-btn')
                </a>

                <!-- Save Button -->
                <button 
                    type="submit" 
                    class="primary-button"
                >
                    @lang('admin::app.settings.taxes.rates.create.save-btn')
                </button>
            </div>
        </div>

        <v-create-taxrate>
            <!-- Shimmer Effect -->
            <x-admin::shimmer.settings.taxes.rates />
        </v-create-taxrate>
    </x-admin::form>

    {!! view_render_event('bagisto.admin.settings.taxes.rates.create.after') !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-create-taxrate-template"
        >

            {!! view_render_event('bagisto.admin.settings.taxes.rates.create.create_form_controls.before') !!}

            <!-- Tax Rates information's -->
            <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
                <!-- Left Component -->
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.taxes.rates.create.general')
                        </p>

                        <!-- Identifier -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.taxes.rates.create.identifier')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="identifier"
                                rules="required"
                                :value="old('identifier')"
                                :label="trans('admin::app.settings.taxes.rates.create.identifier')"
                                :placeholder="trans('admin::app.settings.taxes.rates.create.identifier')"
                            />

                            <x-admin::form.control-group.error control-name="identifier" />
                        </x-admin::form.control-group>

                        <!-- Country -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.taxes.rates.create.country')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                name="country"
                                rules="required"
                                :value="old('country')"
                                v-model="country"
                                :label="trans('admin::app.settings.taxes.rates.create.country')"
                                :placeholder="trans('admin::app.settings.taxes.rates.create.country')"
                            >
                                <!-- Default Option -->
                                <option value="">
                                    @lang('admin::app.settings.taxes.rates.create.select-country')
                                </option>

                                @foreach (core()->countries() as $country)
                                    <option value="{{ $country->code }}">
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="country" />
                        </x-admin::form.control-group>

                        <!-- State -->
                        <x-admin::form.control-group>
                            <!-- Country Have States -->
                            <template v-if="haveStates()">
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.taxes.rates.create.state')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="state"
                                    :value="old('state')"
                                    v-model="state"
                                    :label="trans('admin::app.settings.taxes.rates.create.state')"
                                    :placeholder="trans('admin::app.settings.taxes.rates.create.state')"
                                >
                                    <option value="">
                                        @lang('admin::app.settings.taxes.rates.edit.select-state')
                                    </option>

                                        <option
                                            v-for='(state, index) in countryStates[country]'
                                            :value="state.code"
                                        >
                                            @{{ state.default_name }}
                                        </option>
                                    </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="state" />
                            </template>

                            <!-- Country Have not States -->
                            <template v-else>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.taxes.rates.create.state')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="state"
                                    :value="old('state')"
                                    :label="trans('admin::app.settings.taxes.rates.create.state')"
                                    :placeholder="trans('admin::app.settings.taxes.rates.create.state')"
                                />

                                <x-admin::form.control-group.error control-name="state" />
                            </template>
                        </x-admin::form.control-group>

                        <!-- Tax Rate -->
                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.taxes.rates.create.tax-rate')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="tax_rate"
                                {{-- rules="required|decimal|min:0|max:100" --}}
                                :value="old('tax_rate')"
                                :label="trans('admin::app.settings.taxes.rates.create.tax-rate')"
                                :placeholder="trans('admin::app.settings.taxes.rates.create.tax-rate')"
                            />

                            <x-admin::form.control-group.error control-name="tax_rate" />
                        </x-admin::form.control-group>
                    </div>
                </div>

                <!-- Right Component -->
                <div class="flex w-[360px] max-w-full flex-col gap-2 max-md:w-full">
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('admin::app.settings.taxes.rates.create.settings')
                            </p>
                        </x-slot>

                        <x-slot:content>
                            <!-- Enable Zip Range -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.taxes.rates.create.is-zip')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="switch"
                                    name="is_zip"
                                    :value="1"
                                    v-model="is_zip"
                                    :label="trans('admin::app.settings.taxes.rates.create.is-zip')"
                                    :placeholder="trans('admin::app.settings.taxes.rates.create.is-zip')"
                                />

                                <x-admin::form.control-group.error control-name="is_zip" />
                            </x-admin::form.control-group>

                            <!-- Zip Code -->
                            <x-admin::form.control-group v-if="! is_zip" class="!mb-0">
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.taxes.rates.create.zip-code')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="zip_code"
                                    :value="old('zip_code')"
                                    :label="trans('admin::app.settings.taxes.rates.create.zip-code')"
                                    :placeholder="trans('admin::app.settings.taxes.rates.create.zip-code')"
                                />

                                <x-admin::form.control-group.error control-name="zip_code" />
                            </x-admin::form.control-group>

                            <div v-if="is_zip">
                                <!-- Zip From -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.settings.taxes.rates.create.zip-from')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="zip_from"
                                        rules="required"
                                        :value="old('zip_from')"
                                        :label="trans('admin::app.settings.taxes.rates.create.zip-from')"
                                        :placeholder="trans('admin::app.settings.taxes.rates.create.zip-from')"
                                    />

                                    <x-admin::form.control-group.error control-name="zip_from" />
                                </x-admin::form.control-group>

                                <!-- Zip To -->
                                <x-admin::form.control-group class="!mb-0">
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.settings.taxes.rates.create.zip-to')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="zip_to"
                                        rules="required"
                                        :value="old('zip_to')"
                                        :label="trans('admin::app.settings.taxes.rates.create.zip-to')"
                                        :placeholder="trans('admin::app.settings.taxes.rates.create.zip-to')"
                                    />

                                    <x-admin::form.control-group.error control-name="zip_to" />
                                </x-admin::form.control-group>
                            </div>
                        </x-slot>
                    </x-admin::accordion>
                </div>
            </div>

            {!! view_render_event('bagisto.admin.settings.taxes.rates.create.create_form_controls.after') !!}

        </script>

        <script type="module">
            app.component('v-create-taxrate', {
                template: '#v-create-taxrate-template',

                data() {
                    return {
                        is_zip: false,

                        country: "{{ old('country')  }}",

                        state: "{{ old('state')  }}",

                        countryStates: @json(core()->groupedStatesByCountries())
                    }
                },

                methods: {
                    haveStates: function () {
                        /*
                        * The double negation operator is used to convert the value to a boolean.
                        * It ensures that the final result is a boolean value,
                        * true if the array has a length greater than 0, and otherwise false.
                        */
                        return !!this.countryStates[this.country]?.length;
                    },
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>
