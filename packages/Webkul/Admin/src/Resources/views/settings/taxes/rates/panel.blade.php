<x-admin::layouts :hideNavigation="true">
    <x-slot:title>
        {{ isset($taxRate) ? trans('admin::app.settings.taxes.rates.edit.title') : trans('admin::app.settings.taxes.rates.create.title') }}
    </x-slot>

    <style>
        body { background: #f8f9fb !important; }
        body.in-iframe { padding: 0 !important; margin: 0 !important; }
        body.in-iframe > div { padding: 0 !important; min-height: auto !important; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>

    <script>
        if (window !== window.parent) {
            document.documentElement.classList.add('in-iframe');
            document.addEventListener('DOMContentLoaded', function() { document.body.classList.add('in-iframe'); });
        }
    </script>

    <v-tax-rate-panel></v-tax-rate-panel>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-tax-rate-panel-template">
            <div>
                <!-- Close button -->
                <div style="padding:12px 24px 0; display:flex; justify-content:flex-start;">
                    <button @click="cancel" type="button" style="display:flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:10px; background:#f3f4f6; border:none; cursor:pointer; transition:all 0.2s;" title="Закрыть"
                        onmouseenter="this.style.background='#e5e7eb'; this.style.transform='scale(1.05)';"
                        onmouseleave="this.style.background='#f3f4f6'; this.style.transform='scale(1)';"
                    >
                        <svg style="width:18px; height:18px; color:#6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <x-admin::form v-slot="{ meta, errors, handleSubmit }" as="div">
                    <form @submit="handleSubmit($event, save)" ref="panelForm">
                        @if(isset($taxRate))
                            <input type="hidden" name="id" value="{{ $taxRate->id }}">
                        @endif

                        <!-- Content -->
                        <div style="padding:10px 24px 20px;">
                            <!-- General section -->
                            <div style="background:white; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.05); border:1px solid #f3f4f6; overflow:hidden;">
                                <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6;">
                                    <p style="font-size:14px; font-weight:600; color:#111827; margin:0;">Основные данные</p>
                                </div>
                                <div style="padding:20px; display:flex; flex-direction:column; gap:16px;">
                                    <!-- Identifier -->
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label class="required">
                                            @lang('admin::app.settings.taxes.rates.create.identifier')
                                        </x-admin::form.control-group.label>
                                        <x-admin::form.control-group.control type="text" name="identifier" rules="required" value="{{ isset($taxRate) ? $taxRate->identifier : '' }}" :label="trans('admin::app.settings.taxes.rates.create.identifier')" :placeholder="trans('admin::app.settings.taxes.rates.create.identifier')" />
                                        <x-admin::form.control-group.error control-name="identifier" />
                                    </x-admin::form.control-group>

                                    <!-- Country -->
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label class="required">
                                            @lang('admin::app.settings.taxes.rates.create.country')
                                        </x-admin::form.control-group.label>
                                        <x-admin::form.control-group.control type="select" name="country" rules="required" v-model="country" :label="trans('admin::app.settings.taxes.rates.create.country')">
                                            <option value="">@lang('admin::app.settings.taxes.rates.create.select-country')</option>
                                            @foreach (core()->countries() as $country)
                                                <option value="{{ $country->code }}">{{ $country->name }}</option>
                                            @endforeach
                                        </x-admin::form.control-group.control>
                                        <x-admin::form.control-group.error control-name="country" />
                                    </x-admin::form.control-group>

                                    <!-- State -->
                                    <x-admin::form.control-group>
                                        <template v-if="haveStates()">
                                            <x-admin::form.control-group.label>
                                                @lang('admin::app.settings.taxes.rates.create.state')
                                            </x-admin::form.control-group.label>
                                            <x-admin::form.control-group.control type="select" name="state" v-model="state" :label="trans('admin::app.settings.taxes.rates.create.state')">
                                                <option value="">@lang('admin::app.settings.taxes.rates.edit.select-state')</option>
                                                <option v-for="(s, index) in countryStates[country]" :value="s.code">@{{ s.default_name }}</option>
                                            </x-admin::form.control-group.control>
                                            <x-admin::form.control-group.error control-name="state" />
                                        </template>
                                        <template v-else>
                                            <x-admin::form.control-group.label>
                                                @lang('admin::app.settings.taxes.rates.create.state')
                                            </x-admin::form.control-group.label>
                                            <x-admin::form.control-group.control type="text" name="state" value="{{ isset($taxRate) ? $taxRate->state : '' }}" :label="trans('admin::app.settings.taxes.rates.create.state')" :placeholder="trans('admin::app.settings.taxes.rates.create.state')" />
                                            <x-admin::form.control-group.error control-name="state" />
                                        </template>
                                    </x-admin::form.control-group>

                                    <!-- Tax Rate -->
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label class="required">
                                            @lang('admin::app.settings.taxes.rates.create.tax-rate')
                                        </x-admin::form.control-group.label>
                                        <x-admin::form.control-group.control type="text" name="tax_rate" rules="required" value="{{ isset($taxRate) ? $taxRate->tax_rate : '' }}" :label="trans('admin::app.settings.taxes.rates.create.tax-rate')" :placeholder="trans('admin::app.settings.taxes.rates.create.tax-rate')" />
                                        <x-admin::form.control-group.error control-name="tax_rate" />
                                    </x-admin::form.control-group>
                                </div>
                            </div>

                            <!-- Zip section -->
                            <div style="background:white; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.05); border:1px solid #f3f4f6; overflow:hidden; margin-top:16px;">
                                <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6;">
                                    <p style="font-size:14px; font-weight:600; color:#111827; margin:0;">ZIP</p>
                                </div>
                                <div style="padding:20px; display:flex; flex-direction:column; gap:16px;">
                                    <!-- Zip Range Switch -->
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label>
                                            @lang('admin::app.settings.taxes.rates.create.is-zip')
                                        </x-admin::form.control-group.label>
                                        <x-admin::form.control-group.control type="switch" name="is_zip" :value="1" v-model="isZip" :label="trans('admin::app.settings.taxes.rates.create.is-zip')" />
                                    </x-admin::form.control-group>

                                    <!-- Zip Code (when !is_zip) -->
                                    <x-admin::form.control-group v-if="!isZip">
                                        <x-admin::form.control-group.label>
                                            @lang('admin::app.settings.taxes.rates.create.zip-code')
                                        </x-admin::form.control-group.label>
                                        <x-admin::form.control-group.control type="text" name="zip_code" value="{{ isset($taxRate) ? $taxRate->zip_code : '' }}" :label="trans('admin::app.settings.taxes.rates.create.zip-code')" :placeholder="trans('admin::app.settings.taxes.rates.create.zip-code')" />
                                        <x-admin::form.control-group.error control-name="zip_code" />
                                    </x-admin::form.control-group>

                                    <!-- Zip From / Zip To (when is_zip) -->
                                    <div v-if="isZip" style="display:flex; gap:16px;">
                                        <x-admin::form.control-group style="flex:1;">
                                            <x-admin::form.control-group.label class="required">
                                                @lang('admin::app.settings.taxes.rates.create.zip-from')
                                            </x-admin::form.control-group.label>
                                            <x-admin::form.control-group.control type="text" name="zip_from" rules="required" value="{{ isset($taxRate) ? $taxRate->zip_from : '' }}" :label="trans('admin::app.settings.taxes.rates.create.zip-from')" :placeholder="trans('admin::app.settings.taxes.rates.create.zip-from')" />
                                            <x-admin::form.control-group.error control-name="zip_from" />
                                        </x-admin::form.control-group>

                                        <x-admin::form.control-group style="flex:1;">
                                            <x-admin::form.control-group.label class="required">
                                                @lang('admin::app.settings.taxes.rates.create.zip-to')
                                            </x-admin::form.control-group.label>
                                            <x-admin::form.control-group.control type="text" name="zip_to" rules="required" value="{{ isset($taxRate) ? $taxRate->zip_to : '' }}" :label="trans('admin::app.settings.taxes.rates.create.zip-to')" :placeholder="trans('admin::app.settings.taxes.rates.create.zip-to')" />
                                            <x-admin::form.control-group.error control-name="zip_to" />
                                        </x-admin::form.control-group>
                                    </div>
                                </div>
                            </div>
                            <!-- Buttons -->
                            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                                <button type="button" class="transparent-button" @click="cancel">Отмена</button>
                                <button type="submit" class="primary-button" :disabled="isLoading">
                                    <template v-if="isLoading"><svg style="width:18px; height:18px; animation:spin 1s linear infinite;" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-dasharray="32" stroke-dashoffset="10" /></svg></template>
                                    @lang('admin::app.settings.taxes.rates.create.save-btn')
                                </button>
                            </div>
                        </div>
                    </form>
                </x-admin::form>
            </div>
        </script>

        <script type="module">
            app.component('v-tax-rate-panel', {
                template: '#v-tax-rate-panel-template',
                data() {
                    return {
                        isLoading: false,
                        country: "{{ isset($taxRate) ? $taxRate->country : '' }}",
                        state: "{{ isset($taxRate) ? $taxRate->state : '' }}",
                        isZip: {{ isset($taxRate) && $taxRate->is_zip ? 'true' : 'false' }},
                        countryStates: @json(core()->groupedStatesByCountries()),
                    };
                },
                methods: {
                    haveStates() {
                        return !!this.countryStates[this.country]?.length;
                    },
                    save(params, { setErrors }) {
                        this.isLoading = true;
                        let formData = new FormData(this.$refs.panelForm);
                        @if(isset($taxRate))
                            formData.append('_method', 'put');
                        @endif
                        this.$axios.post(
                            @if(isset($taxRate))
                                "{{ route('admin.settings.taxes.rates.update', $taxRate->id) }}"
                            @else
                                "{{ route('admin.settings.taxes.rates.store') }}"
                            @endif,
                            formData
                        ).then(response => {
                            this.isLoading = false;
                            if (window.parent !== window) window.parent.postMessage({ type: 'panel-saved', message: response.data.message }, '*');
                        }).catch(error => {
                            this.isLoading = false;
                            if (error.response?.status === 422) setErrors(error.response.data.errors);
                            else this.$emitter.emit('add-flash', { type: 'error', message: error.response?.data?.message || 'Ошибка' });
                        });
                    },
                    cancel() { if (window.parent !== window) window.parent.postMessage({ type: 'panel-closed' }, '*'); },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
