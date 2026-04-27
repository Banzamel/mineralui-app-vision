import {lazy} from 'react'
import {Navigate, Route, Routes} from 'react-router-dom'

import {RoleGuard} from './components/RoleGuard'
import {RouteLoader} from './components/RouteLoader'
import {useAuth} from './features/auth/AuthContext'
import {ProtectedLayout} from './layouts/ProtectedLayout'
import {PublicLayout} from './layouts/PublicLayout'

const DashboardPage = lazy(async () => ({
    default: (await import('./pages/DashboardPage')).DashboardPage,
}))

const LoginPage = lazy(async () => ({
    default: (await import('./pages/LoginPage')).LoginPage,
}))

const UsersPage = lazy(async () => ({
    default: (await import('./pages/UsersPage')).UsersPage,
}))

const ObjectsPage = lazy(async () => ({
    default: (await import('./pages/ObjectsPage')).ObjectsPage,
}))

const CalendarPage = lazy(async () => ({
    default: (await import('./pages/CalendarPage')).CalendarPage,
}))

const InstallPage = lazy(async () => ({
    default: (await import('./pages/InstallPage')).InstallPage,
}))

function ProtectedRoute() {
    const {isReady, user} = useAuth()

    if (!isReady) {
        return <RouteLoader />
    }

    if (!user) {
        return <Navigate to={'/login'} replace />
    }

    return <ProtectedLayout />
}

function PublicOnlyRoute() {
    const {isReady, user} = useAuth()

    if (!isReady) {
        return <RouteLoader />
    }

    if (user) {
        return <Navigate to={'/'} replace />
    }

    return <LoginPage />
}

export function AppRoutes() {
    return (
        <Routes>
            <Route element={<PublicLayout />}>
                <Route path={'/login'} element={<PublicOnlyRoute />} />
                <Route path={'/install'} element={<InstallPage />} />
            </Route>
            <Route element={<ProtectedRoute />}>
                <Route path={'/'} element={<DashboardPage />} />
                <Route path={'/objects'} element={<ObjectsPage />} />
                <Route path={'/calendar'} element={<CalendarPage />} />
                <Route
                    path={'/users'}
                    element={
                        <RoleGuard permission={'users.view'} redirectTo={'/'}>
                            <UsersPage />
                        </RoleGuard>
                    }
                />
            </Route>
            <Route path={'*'} element={<Navigate to={'/'} replace />} />
        </Routes>
    )
}
