export interface Camera {
    id: number
    object_id: number
    name: string
    address: string
    ip: string
    stream_url: string
    stream_login?: string | null
    stream_password?: string | null
    /**
     * Public URL for the main photo. Null when no photo is set. Uploads go through
     * `camerasApi.uploadMainPhoto` (multipart) — this field is read-only on the frontend.
     */
    main_photo_url?: string | null
    is_online: boolean
    /**
     * When true, the album view exposes a toggle to switch between the default photo grid
     * and a motion-preview mode that groups consecutive motion bursts (≤ 5 s gap) into one
     * tile cycling through frames on hover (desktop) / autoplay (mobile).
     */
    motion_preview_enabled?: boolean
    created_at: string
    [key: string]: unknown
}

export interface CameraPayload {
    object_id: number
    name: string
    address: string
    ip: string
    stream_url: string
    stream_login?: string | null
    stream_password?: string | null
    motion_preview_enabled?: boolean
}

export interface CameraLeaf {
    id: number
    name: string
}

export interface Address {
    id: number
    name: string
    cameras: CameraLeaf[]
}

export interface Building {
    id: number
    name: string
    /** Kamery podpięte BEZPOŚREDNIO pod root object (płaska hierarchia bez sub-objects). */
    cameras: CameraLeaf[]
    addresses: Address[]
}
