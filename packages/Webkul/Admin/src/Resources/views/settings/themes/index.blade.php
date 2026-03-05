<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.themes.index.title')
    </x-slot>
   
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); box-shadow: 0 4px 15px rgba(139,92,246,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.settings.themes.index.title')
                </p>
                <p class="text-xs text-gray-400">Управление темами</p>
            </div>
        </div>
        
        <div class="flex items-center gap-x-2.5">
            <div class="flex items-center gap-x-2.5">
                {!! view_render_event('bagisto.admin.settings.themes.create.before') !!}

                <!-- Create Button -->
                <v-create-theme-form>
                    <button
                        type="button"
                        class="primary-button"
                    >
                        @lang('admin::app.settings.themes.index.create-btn')
                    </button>  
                </v-create-theme-form>

                {!! view_render_event('bagisto.admin.settings.themes.create.after') !!}
            </div>
        </div>
    </div>
    
    {!! view_render_event('bagisto.admin.settings.themes.list.before') !!}

    <x-admin::datagrid :src="route('admin.settings.themes.index')" />

    {!! view_render_event('bagisto.admin.settings.themes.list.after') !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-create-theme-form-template"
        >
            <div>
                <!-- Theme Create Button -->
                @if (bouncer()->hasPermission('settings.themes.create'))
                    <button
                        type="button"
                        class="primary-button"
                        @click="$refs.themeCreateModal.toggle()"
                    >
                        @lang('admin::app.settings.themes.index.create-btn')
                    </button>
                @endif

                <!-- Modal Form -->
                <x-admin::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                >
                    <form @submit="handleSubmit($event, create)">
                        <!-- Customer Create Modal -->
                        <x-admin::modal ref="themeCreateModal">
                            <!-- Modal Header -->
                            <x-slot:header>
                                <p class="text-lg font-bold text-gray-800 dark:text-white">
                                    @lang('admin::app.settings.themes.create.title')
                                </p>
                            </x-slot>

                            <!-- Modal Content -->
                            <x-slot:content>
                                <!-- Name -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.settings.themes.create.name')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="name"
                                        rules="required"
                                        :label="trans('admin::app.settings.themes.create.name')"
                                        :placeholder="trans('admin::app.settings.themes.create.name')"
                                    />

                                    <x-admin::form.control-group.error control-name="name" />
                                </x-admin::form.control-group>

                                <!-- Sort Order -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.settings.themes.create.sort-order')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="sort_order"
                                        rules="required|numeric"
                                        :label="trans('admin::app.settings.themes.create.sort-order')"
                                        :placeholder="trans('admin::app.settings.themes.create.sort-order')"
                                    />

                                    <x-admin::form.control-group.error control-name="sort_order" />
                                </x-admin::form.control-group>

                                <!-- Type -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.settings.themes.create.type.title')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="select"
                                        name="type"
                                        rules="required"
                                        value="product_carousel"
                                    >
                                        <option 
                                            v-for="(type, key) in themeTypes"
                                            :value="key"
                                            :text="type"
                                        >
                                        </option>
                                    </x-admin::form.control-group.control>

                                    <x-admin::form.control-group.error control-name="type" />
                                </x-admin::form.control-group>

                                <!-- Channels -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.settings.themes.edit.channels')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="select"
                                        name="channel_id"
                                        rules="required"
                                        :value="1"
                                    >
                                        @foreach (core()->getAllChannels() as $channel)
                                            <option value="{{ $channel->id }}">{{ $channel->name }}</option>
                                        @endforeach 
                                    </x-admin::form.control-group.control>

                                    <x-admin::form.control-group.error control-name="type" />
                                </x-admin::form.control-group>

                                 <!-- Theme Selector -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.settings.themes.create.themes')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="select"
                                        id="theme_code"
                                        name="theme_code"
                                        :value="config('themes.admin-default')"
                                        :label="trans('admin::app.settings.themes.create.themes')"
                                    >
                                        @foreach (config('themes.shop') as $themeCode => $theme)
                                            <option value="{{ $themeCode }}" {{ old('theme') == $themeCode ? 'selected' : '' }}>
                                                {{ $theme['name'] }}
                                            </option>
                                        @endforeach
                                    </x-admin::form.control-group.control>

                                    <x-admin::form.control-group.error control-name="theme" />
                                </x-admin::form.control-group>
                            </x-slot>

                             <!-- Modal Footer -->
                            <x-slot:footer>
                                <!-- Save Button -->
                                <x-admin::button
                                    button-type="submit"
                                    class="primary-button"
                                    :title="trans('admin::app.settings.themes.create.save-btn')"
                                    ::loading="isLoading"
                                    ::disabled="isLoading"
                                />
                            </x-slot>
                        </x-admin::modal>
                    </form>
                </x-admin::form>
            </div>
        </script>

        <script type="module">
            app.component('v-create-theme-form', {
                template: '#v-create-theme-form-template',

                data() {
                    return {
                        themeTypes: {
                            product_carousel: "@lang('admin::app.settings.themes.create.type.product-carousel')",
                            category_carousel: "@lang('admin::app.settings.themes.create.type.category-carousel')",
                            static_content: "@lang('admin::app.settings.themes.create.type.static-content')",
                            image_carousel: "@lang('admin::app.settings.themes.create.type.image-carousel')",
                            footer_links: "@lang('admin::app.settings.themes.create.type.footer-links')",
                            services_content: "@lang('admin::app.settings.themes.create.type.services-content')",
                        },

                        isLoading: false,
                    };
                },

                methods: {
                    create(params, { setErrors }) {
                        this.isLoading = true;

                        this.$axios.post('{{ route('admin.settings.themes.store') }}', params)
                            .then((response) => {
                                this.isLoading = false;

                                if (response.data.redirect_url) {
                                    window.location.href = response.data.redirect_url;
                                } 
                            })
                            .catch((error) => {
                                this.isLoading = false;

                                if (error.response.status == 422) {
                                    setErrors(error.response.data.errors);
                                }
                            });
                    },
                },
            });
        </script>
    @endPushOnce
    
</x-admin::layouts>