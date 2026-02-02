<div class="flex items-center -space-x-2">
    @php
        $restCount = max($order->items->count() - 3, 0);
    @endphp

    @foreach ($order->items->take(3) as $index => $item)
        <div class="relative group" style="z-index: {{ 10 - $index }}">
            <div class="relative h-12 w-12 rounded-xl overflow-hidden ring-2 ring-white dark:ring-gray-900 shadow-md transition-transform duration-200 hover:scale-110 hover:z-20">
                @php
                    $imageUrl = null;
                    
                    // Сначала проверяем category_image
                    if ($item->product?->category_image) {
                        $imageUrl = Storage::url($item->product->category_image);
                    } 
                    // Если нет category_image, используем base_image_url
                    elseif ($item->product?->images->count() > 0) {
                        $imageUrl = $item->product->base_image_url;
                    }
                @endphp

                @if ($imageUrl)
                    <img 
                        class="h-full w-full object-cover" 
                        src="{{ $imageUrl }}"
                    >
                @else
                    <div class="h-full w-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                @endif
                
                <!-- Quantity Badge -->
                @if ($item->qty_ordered > 1)
                    <span class="absolute -bottom-1 -right-1 min-w-[18px] h-[18px] flex items-center justify-center rounded-full bg-violet-500 text-[10px] font-bold text-white ring-2 ring-white dark:ring-gray-900 px-1">
                        {{ $item->qty_ordered }}
                    </span>
                @endif
            </div>
        </div>
    @endforeach

    @if ($restCount >= 1)
        <div class="relative" style="z-index: 1">
            <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-violet-100 to-violet-200 dark:from-violet-900/50 dark:to-violet-800/50 ring-2 ring-white dark:ring-gray-900 flex items-center justify-center shadow-md">
                <span class="text-xs font-bold text-violet-600 dark:text-violet-400">+{{ $restCount }}</span>
            </div>
        </div>
    @endif
</div>