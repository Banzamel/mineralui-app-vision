import {Outlet} from 'react-router-dom'

import {MAppShell, MBody} from '@banzamel/mineralui-pro/layout'

export function PublicLayout() {
    return (
        <MAppShell>
            <MBody>
                <Outlet />
            </MBody>
        </MAppShell>
    )
}
