import type {RoleRef, ScopeGrant} from '../auth/AuthContext'

export interface User {
    id: number
    name: string
    email: string
    /** Public URL from backend's `getAvatarUrlAttribute()`. Uploads go through `usersApi.uploadAvatar`. */
    avatar_url: string | null
    is_active: boolean
    roles: RoleRef[]
    scopes: ScopeGrant[]
    last_login_at: string | null
    created_at: string
}

export interface UserPayload {
    name: string
    email: string
    password?: string
    is_active: boolean
    /** Backend identifies roles by name (Spatie team-scoped role.name is unique within company). */
    role_name: string
}

export interface Role {
    id: number
    name: string
    permissions: string[]
    [key: string]: unknown
}

export interface RolePayload {
    name: string
    permissions: string[]
}

export type PermissionsByModule = Record<string, string[]>

export interface AuthLogsSummary {
    daily: {date: string; count: number}[]
    totals: {users: number; active: number; logins_last_7_days: number}
}

export interface Pagination {
    total: number
    page: number
    per_page: number
}

export interface UserSession {
    id: string
    user_id: number
    ip: string
    user_agent: string
    device: string
    location: string | null
    last_active_at: string
    created_at: string
    current: boolean
}

export type UserActivityType =
    | 'login'
    | 'logout'
    | 'password_reset'
    | 'scopes_updated'
    | 'role_changed'
    | 'model_change'

export interface UserActivityEntry {
    id: string
    user_id: number
    type: UserActivityType
    ip: string | null
    description: string
    at: string
}
