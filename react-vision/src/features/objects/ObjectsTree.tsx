import {useMemo} from 'react'

import {MTreeView} from '@banzamel/mineralui-pro/data'
import type {MTreeNode} from '@banzamel/mineralui-pro/data'

import type {VisionObject} from './types'

interface ObjectsTreeProps {
    roots: VisionObject[]
    selectedId: number | null
    onSelect: (id: number) => void
}

function toNode(obj: VisionObject): MTreeNode {
    return {
        id: String(obj.id),
        label: obj.name,
        kind: obj.children && obj.children.length > 0 ? 'folder' : 'file',
        children: obj.children?.map(toNode),
    }
}

export function ObjectsTree({roots, selectedId, onSelect}: ObjectsTreeProps) {
    const items = useMemo(() => roots.map(toNode), [roots])
    const expandedAll = useMemo(() => {
        const ids: string[] = []
        const walk = (node: MTreeNode) => {
            if (node.children && node.children.length > 0) {
                ids.push(node.id)
                node.children.forEach(walk)
            }
        }
        items.forEach(walk)
        return ids
    }, [items])

    return (
        <MTreeView
            items={items}
            expandable
            selectable
            defaultExpanded={expandedAll}
            selected={selectedId != null ? String(selectedId) : null}
            onSelect={(id) => onSelect(Number(id))}
            fileIcons={false}
            showLines
        />
    )
}
