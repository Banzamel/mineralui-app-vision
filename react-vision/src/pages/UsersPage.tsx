import {useMemo, useState} from 'react'

import {MCardGrid} from '@banzamel/mineralui-pro/cards'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MContainer, MInline, MSection, MSimpleGrid, MStack, MTabs} from '@banzamel/mineralui-pro/layout'

import {Loading} from '../components/Loading'
import {ViewModeToggle} from '../components/ViewModeToggle'
import {camerasApi} from '../features/cameras/api'
import type {Building} from '../features/cameras/types'
import {permissionsApi, rolesApi, usersApi} from '../features/users/api'
import {RolesTable} from '../features/users/RolesTable'
import {UsersPageSkeleton} from '../features/users/UsersPageSkeleton'
import type {AuthLogsSummary, PermissionsByModule, Role, User} from '../features/users/types'
import {
    LoginsChartWidget,
    LoginsTodayWidget,
    RolesCountWidget,
    ScopedUsersWidget,
    UserCountWidget,
} from '../features/users/UsersStats'
import {UsersTable} from '../features/users/UsersTable'
import {useAsync, useViewMode} from '../helpers'
import pl from '../i18n/pl.json'

interface UsersPageData {
    users: User[]
    roles: Role[]
    permissions: PermissionsByModule
    stats: AuthLogsSummary
    buildings: Building[]
}

export function UsersPage() {
    const {t} = useMI18n<typeof pl>()
    const [tabValue, setTabValue] = useState('users')
    const [roleFilter, setRoleFilter] = useState<string | null>(null)
    const [usersView, setUsersView] = useViewMode('users-list', 'cards')
    const [rolesView, setRolesView] = useViewMode('roles-list', 'cards')

    const {data, loading, error, reload} = useAsync<UsersPageData>(async () => {
        const [usersRes, rolesRes, permsRes, statsRes, buildingsRes] = await Promise.all([
            usersApi.list(),
            rolesApi.list(),
            permissionsApi.list(),
            usersApi.authLogsSummary(),
            camerasApi.buildings(),
        ])
        return {
            users: usersRes.data,
            roles: rolesRes.data,
            permissions: permsRes.data,
            stats: statsRes.data,
            buildings: buildingsRes.data,
        }
    }, [])

    function handleShowUsersByRole(role: Role) {
        setRoleFilter(role.name)
        setTabValue('users')
    }

    const users = data?.users ?? []
    const roles = data?.roles ?? []
    const permissions = data?.permissions ?? {}
    const stats = data?.stats ?? null
    const buildings = data?.buildings ?? []

    const dailyCounts = (stats?.daily ?? []).map((d) => d.count)
    const permissionsCount = Object.values(permissions).reduce((sum, list) => sum + list.length, 0)

    const userCountsByRole = useMemo(() => {
        const map: Record<number, number> = {}
        for (const u of users) {
            // Backend może chwilowo nie zwrócić relacji roles (starsza wersja cache / race condition) —
            // defensywnie pomijamy userów bez tablicy zamiast crashować UI.
            for (const r of u.roles ?? []) {
                map[r.id] = (map[r.id] ?? 0) + 1
            }
        }
        return map
    }, [users])

    const tabs = [
        {
            value: 'users',
            label: t('users_page.tab_users'),
            content: (
                <MStack spacing={'sm'}>
                    <MInline justify={'end'}>
                        <ViewModeToggle value={usersView} onChange={setUsersView} />
                    </MInline>
                    <UsersTable
                        users={users}
                        roles={roles}
                        buildings={buildings}
                        externalRoleFilter={roleFilter}
                        viewMode={usersView}
                        onChanged={reload}
                    />
                </MStack>
            ),
        },
        {
            value: 'roles',
            label: t('users_page.tab_roles'),
            content: (
                <MStack spacing={'sm'}>
                    <MInline justify={'end'}>
                        <ViewModeToggle value={rolesView} onChange={setRolesView} />
                    </MInline>
                    <RolesTable
                        roles={roles}
                        userCounts={userCountsByRole}
                        permissionsByModule={permissions}
                        viewMode={rolesView}
                        onChanged={reload}
                        onShowUsers={handleShowUsersByRole}
                    />
                </MStack>
            ),
        },
    ]

    return (
        <MSection as={'main'} spacing={'lg'}>
            <MContainer size={'wide'}>
                <MStack spacing={'lg'}>
                    <Loading
                        loading={loading}
                        error={error}
                        onRetry={reload}
                        minHeight={400}
                        fallback={<UsersPageSkeleton />}
                    >
                        <MSimpleGrid columns={2} minItemWidth={'320px'}>
                            <MCardGrid
                                columns={2}
                                items={[
                                    {
                                        key: 'users',
                                        render: () => (
                                            <UserCountWidget
                                                total={stats?.totals.users ?? users.length}
                                                active={
                                                    stats?.totals.active ??
                                                    users.filter((u) => u.is_active).length
                                                }
                                            />
                                        ),
                                    },
                                    {
                                        key: 'logins-today',
                                        render: () => <LoginsTodayWidget daily={dailyCounts} />,
                                    },
                                    {
                                        key: 'roles',
                                        render: () => (
                                            <RolesCountWidget
                                                rolesCount={roles.length}
                                                permissionsCount={permissionsCount}
                                            />
                                        ),
                                    },
                                    {
                                        key: 'scoped',
                                        render: () => (
                                            <ScopedUsersWidget
                                                withScopes={
                                                    users.filter((u) => (u.scopes ?? []).length > 0).length
                                                }
                                                total={users.length}
                                            />
                                        ),
                                    },
                                ]}
                                renderCard={(item) => item.render()}
                            />
                            <LoginsChartWidget daily={stats?.daily ?? []} />
                        </MSimpleGrid>

                        <MTabs
                            items={tabs}
                            value={tabValue}
                            onValueChange={setTabValue}
                            variant={'underline'}
                        />
                    </Loading>
                </MStack>
            </MContainer>
        </MSection>
    )
}
