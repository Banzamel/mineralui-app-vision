import {api} from '../../helpers/api'
import type {VisionObject, VisionObjectPayload} from './types'

interface ListResponse<T> {
    data: T[]
}

interface ItemResponse<T> {
    data: T
}

export const objectsApi = {
    list: () => api.get<ListResponse<VisionObject>>('/vision/objects'),
    get: (id: number) => api.get<ItemResponse<VisionObject>>(`/vision/objects/${id}`),
    create: (payload: VisionObjectPayload) =>
        api.post<ItemResponse<VisionObject>>('/vision/objects', payload),
    update: (id: number, payload: VisionObjectPayload) =>
        api.patch<ItemResponse<VisionObject>>(`/vision/objects/${id}`, payload),
    /**
     * Replaces the object's main photo. Frontend sends multipart/form-data; backend stores
     * the file on the public disk and returns the updated object with `main_photo_url`.
     */
    uploadMainPhoto: (id: number, file: File) => {
        const fd = new FormData()
        fd.append('image', file)
        return api.post<ItemResponse<VisionObject>>(`/vision/objects/${id}/main-photo`, fd)
    },
    delete: (id: number) => api.delete<void>(`/vision/objects/${id}`),
}
