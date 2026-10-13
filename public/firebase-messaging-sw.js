// Firebase Cloud Messaging Service Worker
// This file handles push notification delivery in the background

importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js');

firebase.initializeApp({
    // Firebase config will be injected at runtime via the main page
    // This service worker uses the same project as the main app
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(function (payload) {
    const title = payload.notification?.title || 'Grocery App';
    const options = {
        body: payload.notification?.body || '',
        icon: '/images/logo.png',
        badge: '/images/logo.png',
        data: payload.data || {},
        tag: payload.data?.type || 'general',
    };

    self.registration.showNotification(title, options);
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    const actionUrl = event.notification.data?.action_url;

    if (actionUrl) {
        event.waitUntil(
            clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
                for (const client of clientList) {
                    if (client.url.includes(self.location.origin) && 'focus' in client) {
                        client.navigate(actionUrl);
                        return client.focus();
                    }
                }
                return clients.openWindow(actionUrl);
            })
        );
    }
});
