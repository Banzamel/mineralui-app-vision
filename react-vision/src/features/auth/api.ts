import {api} from '../../helpers/api'
import type {AuthUser} from './AuthContext'

interface TokenBundle {
    access_token: string
    refresh_token: string
    token_type: string
    expires_in: number
}

interface LoginResponseRaw extends TokenBundle {
    user: MeResponseShape
}

interface RefreshResponseRaw extends TokenBundle {}

interface MeResponseShape {
    id: number
    name: string
    email: string
    company_id: number
    is_active: boolean
    avatar_url: string | null
    roles: string[]
    permissions: string[]
}

interface MeEnvelope {
    data?: MeResponseShape
}

export interface LoginResult {
    user: AuthUser
    tokens: TokenBundle
}

function toAuthUser(raw: MeResponseShape, previousLoginAt: string | null = null): AuthUser {
    return {
        id: raw.id,
        company_id: raw.company_id,
        name: raw.name,
        email: raw.email,
        avatar: raw.avatar_url,
        is_active: raw.is_active,
        roles: raw.roles.map((name, idx) => ({id: idx + 1, name})),
        permissions: raw.permissions,
        scopes: [],
        last_login_at: previousLoginAt,
    }
}

export const authApi = {
    login: async (email: string, password: string): Promise<LoginResult> => {
        const res = await api.post<LoginResponseRaw>('/oauth/login', {email, password})
        return {
            user: toAuthUser(res.user, new Date().toISOString()),
            tokens: {
                access_token: res.access_token,
                refresh_token: res.refresh_token,
                token_type: res.token_type,
                expires_in: res.expires_in,
            },
        }
    },

    refresh: async (refreshToken: string): Promise<TokenBundle> => {
        const res = await api.post<RefreshResponseRaw>('/oauth/refresh', {refresh_token: refreshToken})
        return {
            access_token: res.access_token,
            refresh_token: res.refresh_token,
            token_type: res.token_type,
            expires_in: res.expires_in,
        }
    },

    logout: async (): Promise<void> => {
        try {
            await api.post('/oauth/logout', {})
        } catch {
            // backend może już być niedostępny albo token przeterminowany — log out i tak odpalamy po stronie klienta
        }
    },

    me: async (): Promise<AuthUser> => {
        const res = await api.get<MeResponseShape | MeEnvelope>('/manage/me')
        const raw = 'data' in res && res.data !== undefined ? res.data : (res as MeResponseShape)
        return toAuthUser(raw)
    },
}
