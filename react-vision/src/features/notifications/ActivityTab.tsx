import {MEmptyState, MTimeline, MTimelineItem} from '@banzamel/mineralui-pro/display'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MClockIcon} from '@banzamel/mineralui-pro/icons'

import {Loading} from '../../components/Loading'
import {formatDateTime, useAsync} from '../../helpers'
import pl from '../../i18n/pl.json'
import {activityApi} from './api'
import type {ActivityEntry, ActivityType} from './types'

const typeToColor: Record<ActivityType, 'primary' | 'success' | 'info' | 'warning' | 'neutral'> = {
    login: 'success',
    logout: 'neutral',
    password_changed: 'warning',
    avatar_changed: 'info',
    profile_updated: 'info',
    album_created: 'primary',
    photo_uploaded: 'primary',
    scopes_updated: 'warning',
}

export function ActivityTab() {
    const {t} = useMI18n<typeof pl>()
    const {data, loading, error, reload} = useAsync<ActivityEntry[]>(
        () => activityApi.list().then((res) => res.data),
        [],
    )
    const entries = data ?? []

    return (
        <Loading loading={loading} error={error} onRetry={reload} minHeight={120}>
            {entries.length === 0 ? (
                <MEmptyState
                    icon={<MClockIcon />}
                    title={t('notifications_center.empty_activity')}
                    size={'sm'}
                />
            ) : (
                <MTimeline>
                    {entries.map((entry) => (
                        <MTimelineItem
                            key={entry.id}
                            id={entry.id}
                            title={t(`notifications_center.activity_${entry.type}`)}
                            date={formatDateTime(entry.at)}
                            description={entry.description + (entry.ip ? ` (${entry.ip})` : '')}
                            color={typeToColor[entry.type]}
                        />
                    ))}
                </MTimeline>
            )}
        </Loading>
    )
}
