<!DOCTYPE html>

<html
    class="{{ request()->cookie('dark_mode') ?? 0 ? 'dark' : '' }}"
    lang="{{ app()->getLocale() }}"
    dir="{{ core()->getCurrentLocale()->direction }}"
>

<head>
    {!! view_render_event('bagisto.admin.layout.head.before') !!}

    <title>{{ $title ?? '' }}</title>

    <meta charset="UTF-8">

    <meta
        http-equiv="X-UA-Compatible"
        content="IE=edge"
    >
    <meta
        http-equiv="content-language"
        content="{{ app()->getLocale() }}"
    >
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >
    <meta
        name="base-url"
        content="{{ url()->to('/') }}"
    >
    <meta
        name="currency"
        content="{{ core()->getBaseCurrency()->toJson() }}"
    >
    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    @stack('meta')

    @bagistoVite(['src/Resources/assets/css/app.css', 'src/Resources/assets/js/app.js'])

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    />

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap"
        rel="stylesheet"
    />

    <link
        rel="preload"
        as="image"
        href="{{ url('cache/logo/bagisto.png') }}"
    >

    @if ($favicon = core()->getConfigData('general.design.admin_logo.favicon'))
        <link
            type="image/x-icon"
            href="{{ Storage::url($favicon) }}"
            rel="shortcut icon"
            sizes="16x16"
        >
    @else
        <link
            type="image/x-icon"
            href="{{ bagisto_asset('images/favicon.ico') }}"
            rel="shortcut icon"
            sizes="16x16"
        />
    @endif

    @stack('styles')

    <style>
        {!! core()->getConfigData('general.content.custom_scripts.custom_css') !!}
    </style>

    {!! view_render_event('bagisto.admin.layout.head.after') !!}
</head>

<body class="h-full dark:bg-gray-950">
{!! view_render_event('bagisto.admin.layout.body.before') !!}

<div
    id="app"
    class="h-full"
>
    <!-- Flash Message Blade Component -->
    <x-admin::flash-group />

    <!-- Confirm Modal Blade Component -->
    <x-admin::modal.confirm />

    {!! view_render_event('bagisto.admin.layout.content.before') !!}

    <!-- Page Header Blade Component -->
    <x-admin::layouts.header />

    <div
        class="group/container {{ request()->cookie('sidebar_collapsed') ?? 0 ? 'sidebar-collapsed' : 'sidebar-not-collapsed' }} flex flex-col lg:flex-row gap-0 lg:gap-4"
        ref="appLayout"
    >
        <!-- Page Sidebar Blade Component -->
        <div class="lg:fixed lg:top-[62px] lg:left-0 rtl:lg:right-0 rtl:lg:left-auto lg:z-10 w-full lg:w-auto">
            <x-admin::layouts.sidebar />
        </div>

        <div class="flex min-h-[calc(100vh-62px)] max-w-full flex-1 flex-col bg-white transition-all duration-300 dark:bg-gray-950 pt-3 px-2 sm:px-4 lg:pt-3 lg:px-4 lg:ltr:pl-[286px] lg:group-[.sidebar-collapsed]/container:ltr:pl-[85px] lg:rtl:pr-[286px] lg:group-[.sidebar-collapsed]/container:rtl:pr-[85px]">
            <!-- Added dynamic tabs for third level menus  -->
            <div class="pb-4 lg:pb-6">
                <!-- Todo @suraj-webkul need to optimize below statement. -->
                @if (! request()->routeIs('admin.configuration.index'))
                    <div class="overflow-x-auto">
                        <x-admin::layouts.tabs />
                    </div>
                @endif

                <!-- Page Content Blade Component -->
                <div class="w-full overflow-x-hidden">
                    {{ $slot }}
                </div>
            </div>

            <!-- Powered By -->
            <div class="mt-auto">
{{--                <div class="border-t bg-white py-2 text-center text-xs sm:text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">--}}
{{--                    @lang('admin::app.components.layouts.powered-by.description', [--}}
{{--                        'bagisto' => '<a class="text-blue-600 hover:underline dark:text-darkBlue" href="https://bagisto.com/en/">Bagisto</a>',--}}
{{--                        'webkul' => '<a class="text-blue-600 hover:underline dark:text-darkBlue" href="https://webkul.com/">Webkul</a>',--}}
{{--                    ])--}}
{{--                </div>--}}
            </div>
        </div>
    </div>

    {!! view_render_event('bagisto.admin.layout.content.after') !!}
</div>

{!! view_render_event('bagisto.admin.layout.body.after') !!}

@stack('scripts')

{!! view_render_event('bagisto.admin.layout.vue-app-mount.before') !!}

<!-- FCM Push Notifications -->
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js"></script>
<script>
    // FCM Initialization - wrapped to avoid conflicts
    (function() {
        'use strict';
        //TODO get from .env
        // Firebase configuration
        const firebaseConfig = {
            apiKey: "AIzaSyDVdd39ZOOiMP9j2C9t-Ikglvc1fgbbfS8",
            authDomain: "couriers-3473b.firebaseapp.com",
            projectId: "couriers-3473b",
            storageBucket: "couriers-3473b.appspot.com",
            messagingSenderId: "353175461051",
            appId: "1:353175461051:web:d716ecec53b59845939d9e"
        };

        // VAPID Key from Firebase Console -> Project Settings -> Cloud Messaging -> Web Push certificates
        const VAPID_KEY = "1952201";

        // FCM Service Class
        class FCMService {
            constructor() {
                this.messaging = null;
                this.vapidKey = VAPID_KEY;
            }

            async init() {
                try {
                    // Detailed browser support check
                    console.log('FCM: Checking browser support...');
                    console.log('  - Service Worker support:', 'serviceWorker' in navigator);
                    console.log('  - PushManager support:', 'PushManager' in window);
                    console.log('  - Notification support:', 'Notification' in window);
                    console.log('  - Current protocol:', window.location.protocol);
                    console.log('  - Current hostname:', window.location.hostname);
                    console.log('  - Current URL:', window.location.href);
                    console.log('  - Is secure context:', window.isSecureContext);

                    // Check secure context FIRST (это главная проблема!)
                    const isLocalhost = window.location.hostname === 'localhost' ||
                                       window.location.hostname === '127.0.0.1' ||
                                       window.location.hostname === '[::1]';

                    if (!window.isSecureContext && !isLocalhost) {
                        console.error('❌ FCM: Push notifications require HTTPS or localhost');
                        console.error('');
                        console.error('🔧 Текущий URL:', window.location.href);
                        console.error('');
                        console.error('✅ Решение:');
                        console.error('   Вместо:', window.location.href);
                        console.error('   Используйте: http://localhost' + window.location.pathname);
                        console.error('');
                        console.error('   Или настройте HTTPS в Laragon');
                        return;
                    }

                    // Check if notifications are supported
                    if (!('Notification' in window)) {
                        console.error('FCM: Notifications are not supported in this browser');
                        return;
                    }

                    // Service Worker should be available in secure context
                    if (!('serviceWorker' in navigator)) {
                        console.error('FCM: Service Worker is not available');
                        console.error('Это странно для Chromium браузера в secure context');
                        console.error('Попробуйте:');
                        console.error('  1. Обновить браузер');
                        console.error('  2. Проверить chrome://flags/');
                        console.error('  3. Использовать обычный Chrome/Edge');
                        return;
                    }

                    console.log('✅ FCM: Browser support check passed');

                    // Initialize Firebase
                    if (!firebase.apps.length) {
                        firebase.initializeApp(firebaseConfig);
                        console.log('✅ FCM: Firebase initialized');
                    } else {
                        console.log('FCM: Firebase already initialized');
                    }

                    this.messaging = firebase.messaging();
                    console.log('✅ FCM: Messaging service created');

                    // Register service worker
                    const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js', {
                        scope: '/'
                    });
                    console.log('✅ FCM: Service Worker registered');

                    // Wait for service worker to be ready
                    await navigator.serviceWorker.ready;

                    // Request notification permission
                    const permission = await Notification.requestPermission();
                    console.log('FCM: Permission status:', permission);

                    if (permission === 'granted') {
                        // Get FCM token with service worker registration
                        const token = await this.messaging.getToken({
                            vapidKey: this.vapidKey,
                            serviceWorkerRegistration: registration
                        });

                        if (token) {
                            console.log('✅ FCM Token obtained:', token.substring(0, 20) + '...');
                            await this.sendTokenToServer(token);

                            // Listen for foreground messages
                            this.setupMessageListener();
                        } else {
                            console.warn('FCM: No registration token available');
                        }
                    } else if (permission === 'denied') {
                        console.warn('FCM: Notification permission denied');
                    }
                } catch (error) {
                    console.error('FCM initialization error:', error);
                    if (error.code === 'messaging/token-subscribe-failed') {
                        console.error('FCM: Check VAPID key configuration');
                    }
                }
            }

            setupMessageListener() {
                this.messaging.onMessage((payload) => {
                    console.log('📩 FCM: Message received (foreground)', payload);

                    const notificationTitle = payload.notification?.title || 'Новое уведомление';
                    const notificationOptions = {
                        body: payload.notification?.body || '',
                        icon: '/favicon.ico',
                        badge: '/favicon.ico',
                        tag: payload.data?.type || 'default',
                        requireInteraction: false,
                        data: payload.data
                    };

                    // Show notification if page is visible
                    if (document.visibilityState === 'visible') {
                        new Notification(notificationTitle, notificationOptions);
                    }
                });
            }

            async sendTokenToServer(token) {
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    if (!csrfToken) {
                        console.error('FCM: CSRF token not found');
                        return;
                    }

                    console.log('FCM: Sending token to server...');

                    const response = await fetch('{{ route("admin.fcm.token") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                        },
                        body: JSON.stringify({
                            fcm_token: token
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        console.log('✅ FCM: Token saved to server successfully');
                        console.log('Admin:', data.admin);
                    } else {
                        console.error('FCM: Failed to save token', data);
                    }
                } catch (error) {
                    console.error('FCM: Error saving token to server:', error);
                }
            }
        }

        // Initialize FCM after page load (after Vue mounts)
        window.addEventListener('load', function() {
            // Delay to ensure Vue app is mounted
            setTimeout(function() {
                const fcmService = new FCMService();
                fcmService.init().catch(err => {
                    console.error('FCM: Failed to initialize:', err);
                });
            }, 1000);
        });
    })();
</script>

<script>
    /**
     * Load event, the purpose of using the event is to mount the application
     * after all of our `Vue` components which is present in blade file have
     * been registered in the app. No matter what `app.mount()` should be
     * called in the last.
     */
    window.addEventListener("load", function(event) {
        app.mount("#app");
    });
</script>


{!! view_render_event('bagisto.admin.layout.vue-app-mount.after') !!}
</body>

</html>
