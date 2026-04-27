import {MCard, MCardBody, MCardHeader} from '@banzamel/mineralui-pro/cards'
import {MAreaChart} from '@banzamel/mineralui-pro/data'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MChartIconV2} from '@banzamel/mineralui-pro/icons'
import {MInline, MStack} from '@banzamel/mineralui-pro/layout'
import {MHeading, MText} from '@banzamel/mineralui-pro/typography'

import pl from '../../i18n/pl.json'

interface PhotosPerDayChartProps {
    perDay: {date: string; count: number}[]
}

function shortLabel(date: string): string {
    const parsed = new Date(date)
    return `${String(parsed.getDate()).padStart(2, '0')}.${String(parsed.getMonth() + 1).padStart(2, '0')}`
}

export function PhotosPerDayChart({perDay}: PhotosPerDayChartProps) {
    const {t} = useMI18n<typeof pl>()
    const labels = perDay.map((d) => shortLabel(d.date))
    const values = perDay.map((d) => d.count)

    return (
        <MCard>
            <MCardHeader>
                <MInline justify={'between'} align={'center'}>
                    <MStack spacing={'xs'}>
                        <MHeading level={4}>{t('dashboard.photos_per_day_title')}</MHeading>
                        <MText tone={'muted'} size={'sm'}>
                            {t('dashboard.photos_per_day_subtitle')}
                        </MText>
                    </MStack>
                    <MChartIconV2 size={28} />
                </MInline>
            </MCardHeader>
            <MCardBody>
                <MAreaChart
                    data={[{label: t('dashboard.photos_per_day_series'), data: values, color: 'primary'}]}
                    xAxis={{labels}}
                    height={220}
                    showLegend={false}
                    showGrid
                    curved
                />
            </MCardBody>
        </MCard>
    )
}
