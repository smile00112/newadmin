@props(['count' => 5])

<div style="padding: 16px 24px 20px; animation: pulse-all 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
    <div style="display: flex; gap: 16px; flex-wrap: wrap;">
        <!-- Left column skeleton -->
        <div style="flex: 1; min-width: 300px;">
            <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
                <div style="padding: 16px 20px; border-bottom: 1px solid #f3f4f6; background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%); background-size: 200% 100%; animation: shimmer 2s infinite; height: 20px; border-radius: 4px;"></div>
                <div style="padding: 20px; display: flex; flex-direction: column; gap: 16px;">
                    @for ($i = 0; $i < $count; $i++)
                        <div>
                            <div style="height: 14px; background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%); background-size: 200% 100%; animation: shimmer 2s infinite; border-radius: 4px; margin-bottom: 8px; width: 30%;"></div>
                            <div style="height: 36px; background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%); background-size: 200% 100%; animation: shimmer 2s infinite; border-radius: 8px;"></div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <!-- Right column skeleton -->
        <div style="width: 340px; max-width: 100%; flex-shrink: 0;">
            <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
                <div style="padding: 16px 20px; border-bottom: 1px solid #f3f4f6; background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%); background-size: 200% 100%; animation: shimmer 2s infinite; height: 20px; border-radius: 4px;"></div>
                <div style="padding: 20px; display: flex; flex-direction: column; gap: 16px;">
                    @for ($i = 0; $i < 3; $i++)
                        <div>
                            <div style="height: 14px; background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%); background-size: 200% 100%; animation: shimmer 2s infinite; border-radius: 4px; margin-bottom: 8px; width: 40%;"></div>
                            <div style="height: 36px; background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%); background-size: 200% 100%; animation: shimmer 2s infinite; border-radius: 8px;"></div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    @keyframes pulse-all {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.95; }
    }
</style>
