import type {AlbumPhoto} from './types'

export interface PhotoBurst {
    /** Lead frame — rendered as the static thumbnail when the burst is not playing. */
    lead: AlbumPhoto
    /** All frames in the burst, chronological order. Single-photo entries have length 1. */
    frames: AlbumPhoto[]
}

const DEFAULT_GAP_SEC = 5

/**
 * Groups consecutive photos by their `captured_at` timestamps.
 * Frames captured within `gapSec` of the previous frame end up in the same burst.
 * Lone photos (no neighbour within the window) come back as single-frame entries.
 *
 * The cameras emit ~5 frames at 2–3 s spacing per motion event; gapSec = 5 keeps
 * the grouping tight against neighbouring events while leaving slack for clock
 * drift / network delay between frames of the same burst.
 */
export function groupPhotosIntoBursts(
    photos: AlbumPhoto[],
    gapSec: number = DEFAULT_GAP_SEC,
): PhotoBurst[] {
    if (photos.length === 0) return []

    const sorted = [...photos].sort(
        (a, b) => new Date(a.captured_at).getTime() - new Date(b.captured_at).getTime(),
    )

    const bursts: AlbumPhoto[][] = []
    let current: AlbumPhoto[] = [sorted[0]!]
    for (let i = 1; i < sorted.length; i++) {
        const prev = sorted[i - 1]!
        const curr = sorted[i]!
        const diffSec = (new Date(curr.captured_at).getTime() - new Date(prev.captured_at).getTime()) / 1000
        if (diffSec <= gapSec) {
            current.push(curr)
        } else {
            bursts.push(current)
            current = [curr]
        }
    }
    bursts.push(current)

    return bursts.map((frames) => ({lead: frames[0]!, frames}))
}
