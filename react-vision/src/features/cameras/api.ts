import {api} from '../../helpers/api'
import type {Building, Camera, CameraPayload} from './types'

interface ListResponse<T> {
    data: T[]
}

interface ItemResponse<T> {
    data: T
}

export const camerasApi = {
    list: () => api.get<ListResponse<Camera>>('/vision/cameras'),
    get: (id: number) => api.get<ItemResponse<Camera>>(`/vision/cameras/${id}`),
    create: (payload: CameraPayload) => api.post<ItemResponse<Camera>>('/vision/cameras', payload),
    update: (id: number, payload: CameraPayload) =>
        api.patch<ItemResponse<Camera>>(`/vision/cameras/${id}`, payload),
    /**
     * Replaces the camera's main photo via multipart upload.
     */
    uploadMainPhoto: (id: number, file: File) => {
        const fd = new FormData()
        fd.append('image', file)
        return api.post<ItemResponse<Camera>>(`/vision/cameras/${id}/main-photo`, fd)
    },
    delete: (id: number) => api.delete<void>(`/vision/cameras/${id}`),
    buildings: () => api.get<ListResponse<Building>>('/vision/buildings'),
}
