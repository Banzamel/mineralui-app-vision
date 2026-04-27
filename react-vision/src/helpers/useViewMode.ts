import {useCallback, useState} from 'react'

export type ViewMode = 'cards' | 'table'

const STORAGE_KEY = 'vision-view-modes'

function readOverrides(): Record<string, ViewMode> {
    try {
        const raw = window.localStorage.getItem(STORAGE_KEY)
        if (!raw) return {}
        const parsed = JSON.parse(raw) as unknown
        if (parsed && typeof parsed === 'object') {
            return parsed as Record<string, ViewMode>
        }
    } catch {
        // ignore
    }
    return {}
}

function writeOverrides(map: Record<string, ViewMode>) {
    if (Object.keys(map).length === 0) {
        window.localStorage.removeItem(STORAGE_KEY)
        return
    }
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(map))
}

export function useViewMode(id: string, defaultMode: ViewMode = 'cards'): [ViewMode, (mode: ViewMode) => void] {
    const [mode, setMode] = useState<ViewMode>(() => readOverrides()[id] ?? defaultMode)

    const update = useCallback(
        (next: ViewMode) => {
            setMode(next)
            const overrides = readOverrides()
            if (next === defaultMode) {
                delete overrides[id]
            } else {
                overrides[id] = next
            }
            writeOverrides(overrides)
        },
        [id, defaultMode],
    )

    return [mode, update]
}
