export type NotificationSeverity = 'info' | 'success' | 'warning' | 'error'

export type NotificationType =
    | 'user_login'
    | 'album_created'
    | 'motion_detected'
    | 'camera_offline'
    | 'camera_online'
    | 'backup_completed'
    | 'backup_failed'
    | 'storage_warning'
    | 'album_shared'
    | 'system_alert'
    | 'info'

export interface Notification {
    id: string
    type: NotificationType
    severity: NotificationSeverity
    /** EN fallback string — used when `data`/i18n key is absent or for Web Push payload. */
    title: string
    /** EN fallback string — same fallback role as `title`. */
    message: string
    /**
     * Structured payload for frontend i18n rendering. Keys depend on `type`:
     *  - `user_login`      → `{actor_name}`
     *  - `album_created`   → `{date, camera_name, album_id}`
     * When present, frontend interpolates the i18n template
     * `notifications_center.types.<type>.{title,message}` with these values.
     */
    data: Record<string, unknown> | null
    link: string | null
    read: boolean
    created_at: string
}

export interface UnreadCount {
    count: number
}

export type ActivityType =
    | 'login'
    | 'logout'
    | 'password_changed'
    | 'avatar_changed'
    | 'profile_updated'
    | 'album_created'
    | 'photo_uploaded'
    | 'scopes_updated'

export interface ActivityEntry {
    id: string
    type: ActivityType
    ip: string | null
    description: string
    at: string
}

export interface SystemStatus {
    disk: {
        used_bytes: number
        total_bytes: number
        percent: number
    }
    version: string
}
