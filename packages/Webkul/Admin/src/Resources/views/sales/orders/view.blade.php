<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.sales.orders.view.title', ['order_id' => $order->increment_id])
    </x-slot>

    @php
        // Определяем порядок статусов для прогресс-линии
        $statusOrder = ['pending', 'pending_payment', 'processing', 'preparing', 'ready', 'completed'];
        $currentStatusIndex = array_search($order->status, $statusOrder);
        if ($currentStatusIndex === false) $currentStatusIndex = -1;

        $statusLabels = [
            'pending' => 'Новый',
            'pending_payment' => 'Оплата',
            'processing' => 'Принят',
            'preparing' => 'Готовим',
            'ready' => 'Готов',
            'completed' => 'Завершен'
        ];

        $statusIcons = [
            'pending' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            'pending_payment' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>',
            'processing' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            'preparing' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>',
            'ready' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>',
            'completed' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>'
        ];
    @endphp

    <div class="grid gap-5">
        {!! view_render_event('bagisto.admin.sales.order.title.before', ['order' => $order]) !!}

        <!-- Header Card with Progress Timeline -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-soft border border-gray-100 dark:border-gray-800 overflow-hidden">
            <!-- Header Section -->
            <div class="p-6 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-violet-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                Заказ #{{ $order->increment_id }}
                            </h1>
                            <div class="flex items-center gap-3 mt-1">
                                <span class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ core()->formatDate($order->created_at, 'd M Y, H:i') }}
                                </span>
                                @if($order->customer)
                                <span class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    {{ $order->customer_full_name }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Drag Mode Toggle -->
                        <button id="drag-mode-toggle"
                                type="button"
                                onclick="toggleOrderDragMode()"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium inline-flex items-center gap-2 transition-colors"
                                title="Режим перетаскивания блоков">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                            </svg>
                            <span class="drag-mode-text">Настроить</span>
                        </button>

                        <!-- Order Status Badge -->
                        @switch($order->status)
                            @case('pending')
                                <span class="px-4 py-2 bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 rounded-lg text-sm font-semibold inline-flex items-center gap-2">
                                    <span class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></span>
                                    Новый заказ
                                </span>
                                @break
                            @case('pending_payment')
                                <span class="px-4 py-2 bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 rounded-lg text-sm font-semibold inline-flex items-center gap-2">
                                    <span class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></span>
                                    Ожидание оплаты
                                </span>
                                @break
                            @case('processing')
                                <span class="px-4 py-2 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-lg text-sm font-semibold inline-flex items-center gap-2">
                                    <span class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span>
                                    В обработке
                                </span>
                                @break
                            @case('preparing')
                                <span class="px-4 py-2 bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400 rounded-lg text-sm font-semibold inline-flex items-center gap-2">
                                    <span class="w-2 h-2 bg-violet-500 rounded-full animate-pulse"></span>
                                    Готовим
                                </span>
                                @break
                            @case('ready')
                                <span class="px-4 py-2 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-lg text-sm font-semibold inline-flex items-center gap-2">
                                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                                    Готов к выдаче
                                </span>
                                @break
                            @case('completed')
                                <span class="px-4 py-2 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-lg text-sm font-semibold inline-flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Завершен
                                </span>
                                @break
                            @case('canceled')
                                <span class="px-4 py-2 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded-lg text-sm font-semibold inline-flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Отменен
                                </span>
                                @break
                            @default
                                <span class="px-4 py-2 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-lg text-sm font-semibold">
                                    {{ $order->status }}
                                </span>
                        @endswitch

                        <!-- Back Button -->
                        <a href="{{ route('admin.sales.orders.index') }}"
                           class="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg transition-all duration-200 hover:shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Назад
                        </a>
                    </div>
                </div>
            </div>

            <!-- Progress Timeline - Larkon Style -->
            @if($order->status !== 'canceled')
            <div class="px-8 py-8 bg-gradient-to-r from-slate-50 to-gray-50 dark:from-gray-800 dark:to-gray-850 border-t border-gray-100 dark:border-gray-700">
                @php
                    $progressWidth = $currentStatusIndex >= 0 ? ($currentStatusIndex / (count($statusOrder) - 1)) * 100 : 0;
                @endphp

                <style>
                    @keyframes progressStripes {
                        0% { background-position: 0 0; }
                        100% { background-position: 40px 0; }
                    }
                    @keyframes scaleIn {
                        0% { transform: scale(0); opacity: 0; }
                        100% { transform: scale(1); opacity: 1; }
                    }
                    @keyframes bounceIn {
                        0% { transform: scale(0.3); opacity: 0; }
                        50% { transform: scale(1.05); }
                        70% { transform: scale(0.9); }
                        100% { transform: scale(1); opacity: 1; }
                    }
                    .progress-stripes {
                        background-image: linear-gradient(
                            45deg,
                            rgba(255,255,255,0.15) 25%,
                            transparent 25%,
                            transparent 50%,
                            rgba(255,255,255,0.15) 50%,
                            rgba(255,255,255,0.15) 75%,
                            transparent 75%,
                            transparent
                        );
                        background-size: 40px 40px;
                        animation: progressStripes 1s linear infinite;
                    }
                    .step-bounce { animation: bounceIn 0.6s ease-out forwards; }
                    .step-scale { animation: scaleIn 0.4s ease-out forwards; }
                </style>

                <!-- Larkon Style Progress Bar -->
                <div class="relative">
                    <!-- Background Track -->
                    <div class="absolute top-6 left-0 right-0 h-2 bg-gray-200 dark:bg-gray-700 rounded-full mx-6"></div>

                    <!-- Active Progress with Stripes Animation -->
                    <div class="absolute top-6 left-0 h-2 bg-violet-500 rounded-full mx-6 progress-stripes transition-all duration-1000 ease-out"
                         style="width: calc({{ $progressWidth }}% - 48px)"></div>

                    <!-- Steps -->
                    <div class="relative flex justify-between">
                        @foreach($statusOrder as $index => $status)
                            @php
                                $isCompleted = $currentStatusIndex > $index;
                                $isCurrent = $currentStatusIndex === $index;
                                $isPending = $currentStatusIndex < $index;
                                $delay = $index * 150;
                            @endphp

                            <div class="flex flex-col items-center" style="animation-delay: {{ $delay }}ms">
                                <!-- Circle with Icon -->
                                <div class="relative z-10 {{ $isCompleted || $isCurrent ? 'step-bounce' : '' }}" style="animation-delay: {{ $delay }}ms">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center transition-all duration-500 shadow-soft
                                        {{ $isCompleted ? 'bg-violet-500 text-white' : '' }}
                                        {{ $isCurrent ? 'bg-violet-500 text-white ring-4 ring-violet-200 dark:ring-violet-900' : '' }}
                                        {{ $isPending ? 'bg-white dark:bg-gray-800 text-gray-400 dark:text-gray-500 border-2 border-gray-200 dark:border-gray-600' : '' }}">
                                        @if($isCompleted)
                                            <svg class="w-6 h-6 step-scale" style="animation-delay: {{ $delay + 200 }}ms" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        @elseif($isCurrent)
                                            <div class="relative">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"></path>
                                                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" fill="none"/>
                                                </svg>
                                            </div>
                                        @else
                                            <span class="text-sm font-semibold">{{ $index + 1 }}</span>
                                        @endif
                                    </div>

                                    @if($isCurrent)
                                    <!-- Animated ring for current step -->
                                    <div class="absolute -inset-1 rounded-full border-2 border-violet-400 animate-ping opacity-75"></div>
                                    @endif
                                </div>

                                <!-- Label -->
                                <div class="mt-4 text-center">
                                    <span class="block text-sm font-medium transition-colors duration-300
                                        {{ $isCompleted ? 'text-violet-600 dark:text-violet-400' : '' }}
                                        {{ $isCurrent ? 'text-violet-600 dark:text-violet-400 font-semibold' : '' }}
                                        {{ $isPending ? 'text-gray-400 dark:text-gray-500' : '' }}">
                                        {{ $statusLabels[$status] }}
                                    </span>
                                    @if($isCurrent)
                                    <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 bg-violet-100 dark:bg-violet-900/50 text-violet-600 dark:text-violet-400 text-xs font-medium rounded-full">
                                        <span class="w-1.5 h-1.5 bg-violet-500 rounded-full animate-pulse"></span>
                                        В процессе
                                    </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @else
            <!-- Canceled Order Banner -->
            <div class="m-6 flex items-center gap-4 p-5 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800/50">
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900/50 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-red-700 dark:text-red-400">Заказ отменен</p>
                    <p class="text-sm text-red-600/80 dark:text-red-400/70">Этот заказ был отменен и не будет обработан</p>
                </div>
            </div>
            @endif
        </div>

        {!! view_render_event('bagisto.admin.sales.order.title.after', ['order' => $order]) !!}

        <!-- Main Content Grid -->
        <div class="flex gap-5 max-xl:flex-wrap">
            <!-- Left Column - Order Items & Summary -->
            <div class="flex-1 flex flex-col gap-5 min-w-0 draggable-column" data-column="left">
                {!! view_render_event('bagisto.admin.sales.order.left_component.before', ['order' => $order]) !!}

                <!-- Order Items Card -->
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-soft border border-gray-100 dark:border-gray-800 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-violet-100 dark:bg-violet-900/30 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Товары в заказе</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ count($order->items) }} {{ trans_choice('позиция|позиции|позиций', count($order->items)) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Товар</th>
                                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Цена</th>
                                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Кол-во</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Итого</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($order->items as $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-4">
                                                @php
                                                    $productImage = null;
                                                    if ($item->product && $item->product->images->count()) {
                                                        $productImage = $item->product->images->first()->url;
                                                    } elseif ($item->product && $item->product->base_image) {
                                                        $productImage = $item->product->base_image->url ?? null;
                                                    }
                                                @endphp
                                                <div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 flex-shrink-0 border border-gray-200 dark:border-gray-700">
                                                    @if ($productImage)
                                                        <img src="{{ $productImage }}"
                                                             alt="{{ $item->name }}"
                                                             class="w-full h-full object-cover">
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center">
                                                            <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="font-medium text-gray-800 dark:text-white truncate">{{ $item->name }}</p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">SKU: {{ $item->sku }}</p>
                                                    @if (isset($item->additional['attributes']))
                                                        <div class="mt-1 flex flex-wrap gap-1">
                                                            @foreach ($item->additional['attributes'] as $attribute)
                                                                <span class="inline-flex px-2 py-0.5 bg-gray-100 dark:bg-gray-800 text-xs text-gray-600 dark:text-gray-400 rounded">
                                                                    {{ $attribute['attribute_name'] }}: {{ $attribute['option_label'] }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 text-center">
                                            <span class="font-medium text-gray-800 dark:text-white">{{ core()->formatBasePrice($item->base_price) }}</span>
                                        </td>
                                        <td class="px-5 py-4 text-center">
                                            <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-lg font-medium text-gray-700 dark:text-gray-300">
                                                {{ intval($item->qty_ordered) }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <span class="font-semibold text-gray-900 dark:text-white">{{ core()->formatBasePrice($item->base_total) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Order Summary -->
                    <div class="p-5 bg-gray-50 dark:bg-gray-800/30 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex justify-end">
                            <div class="w-full max-w-sm space-y-3">
                                <div class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                                    <span>Подытог</span>
                                    <span class="font-medium text-gray-800 dark:text-white">{{ core()->formatBasePrice($order->base_sub_total) }}</span>
                                </div>

                                @if ($order->base_shipping_amount > 0)
                                <div class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                                    <span>Доставка</span>
                                    <span class="font-medium text-gray-800 dark:text-white">{{ core()->formatBasePrice($order->base_shipping_amount) }}</span>
                                </div>
                                @endif

                                @if ($order->base_tax_amount > 0)
                                <div class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                                    <span>Налог</span>
                                    <span class="font-medium text-gray-800 dark:text-white">{{ core()->formatBasePrice($order->base_tax_amount) }}</span>
                                </div>
                                @endif

                                @if ($order->base_discount_amount > 0)
                                <div class="flex items-center justify-between text-green-600 dark:text-green-400">
                                    <span>Скидка</span>
                                    <span class="font-medium">-{{ core()->formatBasePrice($order->base_discount_amount) }}</span>
                                </div>
                                @endif

                                <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                    <span class="text-lg font-semibold text-gray-800 dark:text-white">Итого</span>
                                    <span class="text-xl font-bold text-violet-600 dark:text-violet-400">{{ core()->formatBasePrice($order->base_grand_total) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comments Card -->
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-card overflow-hidden">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Комментарии к заказу</h2>
                        </div>
                    </div>

                    <div class="p-5">
                        <!-- Add Comment Form -->
                        <x-admin::form action="{{ route('admin.sales.orders.comment', $order->id) }}" method="post">
                            <div class="mb-4">
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.control
                                        type="textarea"
                                        id="comment"
                                        name="comment"
                                        rules="required"
                                        rows="3"
                                        class="!rounded-xl !border-gray-200 dark:!border-gray-700 focus:!border-violet-500 focus:!ring-violet-500/20"
                                        placeholder="Добавить комментарий к заказу..."
                                    />
                                    <x-admin::form.control-group.error control-name="comment" />
                                </x-admin::form.control-group>
                            </div>

                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="customer_notified" value="1"
                                           class="w-4 h-4 text-violet-600 border-gray-300 rounded focus:ring-violet-500">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Уведомить покупателя</span>
                                </label>

                                <button type="submit"
                                        class="px-5 py-2.5 bg-gradient-to-r from-violet-500 to-violet-600 hover:from-violet-600 hover:to-violet-700 text-white font-medium rounded-xl shadow-lg shadow-violet-500/30 transition-all duration-200 hover:shadow-xl hover:shadow-violet-500/40 hover:-translate-y-0.5">
                                    Добавить
                                </button>
                            </div>
                        </x-admin::form>

                        <!-- Comments List -->
                        @if(count($order->comments))
                        <div class="mt-6 space-y-4">
                            @foreach($order->comments as $comment)
                            <div class="flex gap-3 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl">
                                <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-medium text-gray-800 dark:text-white text-sm">Администратор</span>
                                        <span class="text-xs text-gray-400 dark:text-gray-500">{{ core()->formatDate($comment->created_at, 'd M Y, H:i') }}</span>
                                        @if($comment->customer_notified)
                                        <span class="inline-flex items-center gap-1 text-xs text-green-600 dark:text-green-400">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Уведомлен
                                        </span>
                                        @endif
                                    </div>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $comment->comment }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Order Timeline Card -->
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-soft border border-gray-100 dark:border-gray-800 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Order Timeline</h2>
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="relative">
                            <!-- Timeline Line -->
                            <div class="absolute left-5 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>

                            <!-- Timeline Items -->
                            <div class="space-y-6">
                                @php
                                    // Build timeline events
                                    $timelineEvents = [];

                                    // Order created
                                    $timelineEvents[] = [
                                        'icon' => 'check',
                                        'color' => 'green',
                                        'title' => 'Заказ создан',
                                        'description' => 'Заказ #' . $order->increment_id . ' оформлен',
                                        'date' => $order->created_at,
                                        'completed' => true
                                    ];

                                    // Payment
                                    if($order->payment) {
                                        $paymentTitle = core()->getConfigData('sales.payment_methods.' . $order->payment->method . '.title') ?? $order->payment->method;
                                        $timelineEvents[] = [
                                            'icon' => 'payment',
                                            'color' => 'green',
                                            'title' => 'Оплата',
                                            'description' => $paymentTitle,
                                            'badge' => $order->status !== 'pending_payment' ? 'Оплачено' : 'Ожидание',
                                            'badge_color' => $order->status !== 'pending_payment' ? 'green' : 'orange',
                                            'date' => $order->created_at,
                                            'completed' => $order->status !== 'pending_payment'
                                        ];
                                    }

                                    // Invoice created
                                    if(count($order->invoices)) {
                                        $invoice = $order->invoices->first();
                                        $timelineEvents[] = [
                                            'icon' => 'invoice',
                                            'color' => 'green',
                                            'title' => 'Счёт создан',
                                            'description' => 'Счёт #' . $invoice->id,
                                            'action' => ['label' => 'Скачать счёт', 'url' => route('admin.sales.invoices.print', $invoice->id)],
                                            'date' => $invoice->created_at,
                                            'completed' => true
                                        ];
                                    }

                                    // Processing
                                    $processingStates = ['processing', 'preparing', 'ready', 'completed'];
                                    $isProcessing = in_array($order->status, $processingStates);
                                    $timelineEvents[] = [
                                        'icon' => $isProcessing ? 'check' : 'loading',
                                        'color' => $isProcessing ? 'green' : 'orange',
                                        'title' => $isProcessing ? 'В обработке' : 'Новый',
                                        'description' => $isProcessing ? 'Заказ принят в работу' : 'Ожидает обработки',
                                        'date' => $isProcessing ? $order->updated_at : null,
                                        'completed' => $isProcessing
                                    ];

                                    // Shipment
                                    if(count($order->shipments)) {
                                        $shipment = $order->shipments->first();
                                        $timelineEvents[] = [
                                            'icon' => 'check',
                                            'color' => 'green',
                                            'title' => 'Отправлен',
                                            'description' => 'Заказ отправлен клиенту',
                                            'date' => $shipment->created_at,
                                            'completed' => true
                                        ];
                                    }

                                    // Completed
                                    if($order->status === 'completed') {
                                        $timelineEvents[] = [
                                            'icon' => 'check',
                                            'color' => 'green',
                                            'title' => 'Завершён',
                                            'description' => 'Заказ успешно выполнен',
                                            'date' => $order->updated_at,
                                            'completed' => true
                                        ];
                                    }

                                    // Sort by date descending
                                    usort($timelineEvents, function($a, $b) {
                                        $dateA = $a['date'] ?? now();
                                        $dateB = $b['date'] ?? now();
                                        return $dateB <=> $dateA;
                                    });
                                @endphp

                                @foreach($timelineEvents as $event)
                                <div class="relative flex gap-4">
                                    <!-- Icon -->
                                    <div class="relative z-10 flex-shrink-0">
                                        @if($event['icon'] === 'loading')
                                        <div class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-orange-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                        @elseif($event['icon'] === 'payment')
                                        <div class="w-10 h-10 rounded-full bg-{{ $event['color'] }}-100 dark:bg-{{ $event['color'] }}-900/30 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-{{ $event['color'] }}-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                            </svg>
                                        </div>
                                        @elseif($event['icon'] === 'invoice')
                                        <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        @else
                                        <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                        @endif
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1 pb-2">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <h4 class="font-semibold text-gray-900 dark:text-white">{{ $event['title'] }}</h4>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $event['description'] }}</p>

                                                @if(isset($event['badge']))
                                                <span class="inline-flex items-center mt-2 px-2.5 py-1 rounded-md text-xs font-medium
                                                    {{ $event['badge_color'] === 'green' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' }}">
                                                    {{ $event['badge'] }}
                                                </span>
                                                @endif

                                                @if(isset($event['action']))
                                                <a href="{{ $event['action']['url'] }}"
                                                   class="inline-flex items-center mt-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors">
                                                    {{ $event['action']['label'] }}
                                                </a>
                                                @endif
                                            </div>

                                            @if($event['date'])
                                            <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap ml-4">
                                                {{ core()->formatDate($event['date'], 'd M Y, H:i') }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Location Map Card -->
                @if ($order->shipping_address)
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-soft border border-gray-100 dark:border-gray-800 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Местоположение клиента</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $order->shipping_address->city ?? 'Адрес доставки' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Map Container -->
                    <div class="relative">
                        @php
                            $address = urlencode(
                                ($order->shipping_address->address ?? '') . ', ' .
                                ($order->shipping_address->city ?? '') . ', ' .
                                ($order->shipping_address->state ?? '') . ' ' .
                                ($order->shipping_address->postcode ?? '')
                            );
                        @endphp
                        <div class="h-64 bg-gray-100 dark:bg-gray-800">
                            <iframe
                                src="https://yandex.ru/map-widget/v1/?text={{ $address }}&z=15&l=map"
                                class="w-full h-full border-0"
                                loading="lazy"
                                allowfullscreen>
                            </iframe>
                        </div>

                        <!-- Address Overlay -->
                        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/70 to-transparent">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <div class="text-white">
                                    <p class="font-medium text-sm">{{ $order->shipping_address->address }}</p>
                                    <p class="text-xs text-white/70">
                                        {{ $order->shipping_address->city }}@if($order->shipping_address->state), {{ $order->shipping_address->state }}@endif
                                        @if($order->shipping_address->postcode) {{ $order->shipping_address->postcode }}@endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="p-4 border-t border-gray-100 dark:border-gray-800 flex gap-2">
                        <a href="https://yandex.ru/maps/?text={{ $address }}"
                           target="_blank"
                           class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-violet-500 hover:bg-violet-600 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                            </svg>
                            Открыть в картах
                        </a>
                        <button onclick="navigator.clipboard.writeText('{{ $order->shipping_address->address }}, {{ $order->shipping_address->city }}')"
                                class="flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Копировать
                        </button>
                    </div>
                </div>
                @endif

                {!! view_render_event('bagisto.admin.sales.order.left_component.after', ['order' => $order]) !!}
            </div>

            <!-- Right Column - Sidebar -->
            <div class="w-[380px] flex flex-col gap-4 flex-shrink-0 draggable-column" data-column="right">
                {!! view_render_event('bagisto.admin.sales.order.right_component.before', ['order' => $order]) !!}

                <!-- Quick Actions Card -->
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-card overflow-hidden">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Быстрые действия</h2>
                        </div>
                    </div>

                    <div class="p-5">
                        {!! view_render_event('bagisto.admin.sales.order.status_label.before', ['order' => $order]) !!}

                        <!-- Change Status -->
                        <x-admin::form :action="route('admin.sales.orders.update_status', $order->id)" method="POST">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Изменить статус</label>
                                <select name="status"
                                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-700 dark:text-gray-300 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-colors"
                                        onchange="this.form.submit()">
                                    <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Новый</option>
                                    <option value="pending_payment" {{ $order->status == 'pending_payment' ? 'selected' : '' }}>Ожидание оплаты</option>
                                    <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>В обработке</option>
                                    <option value="preparing" {{ $order->status == 'preparing' ? 'selected' : '' }}>Готовим</option>
                                    <option value="ready" {{ $order->status == 'ready' ? 'selected' : '' }}>Готов</option>
                                    <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Завершен</option>
                                    <option value="canceled" {{ $order->status == 'canceled' ? 'selected' : '' }}>Отменен</option>
                                </select>
                            </div>
                        </x-admin::form>

                        <!-- Print Invoice Button -->
                        @if(count($order->invoices))
                        <a href="{{ route('admin.sales.invoices.print', $order->invoices->first()->id) }}"
                           class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Печать счета
                        </a>
                        @endif

                        {!! view_render_event('bagisto.admin.sales.order.status_label.after', ['order' => $order]) !!}
                    </div>
                </div>

                <!-- Customer & Address Grid -->
                <div class="grid grid-cols-1 gap-4">
                    <!-- Customer Info Card -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-card overflow-hidden">
                        <div class="p-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <h2 class="font-semibold text-gray-800 dark:text-white">Покупатель</h2>
                            </div>
                        </div>

                        <div class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-lg flex-shrink-0">
                                    {{ strtoupper(substr($order->customer_first_name ?? 'G', 0, 1)) }}{{ strtoupper(substr($order->customer_last_name ?? '', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-800 dark:text-white text-sm">{{ $order->customer_full_name }}</p>
                                    @if($order->customer_email)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $order->customer_email }}</p>
                                    @endif
                                    @if($order->shipping_address && $order->shipping_address->phone)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $order->shipping_address->phone }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Address Card -->
                    @if ($order->shipping_address)
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-card overflow-hidden">
                        <div class="p-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <h2 class="font-semibold text-gray-800 dark:text-white">Адрес доставки</h2>
                            </div>
                        </div>

                        <div class="p-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                {{ $order->shipping_address->address }}
                                @if($order->shipping_address->city), {{ $order->shipping_address->city }}@endif
                            </p>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Payment & Info Grid -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Payment Info Card -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-card overflow-hidden">
                        <div class="p-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                </div>
                                <h2 class="font-semibold text-gray-800 dark:text-white text-sm">Оплата</h2>
                            </div>
                        </div>

                        <div class="p-4">
                            <p class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $order->payment ? (core()->getConfigData('sales.payment_methods.' . $order->payment->method . '.title') ?? $order->payment->method) : 'Не указан' }}
                            </p>
                            @if ($order->shipping_address)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Доставка</p>
                            <p class="text-sm font-semibold text-violet-600 dark:text-violet-400">
                                {{ core()->formatBasePrice($order->base_shipping_amount) }}
                            </p>
                            @endif
                        </div>
                    </div>

                    <!-- Order Info Card -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-card overflow-hidden">
                        <div class="p-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h2 class="font-semibold text-gray-800 dark:text-white text-sm">Инфо</h2>
                            </div>
                        </div>

                        <div class="p-4 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Канал</span>
                                <span class="text-xs font-medium text-gray-800 dark:text-white">{{ $order->channel_name }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Создан</span>
                                <span class="text-xs font-medium text-gray-800 dark:text-white">{{ core()->formatDate($order->created_at, 'd.m.Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {!! view_render_event('bagisto.admin.sales.order.right_component.after', ['order' => $order]) !!}
            </div>
        </div>
    </div>

    <!-- Drag & Drop JavaScript -->
    <script>
    // Global variables
    var orderDragState = {
        enabled: false,
        draggedElement: null,
        placeholder: null,
        storageKey: 'order-view-block-order'
    };

    // Global toggle function (called by onclick)
    function toggleOrderDragMode() {
        console.log('toggleOrderDragMode called');

        var state = orderDragState;
        var toggleBtn = document.getElementById('drag-mode-toggle');
        var columns = document.querySelectorAll('.draggable-column');

        if (!toggleBtn) {
            console.error('Toggle button not found');
            return;
        }

        state.enabled = !state.enabled;
        console.log('Drag mode:', state.enabled ? 'ON' : 'OFF');

        // Get all blocks
        var blocks = [];
        columns.forEach(function(column) {
            column.querySelectorAll(':scope > .bg-white, :scope > .grid').forEach(function(block) {
                blocks.push(block);
            });
        });

        if (state.enabled) {
            // Enable drag mode
            toggleBtn.className = 'px-4 py-2 bg-violet-500 hover:bg-violet-600 text-white rounded-lg text-sm font-medium inline-flex items-center gap-2 transition-colors';
            toggleBtn.querySelector('.drag-mode-text').textContent = 'Готово';

            blocks.forEach(function(block) {
                block.setAttribute('draggable', 'true');
                block.classList.add('cursor-move');
                block.style.transition = 'transform 0.2s, box-shadow 0.2s';

                var header = block.querySelector('[class*="border-b"]');
                if (header && !header.querySelector('.drag-indicator')) {
                    var indicator = document.createElement('div');
                    indicator.className = 'drag-indicator absolute top-2 right-2 w-6 h-6 bg-violet-100 dark:bg-violet-900/30 rounded flex items-center justify-center z-10';
                    indicator.innerHTML = '<svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>';
                    header.style.position = 'relative';
                    header.appendChild(indicator);
                }
            });
        } else {
            // Disable drag mode and save
            toggleBtn.className = 'px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium inline-flex items-center gap-2 transition-colors';
            toggleBtn.querySelector('.drag-mode-text').textContent = 'Настроить';

            blocks.forEach(function(block) {
                block.removeAttribute('draggable');
                block.classList.remove('cursor-move');
                block.style.transition = '';

                var indicator = block.querySelector('.drag-indicator');
                if (indicator) indicator.remove();
            });

            // Save block order
            saveOrderBlockOrder();
        }
    }

    function saveOrderBlockOrder() {
        var columns = document.querySelectorAll('.draggable-column');
        var order = {};

        columns.forEach(function(column) {
            var columnId = column.dataset.column;
            order[columnId] = [];
            column.querySelectorAll(':scope > .bg-white, :scope > .grid').forEach(function(block) {
                if (block.dataset.blockId) {
                    order[columnId].push(block.dataset.blockId);
                }
            });
        });

        localStorage.setItem(orderDragState.storageKey, JSON.stringify(order));
        console.log('Order block order saved:', order);
    }

    function loadOrderBlockOrder() {
        var savedOrder = localStorage.getItem(orderDragState.storageKey);
        if (!savedOrder) {
            console.log('No saved order found');
            return;
        }

        try {
            var order = JSON.parse(savedOrder);
            console.log('Loading saved order:', order);

            var columns = document.querySelectorAll('.draggable-column');

            // Collect ALL blocks from ALL columns first
            var allBlocks = {};
            columns.forEach(function(column) {
                column.querySelectorAll(':scope > .bg-white, :scope > .grid').forEach(function(block) {
                    if (block.dataset.blockId) {
                        allBlocks[block.dataset.blockId] = block;
                    }
                });
            });

            console.log('Found blocks:', Object.keys(allBlocks));

            // Now place blocks in correct columns according to saved order
            columns.forEach(function(column) {
                var columnId = column.dataset.column;
                if (!order[columnId]) return;

                order[columnId].forEach(function(blockId) {
                    if (allBlocks[blockId]) {
                        // Move block to this column
                        column.appendChild(allBlocks[blockId]);
                    }
                });
            });

            console.log('Block order restored');
        } catch (e) {
            console.error('Error loading block order:', e);
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        var columns = document.querySelectorAll('.draggable-column');
        var blockCounter = 0;

        // Assign IDs to blocks
        columns.forEach(function(column) {
            column.querySelectorAll(':scope > .bg-white, :scope > .grid').forEach(function(block) {
                if (!block.dataset.blockId) {
                    var heading = block.querySelector('h2, h3');
                    if (heading) {
                        block.dataset.blockId = heading.textContent.trim().toLowerCase().replace(/[^a-zа-яё0-9]/gi, '-').substring(0, 50);
                    } else {
                        block.dataset.blockId = 'block-' + blockCounter++;
                    }
                }
            });
        });

        // Drag event handlers
        document.addEventListener('dragstart', function(e) {
            if (!orderDragState.enabled) return;

            orderDragState.draggedElement = e.target.closest('.bg-white, .grid');
            if (!orderDragState.draggedElement || !orderDragState.draggedElement.closest('.draggable-column')) return;

            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', orderDragState.draggedElement.dataset.blockId || '');

            orderDragState.placeholder = document.createElement('div');
            orderDragState.placeholder.className = 'drag-placeholder border-2 border-dashed border-violet-400 bg-violet-50 dark:bg-violet-900/20 rounded-xl';
            orderDragState.placeholder.style.height = orderDragState.draggedElement.offsetHeight + 'px';
            orderDragState.placeholder.style.marginBottom = '20px';

            setTimeout(function() {
                if (orderDragState.draggedElement) {
                    orderDragState.draggedElement.style.opacity = '0.5';
                    orderDragState.draggedElement.style.transform = 'scale(1.02)';
                }
            }, 0);
        });

        document.addEventListener('dragend', function(e) {
            if (!orderDragState.enabled || !orderDragState.draggedElement) return;

            orderDragState.draggedElement.style.opacity = '';
            orderDragState.draggedElement.style.transform = '';

            if (orderDragState.placeholder && orderDragState.placeholder.parentNode) {
                orderDragState.placeholder.parentNode.removeChild(orderDragState.placeholder);
            }

            orderDragState.draggedElement = null;
            orderDragState.placeholder = null;
        });

        document.addEventListener('dragover', function(e) {
            if (!orderDragState.enabled || !orderDragState.draggedElement) return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            var column = e.target.closest('.draggable-column');
            if (!column) return;

            var draggableElements = Array.from(column.querySelectorAll(':scope > .bg-white:not(.dragging), :scope > .grid:not(.dragging)'));
            var afterElement = draggableElements.reduce(function(closest, child) {
                var box = child.getBoundingClientRect();
                var offset = e.clientY - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                }
                return closest;
            }, { offset: Number.NEGATIVE_INFINITY }).element;

            if (orderDragState.placeholder && orderDragState.placeholder.parentNode) {
                orderDragState.placeholder.parentNode.removeChild(orderDragState.placeholder);
            }

            if (afterElement == null) {
                column.appendChild(orderDragState.placeholder);
            } else {
                column.insertBefore(orderDragState.placeholder, afterElement);
            }
        });

        document.addEventListener('drop', function(e) {
            if (!orderDragState.enabled || !orderDragState.draggedElement) return;
            e.preventDefault();

            var column = e.target.closest('.draggable-column');
            if (!column) return;

            if (orderDragState.placeholder && orderDragState.placeholder.parentNode) {
                orderDragState.placeholder.parentNode.insertBefore(orderDragState.draggedElement, orderDragState.placeholder);
                orderDragState.placeholder.parentNode.removeChild(orderDragState.placeholder);
            }

            orderDragState.draggedElement.style.opacity = '';
            orderDragState.draggedElement.style.transform = '';

            saveOrderBlockOrder();
        });

        // Load saved order
        loadOrderBlockOrder();

        console.log('Order Drag & Drop initialized (global settings)');
    });
    </script>
</x-admin::layouts>
