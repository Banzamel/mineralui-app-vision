import {MBoxIcon, MBuildingIcon, MFolderIcon, MHomeIcon} from '@banzamel/mineralui-pro/icons'

import type {ObjectType} from './types'

interface ObjectIconProps {
    type: ObjectType
    size?: number
}

export function ObjectIcon({type, size = 20}: ObjectIconProps) {
    switch (type) {
        case 'block':
            return <MBuildingIcon size={size} />
        case 'apartment':
        case 'house':
            return <MHomeIcon size={size} />
        case 'hangar':
        case 'garage':
            return <MBoxIcon size={size} />
        default:
            return <MFolderIcon size={size} />
    }
}
