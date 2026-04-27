import {useNavigate} from 'react-router-dom'

import {MCard, MCardBody, MCardTile} from '@banzamel/mineralui-pro/cards'
import {MCameraIcon} from '@banzamel/mineralui-pro/icons'
import {MSimpleGrid} from '@banzamel/mineralui-pro/layout'

import type {Camera} from '../cameras'

interface CameraPreviewsWidgetProps {
    cameras: Camera[]
}

export function CameraPreviewsWidget({cameras}: CameraPreviewsWidgetProps) {
    const navigate = useNavigate()

    return (
        <MCard>
            <MCardBody>
                <MSimpleGrid columns={2} minItemWidth={'220px'}>
                    {cameras.map((camera) => (
                        <MCardTile
                            key={camera.id}
                            title={camera.name}
                            description={camera.address}
                            icon={<MCameraIcon />}
                            image={camera.main_photo_url ?? undefined}
                            mediaFill={!!camera.main_photo_url}
                            overlayPosition={'bottom'}
                            color={camera.is_online ? 'success' : 'neutral'}
                            onClick={() => navigate(`/objects?camera=${camera.id}`)}
                        />
                    ))}
                </MSimpleGrid>
            </MCardBody>
        </MCard>
    )
}
