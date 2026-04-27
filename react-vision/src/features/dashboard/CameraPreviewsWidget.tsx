import {useNavigate} from 'react-router-dom'

import {MCard, MCardBody, MCardHeader, MCardTile} from '@banzamel/mineralui-pro/cards'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MCameraIcon} from '@banzamel/mineralui-pro/icons'
import {MInline, MSimpleGrid, MStack} from '@banzamel/mineralui-pro/layout'
import {MHeading, MText} from '@banzamel/mineralui-pro/typography'

import type {Camera} from '../cameras'
import pl from '../../i18n/pl.json'

interface CameraPreviewsWidgetProps {
    cameras: Camera[]
}

export function CameraPreviewsWidget({cameras}: CameraPreviewsWidgetProps) {
    const {t} = useMI18n<typeof pl>()
    const navigate = useNavigate()

    return (
        <MCard>
            <MCardHeader>
                <MInline justify={'between'} align={'center'}>
                    <MStack spacing={'xs'}>
                        <MHeading level={4}>{t('dashboard.camera_previews_title')}</MHeading>
                        <MText tone={'muted'} size={'sm'}>
                            {t('dashboard.camera_previews_subtitle')}
                        </MText>
                    </MStack>
                    <MCameraIcon size={28} />
                </MInline>
            </MCardHeader>
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
