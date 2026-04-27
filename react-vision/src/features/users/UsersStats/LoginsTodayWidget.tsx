import {MCardFinance} from '@banzamel/mineralui-pro/cards'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MClockIcon} from '@banzamel/mineralui-pro/icons'

import pl from '../../../i18n/pl.json'

interface LoginsTodayWidgetProps {
    daily: number[]
}

export function LoginsTodayWidget({daily}: LoginsTodayWidgetProps) {
    const {t} = useMI18n<typeof pl>()
    const today = daily.length > 0 ? daily[daily.length - 1] : 0
    const yesterday = daily.length > 1 ? daily[daily.length - 2] : 0
    const diff = today - yesterday
    const change = yesterday === 0 ? (today === 0 ? 0 : 100) : Math.round((diff / yesterday) * 100)

    return (
        <MCardFinance
            label={t('users_page.widget_logins_today_title')}
            value={today}
            change={change}
            changeLabel={t('users_page.widget_logins_today_diff_label')}
            icon={<MClockIcon size={32} />}
            sparkline={daily}
        />
    )
}
