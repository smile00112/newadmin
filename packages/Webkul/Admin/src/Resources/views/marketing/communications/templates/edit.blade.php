<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.marketing.communications.templates.edit.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.marketing.communications.templates.edit.before', ['template' => $template]) !!}
    <!-- Input Form -->
    <x-admin::form
        :action="route('admin.marketing.communications.email_templates.update', $template->id)"
        method="PUT"
    >

        {!! view_render_event('bagisto.admin.marketing.communications.templates.edit.edit_form_controls.before', ['template' => $template]) !!}

<div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); box-shadow: 0 4px 15px rgba(236,72,153,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.marketing.communications.templates.edit.title')
                </p>
                <p class="text-xs text-gray-400">Редактирование шаблона</p>
            </div>
        </div>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.marketing.communications.email_templates.index') }}"
                    style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; color: #6b7280; background: #f3f4f6; transition: all 0.15s; text-decoration: none;"
                    onmouseenter="this.style.background='#e5e7eb'"
                    onmouseleave="this.style.background='#f3f4f6'"
                >
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    @lang('admin::app.marketing.communications.templates.edit.back-btn')
                </a>

                <!-- Save Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('admin::app.marketing.communications.templates.edit.save-btn')
                </button>
            </div>
        </div>

        <!-- body content -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left sub-component -->
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">

                {!! view_render_event('bagisto.admin.marketing.communications.templates.edit.card.content.before', ['template' => $template]) !!}

                <!--Content -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <div class="w-full">
                        <!-- Template Textarea -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.marketing.communications.templates.create.content')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="textarea"
                                id="content"
                                name="content"
                                rules="required"
                                value="{{ old('content') ?: $template->content }}"
                                :label="trans('admin::app.marketing.communications.templates.edit.content')"
                                :placeholder="trans('admin::app.marketing.communications.templates.edit.content')"
                                :tinymce="true"
                            />

                            <x-admin::form.control-group.error control-name="content" />
                        </x-admin::form.control-group>
                    </div>
                </div>

                {!! view_render_event('bagisto.admin.marketing.communications.templates.edit.card.content.after', ['template' => $template]) !!}

            </div>

            <!-- Right sub-component -->
            <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                <!-- General -->

                {!! view_render_event('bagisto.admin.marketing.communications.templates.edit.card.accordion.general.before', ['template' => $template]) !!}

                <div class="box-shadow rounded bg-white dark:bg-gray-900">
                    <x-admin::accordion>
                        <x-slot:header>
                            <div class="flex items-center justify-between">
                                <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                    @lang('admin::app.marketing.communications.templates.edit.general')
                                </p>
                            </div>
                        </x-slot>

                        <x-slot:content>
                            <div class="mb-2.5 w-full">
                                <!-- Template Name -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.marketing.communications.templates.edit.name')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="name"
                                        rules="required"
                                        value="{{ old('name') ?: $template->name }}"
                                        :label="trans('admin::app.marketing.communications.templates.edit.name')"
                                        :placeholder="trans('admin::app.marketing.communications.templates.edit.name')"
                                    />

                                    <x-admin::form.control-group.error control-name="name" />
                                </x-admin::form.control-group>

                                <!-- Template Status -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.marketing.communications.templates.edit.status')
                                    </x-admin::form.control-group.label>

                                    @php $selectedOption = old('status') ?: $template->status @endphp

                                    <x-admin::form.control-group.control
                                        type="select"
                                        name="status"
                                        rules="required"
                                        :value="$selectedOption"
                                        :label="trans('admin::app.marketing.communications.templates.edit.status')"
                                    >
                                        @foreach (['active', 'inactive', 'draft'] as $state)
                                            <option
                                                value="{{ $state }}"
                                            >
                                                @lang('admin::app.marketing.communications.templates.edit.' . $state)
                                            </option>
                                        @endforeach
                                    </x-admin::form.control-group.control>

                                    <x-admin::form.control-group.error control-name="status" />
                                </x-admin::form.control-group>
                            </div>
                        </x-slot>
                    </x-admin::accordion>
                </div>

                {!! view_render_event('bagisto.admin.marketing.communications.templates.edit.card.accordion.general.after', ['template' => $template]) !!}

            </div>
        </div>

        {!! view_render_event('bagisto.admin.marketing.communications.templates.edit.edit_form_controls.before', ['template' => $template]) !!}

    </x-admin::form>

    {!! view_render_event('bagisto.admin.marketing.communications.templates.edit.after', ['template' => $template]) !!}

</x-admin::layouts>
