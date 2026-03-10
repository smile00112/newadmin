<x-admin::layouts :hideNavigation="true">
    <x-slot:title>
        {{ isset($template) ? trans('admin::app.marketing.communications.templates.edit.title') : trans('admin::app.marketing.communications.templates.create.title') }}
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

    <v-template-panel></v-template-panel>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-template-panel-template">
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
                        @if(isset($template))
                            <input type="hidden" name="id" value="{{ $template->id }}">
                        @endif

                        <!-- Content -->
                        <div style="padding:10px 24px 20px;">
                            <!-- General section -->
                            <div style="background:white; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.05); border:1px solid #f3f4f6; overflow:hidden;">
                                <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6;">
                                    <p style="font-size:14px; font-weight:600; color:#111827; margin:0;">Основные данные</p>
                                </div>
                                <div style="padding:20px; display:flex; flex-direction:column; gap:16px;">
                                    <!-- Name -->
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label class="required">
                                            @lang('admin::app.marketing.communications.templates.create.name')
                                        </x-admin::form.control-group.label>
                                        <x-admin::form.control-group.control type="text" name="name" rules="required" value="{{ isset($template) ? $template->name : '' }}" :label="trans('admin::app.marketing.communications.templates.create.name')" :placeholder="trans('admin::app.marketing.communications.templates.create.name')" />
                                        <x-admin::form.control-group.error control-name="name" />
                                    </x-admin::form.control-group>

                                    <!-- Status -->
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label class="required">
                                            @lang('admin::app.marketing.communications.templates.create.status')
                                        </x-admin::form.control-group.label>
                                        <x-admin::form.control-group.control type="select" name="status" rules="required" value="{{ isset($template) ? $template->status : '' }}" :label="trans('admin::app.marketing.communications.templates.create.status')">
                                            <option value="" disabled selected hidden>Выберите статус</option>
                                            <option value="active" {{ (isset($template) && $template->status == 'active') ? 'selected' : '' }}>
                                                @lang('admin::app.marketing.communications.templates.create.active')
                                            </option>
                                            <option value="inactive" {{ (isset($template) && $template->status == 'inactive') ? 'selected' : '' }}>
                                                @lang('admin::app.marketing.communications.templates.create.inactive')
                                            </option>
                                            <option value="draft" {{ (isset($template) && $template->status == 'draft') ? 'selected' : '' }}>
                                                @lang('admin::app.marketing.communications.templates.create.draft')
                                            </option>
                                        </x-admin::form.control-group.control>
                                        <x-admin::form.control-group.error control-name="status" />
                                    </x-admin::form.control-group>

                                    <!-- Content -->
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label class="required">
                                            @lang('admin::app.marketing.communications.templates.create.content')
                                        </x-admin::form.control-group.label>
                                        <x-admin::form.control-group.control
                                            type="textarea"
                                            id="content"
                                            name="content"
                                            rules="required"
                                            value="{{ isset($template) ? $template->content : '' }}"
                                            :label="trans('admin::app.marketing.communications.templates.create.content')"
                                            :placeholder="trans('admin::app.marketing.communications.templates.create.content')"
                                            :tinymce="true"
                                        />
                                        <x-admin::form.control-group.error control-name="content" />
                                    </x-admin::form.control-group>
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
            app.component('v-template-panel', {
                template: '#v-template-panel-template',
                data() {
                    return { isLoading: false };
                },
                methods: {
                    save(params, { setErrors }) {
                        this.isLoading = true;
                        let formData = new FormData(this.$refs.panelForm);
                        @if(isset($template))
                            formData.append('_method', 'put');
                        @endif
                        this.$axios.post(
                            @if(isset($template))
                                "{{ route('admin.marketing.communications.email_templates.update', $template->id) }}"
                            @else
                                "{{ route('admin.marketing.communications.email_templates.store') }}"
                            @endif,
                            formData
                        ).then(response => {
                            this.isLoading = false;
                            if (window.parent !== window) {
                                window.parent.postMessage({ type: 'panel-saved', message: response.data.message }, '*');
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
