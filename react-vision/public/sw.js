// Vision service worker — minimal stub.
// Reason for existing: Chrome / Edge fire `beforeinstallprompt` only when a service worker is
// registered. We don't want offline mode (camera live feeds need fresh data) so the worker
// intentionally does no caching — it just claims clients on activate and forwards every fetch
// to the network. Bumping CACHE_VERSION will trigger a clean activation on existing clients.

const CACHE_VERSION = 'vision-1'

self.addEventListener('install', (event) => {
    event.waitUntil(self.skipWaiting())
})

self.addEventListener('activate', (event) => {
    event.waitUntil(
        (async () => {
            // Drop any caches left over from earlier worker versions (defensive — we don't write any).
            const keys = await caches.keys()
            await Promise.all(keys.filter((k) => k !== CACHE_VERSION).map((k) => caches.delete(k)))
            await self.clients.claim()
        })(),
    )
})

self.addEventListener('fetch', () => {
    // No-op: every request goes straight to the network. The worker exists purely to make the
    // app installable; offline support is intentionally out of scope.
})

// Web Push — backend (minishlink/web-push) ships a JSON payload built by SendWebPushListener.
// We render an OS-level notification with the same title/message that lives in the bell-icon
// dropdown so the user gets a single coherent inbox across desktop and mobile.
self.addEventListener('push', (event) => {
    let payload = {}
    if (event.data) {
        try {
            payload = event.data.json()
        } catch (_e) {
            payload = {title: 'Vision', message: event.data.text()}
        }
    }

    const title = payload.title || 'Vision'
    const options = {
        body: payload.message || '',
        icon: '/icons/icon-192.png',
        badge: '/icons/icon-192.png',
        tag: payload.id ? `vision-${payload.id}` : undefined,
        data: {
            link: payload.link || '/',
            id: payload.id,
        },
    }

    event.waitUntil(self.registration.showNotification(title, options))
})

// Tap on the notification → focus an existing app tab if there is one, otherwise open the link.
self.addEventListener('notificationclick', (event) => {
    event.notification.close()
    const link = event.notification.data?.link || '/'

    event.waitUntil(
        (async () => {
            const clientsList = await self.clients.matchAll({type: 'window', includeUncontrolled: true})
            for (const client of clientsList) {
                if ('focus' in client) {
                    await client.focus()
                    if ('navigate' in client) {
                        try { await client.navigate(link) } catch (_e) { /* cross-origin nav blocked */ }
                    }
                    return
                }
            }
            if (self.clients.openWindow) {
                await self.clients.openWindow(link)
            }
        })(),
    )
})
