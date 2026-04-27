import {MCard, MCardBody, MCardHeader} from '@banzamel/mineralui-pro/cards'
import {MAreaChart} from '@banzamel/mineralui-pro/data'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MChartIconV2} from '@banzamel/mineralui-pro/icons'
import {MInline} from '@banzamel/mineralui-pro/layout'
import {MText} from '@banzamel/mineralui-pro/typography'

import pl from '../../../i18n/pl.json'

interface LoginsChartWidgetProps {
    daily: {date: string; count: number}[]
}

function shortLabel(date: string): string {
    const parsed = new Date(date)
    return `${String(parsed.getDate()).padStart(2, '0')}.${String(parsed.getMonth() + 1).padStart(2, '0')}`
}

export function LoginsChartWidget({daily}: LoginsChartWidgetProps) {
    const {t} = useMI18n<typeof pl>()
    const labels = daily.map((d) => shortLabel(d.date))
    const values = daily.map((d) => d.count)

    return (
        <MCard>
            <MCardHeader>
                <MInline justify={'between'} align={'center'}>
                    <MText tone={'muted'} size={'sm'}>
                        {t('users_page.widget_logins_title')}
                    </MText>
                    <MChartIconV2 size={32} />
                </MInline>
            </MCardHeader>
            <MCardBody>
                <MAreaChart
                    data={[{label: t('users_page.widget_logins_series'), data: values, color: 'primary'}]}
                    xAxis={{labels}}
                    height={180}
                    showLegend={false}
                    showXAxis={false}
                    showYAxis={false}
                    showGrid={false}
                    curved
                />
            </MCardBody>
        </MCard>
    )
}
