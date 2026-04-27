import type {Camera} from '../cameras/types'
import {getObjectPath} from '../objects/helpers'
import type {VisionObject} from '../objects/types'
import type {Album, AlbumSearchResult} from './types'

export function enrichAlbums(
    albums: Album[],
    cameras: Camera[],
    objects: VisionObject[],
): AlbumSearchResult[] {
    const cameraById = new Map(cameras.map((c) => [c.id, c] as const))
    return albums
        .map((album) => {
            const camera = cameraById.get(album.camera_id)
            if (!camera) return null
            const path = getObjectPath(objects, camera.object_id)
            return {
                ...album,
                camera_name: camera.name,
                object_id: camera.object_id,
                object_path: path.map((o) => o.name),
            } satisfies AlbumSearchResult
        })
        .filter((x): x is AlbumSearchResult => x !== null)
}

export function searchAlbums(enriched: AlbumSearchResult[], query: string): AlbumSearchResult[] {
    const q = query.trim().toLowerCase()
    if (!q) return enriched
    return enriched.filter((a) => {
        if (a.date.toLowerCase().includes(q)) return true
        if (a.camera_name.toLowerCase().includes(q)) return true
        if (a.object_path.some((p) => p.toLowerCase().includes(q))) return true
        return false
    })
}
