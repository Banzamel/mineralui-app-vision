import {MButton} from '@banzamel/mineralui-pro/controls'
import {MEmptyState} from '@banzamel/mineralui-pro/display'
import {MBadge} from '@banzamel/mineralui-pro/feedback'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {
    MBellIcon,
    MCameraIcon,
    MCheckCircleIcon,
    MCheckIcon,
    MDatabaseIcon,
    MGalleryIcon,
    MInfoIcon,
    MTrashIcon,
    MWarningIcon,
} from '@banzamel/mineralui-pro/icons'
import {MInline, MStack} from '@banzamel/mineralui-pro/layout'
import {MCard, MCardBody} from '@banzamel/mineralui-pro/cards'
import {MHeading, MText} from '@banzamel/mineralui-pro/typography'

import {Loading} from '../../components/Loading'
import {formatDateTime} from '../../helpers'
import pl from '../../i18n/pl.json'
import {useNotifications} from './NotificationsContext'
import {renderNotification} from './renderNotification'
import type {NotificationSeverity, NotificationType} from './types'

const severityToColor: Record<NotificationSeverity, 'info' | 'success' | 'warning' | 'error'> = {
    info: 'info',
    success: 'success',
    warning: 'warning',
    error: 'error',
}

function iconForType(type: NotificationType) {
    switch (type) {
        case 'motion_detected':
        case 'camera_offline':
        case 'camera_online':
            return <MCameraIcon size={20} />
        case 'backup_completed':
        case 'backup_failed':
            return <MDatabaseIcon size={20} />
        case 'storage_warning':
            return <MWarningIcon size={20} />
        case 'album_shared':
            return <MGalleryIcon size={20} />
        case 'system_alert':
            return <MInfoIcon size={20} />
        default:
            return <MBellIcon size={20} />
    }
}

export function NotificationsTab() {
    const {t} = useMI18n<typeof pl>()
    const {notifications, loading, error, reload, markRead, markAllRead, deleteOne, deleteAll} =
        useNotifications()

    const hasUnread = notifications.some((n) => !n.read)

    return (
        <Loading loading={loading} error={error} onRetry={reload} minHeight={120}>
            {notifications.length === 0 ? (
                <MEmptyState
                    icon={<MCheckCircleIcon />}
                    title={t('notifications_center.empty_notifications')}
                    size={'sm'}
                />
            ) : (
                <MStack spacing={'md'}>
                    <MInline justify={'end'}>
                        {hasUnread && (
                            <MButton
                                variant={'ghost'}
                                size={'sm'}
                                startIcon={<MCheckIcon />}
                                onClick={markAllRead}
                            >
                                {t('notifications_center.mark_all_read')}
                            </MButton>
                        )}
                        <MButton
                            variant={'ghost'}
                            size={'sm'}
                            color={'error'}
                            startIcon={<MTrashIcon />}
                            onClick={deleteAll}
                        >
                            {t('notifications_center.delete_all')}
                        </MButton>
                    </MInline>
                    <MStack spacing={'sm'}>
                        {notifications.map((n) => {
                            const rendered = renderNotification(n, t)
                            return (
                            <MCard key={n.id} tone={n.read ? 'subtle' : 'raised'}>
                                <MCardBody>
                                    <MStack spacing={'xs'}>
                                        <MInline
                                            justify={'between'}
                                            align={'center'}
                                            wrap={'nowrap'}
                                        >
                                            <MInline align={'center'}>
                                                {iconForType(n.type)}
                                                <MHeading level={5}>{rendered.title}</MHeading>
                                            </MInline>
                                            <MBadge color={severityToColor[n.severity]} size={'sm'}>
                                                {t(`notifications_center.severity_${n.severity}`)}
                                            </MBadge>
                                        </MInline>
                                        <MText size={'sm'} tone={'muted'}>
                                            {rendered.message}
                                        </MText>
                                        <MInline
                                            justify={'between'}
                                            align={'center'}
                                            wrap={'wrap'}
                                        >
                                            <MText size={'xs'} tone={'muted'}>
                                                {formatDateTime(n.created_at)}
                                            </MText>
                                            <MInline align={'center'}>
                                                {!n.read && (
                                                    <MButton
                                                        variant={'ghost'}
                                                        size={'sm'}
                                                        startIcon={<MCheckIcon />}
                                                        onClick={() => markRead(n.id)}
                                                    >
                                                        {t('notifications_center.mark_read')}
                                                    </MButton>
                                                )}
                                                <MButton
                                                    variant={'ghost'}
                                                    size={'sm'}
                                                    color={'error'}
                                                    iconOnly
                                                    startIcon={<MTrashIcon />}
                                                    aria-label={t('notifications_center.delete_one')}
                                                    onClick={() => deleteOne(n.id)}
                                                />
                                            </MInline>
                                        </MInline>
                                    </MStack>
                                </MCardBody>
                            </MCard>
                            )
                        })}
                    </MStack>
                </MStack>
            )}
        </Loading>
    )
}
