import {useEffect, useMemo, useState} from 'react'

import {MLoadMore} from '@banzamel/mineralui-pro/controls'
import {MSkeleton} from '@banzamel/mineralui-pro/feedback'
import {MStack} from '@banzamel/mineralui-pro/layout'

import {groupPhotosIntoBursts} from './groupBursts'
import type {PhotoBurst} from './groupBursts'
import {MotionPreviewImage} from './MotionPreviewImage'
import type {AlbumPhoto} from './types'

interface AlbumMotionGalleryProps {
    photos: AlbumPhoto[]
    hasMore?: boolean
    loading?: boolean
    onLoadMore?: () => void
    autoLoad?: boolean
    skeletonCount?: number
}

const COLUMNS = 2

/**
 * Detects touch-primary devices via `(hover: none)` media query — the modern, hybrid-safe
 * heuristic for "this user does not have a hover pointer". Returns true for phones/tablets,
 * false for desktops with a mouse.
 */
function useIsTouchDevice(): boolean {
    const [touch, setTouch] = useState(false)
    useEffect(() => {
        if (typeof window === 'undefined' || !window.matchMedia) return
        const mq = window.matchMedia('(hover: none)')
        setTouch(mq.matches)
        const handler = (e: MediaQueryListEvent) => setTouch(e.matches)
        mq.addEventListener('change', handler)
        return () => mq.removeEventListener('change', handler)
    }, [])
    return touch
}

function GridSkeleton({count}: {count: number}) {
    return (
        <div style={{display: 'grid', gridTemplateColumns: `repeat(${COLUMNS}, 1fr)`, gap: '12px'}}>
            {Array.from({length: count}).map((_, i) => (
                <MSkeleton
                    key={i}
                    variant={'rectangle'}
                    width={'100%'}
                    height={180}
                    radius={8}
                    animate={'shimmer'}
                />
            ))}
        </div>
    )
}

function BurstTile({burst, autoplayOnTouch}: {burst: PhotoBurst; autoplayOnTouch: boolean}) {
    if (burst.frames.length < 2) {
        // Lone photo — render as a static thumbnail so the grid stays uniform with bursts.
        return (
            <div
                style={{display: 'block', borderRadius: 8, overflow: 'hidden', aspectRatio: '4 / 3'}}
            >
                <img
                    src={burst.lead.thumbnail_url}
                    alt={burst.lead.captured_at}
                    loading={'lazy'}
                    style={{width: '100%', height: '100%', objectFit: 'cover', display: 'block'}}
                />
            </div>
        )
    }
    return (
        <MotionPreviewImage
            frames={burst.frames.map((f) => ({url: f.url, thumbnail_url: f.thumbnail_url}))}
            autoplay={autoplayOnTouch}
            alt={burst.lead.captured_at}
        />
    )
}

export function AlbumMotionGallery({
    photos,
    hasMore,
    loading,
    onLoadMore,
    autoLoad = true,
    skeletonCount = 12,
}: AlbumMotionGalleryProps) {
    const isTouch = useIsTouchDevice()
    const bursts = useMemo(() => groupPhotosIntoBursts(photos), [photos])

    const showLoadMore = typeof onLoadMore === 'function'
    const showSkeleton = loading === true && photos.length === 0

    if (showSkeleton) {
        return <GridSkeleton count={skeletonCount} />
    }

    const grid = (
        <div style={{display: 'grid', gridTemplateColumns: `repeat(${COLUMNS}, 1fr)`, gap: '12px'}}>
            {bursts.map((b) => (
                <BurstTile key={b.lead.id} burst={b} autoplayOnTouch={isTouch} />
            ))}
        </div>
    )

    if (!showLoadMore) return grid

    return (
        <MStack>
            {grid}
            <MLoadMore
                onLoadMore={onLoadMore}
                loading={loading}
                hasMore={hasMore}
                loaded={photos.length}
                auto={autoLoad}
            />
        </MStack>
    )
}
