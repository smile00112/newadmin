@pushOnce('scripts')
<script type="text/x-template" id="v-analytics-filters-template">
    <div class="flex items-center gap-2 flex-wrap">
        <input type="date" v-model="start" @change="emit"
               class="rounded border px-2 py-1 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200">
        <span class="text-gray-400 text-xs">—</span>
        <input type="date" v-model="end" @change="emit"
               class="rounded border px-2 py-1 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200">
        <select v-model="channel" @change="emit"
                class="rounded border px-2 py-1 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200">
            <option value="">Все каналы</option>
            <option value="app">App</option>
            <option value="kiosk">Kiosk</option>
            <option value="cashier">Cashier</option>
        </select>
    </div>
</script>

<script type="module">
    app.component('v-analytics-filters', {
        template: '#v-analytics-filters-template',
        data() {
            const today = new Date();
            const ago30 = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
            return {
                start: ago30.toISOString().split('T')[0],
                end: today.toISOString().split('T')[0],
                channel: '',
            };
        },
        mounted() { this.emit(); },
        methods: {
            emit() {
                this.$emitter.emit('analytics-filter-changed', {
                    start: this.start,
                    end: this.end,
                    channel: this.channel || null,
                });
            },
        },
    });
</script>
@endPushOnce
