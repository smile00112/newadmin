<x-admin::layouts :hideNavigation="true">
    <x-slot:title>
        {{ isset($campaign) ? trans('admin::app.marketing.communications.campaigns.edit.title') : trans('admin::app.marketing.communications.campaigns.create.title') }}
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
            document.addEventListener('DOMContentLoaded', function() {
                document.body.classList.add('in-iframe');
            });
        }
    </script>

    <v-campaign-panel></v-campaign-panel>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-campaign-panel-template">
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
                        @if(isset($campaign))
                            <input type="hidden" name="id" value="{{ $campaign->id }}">
                        @endif

                        <!-- Content — two column layout -->
                        <div style="padding:10px 24px 20px;">
                            <div style="display:flex; gap:16px; flex-wrap:wrap;">
                                <!-- Left: General -->
                                <div style="flex:1; min-width:300px;">
                                    <div style="background:white; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.05); border:1px solid #f3f4f6; overflow:hidden;">
                                        <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6;">
                                            <p style="font-size:14px; font-weight:600; color:#111827; margin:0;">Основные данные</p>
                                        </div>
                                        <div style="padding:20px; display:flex; flex-direction:column; gap:16px;">
                                            <!-- Name -->
                                            <x-admin::form.control-group>
                                                <x-admin::form.control-group.label class="required">
                                                    @lang('admin::app.marketing.communications.campaigns.create.name')
                                                </x-admin::form.control-group.label>
                                                <x-admin::form.control-group.control type="text" name="name" rules="required" value="{{ isset($campaign) ? $campaign->name : '' }}" :label="trans('admin::app.marketing.communications.campaigns.create.name')" :placeholder="trans('admin::app.marketing.communications.campaigns.create.name')" />
                                                <x-admin::form.control-group.error control-name="name" />
                                            </x-admin::form.control-group>

                                            <!-- Subject -->
                                            <x-admin::form.control-group>
                                                <x-admin::form.control-group.label class="required">
                                                    @lang('admin::app.marketing.communications.campaigns.create.subject')
                                                </x-admin::form.control-group.label>
                                                <x-admin::form.control-group.control type="text" name="subject" rules="required" value="{{ isset($campaign) ? $campaign->subject : '' }}" :label="trans('admin::app.marketing.communications.campaigns.create.subject')" :placeholder="trans('admin::app.marketing.communications.campaigns.create.subject')" />
                                                <x-admin::form.control-group.error control-name="subject" />
                                            </x-admin::form.control-group>

                                            <!-- Event -->
                                            <x-admin::form.control-group>
                                                <x-admin::form.control-group.label class="required">
                                                    @lang('admin::app.marketing.communications.campaigns.create.event')
                                                </x-admin::form.control-group.label>
                                                <x-admin::form.control-group.control type="select" name="marketing_event_id" rules="required" value="{{ isset($campaign) ? $campaign->marketing_event_id : '' }}" :label="trans('admin::app.marketing.communications.campaigns.create.event')">
                                                    <option value="" disabled selected hidden>Выберите событие</option>
                                                    @foreach (app('Webkul\Marketing\Repositories\EventRepository')->all() as $event)
                                                        <option value="{{ $event->id }}" {{ (isset($campaign) && $campaign->marketing_event_id == $event->id) ? 'selected' : '' }}>
                                                            {{ $event->name }}
                                                        </option>
                                                    @endforeach
                                                </x-admin::form.control-group.control>
                                                <x-admin::form.control-group.error control-name="marketing_event_id" />
                                            </x-admin::form.control-group>

                                            <!-- Email Template -->
                                            <x-admin::form.control-group>
                                                <x-admin::form.control-group.label class="required">
                                                    @lang('admin::app.marketing.communications.campaigns.create.email-template')
                                                </x-admin::form.control-group.label>
                                                <x-admin::form.control-group.control type="select" name="marketing_template_id" rules="required" value="{{ isset($campaign) ? $campaign->marketing_template_id : '' }}" :label="trans('admin::app.marketing.communications.campaigns.create.email-template')">
                                                    <option value="" disabled selected hidden>Выберите шаблон</option>
                                                    @foreach ($templates as $template)
                                                        <option value="{{ $template->id }}" {{ (isset($campaign) && $campaign->marketing_template_id == $template->id) ? 'selected' : '' }}>
                                                            {{ $template->name }}
                                                        </option>
                                                    @endforeach
                                                </x-admin::form.control-group.control>
                                                <x-admin::form.control-group.error control-name="marketing_template_id" />
                                            </x-admin::form.control-group>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right: Settings -->
                                <div style="width:320px; min-width:280px;">
                                    <div style="background:white; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.05); border:1px solid #f3f4f6; overflow:hidden;">
                                        <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6;">
                                            <p style="font-size:14px; font-weight:600; color:#111827; margin:0;">Настройки</p>
                                        </div>
                                        <div style="padding:20px; display:flex; flex-direction:column; gap:16px;">
                                            <!-- Channel -->
                                            <x-admin::form.control-group>
                                                <x-admin::form.control-group.label class="required">
                                                    @lang('admin::app.marketing.communications.campaigns.create.channel')
                                                </x-admin::form.control-group.label>
                                                <x-admin::form.control-group.control type="select" name="channel_id" rules="required" value="{{ isset($campaign) ? $campaign->channel_id : '' }}" :label="trans('admin::app.marketing.communications.campaigns.create.channel')">
                                                    <option value="" disabled selected hidden>Выберите канал</option>
                                                    @foreach (app('Webkul\Core\Repositories\ChannelRepository')->all() as $channel)
                                                        <option value="{{ $channel->id }}" {{ (isset($campaign) && $campaign->channel_id == $channel->id) ? 'selected' : '' }}>
                                                            {{ core()->getChannelName($channel) }}
                                                        </option>
                                                    @endforeach
                                                </x-admin::form.control-group.control>
                                                <x-admin::form.control-group.error control-name="channel_id" />
                                            </x-admin::form.control-group>

                                            <!-- Customer Group -->
                                            <x-admin::form.control-group>
                                                <x-admin::form.control-group.label class="required">
                                                    @lang('admin::app.marketing.communications.campaigns.create.customer-group')
                                                </x-admin::form.control-group.label>
                                                <x-admin::form.control-group.control type="select" name="customer_group_id" rules="required" value="{{ isset($campaign) ? $campaign->customer_group_id : '' }}" :label="trans('admin::app.marketing.communications.campaigns.create.customer-group')">
                                                    <option value="" disabled selected hidden>Выберите группу</option>
                                                    @foreach (app('Webkul\Customer\Repositories\CustomerGroupRepository')->all() as $customerGroup)
                                                        <option value="{{ $customerGroup->id }}" {{ (isset($campaign) && $campaign->customer_group_id == $customerGroup->id) ? 'selected' : '' }}>
                                                            {{ $customerGroup->name }}
                                                        </option>
                                                    @endforeach
                                                </x-admin::form.control-group.control>
                                                <x-admin::form.control-group.error control-name="customer_group_id" />
                                            </x-admin::form.control-group>

                                            <!-- Status -->
                                            <x-admin::form.control-group>
                                                <x-admin::form.control-group.label>
                                                    @lang('admin::app.marketing.communications.campaigns.create.status')
                                                </x-admin::form.control-group.label>
                                                <x-admin::form.control-group.control
                                                    type="hidden"
                                                    name="status"
                                                    value="0"
                                                />
                                                <x-admin::form.control-group.control
                                                    type="switch"
                                                    name="status"
                                                    value="1"
                                                    :checked="isset($campaign) ? (bool) $campaign->status : false"
                                                    :label="trans('admin::app.marketing.communications.campaigns.create.status')"
                                                    style="cursor:pointer;"
                                                />
                                                <x-admin::form.control-group.error control-name="status" />
                                            </x-admin::form.control-group>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                                <button type="button" class="transparent-button" @click="cancel">Отмена</button>
                                <button type="submit" class="primary-button" :disabled="isLoading">
                                    <template v-if="isLoading">
                                        <svg style="width:18px; height:18px; animation:spin 1s linear infinite;" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-dasharray="32" stroke-dashoffset="10" /></svg>
                                    </template>
                                    Сохранить
                                </button>
                            </div>
                        </div>
                    </form>
                </x-admin::form>
            </div>
        </script>

        <script type="module">
            app.component('v-campaign-panel', {
                template: '#v-campaign-panel-template',
                data() {
                    return { isLoading: false };
                },
                methods: {
                    save(params, { setErrors }) {
                        this.isLoading = true;
                        let formData = new FormData(this.$refs.panelForm);
                        @if(isset($campaign))
                            formData.append('_method', 'put');
                        @endif
                        this.$axios.post(
                            @if(isset($campaign))
                                "{{ route('admin.marketing.communications.campaigns.update', $campaign->id) }}"
                            @else
                                "{{ route('admin.marketing.communications.campaigns.store') }}"
                            @endif,
                            formData
                        ).then(response => {
                            this.isLoading = false;
                            if (window.parent !== window) {
                                window.parent.postMessage({ type: 'panel-saved', message: response.data?.message || 'Сохранено' }, '*');
                            }
                        }).catch(error => {
                            this.isLoading = false;
                            if (error.response?.status === 422) { setErrors(error.response.data.errors); }
                            else { this.$emitter.emit('add-flash', { type: 'error', message: error.response?.data?.message || 'Ошибка сохранения' }); }
                        });
                    },
                    cancel() {
                        if (window.parent !== window) window.parent.postMessage({ type: 'panel-closed' }, '*');
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
