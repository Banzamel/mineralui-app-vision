import {useEffect, useState} from 'react'

import {installerApi} from './api'
import type {InstallStatus} from './types'

interface UseInstallStatusResult {
    status: InstallStatus | null
    loading: boolean
    installed: boolean
}

// Module-level cache — dziel stan między komponentami (LoginPage + InstallPage route guard)
// żeby nie dublować requestu. Refresh po pełnym reload strony.
let cachedStatus: InstallStatus | null = null
let inFlight: Promise<InstallStatus> | null = null

/**
 * Czyści cache hooka — wywoływane po finalize'ie installera, żeby LoginPage dostał
 * świeży status (installed=true) i ukrył link do kreatora.
 */
export function invalidateInstallStatus(): void {
    cachedStatus = null
    inFlight = null
}

/**
 * Pobiera stan instalatora raz per cykl życia strony i zwraca flagę `installed`.
 * Używane w LoginPage (ukrycie linku do /install po instalacji) oraz w InstallPage
 * (redirect na /login gdy ktoś wklepie URL bezpośrednio po finalizacji).
 */
export function useInstallStatus(): UseInstallStatusResult {
    const [status, setStatus] = useState<InstallStatus | null>(cachedStatus)
    const [loading, setLoading] = useState<boolean>(cachedStatus === null)

    useEffect(() => {
        if (cachedStatus !== null) {
            return
        }
        let cancelled = false
        const promise = inFlight ?? (inFlight = installerApi.status())
        promise
            .then((fresh) => {
                cachedStatus = fresh
                if (!cancelled) setStatus(fresh)
            })
            .catch(() => {
                // Brak statusu — traktujemy jako "nie wiemy, pokaż ostrożnie" (installed=false, link widoczny).
            })
            .finally(() => {
                inFlight = null
                if (!cancelled) setLoading(false)
            })
        return () => {
            cancelled = true
        }
    }, [])

    return {
        status,
        loading,
        installed: status?.installed === true,
    }
}
