<x-admin::layouts>
    <x-slot:title>
        Статистика: {{ $campaign->name }}
    </x-slot>

    <div class="flex items-center justify-between gap-4 mb-5 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.marketing.push_notifications.campaigns.index') }}"
                style="display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:10px;background:#f3f4f6;transition:all 0.15s;color:#6b7280;"
                onmouseenter="this.style.background='#e5e7eb'" onmouseleave="this.style.background='#f3f4f6'"
            >
                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background:linear-gradient(135deg,#6366f1,#4f46e5);box-shadow:0 4px 15px rgba(99,102,241,0.3);min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">{{ $campaign->name }}</p>
                <p class="text-xs text-gray-400">Статистика рассылки · {{ $campaign->created_at->format('d.m.Y H:i') }}</p>
            </div>
        </div>

        @if(in_array($campaign->status, ['draft', 'failed']))
            <form method="POST" action="{{ route('admin.marketing.push_notifications.campaigns.send', $campaign->id) }}"
                onsubmit="return confirm('Запустить рассылку?')">
                @csrf
                <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:9px 18px;background:linear-gradient(135deg,#10b981,#059669);color:white;border:none;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;">
                    <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    Отправить сейчас
                </button>
            </form>
        @endif
    </div>

    {{-- Stats cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:20px;">

        {{-- Total recipients --}}
        <div style="background:white;border-radius:14px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <p style="font-size:12px;font-weight:600;color:#6b7280;">Аудитория</p>
                <div style="width:32px;height:32px;border-radius:8px;background:#ede9fe;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:16px;height:16px;color:#7c3aed;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
            </div>
            @if(in_array($campaign->status, ['draft', 'failed']))
                <p style="font-size:28px;font-weight:800;color:#111827;">{{ number_format($estimatedAudience ?? $campaign->total_recipients) }}</p>
                <p style="font-size:11px;color:#9ca3af;margin-top:2px;">получателей (оценка)</p>
            @else
                <p style="font-size:28px;font-weight:800;color:#111827;">{{ number_format($campaign->total_recipients) }}</p>
                <p style="font-size:11px;color:#9ca3af;margin-top:2px;">получателей</p>
            @endif
        </div>

        {{-- Delivered --}}
        <div style="background:white;border-radius:14px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <p style="font-size:12px;font-weight:600;color:#6b7280;">Доставлено</p>
                <div style="width:32px;height:32px;border-radius:8px;background:#dcfce7;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:16px;height:16px;color:#16a34a;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p style="font-size:28px;font-weight:800;color:#111827;">{{ number_format($campaign->delivered_count) }}</p>
            <p style="font-size:11px;color:#9ca3af;margin-top:2px;">
                {{ $campaign->delivery_rate }}% от аудитории
            </p>
        </div>

        {{-- Opened --}}
        <div style="background:white;border-radius:14px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <p style="font-size:12px;font-weight:600;color:#6b7280;">Открыто</p>
                <div style="width:32px;height:32px;border-radius:8px;background:#fef3c7;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:16px;height:16px;color:#d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </div>
            </div>
            <p style="font-size:28px;font-weight:800;color:#111827;">{{ number_format($campaign->opened_count) }}</p>
            <p style="font-size:11px;color:#9ca3af;margin-top:2px;">
                открытий
            </p>
        </div>

        {{-- Conversion --}}
        <div style="background:white;border-radius:14px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <p style="font-size:12px;font-weight:600;color:#6b7280;">Конверсия</p>
                <div style="width:32px;height:32px;border-radius:8px;background:#dbeafe;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:16px;height:16px;color:#2563eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <p style="font-size:28px;font-weight:800;color:#111827;">{{ $campaign->conversion_rate }}%</p>
            <p style="font-size:11px;color:#9ca3af;margin-top:2px;">
                открыто / аудитория
            </p>
        </div>

        {{-- Status --}}
        <div style="background:white;border-radius:14px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <p style="font-size:12px;font-weight:600;color:#6b7280;">Статус</p>
                <div style="width:32px;height:32px;border-radius:8px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:16px;height:16px;color:#6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p style="font-size:20px;font-weight:800;color:#111827;">{{ $campaign->status_label }}</p>
            @if($campaign->scheduled_at)
                <p style="font-size:11px;color:#9ca3af;margin-top:2px;">{{ $campaign->scheduled_at->format('d.m.Y H:i') }}</p>
            @endif
        </div>
    </div>

    {{-- Progress bar --}}
    @if($campaign->total_recipients > 0 && in_array($campaign->status, ['sending', 'sent']))
        @php $progress = round($campaign->sent_count / $campaign->total_recipients * 100) @endphp
        <div style="background:white;border-radius:14px;padding:16px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.06);margin-bottom:20px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <span style="font-size:13px;font-weight:600;color:#374151;">Прогресс отправки</span>
                <span style="font-size:13px;font-weight:700;color:#6366f1;">{{ $progress }}%</span>
            </div>
            <div style="width:100%;background:#f3f4f6;border-radius:6px;overflow:hidden;">
                <div style="height:8px;background:linear-gradient(90deg,#6366f1,#8b5cf6);border-radius:6px;width:{{ $progress }}%;transition:width 0.3s;"></div>
            </div>
            <p style="font-size:11px;color:#9ca3af;margin-top:6px;">{{ number_format($campaign->sent_count) }} из {{ number_format($campaign->total_recipients) }} обработано</p>
        </div>
    @endif

    {{-- Segment filters summary --}}
    @if(!empty($campaign->segment_filters))
        <div style="background:white;border-radius:14px;padding:16px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.06);margin-bottom:20px;">
            <p style="font-size:13px;font-weight:700;color:#111827;margin-bottom:10px;">Параметры сегментации</p>
            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                @foreach($campaign->segment_filters as $key => $val)
                    @if(!empty($val))
                        @php
                            $labels = [
                                'gender' => ['Male' => 'Мужской', 'Female' => 'Женский', 'Other' => 'Другой'],
                                'has_orders' => ['1' => 'Есть заказы', '0' => 'Нет заказов'],
                            ];
                            $keyLabels = [
                                'customer_group_ids' => 'Группы',
                                'gender' => 'Пол',
                                'has_orders' => 'Заказы',
                                'registered_from' => 'Рег. с',
                                'registered_to' => 'Рег. по',
                                'last_order_from' => 'Последний заказ с',
                                'last_order_to' => 'Последний заказ по',
                            ];
                            $displayVal = is_array($val) ? implode(', ', $val) : ($labels[$key][$val] ?? $val);
                        @endphp
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;background:#ede9fe;color:#7c3aed;border-radius:6px;font-size:12px;font-weight:600;">
                            {{ $keyLabels[$key] ?? $key }}: {{ $displayVal }}
                        </span>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    {{-- Status breakdown --}}
    @if(!empty($statusBreakdown))
        <div style="background:white;border-radius:14px;padding:16px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.06);margin-bottom:20px;">
            <p style="font-size:13px;font-weight:700;color:#111827;margin-bottom:12px;">Разбивка по статусам</p>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:8px;">
                @php
                    $statusColors = [
                        'pending'  => ['bg' => '#f3f4f6', 'color' => '#6b7280', 'label' => 'Ожидает'],
                        'sent'     => ['bg' => '#dcfce7', 'color' => '#16a34a', 'label' => 'Доставлено'],
                        'failed'   => ['bg' => '#fee2e2', 'color' => '#dc2626', 'label' => 'Ошибка'],
                        'opened'   => ['bg' => '#fef3c7', 'color' => '#d97706', 'label' => 'Открыто'],
                    ];
                @endphp
                @foreach($statusBreakdown as $status => $count)
                    @php $sc = $statusColors[$status] ?? ['bg' => '#f3f4f6', 'color' => '#6b7280', 'label' => $status]; @endphp
                    <div style="background:{{ $sc['bg'] }};border-radius:10px;padding:12px 14px;text-align:center;">
                        <p style="font-size:22px;font-weight:800;color:{{ $sc['color'] }};">{{ number_format($count) }}</p>
                        <p style="font-size:11px;font-weight:600;color:{{ $sc['color'] }};margin-top:2px;">{{ $sc['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Logs table --}}
    <div style="background:white;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,0.06);overflow:hidden;">
        <div style="padding:14px 20px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;">
            <p style="font-size:13px;font-weight:700;color:#111827;">Лог отправок</p>
            <p style="font-size:12px;color:#9ca3af;">{{ $logs->total() }} записей</p>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f9fafb;">
                        <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;white-space:nowrap;">Клиент</th>
                        <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;white-space:nowrap;">Email / Телефон</th>
                        <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;">Статус</th>
                        <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;white-space:nowrap;">Отправлено</th>
                        <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;white-space:nowrap;">Открыто</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $statusCfg = [
                                'pending' => ['bg' => '#f3f4f6', 'color' => '#6b7280', 'label' => 'Ожидает'],
                                'sent'    => ['bg' => '#dcfce7', 'color' => '#16a34a', 'label' => 'Доставлено'],
                                'failed'  => ['bg' => '#fee2e2', 'color' => '#dc2626', 'label' => 'Ошибка'],
                                'opened'  => ['bg' => '#fef3c7', 'color' => '#d97706', 'label' => 'Открыто'],
                            ];
                            $sc = $statusCfg[$log->status] ?? ['bg' => '#f3f4f6', 'color' => '#6b7280', 'label' => $log->status];
                        @endphp
                        <tr style="border-bottom:1px solid #f9fafb;transition:background 0.1s;" onmouseenter="this.style.background='#fafafa'" onmouseleave="this.style.background=''">
                            <td style="padding:10px 16px;color:#111827;font-weight:500;">
                                {{ $log->customer ? $log->customer->first_name . ' ' . $log->customer->last_name : 'ID ' . $log->customer_id }}
                            </td>
                            <td style="padding:10px 16px;color:#6b7280;">
                                {{ $log->customer?->email ?? '—' }}
                                @if($log->customer?->phone)
                                    <span style="color:#9ca3af;"> · {{ $log->customer->phone }}</span>
                                @endif
                            </td>
                            <td style="padding:10px 16px;">
                                <span style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:5px;font-size:11px;font-weight:600;background:{{ $sc['bg'] }};color:{{ $sc['color'] }};">
                                    {{ $sc['label'] }}
                                </span>
                                @if($log->error_message)
                                    <span style="display:block;font-size:10px;color:#ef4444;margin-top:2px;" title="{{ $log->error_message }}">{{ Str::limit($log->error_message, 40) }}</span>
                                @endif
                            </td>
                            <td style="padding:10px 16px;color:#6b7280;white-space:nowrap;">
                                {{ $log->sent_at ? $log->sent_at->format('d.m.y H:i') : '—' }}
                            </td>
                            <td style="padding:10px 16px;color:#6b7280;white-space:nowrap;">
                                {{ $log->opened_at ? $log->opened_at->format('d.m.y H:i') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:30px;text-align:center;color:#9ca3af;font-size:13px;">
                                Записей нет
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
            <div style="padding:12px 20px;border-top:1px solid #f3f4f6;">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    @if($campaign->status === 'sending')
        <script>
            // Auto-refresh every 5 seconds while campaign is sending
            setTimeout(() => window.location.reload(), 5000);
        </script>
    @endif
</x-admin::layouts>
