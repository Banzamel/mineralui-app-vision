import {useMemo, useRef} from 'react'

import {albumsApi, enrichAlbums} from '../albums'
import type {Album, AlbumSearchResult} from '../albums/types'
import {camerasApi} from '../cameras'
import type {Camera} from '../cameras'
import {objectsApi} from '../objects'
import type {VisionObject} from '../objects'
import {useRealtimeEvent} from '../realtime'
import {usersApi} from '../users/api'
import type {User} from '../users/types'
import {useAsync} from '../../helpers'

export interface DashboardData {
    objects: VisionObject[]
    cameras: Camera[]
    albums: Album[]
    users: User[]
}

export interface DashboardDerived {
    enriched: AlbumSearchResult[]
    recentAlbums: AlbumSearchResult[]
    featuredCameras: Camera[]
    perDay: {date: string; count: number}[]
    perObject: {label: string; count: number}[]
    perCamera: {label: string; count: number}[]
    usersTotal: number
    usersActive: number
    usersLoggedInToday: number
    totalPhotos: number
    totalAlbums: number
}

function isoDay(value: string): string {
    return value.slice(0, 10)
}

function buildPerDay(albums: Album[]): {date: string; count: number}[] {
    const bucket = new Map<string, number>()
    for (const album of albums) {
        const key = isoDay(album.date)
        bucket.set(key, (bucket.get(key) ?? 0) + album.photos_count)
    }
    const sorted = Array.from(bucket.entries()).sort(([a], [b]) => a.localeCompare(b))
    return sorted.slice(-14).map(([date, count]) => ({date, count}))
}

function buildPerGroup(
    enriched: AlbumSearchResult[],
    keyFn: (a: AlbumSearchResult) => string,
): {label: string; count: number}[] {
    const bucket = new Map<string, number>()
    for (const album of enriched) {
        const key = keyFn(album)
        bucket.set(key, (bucket.get(key) ?? 0) + album.photos_count)
    }
    return Array.from(bucket.entries())
        .map(([label, count]) => ({label, count}))
        .sort((a, b) => b.count - a.count)
        .slice(0, 6)
}

interface UseDashboardDataOptions {
    /**
     * Whether to fetch users alongside objects/cameras/albums. Defaults to true (Dashboard page
     * needs them for UsersSummaryWidget). CalendarPage opts out — it only renders albums on a
     * calendar grid and the users fetch would require `users.view` permission for no reason.
     */
    withUsers?: boolean
}

export function useDashboardData({withUsers = true}: UseDashboardDataOptions = {}) {
    const {data, loading, error, reload} = useAsync<DashboardData>(async () => {
        const [objectsRes, camerasRes, albumsRes, usersRes] = await Promise.all([
            objectsApi.list(),
            camerasApi.list(),
            albumsApi.list(),
            withUsers ? usersApi.list() : Promise.resolve({data: [] as User[]}),
        ])
        return {
            objects: objectsRes.data,
            cameras: camerasRes.data,
            albums: albumsRes.data,
            users: usersRes.data,
        }
    }, [withUsers])

    const derived = useMemo<DashboardDerived | null>(() => {
        if (!data) return null
        const enriched = enrichAlbums(data.albums, data.cameras, data.objects)
        const recentAlbums = [...enriched]
            .sort((a, b) => b.date.localeCompare(a.date))
            .slice(0, 4)
        const featuredCameras = [...data.cameras]
            .sort((a, b) => Number(b.is_online) - Number(a.is_online))
            .slice(0, 2)
        const perDay = buildPerDay(data.albums)
        const perObject = buildPerGroup(enriched, (a) => a.object_path[0] ?? '—')
        const perCamera = buildPerGroup(enriched, (a) => a.camera_name)
        const today = new Date().toISOString().slice(0, 10)
        const usersLoggedInToday = data.users.filter(
            (u) => u.last_login_at && u.last_login_at.slice(0, 10) === today,
        ).length
        const usersActive = data.users.filter((u) => u.is_active).length
        const totalPhotos = data.albums.reduce((sum, a) => sum + a.photos_count, 0)
        return {
            enriched,
            recentAlbums,
            featuredCameras,
            perDay,
            perObject,
            perCamera,
            usersTotal: data.users.length,
            usersActive,
            usersLoggedInToday,
            totalPhotos,
            totalAlbums: data.albums.length,
        }
    }, [data])

    // Reload dashboardu po realtime evencie domenowym — debounce 500 ms żeby nie zarzucać API.
    const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null)
    const debouncedReload = () => {
        if (debounceRef.current) clearTimeout(debounceRef.current)
        debounceRef.current = setTimeout(() => reload(), 500)
    }
    useRealtimeEvent('company', 'objects.created', debouncedReload)
    useRealtimeEvent('company', 'objects.updated', debouncedReload)
    useRealtimeEvent('company', 'objects.deleted', debouncedReload)
    useRealtimeEvent('company', 'cameras.created', debouncedReload)
    useRealtimeEvent('company', 'cameras.updated', debouncedReload)
    useRealtimeEvent('company', 'cameras.deleted', debouncedReload)
    useRealtimeEvent('company', 'albums.created', debouncedReload)
    useRealtimeEvent('company', 'albums.deleted', debouncedReload)

    return {data, derived, loading, error, reload}
}
