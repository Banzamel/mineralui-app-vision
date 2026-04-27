import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MStack, MInline} from '@banzamel/mineralui-pro/layout'
import {MHeading, MText} from '@banzamel/mineralui-pro/typography'

import pl from '../../i18n/pl.json'
import type {InstallPayload} from './types'

interface Props {
    payload: InstallPayload
}

function Row({label, value}: {label: string; value: string}) {
    return (
        <MInline justify={'between'} wrap={'wrap'}>
            <MText tone={'muted'}>{label}</MText>
            <MText>{value || '—'}</MText>
        </MInline>
    )
}

export function StepReview({payload}: Props) {
    const {t} = useMI18n<typeof pl>()

    return (
        <MStack spacing={'lg'}>
            <MStack spacing={'xs'}>
                <MHeading level={4}>{t('install.review_database')}</MHeading>
                <Row label={t('install.db_host')} value={`${payload.database.host}:${payload.database.port}`} />
                <Row label={t('install.db_database')} value={payload.database.database} />
                <Row label={t('install.db_username')} value={payload.database.username} />
            </MStack>

            <MStack spacing={'xs'}>
                <MHeading level={4}>{t('install.review_admin')}</MHeading>
                <Row label={t('install.admin_name')} value={payload.admin.name} />
                <Row label={t('install.admin_email')} value={payload.admin.email} />
            </MStack>

            <MStack spacing={'xs'}>
                <MHeading level={4}>{t('install.review_object')}</MHeading>
                <Row label={t('install.object_name')} value={payload.first_object.name} />
                <Row label={t('install.object_type')} value={t(`object_type.${payload.first_object.type}`)} />
            </MStack>

            <MStack spacing={'xs'}>
                <MHeading level={4}>{t('install.review_camera')}</MHeading>
                <Row label={t('install.camera_name')} value={payload.first_camera.name} />
                <Row label={t('install.camera_ip')} value={payload.first_camera.ip} />
                <Row label={t('install.camera_stream_url')} value={payload.first_camera.stream_url} />
            </MStack>
        </MStack>
    )
}
