import {useNavigate} from 'react-router-dom'

import {MCard, MCardBody, MCardHeader, MCardTile} from '@banzamel/mineralui-pro/cards'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MCalendarIcon, MGalleryIcon} from '@banzamel/mineralui-pro/icons'
import {MInline, MSimpleGrid, MStack} from '@banzamel/mineralui-pro/layout'
import {MHeading, MText} from '@banzamel/mineralui-pro/typography'

import type {AlbumSearchResult} from '../albums/types'
import {interpolate} from '../../i18n/interpolate'
import pl from '../../i18n/pl.json'

interface RecentAlbumsWidgetProps {
    albums: AlbumSearchResult[]
}

export function RecentAlbumsWidget({albums}: RecentAlbumsWidgetProps) {
    const {t} = useMI18n<typeof pl>()
    const navigate = useNavigate()

    return (
        <MCard>
            <MCardHeader>
                <MInline justify={'between'} align={'center'}>
                    <MStack spacing={'xs'}>
                        <MHeading level={4}>{t('dashboard.recent_albums_title')}</MHeading>
                        <MText tone={'muted'} size={'sm'}>
                            {t('dashboard.recent_albums_subtitle')}
                        </MText>
                    </MStack>
                    <MGalleryIcon size={28} />
                </MInline>
            </MCardHeader>
            <MCardBody>
                <MSimpleGrid columns={4} minItemWidth={'200px'}>
                    {albums.map((album) => (
                        <MCardTile
                            key={album.id}
                            title={`${album.camera_name} · ${album.date}`}
                            description={interpolate(t('albums.photos_count'), {count: album.photos_count})}
                            icon={<MCalendarIcon />}
                            image={album.cover_photo}
                            mediaFill
                            overlayPosition={'bottom'}
                            onClick={() => navigate(`/objects?album=${album.id}`)}
                        />
                    ))}
                </MSimpleGrid>
            </MCardBody>
        </MCard>
    )
}
