<x-admin::layouts>
    <!--Page title -->
    <x-slot:title>
        @lang('admin::app.cms.create.title')
    </x-slot>

    <!--Create Page Form -->
    <x-admin::form
        :action="route('admin.cms.store')"
        enctype="multipart/form-data"
    >

        {!! view_render_event('bagisto.admin.cms.pages.create.create_form_controls.before') !!}

        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #64748b 0%, #475569 100%); box-shadow: 0 4px 15px rgba(100,116,139,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.cms.create.title')
                </p>
                <p class="text-xs text-gray-400">Новая CMS-страница</p>
            </div>
        </div>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.cms.index') }}"
                    style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; color: #6b7280; background: #f3f4f6; transition: all 0.15s; text-decoration: none;"
                    onmouseenter="this.style.background='#e5e7eb'"
                    onmouseleave="this.style.background='#f3f4f6'"
                >
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    @lang('admin::app.account.edit.back-btn')
                </a>

                <!--Save Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('admin::app.cms.create.save-btn')
                </button>
            </div>
        </div>

        <!-- body content -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left sub-component -->
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">

                {!! view_render_event('bagisto.admin.cms.pages.create.card.description.before') !!}

                <!--Content -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.cms.create.description')
                    </p>

                    <!-- Html Content -->
                    <x-admin::form.control-group class="!mb-0">
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.cms.create.content')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            id="content"
                            name="html_content"
                            rules="required"
                            :value="old('html_content')"
                            :label="trans('admin::app.cms.create.content')"
                            :placeholder="trans('admin::app.cms.create.content')"
                            :tinymce="true"
                            :prompt="core()->getConfigData('general.magic_ai.content_generation.cms_page_content_prompt')"
                        />

                        <x-admin::form.control-group.error control-name="html_content" />
                    </x-admin::form.control-group>
                </div>

                {!! view_render_event('bagisto.admin.cms.pages.create.card.description.after') !!}

                {!! view_render_event('bagisto.admin.cms.pages.create.card.seo.before') !!}

                <!-- SEO Input Fields -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.cms.create.seo')
                    </p>

                    <!-- SEO Title & Description Blade Component -->
                    <x-admin::seo/>

                    <!-- Meta Title -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.cms.create.meta-title')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="meta_title"
                            name="meta_title"
                            :value="old('meta_title')"
                            :label="trans('admin::app.cms.create.meta-title')"
                            :placeholder="trans('admin::app.cms.create.meta-title')"
                        />

                        <x-admin::form.control-group.error control-name="meta_title" />
                    </x-admin::form.control-group>

                    <!-- URL Key -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.cms.create.url-key')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="url_key"
                            name="url_key"
                            rules="required"
                            :value="old('url_key')"
                            :label="trans('admin::app.cms.create.url-key')"
                            :placeholder="trans('admin::app.cms.create.url-key')"
                        />

                        <x-admin::form.control-group.error control-name="url_key" />
                    </x-admin::form.control-group>

                    <!-- Meta Keywords -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.cms.create.meta-keywords')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            id="meta_keywords"
                            name="meta_keywords"
                            :value="old('meta_keywords')"
                            :label="trans('admin::app.cms.create.meta-keywords')"
                            :placeholder="trans('admin::app.cms.create.meta-keywords')"
                        />

                        <x-admin::form.control-group.error control-name="meta_keywords" />
                    </x-admin::form.control-group>

                    <!-- Meta Description -->
                    <x-admin::form.control-group class="!mb-0">
                        <x-admin::form.control-group.label>
                            @lang('admin::app.cms.create.meta-description')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            id="meta_description"
                            name="meta_description"
                            :value="old('meta_description')"
                            :label="trans('admin::app.cms.create.meta-description')"
                            :placeholder="trans('admin::app.cms.create.meta-description')"
                        />

                        <x-admin::form.control-group.error control-name="meta_description" />
                    </x-admin::form.control-group>
                </div>

                {!! view_render_event('bagisto.admin.cms.pages.create.card.seo.after') !!}
            </div>

            <!-- Right sub-component -->
            <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                <!-- General -->

                {!! view_render_event('bagisto.admin.cms.pages.create.card.accordion.general.before') !!}

                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.cms.create.general')
                        </p>
                    </x-slot>

                    <x-slot:content>
                        <!-- Page Title -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.cms.create.page-title')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                id="page_title"
                                name="page_title"
                                rules="required"
                                :value="old('page_title')"
                                :label="trans('admin::app.cms.create.page-title')"
                                :placeholder="trans('admin::app.cms.create.page-title')"
                            />

                            <x-admin::form.control-group.error control-name="page_title" />
                        </x-admin::form.control-group>

                        <!-- Select Channels -->
                        <x-admin::form.control-group.label>
                            @lang('admin::app.cms.create.channels')
                        </x-admin::form.control-group.label>

                        @foreach(core()->getAllChannels() as $channel)
                            <x-admin::form.control-group class="!mb-2 flex select-none items-center gap-2.5 last:!mb-0">
                                <x-admin::form.control-group.control
                                    type="checkbox"
                                    :id="'channels_' . $channel->id"
                                    name="channels[]"
                                    :value="$channel->id"
                                    :for="'channels_' . $channel->id"
                                    :label="trans('admin::app.cms.create.channels')"
                                />

                                <label
                                    class="cursor-pointer text-xs font-medium text-gray-600 dark:text-gray-300"
                                    for="channels_{{ $channel->id }}" 
                                >
                                    {{ core()->getChannelName($channel) }}
                                </label>
                            </x-admin::form.control-group>
                        @endforeach

                        <x-admin::form.control-group.error control-name="channels[]" />
                    </x-slot>
                </x-admin::accordion>

                {!! view_render_event('bagisto.admin.cms.pages.create.card.accordion.general.after') !!}

            </div>
        </div>

        {!! view_render_event('bagisto.admin.cms.pages.create.create_form_controls.after') !!}

    </x-admin::form>
</x-admin::layouts>
