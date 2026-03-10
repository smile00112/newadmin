<x-admin::layouts :hideNavigation="true">
    <x-slot:title>
        {{ isset($role) ? trans('admin::app.settings.roles.edit.title') : trans('admin::app.settings.roles.create.title') }}
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

    {{-- Hidden tree-view for JS component registration --}}
    <div class="hidden">
        <x-admin::tree.view input-type="checkbox" :items="json_encode([])" />
    </div>

    <v-role-panel></v-role-panel>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-role-panel-template">
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
                        @if(isset($role))
                            <input type="hidden" name="id" value="{{ $role->id }}">
                        @endif

                        <!-- Content — two column layout like original page -->
                        <div style="padding:10px 24px 20px;">
                            <div style="display:flex; gap:16px; flex-wrap:wrap;">
                                <!-- Left: Access Control -->
                                <div style="flex:1; min-width:300px;">
                                    <div style="background:white; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.05); border:1px solid #f3f4f6; overflow:hidden;">
                                        <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6;">
                                            <p style="font-size:14px; font-weight:600; color:#111827; margin:0;">@lang('admin::app.settings.roles.create.access-control')</p>
                                        </div>
                                        <div style="padding:20px; display:flex; flex-direction:column; gap:16px;">
                                            <!-- Permission Type -->
                                            <x-admin::form.control-group>
                                                <x-admin::form.control-group.label class="required">
                                                    @lang('admin::app.settings.roles.create.permissions')
                                                </x-admin::form.control-group.label>
                                                <x-admin::form.control-group.control type="select" name="permission_type" rules="required" v-model="permissionType" :label="trans('admin::app.settings.roles.create.permissions')">
                                                    <option value="custom" {{ (isset($role) && $role->permission_type === 'custom') ? 'selected' : '' }}>
                                                        @lang('admin::app.settings.roles.create.custom')
                                                    </option>
                                                    <option value="all" {{ (isset($role) && $role->permission_type === 'all') ? 'selected' : '' }}>
                                                        @lang('admin::app.settings.roles.create.all')
                                                    </option>
                                                </x-admin::form.control-group.control>
                                                <x-admin::form.control-group.error control-name="permission_type" />
                                            </x-admin::form.control-group>

                                            <!-- Permissions Tree -->
                                            <div v-if="permissionType === 'custom'">
                                                <x-admin::form.control-group.error control-name="permissions" />
                                                <x-admin::tree.view
                                                    input-type="checkbox"
                                                    name-field="permissions"
                                                    value-field="key"
                                                    id-field="key"
                                                    :items="json_encode(acl()->getItems())"
                                                    :value="json_encode(isset($role) ? $role->permissions : [])"
                                                    :fallback-locale="config('app.fallback_locale')"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right: General -->
                                <div style="width:340px; max-width:100%; flex-shrink:0;">
                                    <div style="background:white; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.05); border:1px solid #f3f4f6; overflow:hidden;">
                                        <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6;">
                                            <p style="font-size:14px; font-weight:600; color:#111827; margin:0;">@lang('admin::app.settings.roles.create.general')</p>
                                        </div>
                                        <div style="padding:20px; display:flex; flex-direction:column; gap:16px;">
                                            <!-- Name -->
                                            <x-admin::form.control-group>
                                                <x-admin::form.control-group.label class="required">
                                                    @lang('admin::app.settings.roles.create.name')
                                                </x-admin::form.control-group.label>
                                                <x-admin::form.control-group.control type="text" name="name" rules="required" value="{{ isset($role) ? $role->name : '' }}" :label="trans('admin::app.settings.roles.create.name')" :placeholder="trans('admin::app.settings.roles.create.name')" />
                                                <x-admin::form.control-group.error control-name="name" />
                                            </x-admin::form.control-group>

                                            <!-- Description -->
                                            <x-admin::form.control-group>
                                                <x-admin::form.control-group.label class="required">
                                                    @lang('admin::app.settings.roles.create.description')
                                                </x-admin::form.control-group.label>
                                                <x-admin::form.control-group.control type="textarea" name="description" rules="required" value="{{ isset($role) ? $role->description : '' }}" :label="trans('admin::app.settings.roles.create.description')" :placeholder="trans('admin::app.settings.roles.create.description')" />
                                                <x-admin::form.control-group.error control-name="description" />
                                            </x-admin::form.control-group>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Buttons -->
                            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                                <button type="button" class="transparent-button" @click="cancel">Отмена</button>
                                <button type="submit" class="primary-button" :disabled="isLoading">
                                    <template v-if="isLoading"><svg style="width:18px; height:18px; animation:spin 1s linear infinite;" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-dasharray="32" stroke-dashoffset="10" /></svg></template>
                                    @lang('admin::app.settings.roles.create.save-btn')
                                </button>
                            </div>
                        </div>
                    </form>
                </x-admin::form>
            </div>
        </script>

        <script type="module">
            app.component('v-role-panel', {
                template: '#v-role-panel-template',
                data() {
                    return {
                        isLoading: false,
                        permissionType: '{{ isset($role) ? $role->permission_type : "custom" }}',
                        treeKey: 0,
                    };
                },
                methods: {
                    save(params, { setErrors }) {
                        this.isLoading = true;
                        let formData = new FormData(this.$refs.panelForm);
                        @if(isset($role))
                            formData.append('_method', 'put');
                        @endif
                        this.$axios.post(
                            @if(isset($role))
                                "{{ route('admin.settings.roles.update', $role->id) }}"
                            @else
                                "{{ route('admin.settings.roles.store') }}"
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
