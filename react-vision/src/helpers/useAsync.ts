import {useCallback, useEffect, useRef, useState} from 'react'

export interface UseAsyncResult<T> {
    data: T | null
    loading: boolean
    error: Error | null
    reload: () => Promise<void>
}

export function useAsync<T>(fetcher: () => Promise<T>, deps: unknown[] = []): UseAsyncResult<T> {
    const [data, setData] = useState<T | null>(null)
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState<Error | null>(null)
    const cancelledRef = useRef(false)
    const fetcherRef = useRef(fetcher)
    fetcherRef.current = fetcher

    const run = useCallback(async () => {
        setLoading(true)
        setError(null)
        try {
            const result = await fetcherRef.current()
            if (cancelledRef.current) return
            setData(result)
        } catch (err) {
            if (cancelledRef.current) return
            setError(err instanceof Error ? err : new Error(String(err)))
        } finally {
            if (!cancelledRef.current) {
                setLoading(false)
            }
        }
    }, [])

    useEffect(() => {
        cancelledRef.current = false
        run()
        return () => {
            cancelledRef.current = true
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, deps)

    return {data, loading, error, reload: run}
}
