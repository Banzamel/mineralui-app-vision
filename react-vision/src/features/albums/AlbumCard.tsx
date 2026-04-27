import {MCardTile} from '@banzamel/mineralui-pro/cards'
import {MCalendarIcon} from '@banzamel/mineralui-pro/icons'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'

import {formatDateTime} from '../../helpers'
import pl from '../../i18n/pl.json'
import {interpolate} from '../../i18n/interpolate'
import type {Album} from './types'

interface AlbumCardProps {
    album: Album
    cameraName?: string
    onOpen: (id: number) => void
    menuItems?: {label: string; onClick?: () => void; danger?: boolean}[]
}

export function AlbumCard({album, cameraName, onOpen, menuItems}: AlbumCardProps) {
    const {t} = useMI18n<typeof pl>()
    const title = cameraName ? `${cameraName} · ${album.date}` : album.date
    const description = interpolate(t('albums.photos_count'), {count: album.photos_count})

    return (
        <MCardTile
            title={title}
            description={description}
            icon={<MCalendarIcon />}
            image={album.cover_photo}
            mediaFill
            overlayPosition={'bottom'}
            onClick={() => onOpen(album.id)}
            aria-label={formatDateTime(album.date)}
            menuItems={menuItems}
        />
    )
}
