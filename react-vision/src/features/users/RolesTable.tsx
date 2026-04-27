import {useState, type ReactNode} from 'react'

import {MCard, MCardBody, MCardGrid} from '@banzamel/mineralui-pro/cards'
import {MButton} from '@banzamel/mineralui-pro/controls'
import {MDataTable} from '@banzamel/mineralui-pro/data'
import {MBadge} from '@banzamel/mineralui-pro/feedback'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {
    MEditIcon,
    MEllipsisVerticalIcon,
    MPlusIcon,
    MTrashIcon,
    MUsersIcon,
} from '@banzamel/mineralui-pro/icons'
import {MInline, MStack} from '@banzamel/mineralui-pro/layout'
import {MDropdownItem, MDropdownMenu} from '@banzamel/mineralui-pro/overlays'
import {MHeading, MText} from '@banzamel/mineralui-pro/typography'

import type {ViewMode} from '../../helpers'
import {interpolate} from '../../i18n/interpolate'
import pl from '../../i18n/pl.json'
import {rolesApi} from './api'
import {RoleFormModal} from './modals'
import type {PermissionsByModule, Role} from './types'

const PERMISSIONS_PREVIEW = 6

interface RolesTableProps {
    roles: Role[]
    userCounts: Record<number, number>
    permissionsByModule: PermissionsByModule
    viewMode?: ViewMode
    onChanged: () => void
    onShowUsers: (role: Role) => void
}

export function RolesTable({
    roles,
    userCounts,
    permissionsByModule,
    viewMode = 'cards',
    onChanged,
    onShowUsers,
}: RolesTableProps) {
    const {t} = useMI18n<typeof pl>()
    const [editingRole, setEditingRole] = useState<Role | null>(null)
    const [roleModalOpen, setRoleModalOpen] = useState(false)

    function openCreate() {
        setEditingRole(null)
        setRoleModalOpen(true)
    }

    function openEdit(role: Role) {
        setEditingRole(role)
        setRoleModalOpen(true)
    }

    async function handleDelete(role: Role) {
        await rolesApi.delete(role.id)
        onChanged()
    }

    function renderActionsMenu(row: Role): ReactNode {
        return (
            <MDropdownMenu
                placement={'bottom-end'}
                trigger={
                    <MButton
                        variant={'ghost'}
                        size={'sm'}
                        startIcon={<MEllipsisVerticalIcon />}
                        aria-label={t('roles_page.col_actions')}
                    />
                }
            >
                <MDropdownItem
                    icon={<MUsersIcon />}
                    label={t('roles_page.action_show_users')}
                    onClick={() => onShowUsers(row)}
                />
                <MDropdownItem
                    icon={<MEditIcon />}
                    label={t('roles_page.action_edit')}
                    onClick={() => openEdit(row)}
                />
                <MDropdownItem
                    icon={<MTrashIcon />}
                    color={'error'}
                    label={t('roles_page.action_delete')}
                    onClick={() => handleDelete(row)}
                />
            </MDropdownMenu>
        )
    }

    const columns = [
        {
            key: 'name',
            label: t('roles_page.col_name'),
            sortable: true,
            render: (_: unknown, row: Role) => <MText size={'sm'}>{row.name}</MText>,
        },
        {
            key: 'permissions',
            label: t('roles_page.col_permissions'),
            render: (_: unknown, row: Role) => (
                <MBadge color={'primary'} size={'sm'}>
                    {interpolate(t('roles_page.permissions_count'), {count: row.permissions.length})}
                </MBadge>
            ),
        },
        {
            key: 'users',
            label: t('roles_page.col_users'),
            sortable: true,
            render: (_: unknown, row: Role) => (
                <MBadge color={'neutral'} size={'sm'}>
                    {interpolate(t('roles_page.users_count'), {count: userCounts[row.id] ?? 0})}
                </MBadge>
            ),
        },
        {
            key: 'actions',
            label: t('roles_page.col_actions'),
            align: 'right' as const,
            width: 80,
            render: (_: unknown, row: Role) => <MInline justify={'end'}>{renderActionsMenu(row)}</MInline>,
        },
    ]

    const sortKeys = [{key: 'name' as const, label: t('roles_page.col_name')}]

    const renderRoleCard = (row: Role) => {
        const extra = row.permissions.length - PERMISSIONS_PREVIEW
        return (
            <MCard>
                <MCardBody>
                    <MStack spacing={'sm'}>
                        <MInline justify={'between'} align={'start'} wrap={'nowrap'}>
                            <MStack spacing={'xs'}>
                                <MHeading level={5}>{row.name}</MHeading>
                                <MInline wrap={'wrap'}>
                                    <MBadge color={'primary'} size={'sm'}>
                                        {interpolate(t('roles_page.permissions_count'), {
                                            count: row.permissions.length,
                                        })}
                                    </MBadge>
                                    <MBadge color={'neutral'} size={'sm'} icon={<MUsersIcon size={12} />}>
                                        {interpolate(t('roles_page.users_count'), {
                                            count: userCounts[row.id] ?? 0,
                                        })}
                                    </MBadge>
                                </MInline>
                            </MStack>
                            {renderActionsMenu(row)}
                        </MInline>

                        {row.permissions.length > 0 && (
                            <MInline wrap={'wrap'}>
                                {row.permissions.slice(0, PERMISSIONS_PREVIEW).map((perm) => (
                                    <MBadge key={perm} color={'neutral'} size={'xs'}>
                                        {perm}
                                    </MBadge>
                                ))}
                                {extra > 0 && (
                                    <MBadge color={'neutral'} size={'xs'}>
                                        +{extra}
                                    </MBadge>
                                )}
                            </MInline>
                        )}
                    </MStack>
                </MCardBody>
            </MCard>
        )
    }

    return (
        <MStack spacing={'md'}>
            <MInline justify={'end'}>
                <MButton variant={'filled'} color={'primary'} startIcon={<MPlusIcon />} onClick={openCreate}>
                    {t('users_page.add_role')}
                </MButton>
            </MInline>

            {viewMode === 'table' ? (
                <MDataTable<Role>
                    columns={columns}
                    data={roles}
                    rowKey={'id'}
                    sortable
                    pagination
                    pageSize={10}
                    emptyText={t('roles_page.empty')}
                />
            ) : (
                <MCardGrid<Role>
                    items={roles}
                    renderCard={renderRoleCard}
                    columns={2}
                    searchable
                    searchKeys={['name']}
                    sortable
                    sortKeys={sortKeys}
                    pagination
                    pageSize={12}
                    emptyMessage={t('roles_page.empty')}
                />
            )}

            <RoleFormModal
                open={roleModalOpen}
                role={editingRole}
                permissionsByModule={permissionsByModule}
                onClose={() => setRoleModalOpen(false)}
                onSaved={onChanged}
            />
        </MStack>
    )
}
