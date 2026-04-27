import {useEffect, useRef} from 'react'

import {pushApi} from './pushApi'

const VAPID_PUBLIC_KEY = import.meta.env.VITE_VAPID_PUBLIC_KEY ?? ''
const STORAGE_KEY = 'vision-push-endpoint'

/**
 * Web Push uses URL-safe base64; the Push API expects a Uint8Array of the raw bytes.
 */
function urlBase64ToUint8Array(base64String: string): Uint8Array {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4)
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/')
    const rawData = window.atob(base64)
    const out = new Uint8Array(rawData.length)
    for (let i = 0; i < rawData.length; i++) out[i] = rawData.charCodeAt(i)
    return out
}

function arrayBufferToBase64(buffer: ArrayBuffer | null): string {
    if (!buffer) return ''
    const bytes = new Uint8Array(buffer)
    let binary = ''
    for (let i = 0; i < bytes.byteLength; i++) binary += String.fromCharCode(bytes[i])
    return window.btoa(binary)
}

function isSupported(): boolean {
    return (
        typeof window !== 'undefined' &&
        'serviceWorker' in navigator &&
        'PushManager' in window &&
        'Notification' in window
    )
}

/**
 * Subscribes the browser to web push and forwards the subscription to the backend so it can
 * deliver notifications even when no tab is open. Idempotent: stores the last-saved endpoint in
 * localStorage and skips the network round-trip if nothing changed between renders.
 *
 * Mounted under ProtectedLayout — it only kicks in once the user has an authenticated session,
 * so the POST /vision/push/subscriptions request is never anonymous.
 */
export function usePushSubscription(): void {
    const startedRef = useRef(false)

    useEffect(() => {
        if (startedRef.current) return
        if (!isSupported()) return
        if (!VAPID_PUBLIC_KEY) {
            console.warn('Web Push: VITE_VAPID_PUBLIC_KEY is not set, skipping subscription.')
            return
        }
        if (Notification.permission === 'denied') return

        startedRef.current = true

        const run = async () => {
            try {
                const registration = await navigator.serviceWorker.ready

                if (Notification.permission === 'default') {
                    const result = await Notification.requestPermission()
                    if (result !== 'granted') return
                }
                if (Notification.permission !== 'granted') return

                let subscription = await registration.pushManager.getSubscription()
                if (!subscription) {
                    // Cast keeps TS happy — Uint8Array<ArrayBufferLike> is structurally a BufferSource,
                    // but TS narrows the buffer type and refuses the call without a hint.
                    const applicationServerKey = urlBase64ToUint8Array(VAPID_PUBLIC_KEY) as BufferSource
                    subscription = await registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey,
                    })
                }

                const endpoint = subscription.endpoint
                const lastSent = window.localStorage.getItem(STORAGE_KEY)
                if (lastSent === endpoint) return

                const p256dh = arrayBufferToBase64(subscription.getKey('p256dh'))
                const auth = arrayBufferToBase64(subscription.getKey('auth'))

                await pushApi.save({
                    endpoint,
                    keys: {p256dh, auth},
                    user_agent: navigator.userAgent,
                })
                window.localStorage.setItem(STORAGE_KEY, endpoint)
            } catch (err) {
                console.warn('Web Push subscription failed', err)
            }
        }

        run()
    }, [])
}
