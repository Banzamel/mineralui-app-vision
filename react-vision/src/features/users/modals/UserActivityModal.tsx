import {useEffect, useState} from 'react'

import {MButton} from '@banzamel/mineralui-pro/controls'
import {MEmptyState, MTimeline, MTimelineItem} from '@banzamel/mineralui-pro/display'
import {MBadge, useMToast} from '@banzamel/mineralui-pro/feedback'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MClockIcon, MDeviceMonitorIcon, MTrashIcon} from '@banzamel/mineralui-pro/icons'
import {MInline, MStack, MTabs} from '@banzamel/mineralui-pro/layout'
import {MModal} from '@banzamel/mineralui-pro/overlays'
import {MText} from '@banzamel/mineralui-pro/typography'

import {formatDateTime} from '../../../helpers/format'
import {interpolate} from '../../../i18n/interpolate'
import pl from '../../../i18n/pl.json'
import {usersApi} from '../api'
import type {User, UserActivityEntry, UserActivityType, UserSession} from '../types'

interface UserActivityModalProps {
    open: boolean
    user: User | null
    onClose: () => void
}

const TYPE_COLOR: Record<UserActivityType, 'success' | 'neutral' | 'warning' | 'info' | 'primary'> = {
    login: 'success',
    logout: 'neutral',
    password_reset: 'warning',
    scopes_updated: 'info',
    role_changed: 'primary',
    model_change: 'info',
}

export function UserActivityModal({open, user, onClose}: UserActivityModalProps) {
    const {t} = useMI18n<typeof pl>()
    const {toast} = useMToast()
    const [activity, setActivity] = useState<UserActivityEntry[]>([])
    const [sessions, setSessions] = useState<UserSession[]>([])
    const [loading, setLoading] = useState(false)

    useEffect(() => {
        if (!open || !user) return
        setLoading(true)
        Promise.all([usersApi.activity(user.id), usersApi.sessions(user.id)])
            .then(([act, sess]) => {
                setActivity(act.data.slice().sort((a, b) => b.at.localeCompare(a.at)))
                setSessions(sess.data.slice().sort((a, b) => b.last_active_at.localeCompare(a.last_active_at)))
            })
            .finally(() => setLoading(false))
    }, [open, user])

    async function handleRevoke(session: UserSession) {
        if (!user) return
        await usersApi.revokeSession(user.id, session.id)
        setSessions((prev) => prev.filter((s) => s.id !== session.id))
        toast({
            title: t('user_activity.revoke_toast_title'),
            message: t('user_activity.revoke_toast_message'),
            color: 'success',
        })
    }

    function typeLabel(type: UserActivityType): string {
        switch (type) {
            case 'login':
                return t('user_activity.type_login')
            case 'logout':
                return t('user_activity.type_logout')
            case 'password_reset':
                return t('user_activity.type_password_reset')
            case 'scopes_updated':
                return t('user_activity.type_scopes_updated')
            case 'role_changed':
                return t('user_activity.type_role_changed')
            case 'model_change':
                return t('user_activity.type_model_change')
        }
    }

    const tabs = [
        {
            value: 'history',
            label: t('user_activity.tab_history'),
            content:
                !loading && activity.length === 0 ? (
                    <MEmptyState icon={<MClockIcon />} title={t('user_activity.empty_history')} size={'sm'} />
                ) : (
                    <MTimeline>
                        {activity.map((entry) => (
                            <MTimelineItem
                                key={entry.id}
                                id={entry.id}
                                color={TYPE_COLOR[entry.type]}
                                title={typeLabel(entry.type)}
                                date={entry.ip ? `${formatDateTime(entry.at)} · ${entry.ip}` : formatDateTime(entry.at)}
                                description={entry.description}
                            />
                        ))}
                    </MTimeline>
                ),
        },
        {
            value: 'sessions',
            label: t('user_activity.tab_sessions'),
            content:
                !loading && sessions.length === 0 ? (
                    <MEmptyState
                        icon={<MDeviceMonitorIcon />}
                        title={t('user_activity.empty_sessions')}
                        size={'sm'}
                    />
                ) : (
                    <MStack spacing={'sm'}>
                        {sessions.map((session) => (
                            <MInline key={session.id} align={'center'} justify={'between'}>
                                <MStack spacing={'xs'}>
                                    <MInline align={'center'}>
                                        <MText size={'sm'}>{session.device}</MText>
                                        {session.current && (
                                            <MBadge color={'success'} size={'xs'}>
                                                {t('user_activity.current_session')}
                                            </MBadge>
                                        )}
                                    </MInline>
                                    <MText size={'xs'} tone={'muted'}>
                                        {session.ip}
                                        {session.location ? ` · ${session.location}` : ''}
                                    </MText>
                                    <MText size={'xs'} tone={'muted'}>
                                        {interpolate(t('user_activity.session_last_active'), {
                                            at: formatDateTime(session.last_active_at),
                                        })}
                                    </MText>
                                </MStack>
                                <MButton
                                    variant={'ghost'}
                                    size={'sm'}
                                    color={'error'}
                                    startIcon={<MTrashIcon />}
                                    onClick={() => handleRevoke(session)}
                                    disabled={session.current}
                                    aria-label={t('user_activity.revoke_session')}
                                >
                                    {t('user_activity.revoke_session')}
                                </MButton>
                            </MInline>
                        ))}
                    </MStack>
                ),
        },
    ]

    return (
        <MModal
            open={open}
            onClose={onClose}
            title={interpolate(t('user_activity.title'), {name: user?.name ?? ''})}
            size={'lg'}
            footer={
                <MInline justify={'end'} fullWidth>
                    <MButton variant={'ghost'} onClick={onClose}>
                        {t('user_activity.close')}
                    </MButton>
                </MInline>
            }
        >
            <MTabs items={tabs} defaultValue={'history'} variant={'underline'} />
        </MModal>
    )
}
