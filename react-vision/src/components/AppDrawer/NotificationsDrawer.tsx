import {useState} from 'react'

import {MButton} from '@banzamel/mineralui-pro/controls'
import {MReveal} from '@banzamel/mineralui-pro/display'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MBellIcon, MClockIcon} from '@banzamel/mineralui-pro/icons'
import {MTabs} from '@banzamel/mineralui-pro/layout'
import type {MTabsItem} from '@banzamel/mineralui-pro/layout'
import {MDrawer, MDrawerBody, MDrawerFooter, MDrawerHeader} from '@banzamel/mineralui-pro/overlays'
import {MText} from '@banzamel/mineralui-pro/typography'

import {ActivityTab, NotificationsTab, SystemStatusStrip, useNotifications} from '../../features/notifications'
import pl from '../../i18n/pl.json'

type TabKey = 'notifications' | 'activity'

export function NotificationsDrawer() {
    const {t} = useMI18n<typeof pl>()
    const [open, setOpen] = useState(false)
    const [tab, setTab] = useState<TabKey>('notifications')
    const {unreadCount} = useNotifications()

    const tabs: MTabsItem[] = [
        {
            value: 'notifications',
            icon: <MBellIcon />,
            label: (
                <MText as={'span'} size={'sm'} hidden={'sm'}>
                    {t('notifications_center.tab_notifications')}
                </MText>
            ),
        },
        {
            value: 'activity',
            icon: <MClockIcon />,
            label: (
                <MText as={'span'} size={'sm'} hidden={'sm'}>
                    {t('notifications_center.tab_activity')}
                </MText>
            ),
        },
    ]

    return (
        <>
            <MButton
                variant={'ghost'}
                iconOnly
                startIcon={<MBellIcon />}
                badge={unreadCount > 0 ? unreadCount : undefined}
                badgeColor={'error'}
                aria-label={t('notifications_center.open_label')}
                onClick={() => setOpen(true)}
            />

            <MDrawer open={open} onClose={() => setOpen(false)} side={'right'} size={'md'}>
                <MDrawerHeader>
                    <MTabs
                        items={tabs}
                        value={tab}
                        onValueChange={(v) => setTab(v as TabKey)}
                        variant={'underline'}
                        fullWidth
                        showPanels={false}
                    />
                </MDrawerHeader>
                <MDrawerBody>
                    <MReveal key={tab} trigger={'mount'} direction={'up'} distance={12} duration={0.25}>
                        {tab === 'notifications' && <NotificationsTab />}
                        {tab === 'activity' && <ActivityTab />}
                    </MReveal>
                </MDrawerBody>
                <MDrawerFooter bordered>
                    <SystemStatusStrip />
                </MDrawerFooter>
            </MDrawer>
        </>
    )
}
