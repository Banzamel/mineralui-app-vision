import {useCallback, useEffect, useRef, useState} from 'react'

import {albumsApi} from './api'
import type {AlbumPhoto} from './types'

interface UseAlbumPhotosOptions {
    limit?: number
    enabled?: boolean
}

interface UseAlbumPhotosResult {
    photos: AlbumPhoto[]
    hasMore: boolean
    loading: boolean
    initialLoading: boolean
    error: string | null
    loadMore: () => void
    reload: () => void
}

/**
 * Cursor-paginated photo feed for a given album id.
 * First page runs on mount (or when albumId changes); subsequent pages are fetched
 * by calling `loadMore()` — typically wired to MLoadMore's onLoadMore.
 */
export function useAlbumPhotos(albumId: number | null, options: UseAlbumPhotosOptions = {}): UseAlbumPhotosResult {
    const limit = options.limit ?? 50
    const enabled = options.enabled ?? true

    const [photos, setPhotos] = useState<AlbumPhoto[]>([])
    const [cursor, setCursor] = useState<string | null>(null)
    const [hasMore, setHasMore] = useState(false)
    const [loading, setLoading] = useState(false)
    const [initialLoading, setInitialLoading] = useState(false)
    const [error, setError] = useState<string | null>(null)

    const inFlight = useRef(false)
    const reloadSeq = useRef(0)

    const fetchPage = useCallback(
        async (id: number, nextCursor: string | null, isInitial: boolean, seq: number) => {
            if (inFlight.current) return
            inFlight.current = true
            if (isInitial) setInitialLoading(true)
            setLoading(true)
            setError(null)
            try {
                const page = await albumsApi.photos(id, nextCursor, limit)
                if (seq !== reloadSeq.current) return
                setPhotos((prev) => (isInitial ? page.photos : [...prev, ...page.photos]))
                setCursor(page.nextCursor)
                setHasMore(page.hasMore)
            } catch (e) {
                if (seq !== reloadSeq.current) return
                setError(e instanceof Error ? e.message : 'Nie udało się pobrać zdjęć.')
            } finally {
                if (seq === reloadSeq.current) {
                    setLoading(false)
                    if (isInitial) setInitialLoading(false)
                }
                inFlight.current = false
            }
        },
        [limit],
    )

    const reload = useCallback(() => {
        if (albumId === null || !enabled) return
        const seq = ++reloadSeq.current
        setPhotos([])
        setCursor(null)
        setHasMore(false)
        void fetchPage(albumId, null, true, seq)
    }, [albumId, enabled, fetchPage])

    const loadMore = useCallback(() => {
        if (albumId === null || !enabled || !hasMore || loading) return
        void fetchPage(albumId, cursor, false, reloadSeq.current)
    }, [albumId, cursor, enabled, fetchPage, hasMore, loading])

    useEffect(() => {
        if (albumId === null || !enabled) {
            reloadSeq.current++
            setPhotos([])
            setCursor(null)
            setHasMore(false)
            setError(null)
            setLoading(false)
            setInitialLoading(false)
            return
        }
        reload()
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [albumId, enabled])

    return {photos, hasMore, loading, initialLoading, error, loadMore, reload}
}
