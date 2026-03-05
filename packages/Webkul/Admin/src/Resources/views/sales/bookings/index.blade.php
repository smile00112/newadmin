<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.sales.booking.index.title')
    </x-slot>

    <v-booking-products></v-booking-products>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-booking-products-template"
        >
            <div class="flex items-center justify-between gap-[16px] max-sm:flex-wrap">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); box-shadow: 0 4px 15px rgba(99,102,241,0.3); min-width:44px;">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-800 dark:text-white">
                            @lang('admin::app.sales.booking.index.title')
                        </p>
                        <p class="text-xs text-gray-400">Бронирования</p>
                    </div>
                </div>
        
                <div class="flex items-center gap-2.5">
                    <!-- Export Modal -->
                    <x-admin::datagrid.export
                        v-if="viewType == 'table'"
                        src="{{ route('admin.sales.bookings.index') }}" 
                    />
        
                    <!-- View Switcher -->
                    <div class="grid grid-cols-2 border border-gray-300 dark:border-gray-700">
                        <!-- Calendar Icon -->
                        <button
                            class="icon-calendar cursor-pointer p-1.5 text-xl"
                            :class="{'bg-blue-700 text-white' : viewType === 'calendar'}"
                            @click="viewType = 'calendar'"
                        ></button>

                        <!-- List Icon -->
                        <button
                            class="icon-list cursor-pointer p-1.5 text-xl"
                            :class="{'bg-blue-700 text-white' : viewType === 'table'}"
                            @click="viewType = 'table'"
                        ></button>
                    </div>
                </div>
            </div>

            <template v-if="viewType == 'table'">
                <x-admin::datagrid :src="route('admin.sales.bookings.index')" />
            </template>

            <template v-else>
                @include('admin::sales.bookings.calendar')
            </template>
        </script>

        <script type="module">
            app.component('v-booking-products', {
                template: '#v-booking-products-template',
                
                data() {
                    return {
                        viewType: 'calendar',
                    };
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>