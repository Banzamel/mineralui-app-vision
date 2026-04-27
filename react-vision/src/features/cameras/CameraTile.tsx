import {MCardTile} from '@banzamel/mineralui-pro/cards'
import {MCameraIcon} from '@banzamel/mineralui-pro/icons'

import type {Camera} from './types'

interface CameraTileProps {
    camera: Camera
    onOpen: (id: number) => void
    menuItems?: {label: string; onClick?: () => void; danger?: boolean}[]
}

export function CameraTile({camera, onOpen, menuItems}: CameraTileProps) {
    return (
        <MCardTile
            title={camera.name}
            description={camera.address}
            icon={<MCameraIcon />}
            image={camera.main_photo_url ?? undefined}
            mediaFill={!!camera.main_photo_url}
            overlayPosition={'bottom'}
            color={camera.is_online ? 'success' : 'neutral'}
            onClick={() => onOpen(camera.id)}
            menuItems={menuItems}
        />
    )
}
