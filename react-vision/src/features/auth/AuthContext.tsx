import {createContext, useCallback, useContext, useEffect, useMemo, useState, type ReactNode} from 'react'

import {setRefreshToken, setToken} from '../../helpers/api'
import {authApi} from './api'

export type ScopeType = 'building' | 'address' | 'camera'

export interface ScopeGrant {
    type: ScopeType
    id: number
}

export interface RoleRef {
    id: number
    name: string
}

export interface AuthUser {
    id: number
    company_id: number
    name: string
    email: string
    avatar: string | null
    is_active: boolean
    roles: RoleRef[]
    permissions: string[]
    scopes: ScopeGrant[]
    last_login_at: string | null
}

interface LoginPayload {
    email: string
    password: string
}

interface AuthContextValue {
    isReady: boolean
    user: AuthUser | null
    login: (payload: LoginPayload) => Promise<void>
    logout: () => Promise<void>
    refreshMe: () => Promise<void>
    hasPermission: (permission: string) => boolean
    hasAnyPermission: (permissions: string[]) => boolean
}

const AUTH_STORAGE_KEY = 'vision-auth-user'

const AuthContext = createContext<AuthContextValue | undefined>(undefined)

function persist(next: AuthUser | null) {
    if (next) {
        window.localStorage.setItem(AUTH_STORAGE_KEY, JSON.stringify(next))
    } else {
        window.localStorage.removeItem(AUTH_STORAGE_KEY)
    }
}

export function AuthProvider({children}: {children: ReactNode}) {
    const [isReady, setIsReady] = useState(false)
    const [user, setUser] = useState<AuthUser | null>(null)

    useEffect(() => {
        const raw = window.localStorage.getItem(AUTH_STORAGE_KEY)
        if (raw) {
            try {
                setUser(JSON.parse(raw) as AuthUser)
            } catch {
                window.localStorage.removeItem(AUTH_STORAGE_KEY)
            }
        }
        setIsReady(true)
    }, [])

    const refreshMe = useCallback(async () => {
        const fresh = await authApi.me()
        setUser(fresh)
        persist(fresh)
    }, [])

    const login = useCallback(async ({email, password}: LoginPayload) => {
        const {user: freshUser, tokens} = await authApi.login(email, password)
        setToken(tokens.access_token)
        setRefreshToken(tokens.refresh_token)
        setUser(freshUser)
        persist(freshUser)
    }, [])

    const logout = useCallback(async () => {
        // Fire /oauth/logout while the bearer is still in localStorage — api.ts reads
        // the token synchronously when building headers, so clearing it first would
        // strip the Authorization header and the backend would 401. We don't await:
        // server-side revocation is best-effort and shouldn't block the UI.
        // Tokens cleared right after — subsequent in-flight requests from the
        // unmounting protected layout get short-circuited by api.ts (no token + auth path).
        authApi.logout().catch(() => {})
        setToken(null)
        setRefreshToken(null)
        setUser(null)
        persist(null)
    }, [])

    const value = useMemo<AuthContextValue>(() => {
        const permissions = new Set(user?.permissions ?? [])
        return {
            isReady,
            user,
            login,
            logout,
            refreshMe,
            hasPermission: (p: string) => permissions.has(p),
            hasAnyPermission: (list: string[]) => list.some((p) => permissions.has(p)),
        }
    }, [isReady, user, login, logout, refreshMe])

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth() {
    const context = useContext(AuthContext)
    if (!context) {
        throw new Error('useAuth must be used within AuthProvider')
    }
    return context
}
