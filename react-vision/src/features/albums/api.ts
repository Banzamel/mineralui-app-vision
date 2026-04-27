import {api} from '../../helpers/api'
import type {Album, AlbumPhoto, AlbumPhotosPage} from './types'

interface ListResponse<T> {
    data: T[]
}

interface ItemResponse<T> {
    data: T
}

interface BackendPhoto {
    id: number
    album_id: number
    filename: string
    width: number
    height: number
    bytes: number
    mime: string
    taken_at: string | null
    stream_url: string
    thumbnail_url?: string
}

interface CursorPaginatedResponse<T> {
    data: T[]
    meta?: {
        path?: string
        per_page?: number
        next_cursor?: string | null
        prev_cursor?: string | null
    }
    links?: {
        next?: string | null
        prev?: string | null
    }
}

function toAlbumPhoto(row: BackendPhoto): AlbumPhoto {
    return {
        id: row.id,
        album_id: row.album_id,
        url: row.stream_url,
        // Falls back to the full-size stream while the queue worker is still generating thumbs.
        thumbnail_url: row.thumbnail_url ?? row.stream_url,
        captured_at: row.taken_at ?? '',
    }
}

export const albumsApi = {
    list: () => api.get<ListResponse<Album>>('/vision/albums'),
    get: (id: number) => api.get<ItemResponse<Album>>(`/vision/albums/${id}`),
    delete: (id: number) => api.delete(`/vision/albums/${id}`),
    photos: async (albumId: number, cursor: string | null = null, limit = 50): Promise<AlbumPhotosPage> => {
        const params = new URLSearchParams()
        params.set('limit', String(limit))
        if (cursor) params.set('cursor', cursor)
        const qs = params.toString()
        const res = await api.get<CursorPaginatedResponse<BackendPhoto>>(
            `/vision/albums/${albumId}/photos${qs ? `?${qs}` : ''}`,
        )
        const nextCursor = res.meta?.next_cursor ?? null
        return {
            photos: res.data.map(toAlbumPhoto),
            nextCursor,
            hasMore: nextCursor !== null,
        }
    },
}
