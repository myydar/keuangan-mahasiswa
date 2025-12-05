// Service Worker for PWA and Push Notifications
const CACHE_NAME = 'keuangan-mahasiswa-v1';
const urlsToCache = [
    '/',
    '/dashboard.php',
    '/transaksi.php',
    '/kategori.php',
    '/grafik.php',
    '/assets/css/style.css',
    '/assets/js/app.js',
    'https://cdn.tailwindcss.com',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

// Install Service Worker
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('Cache opened');
                return cache.addAll(urlsToCache);
            })
    );
});

// Activate Service Worker
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Fetch with Cache Strategy
self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                // Cache hit - return response
                if (response) {
                    return response;
                }
                
                // Clone the request
                const fetchRequest = event.request.clone();
                
                return fetch(fetchRequest).then((response) => {
                    // Check if valid response
                    if (!response || response.status !== 200 || response.type !== 'basic') {
                        return response;
                    }
                    
                    // Clone the response
                    const responseToCache = response.clone();
                    
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                    
                    return response;
                });
            })
    );
});

// Push Notification Handler
self.addEventListener('push', (event) => {
    console.log('Push notification received:', event);
    
    let data = {
        title: 'Keuangan Mahasiswa',
        body: 'Anda memiliki notifikasi baru',
        icon: '/assets/images/icon-192.png',
        badge: '/assets/images/badge-72.png',
        tag: 'default'
    };
    
    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data.body = event.data.text();
        }
    }
    
    const options = {
        body: data.body,
        icon: data.icon || '/assets/images/icon-192.png',
        badge: data.badge || '/assets/images/badge-72.png',
        vibrate: [200, 100, 200],
        tag: data.tag || 'default',
        requireInteraction: true,
        actions: [
            {
                action: 'view',
                title: 'Lihat',
                icon: '/assets/images/check-icon.png'
            },
            {
                action: 'close',
                title: 'Tutup',
                icon: '/assets/images/close-icon.png'
            }
        ],
        data: {
            url: data.url || '/dashboard.php',
            dateOfArrival: Date.now()
        }
    };
    
    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// Notification Click Handler
self.addEventListener('notificationclick', (event) => {
    console.log('Notification clicked:', event);
    
    event.notification.close();
    
    if (event.action === 'view') {
        const urlToOpen = event.notification.data.url || '/dashboard.php';
        
        event.waitUntil(
            clients.matchAll({ type: 'window', includeUncontrolled: true })
                .then((clientList) => {
                    // Check if already open
                    for (let i = 0; i < clientList.length; i++) {
                        const client = clientList[i];
                        if (client.url.includes(urlToOpen) && 'focus' in client) {
                            return client.focus();
                        }
                    }
                    
                    // Open new window
                    if (clients.openWindow) {
                        return clients.openWindow(urlToOpen);
                    }
                })
        );
    }
});

// Background Sync for offline transactions
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-transactions') {
        event.waitUntil(syncTransactions());
    }
});

async function syncTransactions() {
    // Get offline transactions from IndexedDB
    // Send to server when online
    console.log('Syncing offline transactions...');
    // Implementation here
}

// Periodic Background Sync (for due date reminders)
self.addEventListener('periodicsync', (event) => {
    if (event.tag === 'check-due-dates') {
        event.waitUntil(checkDueDates());
    }
});

async function checkDueDates() {
    // Check for upcoming due dates
    // Send notifications
    console.log('Checking due dates...');
    // Implementation here
}