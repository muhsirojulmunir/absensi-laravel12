// Firebase Messaging Service Worker
// File ini WAJIB ada di folder public agar browser bisa menerima notifikasi di background

importScripts('https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.2/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "AIzaSyCW4JrRkVYZN2r0JimQqsvN5YIQ23pRgUQ",
    authDomain: "jmn-matrix.firebaseapp.com",
    projectId: "jmn-matrix",
    storageBucket: "jmn-matrix.firebasestorage.app",
    messagingSenderId: "2788168009",
    appId: "1:2788168009:web:c9c75f1a19a7d2ae37ca98",
});

const messaging = firebase.messaging();

// Handle background messages (saat browser ditutup / tab tidak aktif)
messaging.onBackgroundMessage(function (payload) {
    console.log('[SW] Background message received:', payload);

    const notificationTitle = payload.notification?.title || '📢 Pengingat Absensi';
    const notificationOptions = {
        body: payload.notification?.body || 'Jangan lupa absen!',
        icon: '/favicon.ico',
        badge: '/favicon.ico',
        vibrate: [200, 100, 200],
        requireInteraction: true,
        data: {
            url: payload.fcmOptions?.link || payload.data?.url || '/'
        }
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle klik pada notifikasi
self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    const url = event.notification.data?.url || '/';
    event.waitUntil(clients.openWindow(url));
});
