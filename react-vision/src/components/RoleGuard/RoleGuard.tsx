import type {ReactNode} from 'react'
import {Navigate} from 'react-router-dom'

import {useAuth} from '../../features/auth/AuthContext'

interface RoleGuardProps {
    permission?: string
    anyOf?: string[]
    fallback?: ReactNode
    redirectTo?: string
    children: ReactNode
}

export function RoleGuard({permission, anyOf, fallback, redirectTo, children}: RoleGuardProps) {
    const {hasPermission, hasAnyPermission} = useAuth()

    const allowed = permission
        ? hasPermission(permission)
        : anyOf
        ? hasAnyPermission(anyOf)
        : true

    if (allowed) return <>{children}</>
    if (redirectTo) return <Navigate to={redirectTo} replace />
    return <>{fallback ?? null}</>
}
