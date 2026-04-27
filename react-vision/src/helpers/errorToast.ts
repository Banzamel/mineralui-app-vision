import {useCallback} from 'react'

import {useMToast} from '@banzamel/mineralui-pro/feedback'

import {ApiError} from './api'

type ToastColor = 'info' | 'success' | 'warning' | 'error' | 'neutral' | 'primary'

interface ShowErrorOptions {
    title?: string
    fallback?: string
    color?: ToastColor
}

const DEFAULT_TITLE = 'Wystąpił błąd'
const DEFAULT_FALLBACK = 'Coś poszło nie tak. Spróbuj ponownie.'

/**
 * Wyciąga ludzki komunikat z dowolnego błędu. Priorytet:
 *  1. walidacja Laravela (`body.errors` per pole — flatten i join),
 *  2. `body.message` (standard Laravel + nasze *Request + bootstrap/app.php exception renderers),
 *  3. `body.error` (nasz Shared\Exceptions\ApiJsonException::render() zwraca tylko to pole),
 *  4. `body.exception` (stack-trace info dołączane przez Laravel dla AuthException/NotFoundException),
 *  5. `err.message` z helpers/api.ts (np. "POST /x failed (500)"),
 *  6. fallback.
 */
export function extractErrorMessage(err: unknown, fallback: string = DEFAULT_FALLBACK): string {
    if (err instanceof ApiError) {
        const body = err.body as
            | {message?: string; error?: string; exception?: string; errors?: Record<string, string[] | string>}
            | null
            | undefined
        if (body?.errors) {
            const flat = Object.values(body.errors)
                .flatMap((v) => (Array.isArray(v) ? v : [v]))
                .filter((s): s is string => typeof s === 'string' && s.length > 0)
            if (flat.length > 0) return flat.join(' ')
        }
        if (body?.message) return body.message
        if (body?.error) return body.error
        if (body?.exception) return body.exception
        return err.message || fallback
    }
    if (err instanceof Error) return err.message || fallback
    if (typeof err === 'string' && err.length > 0) return err
    return fallback
}

/**
 * Zwraca status code HTTP z ApiError (jeśli taki) — używane przy dekorowaniu tytułu toastu.
 */
function getStatus(err: unknown): number | null {
    return err instanceof ApiError ? err.status : null
}

/**
 * Hook łączący extractErrorMessage z `useMToast()` — pozwala jedną linijką pokazać
 * toast z wyciągniętym komunikatem lub obwinąć asynchroniczną akcję tak, że każde
 * rzucenie automatycznie wpadnie w toast (akcja zwróci undefined zamiast rethrowować).
 *
 * Tytuł toastu dodatkowo zawiera status HTTP (np. "Wystąpił błąd (401)") — już z tytułu
 * widać czy to problem z danymi (4xx) czy backend padł (5xx).
 */
export function useErrorToast() {
    const {toast} = useMToast()

    const showError = useCallback(
        (err: unknown, options: ShowErrorOptions = {}): void => {
            const baseTitle = options.title ?? DEFAULT_TITLE
            const status = getStatus(err)
            const title = status !== null ? `${baseTitle} (${status})` : baseTitle
            toast({
                title,
                message: extractErrorMessage(err, options.fallback),
                color: options.color ?? 'error',
            })
        },
        [toast],
    )

    const wrapAsync = useCallback(
        async <T>(fn: () => Promise<T>, options: ShowErrorOptions = {}): Promise<T | undefined> => {
            try {
                return await fn()
            } catch (err) {
                showError(err, options)
                return undefined
            }
        },
        [showError],
    )

    return {showError, wrapAsync}
}
