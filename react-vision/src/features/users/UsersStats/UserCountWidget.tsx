import {MCardWidget} from '@banzamel/mineralui-pro/cards'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MUsersIcon} from '@banzamel/mineralui-pro/icons'

import {interpolate} from '../../../i18n/interpolate'
import pl from '../../../i18n/pl.json'

interface UserCountWidgetProps {
    total: number
    active: number
}

export function UserCountWidget({total, active}: UserCountWidgetProps) {
    const {t} = useMI18n<typeof pl>()

    return (
        <MCardWidget
            title={t('users_page.widget_count_title')}
            value={total}
            helperText={interpolate(t('users_page.widget_count_active'), {count: active})}
            icon={<MUsersIcon size={32} />}
        />
    )
}
