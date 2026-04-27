import {api} from '../../helpers/api'
import type {ScopeGrant} from '../auth/AuthContext'
import type {
    AuthLogsSummary,
    PermissionsByModule,
    Role,
    RolePayload,
    User,
    UserActivityEntry,
    UserPayload,
    UserSession,
    Pagination,
} from './types'

interface ListResponse<T> {
    data: T[]
    meta?: Pagination
}

interface ItemResponse<T> {
    data: T
}

export const usersApi = {
    list: () => api.get<ListResponse<User>>('/administration/users'),
    get: (id: number) => api.get<ItemResponse<User>>(`/administration/users/${id}`),
    create: (payload: UserPayload) => api.post<ItemResponse<User>>('/administration/users', payload),
    update: (id: number, payload: UserPayload) => api.put<ItemResponse<User>>(`/administration/users/${id}`, payload),
    delete: (id: number) => api.delete<void>(`/administration/users/${id}`),
    bulkDelete: (ids: number[]) => Promise.all(ids.map((id) => api.delete<void>(`/administration/users/${id}`))),
    bulkSetActive: (ids: number[], active: boolean) =>
        Promise.all(ids.map((id) => api.patch<void>(`/administration/users/${id}/active`, {is_active: active}))),
    uploadAvatar: (id: number, file: File) => {
        const form = new FormData()
        form.append('avatar', file)
        return api.post<ItemResponse<User>>(`/administration/users/${id}/avatar`, form)
    },
    updateScopes: (id: number, scopes: ScopeGrant[]) =>
        // Backend Request validator wymaga `scope_id` (string) — frontend operuje na
        // ScopeGrant.id (number). Remap przy zapisie.
        api.put<ItemResponse<User>>(`/administration/users/${id}/scopes`, {
            scopes: scopes.map((s) => ({type: s.type, scope_id: String(s.id)})),
        }),
    authLogsSummary: () => api.get<ItemResponse<AuthLogsSummary>>('/administration/auth-logs'),
    resetPassword: (id: number) => api.post<void>(`/administration/users/${id}/reset-password`),
    sessions: async (userId: number) => {
        const res = await api.get<ListResponse<UserSession>>('/administration/user-sessions')
        return {data: res.data.filter((session) => session.user_id === userId)}
    },
    revokeSession: (userId: number, sessionId: string) =>
        api.delete<void>(`/administration/users/${userId}/sessions/${sessionId}`),
    activity: async (userId: number) => {
        const res = await api.get<ListResponse<UserActivityEntry>>('/administration/user-activity')
        return {data: res.data.filter((entry) => entry.user_id === userId)}
    },
}

export const rolesApi = {
    list: () => api.get<ListResponse<Role>>('/administration/roles'),
    create: (payload: RolePayload) => api.post<ItemResponse<Role>>('/administration/roles', payload),
    update: (id: number, payload: RolePayload) => api.put<ItemResponse<Role>>(`/administration/roles/${id}`, payload),
    delete: (id: number) => api.delete<void>(`/administration/roles/${id}`),
}

export const permissionsApi = {
    list: () => api.get<ItemResponse<PermissionsByModule>>('/administration/permissions'),
}
