<x-admin::layouts :hideNavigation="true">
    <x-slot:title>
        {{ isset($user) ? trans('admin::app.settings.users.index.edit.title') : trans('admin::app.settings.users.index.create.title') }}
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

    <v-user-panel></v-user-panel>

    {{-- Hidden media.images for JS component registration --}}
    <div class="hidden">
        <x-admin::media.images name="image" />
    </div>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-user-panel-template">
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
                    <form @submit="handleSubmit($event, save)" ref="panelForm" enctype="multipart/form-data">
                        @if(isset($user))
                            <input type="hidden" name="id" value="{{ $user->id }}">
                        @endif

                        <!-- Content — two column layout -->
                        <div style="padding:10px 24px 20px;">
                            <div style="display:flex; gap:16px; flex-wrap:wrap;">
                                <!-- Left: Account Details -->
                                <div style="flex:1; min-width:300px;">
                                    <div style="background:white; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.05); border:1px solid #f3f4f6; overflow:hidden;">
                                        <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6;">
                                            <p style="font-size:14px; font-weight:600; color:#111827; margin:0;">Данные аккаунта</p>
                                        </div>
                                        <div style="padding:20px; display:flex; flex-direction:column; gap:16px;">
                                            <!-- Name -->
                                            <x-admin::form.control-group>
                                                <x-admin::form.control-group.label class="required">
                                                    @lang('admin::app.settings.users.index.create.name')
                                                </x-admin::form.control-group.label>
                                                <x-admin::form.control-group.control type="text" name="name" rules="required" value="{{ isset($user) ? $user->name : '' }}" :label="trans('admin::app.settings.users.index.create.name')" :placeholder="trans('admin::app.settings.users.index.create.name')" />
                                                <x-admin::form.control-group.error control-name="name" />
                                            </x-admin::form.control-group>

                                            <!-- Email -->
                                            <x-admin::form.control-group>
                                                <x-admin::form.control-group.label class="required">
                                                    @lang('admin::app.settings.users.index.create.email')
                                                </x-admin::form.control-group.label>
                                                <x-admin::form.control-group.control type="email" name="email" rules="required|email" value="{{ isset($user) ? $user->email : '' }}" :label="trans('admin::app.settings.users.index.create.email')" placeholder="email@example.com" />
                                                <x-admin::form.control-group.error control-name="email" />
                                            </x-admin::form.control-group>

                                            <!-- Password -->
                                            <x-admin::form.control-group>
                                                <x-admin::form.control-group.label>
                                                    @lang('admin::app.settings.users.index.create.password')
                                                </x-admin::form.control-group.label>
                                                <x-admin::form.control-group.control type="password" name="password" rules="min:6" ref="password" :label="trans('admin::app.settings.users.index.create.password')" :placeholder="trans('admin::app.settings.users.index.create.password')" />
                                                <x-admin::form.control-group.error control-name="password" />
                                            </x-admin::form.control-group>

                                            <!-- Confirm Password -->
                                            <x-admin::form.control-group>
                                                <x-admin::form.control-group.label>
                                                    @lang('admin::app.settings.users.index.create.confirm-password')
                                                </x-admin::form.control-group.label>
                                                <x-admin::form.control-group.control type="password" name="password_confirmation" rules="confirmed:@password" :label="trans('admin::app.settings.users.index.create.confirm-password')" :placeholder="trans('admin::app.settings.users.index.create.confirm-password')" />
                                                <x-admin::form.control-group.error control-name="password_confirmation" />
                                            </x-admin::form.control-group>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right: Role, Status, Avatar -->
                                <div style="width:340px; max-width:100%; flex-shrink:0;">
                                    <!-- Role & Status -->
                                    <div style="background:white; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.05); border:1px solid #f3f4f6; overflow:hidden;">
                                        <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6;">
                                            <p style="font-size:14px; font-weight:600; color:#111827; margin:0;">@lang('admin::app.settings.users.index.create.role')</p>
                                        </div>
                                        <div style="padding:20px; display:flex; flex-direction:column; gap:16px;">
                                            <!-- Role -->
                                            <x-admin::form.control-group>
                                                <x-admin::form.control-group.label class="required">
                                                    @lang('admin::app.settings.users.index.create.role')
                                                </x-admin::form.control-group.label>
                                                <x-admin::form.control-group.control type="select" name="role_id" rules="required" value="{{ isset($user) ? $user->role_id : '' }}" :label="trans('admin::app.settings.users.index.create.role')">
                                                    <option value="" disabled selected hidden>Выберите роль</option>
                                                    @foreach($roles as $role)
                                                        <option value="{{ $role->id }}" {{ (isset($user) && $user->role_id == $role->id) ? 'selected' : '' }}>{{ $role->name }}</option>
                                                    @endforeach
                                                </x-admin::form.control-group.control>
                                                <x-admin::form.control-group.error control-name="role_id" />
                                            </x-admin::form.control-group>

                                            <!-- Status -->
                                            @if(isset($user) && $currentUserId != $user->id)
                                                <x-admin::form.control-group>
                                                    <x-admin::form.control-group.label>
                                                        @lang('admin::app.settings.users.index.create.status')
                                                    </x-admin::form.control-group.label>
                                                    <x-admin::form.control-group.control type="switch" name="status" value="1" :checked="isset($user) ? (bool) $user->status : true" :label="trans('admin::app.settings.users.index.create.status')" />
                                                    <x-admin::form.control-group.error control-name="status" />
                                                </x-admin::form.control-group>
                                            @elseif(isset($user))
                                                <input type="hidden" name="status" value="{{ $user->status }}">
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Avatar -->
                                    <div style="background:white; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.05); border:1px solid #f3f4f6; overflow:hidden; margin-top:16px;">
                                        <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6;">
                                            <p style="font-size:14px; font-weight:600; color:#111827; margin:0;">Аватар</p>
                                        </div>
                                        <div style="padding:20px;">
                                            <v-media-images name="image" :uploaded-images="images"></v-media-images>
                                            <p class="text-xs text-gray-600 dark:text-gray-300" style="margin-top:8px;">
                                                @lang('admin::app.settings.users.index.create.upload-image-info')
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Buttons -->
                            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                                <button type="button" class="transparent-button" @click="cancel">Отмена</button>
                                <button type="submit" class="primary-button" :disabled="isLoading">
                                    <template v-if="isLoading"><svg style="width:18px; height:18px; animation:spin 1s linear infinite;" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-dasharray="32" stroke-dashoffset="10" /></svg></template>
                                    @lang('admin::app.settings.users.index.create.save-btn')
                                </button>
                            </div>
                        </div>
                    </form>
                </x-admin::form>
            </div>
        </script>

        <script type="module">
            app.component('v-user-panel', {
                template: '#v-user-panel-template',
                data() {
                    return {
                        isLoading: false,
                        images: @json($uploadedImages ?? []),
                    };
                },
                methods: {
                    save(params, { setErrors }) {
                        this.isLoading = true;
                        let formData = new FormData(this.$refs.panelForm);
                        @if(isset($user))
                            formData.append('_method', 'put');
                        @endif
                        this.$axios.post(
                            @if(isset($user))
                                "{{ route('admin.settings.users.update') }}"
                            @else
                                "{{ route('admin.settings.users.store') }}"
                            @endif,
                            formData,
                            { headers: { 'Content-Type': 'multipart/form-data' } }
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
