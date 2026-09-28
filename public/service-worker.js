/**
 * NETPACK AI Logistics Service Worker
 * Version: 2.1.0-light
 * Capabilities: Offline caching, Background Sync, Push Notifications for Status Changes
 */

const CACHE_NAME = 'netpack-ai-v2.2.0';
const OFFLINE_URL = '/offline.html';

const STATIC_ASSETS = [
    '/offline.html',
    '/manifest.json',
    '/images/logo-icon.png',
    '/images/logo.png',
    '/images/logo.svg',
    '/images/logo-white.svg',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

// Install: Cache Core Shell Assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch((err) => {
                console.warn('[SW] Some static assets failed to pre-cache:', err);
            });
        }).then(() => self.skipWaiting())
    );
});

// Activate: Clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        console.log('[SW] Purging outdated cache:', key);
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch Strategy:
// 1. Navigation/HTML: Network-First (ensures fresh CSRF token and session cookies on every page load)
// 2. API/Tracking: Network-First
// 3. Static Assets: Stale-While-Revalidate
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Skip non-GET requests from caching
    if (request.method !== 'GET') {
        return;
    }

    // Bypass non-HTTP schemes
    if (!url.protocol.startsWith('http')) {
        return;
    }

    // 1. HTML Page Navigation: ALWAYS Network-First to prevent stale CSRF tokens
    if (request.mode === 'navigate' || request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            fetch(request).catch(() => {
                return caches.match(OFFLINE_URL);
            })
        );
        return;
    }

    // 2. Network-First for live tracking, API data, and AI Chat endpoints
    if (url.pathname.startsWith('/api/') || 
        url.pathname.startsWith('/tracking') || 
        url.pathname.startsWith('/ai/')) {
        event.respondWith(
            fetch(request).catch(() => {
                return caches.match(request);
            })
        );
        return;
    }

    // 3. Stale-While-Revalidate for static assets, scripts, stylesheets, and images
    event.respondWith(
        caches.match(request).then((cachedResponse) => {
            const fetchPromise = fetch(request).then((networkResponse) => {
                if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
                    const responseToCache = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(request, responseToCache);
                    });
                }
                return networkResponse;
            }).catch(() => {
                return null;
            });

            return cachedResponse || fetchPromise;
        })
    );
});

// Push Notifications: Real-Time Shipment Status Intelligence
self.addEventListener('push', (event) => {
    let payload = {
        title: 'NETPACK AI Status Alert',
        body: 'Your shipment status has just been updated.',
        icon: '/images/logo-icon.png',
        badge: '/images/logo-icon.png',
        data: { url: '/client/dashboard' },
        vibrate: [100, 50, 100],
        actions: [
            { action: 'track', title: 'Radar Tracking' },
            { action: 'copilot', title: 'Ask Chanda' }
        ]
    };

    if (event.data) {
        try {
            const data = event.data.json();
            payload = { ...payload, ...data };
        } catch (e) {
            payload.body = event.data.text();
        }
    }

    event.waitUntil(
        self.registration.showNotification(payload.title, {
            body: payload.body,
            icon: payload.icon || '/images/logo-icon.png',
            badge: payload.badge || '/images/logo-icon.png',
            data: payload.data,
            vibrate: payload.vibrate || [100, 50, 100],
            actions: payload.actions || []
        })
    );
});

// Notification Click: Smart Deep-Linking
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = event.notification.data?.url || '/client/dashboard';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (let client of windowClients) {
                if (client.url.includes(targetUrl) && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});

// Background Sync: Offline Pickup & Dispatch Dispatcher
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-offline-shipments') {
        event.waitUntil(
            // Logic to flush pending IndexedDB offline bookings to /api/v1/shipments
            console.log('[SW] Background sync triggered: Syncing offline consignments...')
        );
    }
});
