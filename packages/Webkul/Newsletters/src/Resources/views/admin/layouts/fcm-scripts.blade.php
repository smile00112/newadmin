<!-- FCM Scripts -->
{{--@php--}}
{{--    $firebaseConfig = \App\Helpers\FirebaseHelper::getConfig();--}}
{{--    $vapidKey = \App\Helpers\FirebaseHelper::getVapidKey();--}}
{{--@endphp--}}

{{--@if($firebaseConfig)--}}

<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js"></script>

<script>
    // Firebase configuration from JSON file
    {{--const firebaseConfig = @json($firebaseConfig);--}}
    const firebaseConfig = {
        apiKey: "AIzaSyBZEJmlJwm18F2nzDkO-PJF2B-sTzUpYE0",
        authDomain: "couriers-3473b.firebaseapp.com",
        projectId: "couriers-3473b",
        storageBucket: "couriers-3473b.appspot.com",
        messagingSenderId: "353175461051",
        appId: "1:353175461051:web:d716ecec53b59845939d9e"
    };

    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);

    // Initialize Firebase Cloud Messaging
    const messaging = firebase.messaging();

    // FCM Token Management
    class FCMService {
        constructor() {
            this.messaging = messaging;
            {{--this.vapidKey = @json($vapidKey);--}}
                this.vapidKey = "1952201";
            this.init();
        }

        async init() {
            try {
                // Register service worker
                if ('serviceWorker' in navigator) {
                    const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                    console.log('Service Worker registered:', registration);
                }

                // Request permission
                const permission = await Notification.requestPermission();

                if (permission === 'granted') {
                    // Get FCM token
                    const token = await this.messaging.getToken({
                        vapidKey: this.vapidKey
                    });

                    if (token) {
                        console.log('FCM Token:', token);
                        // Send token to server
                        await this.sendTokenToServer(token);
                    }
                }
            } catch (error) {
                console.error('FCM initialization error:', error);
            }
        }

        async sendTokenToServer(token) {
            try {
                const response = await fetch('{{ route("admin.fcm.token") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        fcm_token: token,
                        user_id: {{ auth()->guard('admin')->id() }}
                    })
                });

                if (response.ok) {
                    console.log('FCM token saved successfully');
                }
            } catch (error) {
                console.error('Error saving FCM token:', error);
            }
        }
    }

    // Initialize FCM when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        new FCMService();
    });
</script>
{{--@else--}}
{{--    <script>--}}
{{--        console.error('Firebase configuration not found');--}}
{{--    </script>--}}
{{--@endif--}}





{{--@push('scripts')--}}
{{--    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"></script>--}}
{{--    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js"></script>--}}
{{--    <script>--}}
{{--        // Firebase configuration--}}
{{--        const firebaseConfig = {--}}
{{--            apiKey: "AIzaSyBZEJmlJwm18F2nzDkO-PJF2B-sTzUpYE0",--}}
{{--            authDomain: "couriers-3473b.firebaseapp.com",--}}
{{--            projectId: "couriers-3473b",--}}
{{--            storageBucket: "couriers-3473b.appspot.com",--}}
{{--            messagingSenderId: "353175461051",--}}
{{--            appId: "1:353175461051:web:d716ecec53b59845939d9e"--}}
{{--        };--}}
{{--console.log('firebaseConfig', firebaseConfig)--}}
{{--        // // Initialize Firebase--}}
{{--        // firebase.initializeApp(firebaseConfig);--}}
{{--        //--}}
{{--        // // Initialize Firebase Cloud Messaging--}}
{{--        // const messaging = firebase.messaging();--}}

{{--        // Initialize Firebase--}}
{{--        const app = initializeApp(firebaseConfig);--}}
{{--        const messaging = getMessaging(app);--}}

{{--        // Request permission and get token--}}
{{--        export const requestForToken = async () => {--}}
{{--            try {--}}
{{--                const permission = await Notification.requestPermission();--}}
{{--                if (permission === "granted") {--}}
{{--                    const token = await getToken(messaging, {--}}
{{--                        vapidKey: "1952201", // Получите в консоли Firebase: Cloud Messaging -> Web Push certificates--}}
{{--                    });--}}
{{--                    console.log("FCM Token:", token);--}}

{{--                    // Отправляем токен на сервер для сохранения--}}
{{--                    await sendTokenToServer(token);--}}
{{--                    return token;--}}
{{--                } else {--}}
{{--                    console.warn("Notification permission denied");--}}
{{--                }--}}
{{--            } catch (error) {--}}
{{--                console.error("Error getting FCM token:", error);--}}
{{--            }--}}
{{--        };--}}

{{--        // Function to send token to your Bagisto backend--}}
{{--        const sendTokenToServer = async (token) => {--}}
{{--            try {--}}
{{--                const response = await fetch('/api/admin/fcm-token', { // Ваш кастомный API endpoint--}}
{{--                    method: 'POST',--}}
{{--                    headers: {--}}
{{--                        'Content-Type': 'application/json',--}}
{{--                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')--}}
{{--                    },--}}
{{--                    body: JSON.stringify({ fcm_token: token })--}}
{{--                });--}}

{{--                if (!response.ok) {--}}
{{--                    throw new Error('Failed to save token');--}}
{{--                }--}}

{{--                console.log('Token successfully saved to server');--}}
{{--            } catch (error) {--}}
{{--                console.error('Error sending token to server:', error);--}}
{{--            }--}}
{{--        };--}}

{{--        // Listen for messages when app is in foreground--}}
{{--        onMessage(messaging, (payload) => {--}}
{{--            console.log("Message received in foreground: ", payload);--}}
{{--            // Здесь можно показать уведомление в интерфейсе--}}
{{--        });--}}

{{--    </script>--}}
{{--@endpush--}}
