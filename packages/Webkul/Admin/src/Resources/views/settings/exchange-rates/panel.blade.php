<x-admin::layouts :hideNavigation="true">
    <x-slot:title>
        {{ isset($exchangeRate) ? trans('admin::app.settings.exchange-rates.index.edit.title') : trans('admin::app.settings.exchange-rates.index.create.title') }}
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

    <v-exchange-rate-panel></v-exchange-rate-panel>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-exchange-rate-panel-template">
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
                        @if(isset($exchangeRate))
                            <input type="hidden" name="id" value="{{ $exchangeRate->id }}">
                        @endif

                        <!-- Content -->
                        <div style="padding:10px 24px 20px;">
                            <div style="background:white; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.05); border:1px solid #f3f4f6; overflow:hidden;">
                                <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6;">
                                    <p style="font-size:14px; font-weight:600; color:#111827; margin:0;">Основные данные</p>
                                </div>
                                <div style="padding:20px; display:flex; flex-direction:column; gap:16px;">
                                    <!-- Source Currency (disabled) -->
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label>
                                            @lang('admin::app.settings.exchange-rates.index.create.source-currency')
                                        </x-admin::form.control-group.label>
                                        <x-admin::form.control-group.control type="text" name="base_currency" value="{{ core()->getBaseCurrencyCode() }}" disabled="disabled" />
                                    </x-admin::form.control-group>

                                    <!-- Target Currency -->
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label class="required">
                                            @lang('admin::app.settings.exchange-rates.index.create.target-currency')
                                        </x-admin::form.control-group.label>
                                        <x-admin::form.control-group.control type="select" name="target_currency" rules="required" value="{{ isset($exchangeRate) ? $exchangeRate->target_currency : '' }}" :label="trans('admin::app.settings.exchange-rates.index.create.target-currency')">
                                            <option value="">@lang('admin::app.settings.exchange-rates.index.create.select-target-currency')</option>
                                            @foreach($currencies as $currency)
                                                <option value="{{ $currency->id }}" {{ (isset($exchangeRate) && $exchangeRate->target_currency == $currency->id) ? 'selected' : '' }}>{{ $currency->name }}</option>
                                            @endforeach
                                        </x-admin::form.control-group.control>
                                        <x-admin::form.control-group.error control-name="target_currency" />
                                    </x-admin::form.control-group>

                                    <!-- Rate -->
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label class="required">
                                            @lang('admin::app.settings.exchange-rates.index.create.rate')
                                        </x-admin::form.control-group.label>
                                        <x-admin::form.control-group.control type="text" name="rate" rules="required" value="{{ isset($exchangeRate) ? $exchangeRate->rate : '' }}" :label="trans('admin::app.settings.exchange-rates.index.create.rate')" :placeholder="trans('admin::app.settings.exchange-rates.index.create.rate')" />
                                        <x-admin::form.control-group.error control-name="rate" />
                                    </x-admin::form.control-group>
                                </div>
                            </div>
                            <!-- Buttons -->
                            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                                <button type="button" class="transparent-button" @click="cancel">Отмена</button>
                                <button type="submit" class="primary-button" :disabled="isLoading">
                                    <template v-if="isLoading"><svg style="width:18px; height:18px; animation:spin 1s linear infinite;" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-dasharray="32" stroke-dashoffset="10" /></svg></template>
                                    @lang('admin::app.settings.exchange-rates.index.create.save-btn')
                                </button>
                            </div>
                        </div>
                    </form>
                </x-admin::form>
            </div>
        </script>

        <script type="module">
            app.component('v-exchange-rate-panel', {
                template: '#v-exchange-rate-panel-template',
                data() { return { isLoading: false }; },
                methods: {
                    save(params, { setErrors }) {
                        this.isLoading = true;
                        let formData = new FormData(this.$refs.panelForm);
                        @if(isset($exchangeRate)) formData.append('_method', 'put'); @endif
                        this.$axios.post(
                            @if(isset($exchangeRate)) "{{ route('admin.settings.exchange_rates.update') }}" @else "{{ route('admin.settings.exchange_rates.store') }}" @endif,
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
