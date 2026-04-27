import {MCard, MCardBody, MCardHeader} from '@banzamel/mineralui-pro/cards'
import {MCountUp} from '@banzamel/mineralui-pro/display'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MUsersIcon} from '@banzamel/mineralui-pro/icons'
import {MInline, MStack} from '@banzamel/mineralui-pro/layout'
import {MAvatarStack} from '@banzamel/mineralui-pro/media'
import {MHeading, MText} from '@banzamel/mineralui-pro/typography'

import type {User} from '../users/types'
import {interpolate} from '../../i18n/interpolate'
import pl from '../../i18n/pl.json'

interface UsersSummaryWidgetProps {
    users: User[]
    loggedInToday: number
    activeCount: number
}

export function UsersSummaryWidget({users, loggedInToday, activeCount}: UsersSummaryWidgetProps) {
    const {t} = useMI18n<typeof pl>()
    const stackItems = users.slice(0, 6).map((u) => ({name: u.name, avatar: u.avatar_url ?? undefined}))

    return (
        <MCard>
            <MCardHeader>
                <MInline justify={'between'} align={'center'}>
                    <MStack spacing={'xs'}>
                        <MHeading level={4}>{t('dashboard.users_summary_title')}</MHeading>
                        <MText tone={'muted'} size={'sm'}>
                            {interpolate(t('dashboard.users_summary_subtitle'), {active: activeCount})}
                        </MText>
                    </MStack>
                    <MUsersIcon size={28} />
                </MInline>
            </MCardHeader>
            <MCardBody>
                <MStack spacing={'md'}>
                    <MInline align={'center'} wrap={'wrap'}>
                        <MStack spacing={'xs'}>
                            <MText tone={'muted'} size={'xs'}>
                                {t('dashboard.users_total')}
                            </MText>
                            <MHeading level={2}>
                                <MCountUp value={users.length} duration={800} />
                            </MHeading>
                        </MStack>
                        <MStack spacing={'xs'}>
                            <MText tone={'muted'} size={'xs'}>
                                {t('dashboard.users_logged_in_today')}
                            </MText>
                            <MHeading level={2} color={'success'}>
                                <MCountUp value={loggedInToday} duration={800} />
                            </MHeading>
                        </MStack>
                    </MInline>
                    <MAvatarStack items={stackItems} max={5} size={'md'} color={'primary'} overlap={12} />
                </MStack>
            </MCardBody>
        </MCard>
    )
}
