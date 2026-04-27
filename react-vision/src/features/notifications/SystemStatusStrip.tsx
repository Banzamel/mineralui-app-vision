import {useEffect, useState} from 'react'

import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MInline, MStack} from '@banzamel/mineralui-pro/layout'
import {MBadge} from '@banzamel/mineralui-pro/feedback'
import {MText} from '@banzamel/mineralui-pro/typography'

import pl from '../../i18n/pl.json'
import {systemStatusApi} from './api'
import type {SystemStatus} from './types'

function formatBytes(bytes: number): string {
    const units = ['B', 'KB', 'MB', 'GB', 'TB']
    let value = bytes
    let idx = 0
    while (value >= 1024 && idx < units.length - 1) {
        value /= 1024
        idx++
    }
    return `${value.toFixed(1)} ${units[idx]}`
}

export function SystemStatusStrip() {
    const {t} = useMI18n<typeof pl>()
    const [status, setStatus] = useState<SystemStatus | null>(null)

    useEffect(() => {
        systemStatusApi.current().then((res) => setStatus(res.data))
    }, [])

    if (!status) {
        return null
    }

    return (
        <MStack spacing={'xs'}>
            <MInline justify={'between'} align={'center'} wrap={'wrap'}>
                <MInline align={'center'}>
                    <MText size={'xs'} tone={'muted'}>
                        {t('notifications_center.status_disk')}
                    </MText>
                    <MBadge color={status.disk.percent >= 80 ? 'warning' : 'neutral'} size={'sm'}>
                        {status.disk.percent}%
                    </MBadge>
                </MInline>

                <MText size={'xs'} tone={'muted'}>
                    {t('notifications_center.status_version')} {status.version}
                </MText>
            </MInline>
            <MText size={'xs'} tone={'muted'}>
                {formatBytes(status.disk.used_bytes)} / {formatBytes(status.disk.total_bytes)}
            </MText>
        </MStack>
    )
}
