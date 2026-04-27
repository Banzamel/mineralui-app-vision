import {MCardWidget} from '@banzamel/mineralui-pro/cards'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MCameraIcon} from '@banzamel/mineralui-pro/icons'

import {interpolate} from '../../../i18n/interpolate'
import pl from '../../../i18n/pl.json'

interface ScopedUsersWidgetProps {
    withScopes: number
    total: number
}

export function ScopedUsersWidget({withScopes, total}: ScopedUsersWidgetProps) {
    const {t} = useMI18n<typeof pl>()

    return (
        <MCardWidget
            title={t('users_page.widget_scoped_title')}
            value={withScopes}
            helperText={interpolate(t('users_page.widget_scoped_of'), {total})}
            icon={<MCameraIcon size={32} />}
        />
    )
}
