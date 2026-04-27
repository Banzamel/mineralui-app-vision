import {api} from '../../helpers/api'
import type {ActivityEntry, Notification, SystemStatus, UnreadCount} from './types'

interface ListResponse<T> {
    data: T[]
}

interface ItemResponse<T> {
    data: T
}

export const notificationsApi = {
    list: () => api.get<ListResponse<Notification>>('/notifications'),
    unreadCount: () => api.get<ItemResponse<UnreadCount>>('/notifications/unread-count'),
    markAsRead: (id: string) => api.patch<void>(`/notifications/${id}/read`),
    markAllAsRead: () => api.post<void>('/notifications/read-all'),
    delete: (id: string) => api.delete<void>(`/notifications/${id}`),
    deleteAll: () => api.delete<void>('/notifications'),
}

export const activityApi = {
    list: () => api.get<ListResponse<ActivityEntry>>('/my-activity'),
}

export const systemStatusApi = {
    current: () => api.get<ItemResponse<SystemStatus>>('/system/status'),
}
