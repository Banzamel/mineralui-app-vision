import {Outlet} from 'react-router-dom'

import {MAppShell, MBody} from '@banzamel/mineralui-pro/layout'

import {Footer} from '../components/AppFooter'
import {Navbar} from '../components/AppNavbar'
import {NotificationsProvider, usePushSubscription} from '../features/notifications'
import {RealtimeProvider} from '../features/realtime'

function PushSubscriber() {
    usePushSubscription()
    return null
}

export function ProtectedLayout() {
    return (
        <RealtimeProvider>
            <NotificationsProvider>
                <PushSubscriber />
                <MAppShell>
                    <Navbar />
                    <MBody>
                        <Outlet />
                    </MBody>
                    <Footer />
                </MAppShell>
            </NotificationsProvider>
        </RealtimeProvider>
    )
}
