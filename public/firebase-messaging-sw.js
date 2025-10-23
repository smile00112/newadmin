// Firebase service worker
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

// Firebase configuration
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

// Handle background messages
messaging.onBackgroundMessage(function(payload) {
    console.log('Received background message ', payload);

    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/favicon.ico'
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});
