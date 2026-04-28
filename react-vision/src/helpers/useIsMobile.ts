import {useEffect, useState} from 'react'

/**
 * Returns true when the viewport width is below `breakpointPx`. Listens for resize/zoom
 * via `matchMedia`, so the value flips live as the user rotates the device or drags the
 * window across the breakpoint. Default breakpoint matches the project's mobile cut-off
 * (the `md` Tailwind/MineralUI breakpoint at 768 px).
 */
export function useIsMobile(breakpointPx: number = 768): boolean {
    const [isMobile, setIsMobile] = useState<boolean>(() => {
        if (typeof window === 'undefined' || !window.matchMedia) return false
        return window.matchMedia(`(max-width: ${breakpointPx - 1}px)`).matches
    })

    useEffect(() => {
        if (typeof window === 'undefined' || !window.matchMedia) return
        const mq = window.matchMedia(`(max-width: ${breakpointPx - 1}px)`)
        setIsMobile(mq.matches)
        const handler = (e: MediaQueryListEvent) => setIsMobile(e.matches)
        mq.addEventListener('change', handler)
        return () => mq.removeEventListener('change', handler)
    }, [breakpointPx])

    return isMobile
}
