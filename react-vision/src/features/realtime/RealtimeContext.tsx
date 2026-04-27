import {createContext, useCallback, useContext, useEffect, useMemo, useRef, useState, type ReactNode} from 'react'

import {createEchoInstance, destroyEchoInstance} from '../../helpers/echo'
import {useAuth} from '../auth/AuthContext'

type Handler = (payload: unknown) => void

interface ChannelHandle {
    listen: (event: string, fn: Handler) => unknown
    stopListening: (event: string) => unknown
}

interface RealtimeContextValue {
    /**
     * Podpina handler do nazwanego eventu na prywatnym kanale firmy lub usera.
     * Zwraca funkcję cofającą subskrypcję (do wywołania w cleanup useEffect-u).
     * Nazwy eventów backendowe (broadcastAs) — bez kropki na początku.
     */
    subscribe: (channel: 'company' | 'user', event: string, handler: Handler) => () => void
}

const RealtimeContext = createContext<RealtimeContextValue | null>(null)

interface ChannelRefs {
    company: ChannelHandle | null
    user: ChannelHandle | null
}

export function RealtimeProvider({children}: {children: ReactNode}) {
    const {user} = useAuth()
    const channelsRef = useRef<ChannelRefs>({company: null, user: null})
    const pendingRef = useRef<Array<{channel: 'company' | 'user'; event: string; fn: Handler}>>([])
    const [, setReady] = useState(0)

    useEffect(() => {
        if (!user) return undefined

        const echo = createEchoInstance()
        const companyChannel = echo.private(`vision.company.${user.company_id}`) as unknown as ChannelHandle
        const userChannel = echo.private(`vision.user.${user.id}`) as unknown as ChannelHandle

        channelsRef.current = {company: companyChannel, user: userChannel}

        // Wpinamy zaległe subskrypcje (te zarejestrowane zanim Echo wstał).
        for (const p of pendingRef.current) {
            const target = p.channel === 'company' ? companyChannel : userChannel
            target.listen(`.${p.event}`, p.fn)
        }
        setReady((n) => n + 1)

        return () => {
            channelsRef.current = {company: null, user: null}
            echo.leave(`vision.company.${user.company_id}`)
            echo.leave(`vision.user.${user.id}`)
        }
    }, [user?.id, user?.company_id])

    useEffect(() => () => destroyEchoInstance(), [])

    const subscribe = useCallback<RealtimeContextValue['subscribe']>(
        (channel, event, handler) => {
            const target = channelsRef.current[channel]
            if (target) {
                target.listen(`.${event}`, handler)
            } else {
                pendingRef.current.push({channel, event, fn: handler})
            }
            return () => {
                const live = channelsRef.current[channel]
                if (live) live.stopListening(`.${event}`)
                pendingRef.current = pendingRef.current.filter(
                    (p) => !(p.channel === channel && p.event === event && p.fn === handler),
                )
            }
        },
        [],
    )

    const value = useMemo<RealtimeContextValue>(() => ({subscribe}), [subscribe])

    return <RealtimeContext.Provider value={value}>{children}</RealtimeContext.Provider>
}

export function useRealtime(): RealtimeContextValue {
    const ctx = useContext(RealtimeContext)
    if (!ctx) throw new Error('useRealtime must be used within RealtimeProvider')
    return ctx
}

/**
 * Wygodny hook — automatycznie subskrybuje + odsubskrybowuje przy unmoncie.
 */
export function useRealtimeEvent(
    channel: 'company' | 'user',
    event: string,
    handler: Handler,
    deps: ReadonlyArray<unknown> = [],
) {
    const {subscribe} = useRealtime()
    useEffect(() => {
        return subscribe(channel, event, handler)
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [channel, event, subscribe, ...deps])
}
