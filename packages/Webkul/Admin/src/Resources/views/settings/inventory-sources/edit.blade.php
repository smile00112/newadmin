<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.inventory-sources.edit.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.settings.inventory_sources.edit.before', ['inventorySource' => $inventorySource]) !!}

    <x-admin::form
        :action="route('admin.settings.inventory_sources.update', $inventorySource->id)"
        enctype="multipart/form-data"
        method="PUT"
    >

        {!! view_render_event('bagisto.admin.settings.inventory_sources.edit.edit_form_controls.before', ['inventorySource' => $inventorySource]) !!}

        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); box-shadow: 0 4px 15px rgba(245,158,11,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.settings.inventory-sources.edit.title')
                </p>
                <p class="text-xs text-gray-400">Редактирование склада</p>
            </div>
        </div>
            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.settings.inventory_sources.index') }}"
                    style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; color: #6b7280; background: #f3f4f6; transition: all 0.15s; text-decoration: none;"
                    onmouseenter="this.style.background='#e5e7eb'"
                    onmouseleave="this.style.background='#f3f4f6'"
                >
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    @lang('admin::app.settings.inventory-sources.edit.back-btn')
                </a>

                <!-- Save Inventory -->
                <div class="flex items-center gap-x-2.5">
                    <button
                        type="submit"
                        class="primary-button"
                    >
                        @lang('admin::app.settings.inventory-sources.edit.save-btn')
                    </button>
                </div>
            </div>
        </div>

        <!-- Full Panel -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">

            <!-- Left Section -->
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">

                {!! view_render_event('bagisto.admin.settings.inventory_sources.edit.card.general.before', ['inventorySource' => $inventorySource]) !!}

                <!-- General -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.settings.inventory-sources.edit.general')
                    </p>

                    <!-- Code -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.settings.inventory-sources.edit.code')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="code"
                            name="code"
                            rules="required"
                            :value="old('code') ?? $inventorySource->code"
                            :label="trans('admin::app.settings.inventory-sources.edit.code')"
                            :placeholder="trans('admin::app.settings.inventory-sources.edit.code')"
                        />

                        <x-admin::form.control-group.error control-name="code" />
                    </x-admin::form.control-group>

                    <!-- Name -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.settings.inventory-sources.edit.name')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="name"
                            name="name"
                            rules="required"
                            :value="old('name') ?? $inventorySource->name"
                            :label="trans('admin::app.settings.inventory-sources.edit.name')"
                            :placeholder="trans('admin::app.settings.inventory-sources.edit.name')"
                        />

                        <x-admin::form.control-group.error control-name="name" />
                    </x-admin::form.control-group>

                    <!-- Description -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.settings.inventory-sources.edit.description')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            class="text-gray-600 dark:text-gray-300"
                            id="description"
                            name="description"
                            :value="old('description') ?? $inventorySource->description"
                            :label="trans('admin::app.settings.inventory-sources.edit.description')"
                            :placeholder="trans('admin::app.settings.inventory-sources.edit.description')"
                        />

                        <x-admin::form.control-group.error control-name="description" />
                    </x-admin::form.control-group>
                </div>

                {!! view_render_event('bagisto.admin.settings.inventory_sources.edit.card.general.after', ['inventorySource' => $inventorySource]) !!}

                {!! view_render_event('bagisto.admin.settings.inventory_sources.edit.card.contact_info.before', ['inventorySource' => $inventorySource]) !!}

                <!-- Contact Information -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.settings.inventory-sources.edit.contact-info')
                    </p>

                    <!-- Contact Name -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.settings.inventory-sources.edit.contact-name')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="contact_name"
                            id="contact_name"
                            rules="required"
                            :value="old('contact_name') ?? $inventorySource->contact_name"
                            :label="trans('admin::app.settings.inventory-sources.edit.contact-name')"
                            :placeholder="trans('admin::app.settings.inventory-sources.edit.contact-name')"
                        />

                        <x-admin::form.control-group.error control-name="contact_name" />
                    </x-admin::form.control-group>

                    <!-- Contact Email -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.settings.inventory-sources.edit.contact-email')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="email"
                            id="contact_email"
                            name="contact_email"
                            rules="required|email"
                            :value="old('contact_email') ?? $inventorySource->contact_email"
                            :label="trans('admin::app.settings.inventory-sources.edit.contact-email')"
                            :placeholder="trans('admin::app.settings.inventory-sources.edit.contact-email')"
                        />

                        <x-admin::form.control-group.error control-name="contact_email" />
                    </x-admin::form.control-group>

                    <!-- Contact Number -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.settings.inventory-sources.edit.contact-number')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="contact_number"
                            name="contact_number"
                            rules="required"
                            :value="old('contact_number') ?? $inventorySource->contact_number"
                            :label="trans('admin::app.settings.inventory-sources.edit.contact-number')"
                            :placeholder="trans('admin::app.settings.inventory-sources.edit.contact-number')"
                        />

                        <x-admin::form.control-group.error control-name="contact_number" />
                    </x-admin::form.control-group>

                    <!-- Contact Fax -->
                    <x-admin::form.control-group class="!mb-0">
                        <x-admin::form.control-group.label>
                            @lang('admin::app.settings.inventory-sources.edit.contact-fax')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="contact_fax"
                            name="contact_fax"
                            :value="old('contact_fax') ?? $inventorySource->contact_fax"
                            :label="trans('admin::app.settings.inventory-sources.edit.contact-fax')"
                            :placeholder="trans('admin::app.settings.inventory-sources.edit.contact-fax')"
                        />

                        <x-admin::form.control-group.error control-name="contact_fax" />
                    </x-admin::form.control-group>
                </div>

                {!! view_render_event('bagisto.admin.settings.inventory_sources.edit.card.contact_info.after', ['inventorySource' => $inventorySource]) !!}

                {!! view_render_event('bagisto.admin.settings.inventory_sources.edit.card.source_address.before', ['inventorySource' => $inventorySource]) !!}

                <!-- Create Inventory -->
                <v-source-address></v-source-address>

                {!! view_render_event('bagisto.admin.settings.inventory_sources.edit.card.source_address.after', ['inventorySource' => $inventorySource]) !!}

            </div>

            <!-- Right Section -->
            <div class="flex w-[360px] max-w-full flex-col gap-2">

                {!! view_render_event('bagisto.admin.settings.inventory_sources.edit.card.accordion.settings.before', ['inventorySource' => $inventorySource]) !!}

                <!-- Settings -->
                <x-admin::accordion>
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('admin::app.settings.inventory-sources.edit.settings')
                            </p>
                        </div>
                    </x-slot>

                    <x-slot:content>
                        <!-- Latitude -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.settings.inventory-sources.edit.latitude')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                id="latitude"
                                name="latitude"
                                :value="old('latitude') ?? $inventorySource->latitude"
                                :label="trans('admin::app.settings.inventory-sources.edit.latitude')"
                                :placeholder="trans('admin::app.settings.inventory-sources.edit.latitude')"
                            />

                            <x-admin::form.control-group.error control-name="latitude" />
                        </x-admin::form.control-group>

                        <!-- Longitude -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.settings.inventory-sources.edit.longitude')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                id="longitude"
                                name="longitude"
                                :value="old('longitude') ?? $inventorySource->longitude"
                                :label="trans('admin::app.settings.inventory-sources.edit.longitude')"
                                :placeholder="trans('admin::app.settings.inventory-sources.edit.longitude')"
                            />

                            <x-admin::form.control-group.error control-name="longitude" />
                        </x-admin::form.control-group>

                        <!-- Priority -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.settings.inventory-sources.edit.priority')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                id="priority"
                                name="priority"
                                :value="old('priority') ?? $inventorySource->priority"
                                :label="trans('admin::app.settings.inventory-sources.edit.priority')"
                                :placeholder="trans('admin::app.settings.inventory-sources.edit.priority')"
                            />

                            <x-admin::form.control-group.error control-name="priority" />

                        </x-admin::form.control-group>

                        <!-- Status -->
                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label>
                                @lang('admin::app.settings.inventory-sources.edit.status')
                            </x-admin::form.control-group.label>

                            @php $selectedValue = old('status') ?: $inventorySource->status; @endphp

                            <x-admin::form.control-group.control
                                type="hidden"
                                name="status"
                                value="0"
                            />

                            <x-admin::form.control-group.control
                                type="switch"
                                name="status"
                                value="1"
                                :label="trans('admin::app.settings.inventory-sources.edit.status')"
                                :placeholder="trans('admin::app.settings.inventory-sources.edit.status')"
                                :checked="(bool) $selectedValue"
                            />

                            <x-admin::form.control-group.error control-name="status" />
                        </x-admin::form.control-group>
                    </x-slot>
                </x-admin::accordion>

                {!! view_render_event('bagisto.admin.settings.inventory_sources.edit.card.accordion.settings.after', ['inventorySource' => $inventorySource]) !!}

                {!! view_render_event('bagisto.admin.settings.inventory_sources.edit.card.accordion.pickup_points.before', ['inventorySource' => $inventorySource]) !!}

                <!-- Pickup Points -->
               <v-pickup-points :inventory-source-id="{{ $inventorySource->id }}"></v-pickup-points>{{-- --}}

                {!! view_render_event('bagisto.admin.settings.inventory_sources.edit.card.accordion.pickup_points.after', ['inventorySource' => $inventorySource]) !!}

            </div>
        </div>

        {!! view_render_event('bagisto.admin.settings.inventory_sources.edit.edit_form_controls.after', ['inventorySource' => $inventorySource]) !!}

    </x-admin::form>

    {!! view_render_event('bagisto.admin.settings.inventory_sources.edit.after', ['inventorySource' => $inventorySource]) !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-source-address-template"
        >
            <!-- Source Address -->
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    @lang('admin::app.settings.inventory-sources.edit.source-address')
                </p>

                <!-- Country -->
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        @lang('admin::app.settings.inventory-sources.edit.country')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="select"
                        id="country"
                        name="country"
                        rules="required"
                        v-model="country"
                        :label="trans('admin::app.settings.inventory-sources.edit.country')"
                        :placeholder="trans('admin::app.settings.inventory-sources.edit.country')"
                    >
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
                    <x-admin::form.control-group.label class="required">
                        @lang('admin::app.settings.inventory-sources.edit.state')
                    </x-admin::form.control-group.label>

                    <template v-if="haveStates()">
                        <x-admin::form.control-group.control
                            type="select"
                            id="state"
                            name="state"
                            rules="required"
                            v-model="state"
                            :label="trans('admin::app.settings.inventory-sources.edit.state')"
                            :placeholder="trans('admin::app.settings.inventory-sources.edit.state')"
                        >
                            <option
                                v-for='(state, index) in countryStates[country]'
                                :value="state.code"
                            >
                                @{{ state.default_name }}
                            </option>
                        </x-admin::form.control-group.control>
                    </template>

                    <template v-else>
                        <x-admin::form.control-group.control
                            type="text"
                            id="state"
                            name="state"
                            rules="required"
                            :value="old('state') ?? $inventorySource->code"
                            v-model="state"
                            :label="trans('admin::app.settings.inventory-sources.edit.state')"
                            :placeholder="trans('admin::app.settings.inventory-sources.edit.state')"
                        />
                    </template>

                    <x-admin::form.control-group.error control-name="state" />
                </x-admin::form.control-group>

                <!-- City -->
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        @lang('admin::app.settings.inventory-sources.edit.city')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="text"
                        id="city"
                        name="city"
                        rules="required"
                        :value="old('city') ?? $inventorySource->city"
                        :label="trans('admin::app.settings.inventory-sources.edit.city')"
                        :placeholder="trans('admin::app.settings.inventory-sources.edit.city')"
                    />

                    <x-admin::form.control-group.error control-name="city" />
                </x-admin::form.control-group>

                <!-- Street -->
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        @lang('admin::app.settings.inventory-sources.edit.street')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="text"
                        name="street"
                        id="street"
                        rules="required"
                        :value="old('street') ?? $inventorySource->street"
                        :label="trans('admin::app.settings.inventory-sources.edit.street')"
                        :placeholder="trans('admin::app.settings.inventory-sources.edit.street')"
                    />

                    <x-admin::form.control-group.error control-name="street" />
                </x-admin::form.control-group>

                <!-- Post Code -->
                <x-admin::form.control-group class="!mb-0">
                    <x-admin::form.control-group.label class="required">
                        @lang('admin::app.settings.inventory-sources.edit.postcode')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="text"
                        id="postcode"
                        name="postcode"
                        rules="required|postcode"
                        :value="old('postcode') ?? $inventorySource->postcode"
                        :label="trans('admin::app.settings.inventory-sources.edit.postcode')"
                        :placeholder="trans('admin::app.settings.inventory-sources.edit.postcode')"
                    />

                    <x-admin::form.control-group.error control-name="postcode" />
                </x-admin::form.control-group>
            </div>
        </script>

        <script type="module">
            app.component('v-source-address', {
                template: '#v-source-address-template',

                data() {
                    return {
                        country: "{{ old('country') ?? $inventorySource->country }}",

                        state: "{{ old('state') ?? $inventorySource->state }}",

                        countryStates: @json(core()->groupedStatesByCountries())
                    }
                },

                methods: {
                    haveStates() {
                        if (this.countryStates[this.country] && this.countryStates[this.country].length) {
                            return true;
                        }

                        return false;
                    },
                }
            })
        </script>

        <script
            type="text/x-template"
            id="v-pickup-points-template"
        >
            <div>
                <!-- Pickup Points Accordion -->
                <x-admin::accordion>
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('admin::app.settings.pickup-points.title')
                            </p>
                        </div>
                    </x-slot>

                    <x-slot:content>
                        <!-- Add Button -->
                        <div class="mb-4">
                            <button
                                type="button"
                                class="primary-button w-full"
                                @click="openCreateModal"
                            >
                                @lang('admin::app.settings.pickup-points.add-btn')
                            </button>
                        </div>

                        <!-- Pickup Points List -->
                        <div class="space-y-3">
                            <div
                                v-if="pickupPoints.length === 0"
                                class="px-6 py-12 text-center"
                            >
                                <div class="flex flex-col items-center">
                                    <i class="icon-location mx-auto h-12 w-12 text-gray-400"></i>
                                    <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                                        @lang('admin::app.settings.pickup-points.empty-state.title')
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        @lang('admin::app.settings.pickup-points.empty-state.description')
                                    </p>
                                </div>
                            </div>

                            <div
                                v-for="point in pickupPoints"
                                :key="point.id"
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-900"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                                            @{{ point.name }}
                                        </p>
                                        <p
                                            v-if="point.address"
                                            class="text-xs text-gray-600 dark:text-gray-400 mb-1"
                                        >
                                            @{{ point.address }}
                                        </p>
                                        <p
                                            v-if="point.working_hours"
                                            class="text-xs text-gray-600 dark:text-gray-400"
                                        >
                                            @{{ point.working_hours }}
                                        </p>
                                        <div
                                            v-if="point.latitude && point.longitude"
                                            class="mt-2 text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            @{{ point.latitude }}, @{{ point.longitude }}
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 ml-4">
                                        <button
                                            type="button"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"
                                            @click="editPoint(point)"
                                            :title="translations.editBtn"
                                        >
                                            <i class="icon-edit"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"
                                            @click="deletePoint(point.id)"
                                            :title="translations.deleteBtn"
                                        >
                                            <i class="icon-delete"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-slot>
                </x-admin::accordion>

                <!-- Create/Edit Modal -->
                <x-admin::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                >
                    <form @submit="handleSubmit($event, savePoint)">
                        <x-admin::modal ref="pickupPointModal">
                            <x-slot:header>
                                <p class="text-lg font-medium text-gray-900 dark:text-white">
                                    <span v-if="isEditing">
                                        @lang('admin::app.settings.pickup-points.edit.title')
                                    </span>
                                    <span v-else>
                                        @lang('admin::app.settings.pickup-points.create.title')
                                    </span>
                                </p>
                            </x-slot>

                            <x-slot:content>
                                <div class="space-y-4">
                                    <!-- Name -->
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label class="required">
                                            @lang('admin::app.settings.pickup-points.name')
                                        </x-admin::form.control-group.label>

                                        <x-admin::form.control-group.control
                                            type="text"
                                            id="name"
                                            name="name"
                                            rules="required"
                                            v-model="formData.name"
                                            :label="trans('admin::app.settings.pickup-points.name')"
                                            :placeholder="trans('admin::app.settings.pickup-points.name')"
                                        />

                                        <x-admin::form.control-group.error control-name="name" />
                                    </x-admin::form.control-group>

                                    <!-- Latitude -->
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label>
                                            @lang('admin::app.settings.pickup-points.latitude')
                                        </x-admin::form.control-group.label>

                                        <x-admin::form.control-group.control
                                            type="number"
                                            id="latitude"
                                            name="latitude"
                                            step="any"
                                            v-model="formData.latitude"
                                            :label="trans('admin::app.settings.pickup-points.latitude')"
                                            :placeholder="trans('admin::app.settings.pickup-points.latitude')"
                                        />

                                        <x-admin::form.control-group.error control-name="latitude" />
                                    </x-admin::form.control-group>

                                    <!-- Longitude -->
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label>
                                            @lang('admin::app.settings.pickup-points.longitude')
                                        </x-admin::form.control-group.label>

                                        <x-admin::form.control-group.control
                                            type="number"
                                            id="longitude"
                                            name="longitude"
                                            step="any"
                                            v-model="formData.longitude"
                                            :label="trans('admin::app.settings.pickup-points.longitude')"
                                            :placeholder="trans('admin::app.settings.pickup-points.longitude')"
                                        />

                                        <x-admin::form.control-group.error control-name="longitude" />
                                    </x-admin::form.control-group>

                                    <!-- Address -->
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label>
                                            @lang('admin::app.settings.pickup-points.address')
                                        </x-admin::form.control-group.label>

                                        <x-admin::form.control-group.control
                                            type="text"
                                            id="address"
                                            name="address"
                                            rows="3"
                                            v-model="formData.address"
                                            :label="trans('admin::app.settings.pickup-points.address')"
                                            :placeholder="trans('admin::app.settings.pickup-points.address')"
                                        />

                                        <x-admin::form.control-group.error control-name="address" />
                                    </x-admin::form.control-group>

                                    <!-- Working Hours -->
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label>
                                            @lang('admin::app.settings.pickup-points.working-hours')
                                        </x-admin::form.control-group.label>

                                        <x-admin::form.control-group.control
                                            type="textarea"
                                            id="working_hours"
                                            name="working_hours"
                                            rows="3"
                                            v-model="formData.working_hours"
                                            :label="trans('admin::app.settings.pickup-points.working-hours')"
                                            :placeholder="trans('admin::app.settings.pickup-points.working-hours')"
                                        />

                                        <x-admin::form.control-group.error control-name="working_hours" />
                                    </x-admin::form.control-group>

                                    <!-- Map Icon -->
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label>
                                            @lang('admin::app.settings.pickup-points.map-icon')
                                        </x-admin::form.control-group.label>

                                        <div v-if="formData.map_icon_url" class="mb-2">
                                            <img
                                                :src="formData.map_icon_url"
                                                class="h-[60px] w-[60px] overflow-hidden rounded border object-cover hover:border-gray-400 dark:border-gray-800"
                                                alt="Map Icon"
                                            />
                                        </div>

                                        <v-field
                                            type="file"
                                            id="map_icon"
                                            name="map_icon"
                                            accept="image/*"
                                            label="{{ trans('admin::app.settings.pickup-points.map-icon') }}"
                                            v-slot="{ handleChange, handleBlur }"
                                        >
                                            <input
                                                type="file"
                                                id="map_icon"
                                                name="map_icon"
                                                accept="image/*"
                                                class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:file:bg-gray-800 dark:file:dark:text-white dark:hover:border-gray-400 dark:focus:border-gray-400"
                                                @change="handleFileChange($event); handleChange($event)"
                                                @blur="handleBlur"
                                            />
                                        </v-field>

                                        <x-admin::form.control-group.error control-name="map_icon" />
                                    </x-admin::form.control-group>

                                    <input
                                        type="hidden"
                                        name="inventory_source_id"
                                        v-model="inventorySourceId"
                                    />
                                </div>
                            </x-slot>

                            <x-slot:footer>
                                <div class="flex justify-end gap-x-2 pt-4 mt-6 border-t border-gray-200 dark:border-gray-700">
                                    <button
                                        type="button"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
                                        @click="$refs.pickupPointModal.toggle()"
                                    >
                                        @lang('admin::app.settings.pickup-points.cancel-btn')
                                    </button>

                                    <button
                                        type="submit"
                                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        :disabled="isLoading"
                                    >
                                        <span v-if="!isLoading">@{{ submitButtonText }}</span>
                                        <span v-else>
                                            <svg
                                                class="inline-block h-4 w-4 animate-spin"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                            >
                                                <circle
                                                    class="opacity-25"
                                                    cx="12"
                                                    cy="12"
                                                    r="10"
                                                    stroke="currentColor"
                                                    stroke-width="4"
                                                ></circle>
                                                <path
                                                    class="opacity-75"
                                                    fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                                ></path>
                                            </svg>
                                        </span>
                                    </button>
                                </div>
                            </x-slot>
                        </x-admin::modal>
                    </form>
                </x-admin::form>
            </div>
        </script>

        <script type="module">
            app.component('v-pickup-points', {
                template: '#v-pickup-points-template',

                props: {
                    inventorySourceId: {
                        type: Number,
                        required: true,
                    },
                },

                data() {
                    return {
                        pickupPoints: [],
                        isLoading: false,
                        isEditing: false,
                        formData: {
                            id: null,
                            name: '',
                            latitude: null,
                            longitude: null,
                            address: '',
                            working_hours: '',
                            map_icon: null,
                            map_icon_url: null,
                        },
                        translations: {
                            saveBtn: '{{ trans('admin::app.settings.pickup-points.save-btn') }}',
                            updateBtn: '{{ trans('admin::app.settings.pickup-points.update-btn') }}',
                            editBtn: '{{ trans('admin::app.settings.pickup-points.edit-btn') }}',
                            deleteBtn: '{{ trans('admin::app.settings.pickup-points.delete-btn') }}',
                            deleteConfirm: '{{ trans('admin::app.settings.pickup-points.delete-confirm') }}',
                            deleteFailed: '{{ trans('admin::app.settings.pickup-points.delete-failed') }}',
                            saveFailed: '{{ trans('admin::app.settings.pickup-points.save-failed') }}',
                        },
                    };
                },

                computed: {
                    submitButtonText() {
                        return this.isEditing ? this.translations.updateBtn : this.translations.saveBtn;
                    },
                },

                mounted() {
                    this.loadPickupPoints();
                },

                methods: {
                    loadPickupPoints() {
                        if (!this.inventorySourceId || !this.$axios) {
                            return;
                        }

                        const url = `{{ route('admin.settings.pickup_points.index', ':id') }}`.replace(':id', this.inventorySourceId);

                        this.$axios.get(url)
                            .then((response) => {
                                if (response && response.data && response.data.data) {
                                    this.pickupPoints = response.data.data;
                                } else {
                                    this.pickupPoints = [];
                                }
                            })
                            .catch(() => {
                                this.pickupPoints = [];
                            });
                    },

                    openCreateModal() {
                        this.isEditing = false;
                        this.resetForm();
                        this.$refs.pickupPointModal.toggle();
                    },

                    editPoint(point) {
                        this.isEditing = true;
                        this.formData = {
                            id: point.id,
                            name: point.name || '',
                            latitude: point.latitude || null,
                            longitude: point.longitude || null,
                            address: point.address || '',
                            working_hours: point.working_hours || '',
                            map_icon: null,
                            map_icon_url: point.map_icon || null,
                        };
                        this.$refs.pickupPointModal.toggle();
                    },

                    deletePoint(id) {
                        if (!confirm(this.translations.deleteConfirm)) {
                            return;
                        }

                        this.isLoading = true;

                        this.$axios.delete(`{{ route('admin.settings.pickup_points.delete', ':id') }}`.replace(':id', id))
                            .then((response) => {
                                this.isLoading = false;
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                                this.loadPickupPoints();
                            })
                            .catch((error) => {
                                this.isLoading = false;
                                const message = error.response?.data?.message || this.translations.deleteFailed;
                                this.$emitter.emit('add-flash', { type: 'error', message: message });
                            });
                    },

                    savePoint(params, { setErrors, resetForm }) {
                        this.isLoading = true;

                        const formData = new FormData();
                        formData.append('name', params.name || '');
                        formData.append('latitude', params.latitude || '');
                        formData.append('longitude', params.longitude || '');
                        formData.append('address', params.address || '');
                        formData.append('working_hours', params.working_hours || '');
                        formData.append('inventory_source_id', this.inventorySourceId);

                        if (this.formData.map_icon) {
                            formData.append('map_icon', this.formData.map_icon);
                        }

                        const url = this.isEditing
                            ? `{{ route('admin.settings.pickup_points.update', ':id') }}`.replace(':id', this.formData.id)
                            : `{{ route('admin.settings.pickup_points.store') }}`;

                        const method = this.isEditing ? 'put' : 'post';

                        this.$axios[method](url, formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data',
                            },
                        })
                            .then((response) => {
                                this.isLoading = false;
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                                this.$refs.pickupPointModal.toggle();
                                this.resetForm();
                                resetForm();
                                this.loadPickupPoints();
                            })
                            .catch((error) => {
                                this.isLoading = false;
                                if (error.response && error.response.status == 422) {
                                    setErrors(error.response.data.errors);
                                } else {
                                    const message = error.response?.data?.message || this.translations.saveFailed;
                                    this.$emitter.emit('add-flash', { type: 'error', message: message });
                                }
                            });
                    },

                    handleFileChange(event) {
                        const file = event.target.files[0];
                        if (file) {
                            this.formData.map_icon = file;
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.$nextTick(() => {
                                    this.formData.map_icon_url = e.target.result;
                                });
                            };
                            reader.readAsDataURL(file);
                        } else {
                            // Сброс при удалении файла
                            this.formData.map_icon = null;
                            this.formData.map_icon_url = null;
                        }
                    },

                    resetForm() {
                        this.formData = {
                            id: null,
                            name: '',
                            latitude: null,
                            longitude: null,
                            address: '',
                            working_hours: '',
                            map_icon: null,
                            map_icon_url: null,
                        };
                    },
                },
            });
        </script>
    @endpushOnce
</x-admin::layouts>
