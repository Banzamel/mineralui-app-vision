export interface AlbumPhoto {
    id: number
    album_id: number
    url: string
    thumbnail_url: string
    captured_at: string
}

export interface AlbumPhotosPage {
    photos: AlbumPhoto[]
    nextCursor: string | null
    hasMore: boolean
}

export interface Album {
    id: number
    camera_id: number
    date: string
    photos_count: number
    cover_photo: string
    photos?: AlbumPhoto[]
}

export interface AlbumSearchResult extends Album {
    camera_name: string
    object_id: number
    object_path: string[]
    [key: string]: unknown
}
