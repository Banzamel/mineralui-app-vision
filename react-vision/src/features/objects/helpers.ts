import type {VisionObject} from './types'

export const MAX_OBJECT_DEPTH = 2

export function buildObjectTree(flat: VisionObject[]): VisionObject[] {
    const byId = new Map<number, VisionObject>()
    for (const obj of flat) byId.set(obj.id, {...obj, children: []})

    const roots: VisionObject[] = []
    for (const obj of byId.values()) {
        if (obj.parent_id == null) {
            roots.push(obj)
        } else {
            const parent = byId.get(obj.parent_id)
            if (parent) parent.children!.push(obj)
            else roots.push(obj)
        }
    }
    return roots
}

export function getObjectPath(flat: VisionObject[], id: number): VisionObject[] {
    const byId = new Map(flat.map((o) => [o.id, o] as const))
    const path: VisionObject[] = []
    let current = byId.get(id)
    while (current) {
        path.unshift(current)
        current = current.parent_id != null ? byId.get(current.parent_id) : undefined
    }
    return path
}

export function getDescendantIds(flat: VisionObject[], id: number): Set<number> {
    const childrenByParent = new Map<number, number[]>()
    for (const obj of flat) {
        if (obj.parent_id != null) {
            const list = childrenByParent.get(obj.parent_id) ?? []
            list.push(obj.id)
            childrenByParent.set(obj.parent_id, list)
        }
    }
    const ids = new Set<number>()
    const queue = [id]
    while (queue.length > 0) {
        const current = queue.shift()!
        const children = childrenByParent.get(current) ?? []
        for (const child of children) {
            ids.add(child)
            queue.push(child)
        }
    }
    return ids
}

export function canHaveChildren(parent: VisionObject | null): boolean {
    if (parent == null) return true
    return parent.depth < MAX_OBJECT_DEPTH
}
