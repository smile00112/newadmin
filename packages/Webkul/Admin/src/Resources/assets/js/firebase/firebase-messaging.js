// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import { getMessaging, getToken, onMessage } from "firebase/messaging";

// Your web app's Firebase configuration
const firebaseConfig = {
    apiKey: "YOUR_API_KEY",
    authDomain: "YOUR_PROJECT.firebaseapp.com",
    projectId: "YOUR_PROJECT_ID",
    storageBucket: "YOUR_PROJECT.appspot.com",
    messagingSenderId: "YOUR_SENDER_ID",
    appId: "YOUR_APP_ID"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

// Request permission and get token
export const requestForToken = async () => {
    try {
        const permission = await Notification.requestPermission();
        if (permission === "granted") {
            const token = await getToken(messaging, {
                vapidKey: "YOUR_VAPID_KEY", // Получите в консоли Firebase: Cloud Messaging -> Web Push certificates
            });
            console.log("FCM Token:", token);

            // Отправляем токен на сервер для сохранения
            await sendTokenToServer(token);
            return token;
        } else {
            console.warn("Notification permission denied");
        }
    } catch (error) {
        console.error("Error getting FCM token:", error);
    }
};

// Function to send token to your Bagisto backend
const sendTokenToServer = async (token) => {
    try {
        const response = await fetch('/api/admin/fcm-token', { // Ваш кастомный API endpoint
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ fcm_token: token })
        });

        if (!response.ok) {
            throw new Error('Failed to save token');
        }

        console.log('Token successfully saved to server');
    } catch (error) {
        console.error('Error sending token to server:', error);
    }
};

// Listen for messages when app is in foreground
onMessage(messaging, (payload) => {
    console.log("Message received in foreground: ", payload);
    // Здесь можно показать уведомление в интерфейсе
});
