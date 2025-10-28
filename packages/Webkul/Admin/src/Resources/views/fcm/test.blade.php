<x-admin::layouts>
    <x-slot:title>
        FCM Push Уведомления - Тестирование
    </x-slot>

    <div class="flex gap-2.5 mt-3.5 max-xl:flex-wrap">
        <div class="flex flex-col gap-2 flex-1 max-xl:flex-auto">
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-4 sm:p-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    🔔 Тестирование FCM Push Уведомлений
                </h1>

                <!-- FCM Token Status -->
                <div class="mb-6 p-4 rounded-lg {{ $hasFcmToken ? 'bg-green-50 border border-green-200 dark:bg-green-900/20 dark:border-green-800' : 'bg-yellow-50 border border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800' }}">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            @if($hasFcmToken)
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @else
                                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            @endif
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium {{ $hasFcmToken ? 'text-green-800 dark:text-green-200' : 'text-yellow-800 dark:text-yellow-200' }}">
                                {{ $hasFcmToken ? 'FCM токен зарегистрирован' : 'FCM токен не найден' }}
                            </h3>
                            <div class="mt-2 text-sm {{ $hasFcmToken ? 'text-green-700 dark:text-green-300' : 'text-yellow-700 dark:text-yellow-300' }}">
                                <p>
                                    @if($hasFcmToken)
                                        Ваш браузер подписан на push-уведомления. Вы можете отправить тестовое уведомление.
                                        <br><span class="font-mono text-xs">{{ substr($user->fcm_token, 0, 50) }}...</span>
                                    @else
                                        Разрешите уведомления в браузере. Обновите страницу после разрешения.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test Notification Form -->
                <div class="space-y-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Отправить тестовое уведомление
                        </h2>
                        
                        <form id="testNotificationForm" class="space-y-4">
                            @csrf
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Заголовок
                                </label>
                                <input 
                                    type="text" 
                                    id="notification_title" 
                                    name="title"
                                    value="Тестовое уведомление"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Текст сообщения
                                </label>
                                <textarea 
                                    id="notification_body" 
                                    name="body"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                >Это тестовое push-уведомление от Dolinger Admin панели! 🚀</textarea>
                            </div>

                            <div class="flex gap-3">
                                <button 
                                    type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    {{ !$hasFcmToken ? 'disabled' : '' }}
                                >
                                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                    Отправить себе
                                </button>

                                <button 
                                    type="button"
                                    onclick="sendToAllAdmins()"
                                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                >
                                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    Отправить всем админам
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Response Message -->
                    <div id="responseMessage" class="hidden"></div>
                </div>

                <!-- Instructions -->
                <div class="mt-8 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">
                        📖 Инструкция:
                    </h3>
                    <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 list-disc list-inside">
                        <li>Разрешите уведомления в браузере при первом входе</li>
                        <li>FCM токен автоматически сохранится в вашем профиле</li>
                        <li>Используйте эту страницу для тестирования отправки уведомлений</li>
                        <li>Уведомления работают даже когда вкладка неактивна</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('testNotificationForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            try {
                const response = await fetch('{{ route("admin.fcm.send-test") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                showResponse(result.message, result.success ? 'success' : 'error');
            } catch (error) {
                showResponse('Ошибка отправки: ' + error.message, 'error');
            }
        });

        async function sendToAllAdmins() {
            const title = document.getElementById('notification_title').value;
            const body = document.getElementById('notification_body').value;
            
            if (confirm('Отправить уведомление всем администраторам?')) {
                try {
                    const response = await fetch('{{ route("admin.fcm.send-all") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ title, body })
                    });
                    
                    const result = await response.json();
                    showResponse(result.message, 'success');
                } catch (error) {
                    showResponse('Ошибка отправки: ' + error.message, 'error');
                }
            }
        }

        function showResponse(message, type) {
            const responseDiv = document.getElementById('responseMessage');
            responseDiv.className = `p-4 rounded-lg ${type === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-200'}`;
            responseDiv.textContent = message;
            responseDiv.classList.remove('hidden');
            
            setTimeout(() => {
                responseDiv.classList.add('hidden');
            }, 5000);
        }
    </script>
    @endpush
</x-admin::layouts>

