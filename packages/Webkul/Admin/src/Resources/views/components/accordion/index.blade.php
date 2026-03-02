@props([
    'isActive' => true,
])

<div {{ $attributes->merge(['class' => 'box-shadow rounded-2xl bg-white dark:bg-gray-900 overflow-hidden']) }}>
    <v-accordion
        is-active="{{ $isActive }}"
        {{ $attributes }}
    >
        <x-admin::shimmer.accordion class="h-[271px] w-[360px]" />

        @isset($header)
            <template v-slot:header="{ toggle, isOpen }">
                <div {{ $header->attributes->merge(['class' => 'flex items-center justify-between p-4 bg-gradient-to-r from-gray-50/50 to-transparent dark:from-gray-800/30 border-b border-gray-100 dark:border-gray-800']) }}>
                    {{ $header }}

                    <button
                        class="flex items-center justify-center w-8 h-8 cursor-pointer rounded-xl text-xl transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-gray-700 dark:hover:text-indigo-400"
                        :class="[isOpen ? 'icon-arrow-up rotate-0' : 'icon-arrow-down']"
                        @click="toggle"
                    ></button>
                </div>
            </template>
        @endisset

        @isset($content)
            <template v-slot:content="{ isOpen }">
                <div
                    {{ $content->attributes->merge(['class' => 'px-5 py-4 animate-fade-in']) }}
                    v-show="isOpen"
                >
                    {{ $content }}
                </div>
            </template>
        @endisset
    </v-accordion>
</div>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-accordion-template"
    >
        <div>
            <slot
                name="header"
                :toggle="toggle"
                :isOpen="isOpen"
            >
                Default Header
            </slot>

            <slot
                name="content"
                :isOpen="isOpen"
            >
                Default Content
            </slot>
        </div>
    </script>

    <script type="module">
        app.component('v-accordion', {
            template: '#v-accordion-template',

            props: [
                'isActive',
            ],

            data() {
                return {
                    isOpen: this.isActive,
                };
            },

            methods: {
                toggle() {
                    this.isOpen = ! this.isOpen;

                    this.$emit('toggle', { isActive: this.isOpen });
                },
            },
        });
    </script>
@endPushOnce
