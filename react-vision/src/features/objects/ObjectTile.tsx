import {MCardTile} from '@banzamel/mineralui-pro/cards'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'

import pl from '../../i18n/pl.json'
import {ObjectIcon} from './ObjectIcon'
import type {VisionObject} from './types'

interface ObjectTileProps {
    object: VisionObject
    onOpen: (id: number) => void
    menuItems?: {label: string; onClick?: () => void; danger?: boolean}[]
}

export function ObjectTile({object, onOpen, menuItems}: ObjectTileProps) {
    const {t} = useMI18n<typeof pl>()
    const typeLabel = t(`object_type.${object.type}`)
    const meta = (object.address ?? '').trim() || (object.description ?? '').trim() || t('objects_dashboard.no_meta')

    return (
        <MCardTile
            title={object.name}
            description={`${typeLabel} · ${meta}`}
            icon={<ObjectIcon type={object.type} />}
            image={object.main_photo_url ?? undefined}
            mediaFill={!!object.main_photo_url}
            overlayPosition={'bottom'}
            onClick={() => onOpen(object.id)}
            menuItems={menuItems}
        />
    )
}
