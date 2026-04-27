import {MCardWidget} from '@banzamel/mineralui-pro/cards'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MLockIcon} from '@banzamel/mineralui-pro/icons'

import {interpolate} from '../../../i18n/interpolate'
import pl from '../../../i18n/pl.json'

interface RolesCountWidgetProps {
    rolesCount: number
    permissionsCount: number
}

export function RolesCountWidget({rolesCount, permissionsCount}: RolesCountWidgetProps) {
    const {t} = useMI18n<typeof pl>()

    return (
        <MCardWidget
            title={t('users_page.widget_roles_title')}
            value={rolesCount}
            helperText={interpolate(t('users_page.widget_roles_perms'), {count: permissionsCount})}
            icon={<MLockIcon size={32} />}
        />
    )
}
