import {useEffect, useMemo, useState} from 'react'

import {MButton} from '@banzamel/mineralui-pro/controls'
import {MTreeView} from '@banzamel/mineralui-pro/data'
import type {MTreeNode} from '@banzamel/mineralui-pro/data'
import {useMToast} from '@banzamel/mineralui-pro/feedback'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MInput} from '@banzamel/mineralui-pro/inputs'
import {MInline, MStack} from '@banzamel/mineralui-pro/layout'
import {MModal} from '@banzamel/mineralui-pro/overlays'
import {MHeading} from '@banzamel/mineralui-pro/typography'

import pl from '../../../i18n/pl.json'
import {rolesApi} from '../api'
import type {PermissionsByModule, Role, RolePayload} from '../types'

interface RoleFormModalProps {
    open: boolean
    role: Role | null
    permissionsByModule: PermissionsByModule
    onClose: () => void
    onSaved: () => void
}

const MODULE_PREFIX = 'module:'

function permissionsToChecked(perms: string[], byModule: PermissionsByModule): string[] {
    const granted = new Set(perms)
    const checked = new Set(perms)
    for (const [moduleName, modulePerms] of Object.entries(byModule)) {
        if (modulePerms.length > 0 && modulePerms.every((p) => granted.has(p))) {
            checked.add(`${MODULE_PREFIX}${moduleName}`)
        }
    }
    return Array.from(checked)
}

function checkedToPermissions(checked: string[]): string[] {
    return checked.filter((id) => !id.startsWith(MODULE_PREFIX))
}

export function RoleFormModal({open, role, permissionsByModule, onClose, onSaved}: RoleFormModalProps) {
    const {t} = useMI18n<typeof pl>()
    const {toast} = useMToast()
    const [name, setName] = useState('')
    const [checked, setChecked] = useState<string[]>([])
    const [expanded, setExpanded] = useState<string[]>([])
    const [saving, setSaving] = useState(false)

    useEffect(() => {
        if (!open) return
        setName(role?.name ?? '')
        setChecked(permissionsToChecked(role?.permissions ?? [], permissionsByModule))
        setExpanded(Object.keys(permissionsByModule).map((m) => `${MODULE_PREFIX}${m}`))
    }, [open, role, permissionsByModule])

    const items = useMemo<MTreeNode[]>(
        () =>
            Object.entries(permissionsByModule).map(([moduleName, perms]) => ({
                id: `${MODULE_PREFIX}${moduleName}`,
                label: moduleName.charAt(0).toUpperCase() + moduleName.slice(1),
                kind: 'folder',
                children: perms.map((p) => ({id: p, label: p})),
            })),
        [permissionsByModule],
    )

    async function handleSave() {
        setSaving(true)
        try {
            const payload: RolePayload = {name, permissions: checkedToPermissions(checked)}
            if (role) await rolesApi.update(role.id, payload)
            else await rolesApi.create(payload)
            toast({
                title: t('role_form.saved_title'),
                message: t('role_form.saved_message'),
                color: 'success',
            })
            onSaved()
            onClose()
        } finally {
            setSaving(false)
        }
    }

    return (
        <MModal
            open={open}
            onClose={onClose}
            title={role ? t('role_form.title_edit') : t('role_form.title_create')}
            size={'lg'}
            footer={
                <MInline justify={'between'} wrap={'wrap'}>
                    <MButton variant={'ghost'} onClick={onClose} disabled={saving}>
                        {t('role_form.cancel')}
                    </MButton>
                    <MButton variant={'filled'} color={'primary'} onClick={handleSave} loading={saving} disabled={saving}>
                        {t('role_form.save')}
                    </MButton>
                </MInline>
            }
        >
            <MStack spacing={'md'}>
                <MInput
                    label={t('role_form.name')}
                    value={name}
                    onChange={(event) => setName(event.target.value)}
                    fullWidth
                />
                <MStack spacing={'sm'}>
                    <MHeading level={5}>{t('role_form.permissions')}</MHeading>
                    <MTreeView
                        items={items}
                        checkable
                        checked={checked}
                        onCheckedChange={setChecked}
                        expanded={expanded}
                        onExpandChange={setExpanded}
                        fileIcons={false}
                    />
                </MStack>
            </MStack>
        </MModal>
    )
}
