import {useMemo} from 'react'

import {MLoadMore} from '@banzamel/mineralui-pro/controls'
import {MSkeleton} from '@banzamel/mineralui-pro/feedback'
import {MGallery} from '@banzamel/mineralui-pro/media'
import type {MGalleryItem} from '@banzamel/mineralui-pro/media'
import {MStack} from '@banzamel/mineralui-pro/layout'

import {formatDateTime} from '../../helpers'
import type {AlbumPhoto} from './types'

interface AlbumGalleryProps {
    photos: AlbumPhoto[]
    columns?: 2 | 3 | 4 | 5 | 6
    hasMore?: boolean
    loading?: boolean
    onLoadMore?: () => void
    autoLoad?: boolean
    skeletonCount?: number
}

function GallerySkeleton({columns, count}: {columns: number; count: number}) {
    return (
        <div
            style={{
                display: 'grid',
                gridTemplateColumns: `repeat(${columns}, 1fr)`,
                gap: '12px',
            }}
        >
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

export function AlbumGallery({
    photos,
    columns = 3,
    hasMore,
    loading,
    onLoadMore,
    autoLoad = true,
    skeletonCount = 12,
}: AlbumGalleryProps) {
    const items = useMemo<MGalleryItem[]>(
        () =>
            photos.map((p) => ({
                src: p.url,
                thumbnail: p.thumbnail_url,
                alt: p.captured_at,
                caption: formatDateTime(p.captured_at),
            })),
        [photos],
    )

    const showLoadMore = typeof onLoadMore === 'function'
    // Mount-time placeholder so the page is not visibly empty during the first network round-trip.
    const showSkeleton = loading === true && photos.length === 0

    if (showSkeleton) {
        return <GallerySkeleton columns={columns} count={skeletonCount} />
    }

    if (!showLoadMore) {
        return <MGallery items={items} columns={columns} rounded preview hoverEffect={'zoom-dim'} />
    }

    return (
        <MStack>
            <MGallery items={items} columns={columns} rounded preview hoverEffect={'zoom-dim'} />
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
