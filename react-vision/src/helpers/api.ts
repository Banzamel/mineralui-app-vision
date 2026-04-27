const BASE_URL = import.meta.env.VITE_API_URL ?? '/api'
const TOKEN_KEY = 'vision-auth-token'
const REFRESH_TOKEN_KEY = 'vision-refresh-token'

// Paths that live outside the /api prefix on the Laravel side (Passport oauth + broadcasting auth).
// Vite proxy has matching entries in vite.config.ts, so these are forwarded to the backend untouched.
// Installer endpointy NIE są tu — żyją pod /api/install/* żeby nie kolidowały z React route /install.
const ABSOLUTE_PREFIXES = ['/oauth/', '/broadcasting/']

export class ApiError extends Error {
    status: number
    body: unknown

    constructor(status: number, message: string, body?: unknown) {
        super(message)
        this.status = status
        this.body = body
    }
}

type Method = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'

interface RequestOptions {
    query?: Record<string, string | number | boolean | undefined | null>
    body?: unknown
    headers?: Record<string, string>
    signal?: AbortSignal
    raw?: boolean
    _retry?: boolean
}

function getToken(): string | null {
    return window.localStorage.getItem(TOKEN_KEY)
}

function getRefreshToken(): string | null {
    return window.localStorage.getItem(REFRESH_TOKEN_KEY)
}

export function setToken(token: string | null) {
    if (token) {
        window.localStorage.setItem(TOKEN_KEY, token)
    } else {
        window.localStorage.removeItem(TOKEN_KEY)
    }
}

export function setRefreshToken(token: string | null) {
    if (token) {
        window.localStorage.setItem(REFRESH_TOKEN_KEY, token)
    } else {
        window.localStorage.removeItem(REFRESH_TOKEN_KEY)
    }
}

function buildUrl(path: string, query?: RequestOptions['query']): string {
    const isAbsoluteFromRoot = ABSOLUTE_PREFIXES.some((prefix) => path.startsWith(prefix))
    const resolved = path.startsWith('http')
        ? path
        : isAbsoluteFromRoot
            ? path
            : `${BASE_URL}${path}`
    const url = new URL(resolved, window.location.origin)
    if (query) {
        for (const [key, value] of Object.entries(query)) {
            if (value !== undefined && value !== null && value !== '') {
                url.searchParams.set(key, String(value))
            }
        }
    }
    return url.pathname + url.search
}

async function parseResponse(res: Response): Promise<unknown> {
    if (res.status === 204) return null
    const text = await res.text()
    if (!text) return null
    try {
        return JSON.parse(text)
    } catch {
        return text
    }
}

// Single in-flight refresh promise — gdy kilka równoległych requestów dostanie 401 jednocześnie,
// wszystkie czekają na ten sam refresh zamiast odpalać N odświeżeń.
let refreshInFlight: Promise<boolean> | null = null

async function attemptRefresh(): Promise<boolean> {
    if (refreshInFlight) return refreshInFlight
    const refreshToken = getRefreshToken()
    if (!refreshToken) return false

    refreshInFlight = (async () => {
        try {
            const res = await fetch(buildUrl('/oauth/refresh'), {
                method: 'POST',
                headers: {'Content-Type': 'application/json', Accept: 'application/json'},
                body: JSON.stringify({refresh_token: refreshToken}),
            })
            if (!res.ok) return false
            const data = (await parseResponse(res)) as {access_token?: string; refresh_token?: string} | null
            if (!data?.access_token) return false
            setToken(data.access_token)
            if (data.refresh_token) setRefreshToken(data.refresh_token)
            return true
        } catch {
            return false
        } finally {
            refreshInFlight = null
        }
    })()

    return refreshInFlight
}

// Paths that don't require an auth token. Everything else short-circuits when no token is set
// (e.g. right after logout) so we don't spam the backend with 401 noise.
const PUBLIC_PATH_PREFIXES = ['/oauth/', '/install/', '/storage/']

function pathRequiresAuth(path: string): boolean {
    return !PUBLIC_PATH_PREFIXES.some((prefix) => path.startsWith(prefix))
}

async function request<T>(method: Method, path: string, options: RequestOptions = {}): Promise<T> {
    const url = buildUrl(path, options.query)
    const headers: Record<string, string> = {
        Accept: 'application/json',
        ...options.headers,
    }

    const token = getToken()

    // Short-circuit: no token + protected path → fail locally without hitting the network.
    // Avoids a flood of 401s after logout when in-flight effects from the unmounting protected
    // layout still try to fetch /api/notifications, /api/system/status, etc.
    if (!token && pathRequiresAuth(path)) {
        throw new ApiError(401, 'Not authenticated')
    }

    if (token) headers.Authorization = `Bearer ${token}`

    let body: BodyInit | undefined
    if (options.body instanceof FormData) {
        body = options.body
    } else if (options.body !== undefined) {
        headers['Content-Type'] = 'application/json'
        body = JSON.stringify(options.body)
    }

    const res = await fetch(url, {method, headers, body, signal: options.signal})

    // Auto-refresh na 401 — próbujemy raz, poza samym /oauth/refresh (żeby nie zapętlić).
    if (res.status === 401 && !options._retry && !path.startsWith('/oauth/refresh')) {
        const ok = await attemptRefresh()
        if (ok) {
            return request<T>(method, path, {...options, _retry: true})
        }
    }

    const data = await parseResponse(res)

    if (!res.ok) {
        const message = (data && typeof data === 'object' && 'message' in data && typeof (data as {message: unknown}).message === 'string')
            ? (data as {message: string}).message
            : `${method} ${path} failed (${res.status})`
        throw new ApiError(res.status, message, data)
    }

    return data as T
}

export const api = {
    get: <T>(path: string, options?: RequestOptions) => request<T>('GET', path, options),
    post: <T>(path: string, body?: unknown, options?: RequestOptions) => request<T>('POST', path, {...options, body}),
    put: <T>(path: string, body?: unknown, options?: RequestOptions) => request<T>('PUT', path, {...options, body}),
    patch: <T>(path: string, body?: unknown, options?: RequestOptions) => request<T>('PATCH', path, {...options, body}),
    delete: <T>(path: string, options?: RequestOptions) => request<T>('DELETE', path, options),
}

export const apiConfig = {
    baseUrl: BASE_URL,
}
