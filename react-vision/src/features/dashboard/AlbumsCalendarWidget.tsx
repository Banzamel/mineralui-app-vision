import {useMemo} from 'react'
import {useNavigate} from 'react-router-dom'

import {MCard, MCardBody, MCardHeader} from '@banzamel/mineralui-pro/cards'
import {MCalendarBoard} from '@banzamel/mineralui-pro/data'
import type {MCalendarEvent, MCalendarEventActionItem} from '@banzamel/mineralui-pro/data'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MCalendarIcon, MExternalLinkIcon, MTrashIcon} from '@banzamel/mineralui-pro/icons'
import {MInline, MStack} from '@banzamel/mineralui-pro/layout'
import {MHeading, MText} from '@banzamel/mineralui-pro/typography'

import {albumsApi} from '../albums'
import type {AlbumSearchResult} from '../albums/types'
import {interpolate} from '../../i18n/interpolate'
import pl from '../../i18n/pl.json'

interface AlbumsCalendarWidgetProps {
    enriched: AlbumSearchResult[]
    onReload?: () => void
}

export function AlbumsCalendarWidget({enriched, onReload}: AlbumsCalendarWidgetProps) {
    const {t} = useMI18n<typeof pl>()
    const navigate = useNavigate()

    const events = useMemo<MCalendarEvent[]>(
        () =>
            enriched.map((album) => {
                const objectLabel = album.object_path.join(' / ') || '—'
                const photosLabel = interpolate(t('albums.photos_count'), {count: album.photos_count})
                const menuItems: MCalendarEventActionItem[] = [
                    {
                        id: 'open',
                        label: t('calendar_page.open_album'),
                        icon: <MExternalLinkIcon />,
                        onSelect: () => navigate(`/objects?album=${album.id}`),
                    },
                    {
                        id: 'delete',
                        label: t('calendar_page.delete_album'),
                        icon: <MTrashIcon />,
                        color: 'error',
                        onSelect: () => {
                            void albumsApi.delete(album.id).then(() => onReload?.())
                        },
                    },
                ]
                return {
                    id: String(album.id),
                    title: album.camera_name,
                    description: `${objectLabel} · ${photosLabel}`,
                    date: album.date,
                    status: 'done',
                    color: 'primary',
                    badgeLabel: String(album.photos_count),
                    menuItems,
                    meta: {albumId: album.id, objectLabel, photosLabel},
                }
            }),
        [enriched, navigate, onReload, t],
    )

    const latestDate = enriched.reduce(
        (max, a) => (a.date > max ? a.date : max),
        enriched[0]?.date ?? new Date().toISOString().slice(0, 10),
    )
    const defaultMonth = latestDate ? new Date(latestDate) : new Date()

    return (
        <MCard>
            <MCardHeader>
                <MInline justify={'between'} align={'center'}>
                    <MStack spacing={'xs'}>
                        <MHeading level={4}>{t('calendar_page.title')}</MHeading>
                        <MText tone={'muted'} size={'sm'}>
                            {t('calendar_page.subtitle')}
                        </MText>
                    </MStack>
                    <MCalendarIcon size={28} />
                </MInline>
            </MCardHeader>
            <MCardBody>
                <MCalendarBoard
                    defaultMonth={defaultMonth}
                    events={events}
                    locale={'pl'}
                    weekStartsOn={1}
                    fullWidth
                    showTimeline={false}
                    showHourBar={false}
                    detailsMode={'popover'}
                    emptyStateText={t('calendar_page.empty')}
                    onEventSelect={(event) => {
                        const id = Number((event.meta as {albumId?: number} | undefined)?.albumId)
                        if (Number.isFinite(id)) navigate(`/objects?album=${id}`)
                    }}
                />
            </MCardBody>
        </MCard>
    )
}
