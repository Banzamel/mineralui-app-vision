export type ObjectType = 'block' | 'apartment' | 'house' | 'hangar' | 'garage' | 'other'

export interface VisionObject {
    id: number
    parent_id: number | null
    name: string
    type: ObjectType
    address: string
    description?: string
    /**
     * Public URL for the main photo (`Storage::disk('public')->url(main_photo_path)`).
     * Null when no photo is set. Frontend never sends this — uploads go through the
     * separate `objectsApi.uploadMainPhoto` endpoint.
     */
    main_photo_url?: string | null
    depth: number
    created_at: string
    children?: VisionObject[]
    cameras_count?: number
    [key: string]: unknown
}

export interface VisionObjectPayload {
    parent_id: number | null
    name: string
    type: ObjectType
    address: string
    description?: string
}
