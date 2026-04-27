import {createContext, useCallback, useContext, useEffect, useMemo, useState, type ReactNode} from 'react'

import {useRealtimeEvent} from '../realtime'
import {notificationsApi} from './api'
import type {Notification} from './types'

interface NotificationsContextValue {
    notifications: Notification[]
    unreadCount: number
    loading: boolean
    error: Error | null
    reload: () => Promise<void>
    markRead: (id: string) => void
    markAllRead: () => void
    deleteOne: (id: string) => void
    deleteAll: () => void
}

const NotificationsContext = createContext<NotificationsContextValue | null>(null)

export function NotificationsProvider({children}: {children: ReactNode}) {
    const [notifications, setNotifications] = useState<Notification[]>([])
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState<Error | null>(null)

    const reload = useCallback(async () => {
        setLoading(true)
        setError(null)
        try {
            const res = await notificationsApi.list()
            // Backend zwraca {data: [...]} (Laravel Resource wrap) albo czasem gołą tablicę;
            // defensywnie akceptujemy oba shape'y i nigdy nie wpuszczamy undefined do state.
            const list = Array.isArray(res) ? res : (res?.data ?? [])
            setNotifications(list)
        } catch (err) {
            setError(err instanceof Error ? err : new Error(String(err)))
        } finally {
            setLoading(false)
        }
    }, [])

    useEffect(() => {
        reload()
    }, [reload])

    // Realtime — gdy backend wysyła nową notyfikację, wpinamy ją na początek listy.
    useRealtimeEvent('user', 'notifications.created', (payload) => {
        const incoming = payload as Notification
        setNotifications((prev) => {
            if (prev.some((n) => n.id === incoming.id)) return prev
            return [incoming, ...prev]
        })
    })

    const markRead = useCallback((id: string) => {
        setNotifications((prev) => prev.map((n) => (n.id === id ? {...n, read: true} : n)))
        notificationsApi.markAsRead(id).catch(() => {})
    }, [])

    const markAllRead = useCallback(() => {
        setNotifications((prev) => prev.map((n) => ({...n, read: true})))
        notificationsApi.markAllAsRead().catch(() => {})
    }, [])

    const deleteOne = useCallback((id: string) => {
        setNotifications((prev) => prev.filter((n) => n.id !== id))
        notificationsApi.delete(id).catch(() => {})
    }, [])

    const deleteAll = useCallback(() => {
        setNotifications([])
        notificationsApi.deleteAll().catch(() => {})
    }, [])

    const value = useMemo<NotificationsContextValue>(
        () => ({
            notifications,
            unreadCount: notifications.filter((n) => !n.read).length,
            loading,
            error,
            reload,
            markRead,
            markAllRead,
            deleteOne,
            deleteAll,
        }),
        [notifications, loading, error, reload, markRead, markAllRead, deleteOne, deleteAll],
    )

    return <NotificationsContext.Provider value={value}>{children}</NotificationsContext.Provider>
}

export function useNotifications(): NotificationsContextValue {
    const ctx = useContext(NotificationsContext)
    if (!ctx) {
        throw new Error('useNotifications must be used within NotificationsProvider')
    }
    return ctx
}
