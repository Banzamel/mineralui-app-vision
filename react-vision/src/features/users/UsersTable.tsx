import {useEffect, useMemo, useState, type ReactNode} from 'react'

import {MCard, MCardBody, MCardGrid} from '@banzamel/mineralui-pro/cards'
import {MButton} from '@banzamel/mineralui-pro/controls'
import {MDataTable} from '@banzamel/mineralui-pro/data'
import {MBadge, useMToast} from '@banzamel/mineralui-pro/feedback'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {
    MChevronDownIcon,
    MClockIcon,
    MEditIcon,
    MEllipsisVerticalIcon,
    MEyeIcon,
    MEyeOffIcon,
    MLockIcon,
    MPlusIcon,
    MRefreshIcon,
    MTrashIcon,
} from '@banzamel/mineralui-pro/icons'
import {MInline, MStack} from '@banzamel/mineralui-pro/layout'
import {MAvatar} from '@banzamel/mineralui-pro/media'
import {MDropdownItem, MDropdownMenu} from '@banzamel/mineralui-pro/overlays'
import {MText} from '@banzamel/mineralui-pro/typography'

import {formatDateTime} from '../../helpers/format'
import type {ViewMode} from '../../helpers'
import {interpolate} from '../../i18n/interpolate'
import pl from '../../i18n/pl.json'
import type {Building} from '../cameras/types'
import {usersApi} from './api'
import {UserActivityModal, UserFormModal, UserScopesModal} from './modals'
import type {Role, User} from './types'

interface UserRow extends User {
    role_name: string
    status_label: string
    [key: string]: unknown
}

interface UsersTableProps {
    users: User[]
    roles: Role[]
    buildings: Building[]
    externalRoleFilter?: string | null
    viewMode?: ViewMode
    onChanged: () => void
}

export function UsersTable({
    users,
    roles,
    buildings,
    externalRoleFilter,
    viewMode = 'cards',
    onChanged,
}: UsersTableProps) {
    const {t} = useMI18n<typeof pl>()
    const {toast} = useMToast()

    const [selectedKeys, setSelectedKeys] = useState<string[]>([])
    const [filters, setFilters] = useState<Record<string, string[]>>({})

    const [editingUser, setEditingUser] = useState<User | null>(null)
    const [scopesUser, setScopesUser] = useState<User | null>(null)
    const [activityUser, setActivityUser] = useState<User | null>(null)
    const [userModalOpen, setUserModalOpen] = useState(false)
    const [scopesModalOpen, setScopesModalOpen] = useState(false)
    const [activityModalOpen, setActivityModalOpen] = useState(false)

    const statusActive = t('users_page.status_active')
    const statusInactive = t('users_page.status_inactive')

    const rows = useMemo<UserRow[]>(
        () =>
            users.map((u) => ({
                ...u,
                role_name: u.roles[0]?.name ?? '',
                status_label: u.is_active ? statusActive : statusInactive,
            })),
        [users, statusActive, statusInactive],
    )

    useEffect(() => {
        if (!externalRoleFilter) return
        setFilters((prev) => ({...prev, role_name: [externalRoleFilter]}))
    }, [externalRoleFilter])

    function openCreateUser() {
        setEditingUser(null)
        setUserModalOpen(true)
    }

    function openEditUser(user: User) {
        setEditingUser(user)
        setUserModalOpen(true)
    }

    function openScopes(user: User) {
        setScopesUser(user)
        setScopesModalOpen(true)
    }

    function openActivity(user: User) {
        setActivityUser(user)
        setActivityModalOpen(true)
    }

    async function handleResetPassword(user: User) {
        await usersApi.resetPassword(user.id)
        toast({
            title: t('users_page.password_reset_toast_title'),
            message: t('users_page.password_reset_toast_message'),
            color: 'success',
        })
    }

    async function handleDelete(user: User) {
        await usersApi.delete(user.id)
        onChanged()
    }

    async function handleBulkDelete() {
        await usersApi.bulkDelete(selectedKeys.map(Number))
        setSelectedKeys([])
        onChanged()
    }

    async function handleBulkSetActive(active: boolean) {
        await usersApi.bulkSetActive(selectedKeys.map(Number), active)
        setSelectedKeys([])
        onChanged()
    }

    function renderActionsMenu(row: User): ReactNode {
        return (
            <MDropdownMenu
                placement={'bottom-end'}
                trigger={
                    <MButton
                        variant={'ghost'}
                        size={'sm'}
                        startIcon={<MEllipsisVerticalIcon />}
                        aria-label={t('users_page.col_actions')}
                    />
                }
            >
                <MDropdownItem
                    icon={<MEditIcon />}
                    label={t('users_page.action_edit')}
                    onClick={() => openEditUser(row)}
                />
                <MDropdownItem
                    icon={<MLockIcon />}
                    label={t('users_page.action_scopes')}
                    onClick={() => openScopes(row)}
                />
                <MDropdownItem
                    icon={<MClockIcon />}
                    label={t('users_page.action_activity')}
                    onClick={() => openActivity(row)}
                />
                <MDropdownItem
                    icon={<MRefreshIcon />}
                    label={t('users_page.action_reset_password')}
                    onClick={() => handleResetPassword(row)}
                />
                <MDropdownItem
                    icon={<MTrashIcon />}
                    color={'error'}
                    label={t('users_page.action_delete')}
                    onClick={() => handleDelete(row)}
                />
            </MDropdownMenu>
        )
    }

    const columns = [
        {
            key: 'name',
            label: t('users_page.col_user'),
            sortable: true,
            render: (_: unknown, row: UserRow) => (
                <MInline align={'center'}>
                    <MAvatar src={row.avatar_url ?? undefined} name={row.name} size={'sm'} />
                    <MStack spacing={'none'}>
                        <MInline align={'center'}>
                            <MText size={'sm'}>{row.name}</MText>
                            {(row.scopes ?? []).length === 0 && (
                                <MBadge color={'warning'} size={'xs'} title={t('users_page.no_scopes_tooltip')}>
                                    {t('users_page.no_scopes_badge')}
                                </MBadge>
                            )}
                        </MInline>
                        <MText tone={'muted'} size={'xs'}>
                            {row.email}
                        </MText>
                    </MStack>
                </MInline>
            ),
        },
        {
            key: 'role_name',
            label: t('users_page.col_roles'),
            render: (_: unknown, row: UserRow) => (
                <MInline wrap={'wrap'}>
                    {row.roles.map((role) => (
                        <MBadge key={role.id} color={'primary'} size={'sm'}>
                            {role.name}
                        </MBadge>
                    ))}
                </MInline>
            ),
        },
        {
            key: 'status_label',
            label: t('users_page.col_status'),
            render: (_: unknown, row: UserRow) => (
                <MBadge color={row.is_active ? 'success' : 'neutral'} size={'sm'}>
                    {row.status_label}
                </MBadge>
            ),
        },
        {
            key: 'last_login_at',
            label: t('users_page.col_last_login'),
            sortable: true,
            render: (_: unknown, row: UserRow) => (
                <MText size={'sm'} tone={row.last_login_at ? 'default' : 'muted'}>
                    {formatDateTime(row.last_login_at, t('users_page.never'))}
                </MText>
            ),
        },
        {
            key: 'actions',
            label: t('users_page.col_actions'),
            align: 'right' as const,
            width: 80,
            render: (_: unknown, row: UserRow) => <MInline justify={'end'}>{renderActionsMenu(row)}</MInline>,
        },
    ]

    const filterKeys = [
        {key: 'role_name' as const, label: t('users_page.filter_role_label'), options: roles.map((r) => r.name)},
        {
            key: 'status_label' as const,
            label: t('users_page.filter_status_label'),
            options: [statusActive, statusInactive],
        },
    ]

    const sortKeys = [
        {key: 'name' as const, label: t('users_page.col_user')},
        {key: 'last_login_at' as const, label: t('users_page.col_last_login')},
    ]

    const renderUserCard = (row: UserRow) => (
        <MCard>
            <MCardBody>
                <MInline justify={'between'} align={'center'} wrap={'wrap'}>
                    <MInline align={'center'} wrap={'wrap'}>
                        <MInline align={'center'}>
                            <MAvatar src={row.avatar_url ?? undefined} name={row.name} size={'md'} />
                            <MStack spacing={'none'}>
                                <MText size={'sm'} weight={'semibold'}>
                                    {row.name}
                                </MText>
                                <MText tone={'muted'} size={'xs'}>
                                    {row.email}
                                </MText>
                            </MStack>
                        </MInline>

                        <MInline wrap={'wrap'}>
                            {row.roles.map((role) => (
                                <MBadge key={role.id} color={'primary'} size={'sm'}>
                                    {role.name}
                                </MBadge>
                            ))}
                            {(row.scopes ?? []).length === 0 && (
                                <MBadge color={'warning'} size={'sm'} title={t('users_page.no_scopes_tooltip')}>
                                    {t('users_page.no_scopes_badge')}
                                </MBadge>
                            )}
                        </MInline>

                        <MInline align={'center'}>
                            <MClockIcon size={14} />
                            <MText size={'xs'} tone={'muted'}>
                                {formatDateTime(row.last_login_at, t('users_page.never'))}
                            </MText>
                        </MInline>
                    </MInline>

                    <MInline align={'center'}>
                        <MBadge color={row.is_active ? 'success' : 'neutral'} size={'sm'}>
                            {row.status_label}
                        </MBadge>
                        {renderActionsMenu(row)}
                    </MInline>
                </MInline>
            </MCardBody>
        </MCard>
    )

    return (
        <MStack spacing={'md'}>
            <MInline justify={'end'} align={'center'}>
                {viewMode === 'table' && selectedKeys.length > 0 && (
                    <MDropdownMenu
                        trigger={
                            <MButton variant={'outlined'} endIcon={<MChevronDownIcon />}>
                                {interpolate(t('users_page.bulk_menu'), {count: selectedKeys.length})}
                            </MButton>
                        }
                        placement={'bottom-end'}
                    >
                        <MDropdownItem
                            icon={<MEyeIcon />}
                            label={t('users_page.bulk_activate')}
                            onClick={() => handleBulkSetActive(true)}
                        />
                        <MDropdownItem
                            icon={<MEyeOffIcon />}
                            label={t('users_page.bulk_deactivate')}
                            onClick={() => handleBulkSetActive(false)}
                        />
                        <MDropdownItem
                            icon={<MTrashIcon />}
                            color={'error'}
                            label={t('users_page.bulk_delete')}
                            onClick={handleBulkDelete}
                        />
                    </MDropdownMenu>
                )}
                <MButton variant={'filled'} color={'primary'} startIcon={<MPlusIcon />} onClick={openCreateUser}>
                    {t('users_page.add_user')}
                </MButton>
            </MInline>

            {viewMode === 'table' ? (
                <MDataTable<UserRow>
                    columns={columns}
                    data={rows}
                    rowKey={'id'}
                    selectable
                    sortable
                    filterable
                    pagination
                    pageSize={10}
                    searchKeys={['name', 'email']}
                    filterKeys={filterKeys}
                    filters={filters}
                    onFiltersChange={setFilters}
                    selectedKeys={selectedKeys}
                    onSelectionChange={setSelectedKeys}
                    emptyText={t('users_page.empty')}
                />
            ) : (
                <MCardGrid<UserRow>
                    items={rows}
                    renderCard={renderUserCard}
                    columns={1}
                    searchable
                    searchKeys={['name', 'email']}
                    searchPlaceholder={t('users_page.search_placeholder')}
                    filterable
                    filterKeys={filterKeys}
                    filters={filters}
                    onFiltersChange={setFilters}
                    sortable
                    sortKeys={sortKeys}
                    pagination
                    pageSize={10}
                    emptyMessage={t('users_page.empty')}
                />
            )}

            <UserFormModal
                open={userModalOpen}
                user={editingUser}
                roles={roles}
                onClose={() => setUserModalOpen(false)}
                onSaved={onChanged}
            />
            <UserScopesModal
                open={scopesModalOpen}
                user={scopesUser}
                buildings={buildings}
                onClose={() => setScopesModalOpen(false)}
                onSaved={onChanged}
            />
            <UserActivityModal
                open={activityModalOpen}
                user={activityUser}
                onClose={() => setActivityModalOpen(false)}
            />
        </MStack>
    )
}
