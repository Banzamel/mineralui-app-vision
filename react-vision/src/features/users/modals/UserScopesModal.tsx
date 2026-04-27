import {useEffect, useMemo, useState} from 'react'

import {MButton} from '@banzamel/mineralui-pro/controls'
import {MTreeView} from '@banzamel/mineralui-pro/data'
import type {MTreeNode} from '@banzamel/mineralui-pro/data'
import {MEmptyState} from '@banzamel/mineralui-pro/display'
import {useMToast} from '@banzamel/mineralui-pro/feedback'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MBuildingIcon} from '@banzamel/mineralui-pro/icons'
import {MInline, MStack} from '@banzamel/mineralui-pro/layout'
import {MModal} from '@banzamel/mineralui-pro/overlays'
import {MText} from '@banzamel/mineralui-pro/typography'

import {interpolate} from '../../../i18n/interpolate'
import pl from '../../../i18n/pl.json'
import type {ScopeGrant, ScopeType} from '../../auth/AuthContext'
import type {Address, Building, CameraLeaf} from '../../cameras'
import {usersApi} from '../api'
import type {User} from '../types'

interface UserScopesModalProps {
    open: boolean
    user: User | null
    buildings: Building[]
    onClose: () => void
    onSaved: () => void
}

// ── Wewnętrzny model drzewa ───────────────────────────────────────────────────
// Buildings, addresses i cameras są scalone w jednolity ScopeNode. Pozwala to
// `scopesToChecked` / `checkedToScopes` / `toTreeNodes` operować na rekurencji
// jednego kształtu zamiast na ręcznie rozpisanych pętlach per poziom.

interface ScopeNode {
    key: string             // np. "building:1"
    type: ScopeType
    id: number
    label: string
    isFolder: boolean       // building / address mają dzieci, kamera to liść
    children: ScopeNode[]
}

function scopeKey(type: ScopeType, id: number): string {
    return `${type}:${id}`
}

function cameraNode(c: CameraLeaf): ScopeNode {
    return {key: scopeKey('camera', c.id), type: 'camera', id: c.id, label: c.name, isFolder: false, children: []}
}

function addressNode(a: Address): ScopeNode {
    return {
        key: scopeKey('address', a.id),
        type: 'address',
        id: a.id,
        label: a.name,
        isFolder: true,
        children: a.cameras.map(cameraNode),
    }
}

function buildScopeTree(buildings: Building[]): ScopeNode[] {
    return buildings.map((b) => ({
        key: scopeKey('building', b.id),
        type: 'building',
        id: b.id,
        label: b.name,
        isFolder: true,
        // Płaska hierarchia: kamery directly pod root + sub-objects (addresses) z ich kamerami.
        children: [...b.cameras.map(cameraNode), ...b.addresses.map(addressNode)],
    }))
}

function toTreeNodes(nodes: ScopeNode[]): MTreeNode[] {
    return nodes.map((n) => {
        const node: MTreeNode = {id: n.key, label: n.label}
        if (n.isFolder) node.kind = 'folder'
        if (n.children.length > 0) node.children = toTreeNodes(n.children)
        return node
    })
}

/**
 * Z `[{type, id}]` (zakres usera w bazie) buduje listę kluczy do zaznaczenia
 * w drzewie. Aggregation: gdy wszystkie dzieci węzła trafiają do checked,
 * sam węzeł też jest zaznaczany — UI prezentuje to jako parent z ticki.
 */
function scopesToChecked(scopes: ScopeGrant[], roots: ScopeNode[]): string[] {
    const granted = new Set(scopes.map((s) => scopeKey(s.type, s.id)))
    const checked: string[] = []

    function walk(node: ScopeNode, parentGranted: boolean): boolean {
        // Granted explicitly OR ancestor granted → cały subtree zaznaczony.
        if (parentGranted || granted.has(node.key)) {
            checked.push(node.key)
            for (const child of node.children) walk(child, true)
            return true
        }
        // Liść który nie jest zaznaczony.
        if (node.children.length === 0) return false
        // Folder: zaznaczony tylko gdy wszystkie dzieci są zaznaczone.
        let allChildrenChecked = true
        for (const child of node.children) {
            if (!walk(child, false)) allChildrenChecked = false
        }
        if (allChildrenChecked) {
            checked.push(node.key)
            return true
        }
        return false
    }

    for (const root of roots) walk(root, false)
    return checked
}

/**
 * Odwrotność `scopesToChecked` — z listy zaznaczonych kluczy buduje minimalną
 * listę scope rows. Najwyższy zaznaczony przodek "pochłania" potomków
 * (CameraScopePolicy i tak rozwija buildingi/addresses do realnych kamer).
 */
function checkedToScopes(checked: string[], roots: ScopeNode[]): ScopeGrant[] {
    const set = new Set(checked)
    const out: ScopeGrant[] = []

    function walk(node: ScopeNode): void {
        if (set.has(node.key)) {
            out.push({type: node.type, id: node.id})
            return // wyższy poziom subsumuje dzieci
        }
        for (const child of node.children) walk(child)
    }

    for (const root of roots) walk(root)
    return out
}

export function UserScopesModal({open, user, buildings, onClose, onSaved}: UserScopesModalProps) {
    const {t} = useMI18n<typeof pl>()
    const {toast} = useMToast()
    const [checked, setChecked] = useState<string[]>([])
    const [expanded, setExpanded] = useState<string[]>([])
    const [saving, setSaving] = useState(false)

    const tree = useMemo(() => buildScopeTree(buildings), [buildings])
    const items = useMemo(() => toTreeNodes(tree), [tree])

    useEffect(() => {
        if (!open || !user) return
        setChecked(scopesToChecked(user.scopes, tree))
        setExpanded(buildings.map((b) => scopeKey('building', b.id)))
    }, [open, user, tree, buildings])

    async function handleSave() {
        if (!user) return
        setSaving(true)
        try {
            await usersApi.updateScopes(user.id, checkedToScopes(checked, tree))
            toast({
                title: t('user_scopes.saved_title'),
                message: t('user_scopes.saved_message'),
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
            title={interpolate(t('user_scopes.title'), {name: user?.name ?? ''})}
            size={'lg'}
            footer={
                <MInline justify={'between'} wrap={'wrap'}>
                    <MButton variant={'ghost'} onClick={onClose} disabled={saving}>
                        {t('user_scopes.cancel')}
                    </MButton>
                    <MButton variant={'filled'} color={'primary'} onClick={handleSave} loading={saving} disabled={saving}>
                        {t('user_scopes.save')}
                    </MButton>
                </MInline>
            }
        >
            <MStack spacing={'sm'}>
                <MText size={'sm'} tone={'muted'}>
                    {t('user_scopes.hint')}
                </MText>
                {buildings.length === 0 ? (
                    <MEmptyState icon={<MBuildingIcon />} title={t('user_scopes.empty')} size={'sm'} />
                ) : (
                    <MTreeView
                        items={items}
                        checkable
                        checked={checked}
                        onCheckedChange={setChecked}
                        expanded={expanded}
                        onExpandChange={setExpanded}
                        fileIcons={false}
                    />
                )}
            </MStack>
        </MModal>
    )
}
