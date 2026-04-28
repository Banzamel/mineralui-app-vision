import {MCard, MCardBody, MCardHeader} from '@banzamel/mineralui-pro/cards'
import {MBarChart} from '@banzamel/mineralui-pro/data'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MCameraIcon} from '@banzamel/mineralui-pro/icons'
import {MInline, MStack} from '@banzamel/mineralui-pro/layout'
import {MHeading, MText} from '@banzamel/mineralui-pro/typography'

import pl from '../../i18n/pl.json'

interface PhotosByCameraMonthlyChartProps {
    perMonthByCamera: {
        labels: string[]
        series: {label: string; data: number[]}[]
    }
}

const PALETTE = ['primary', 'info', 'success', 'warning', 'error'] as const

export function PhotosByCameraMonthlyChart({perMonthByCamera}: PhotosByCameraMonthlyChartProps) {
    const {t} = useMI18n<typeof pl>()
    const datasets = perMonthByCamera.series.map((s, idx) => ({
        label: s.label,
        data: s.data,
        color: PALETTE[idx % PALETTE.length],
    }))

    return (
        <MCard>
            <MCardHeader>
                <MInline justify={'between'} align={'center'}>
                    <MStack spacing={'xs'}>
                        <MHeading level={4}>{t('dashboard.photos_by_camera_monthly_title')}</MHeading>
                        <MText tone={'muted'} size={'sm'}>
                            {t('dashboard.photos_by_camera_monthly_subtitle')}
                        </MText>
                    </MStack>
                    <MCameraIcon size={28} />
                </MInline>
            </MCardHeader>
            <MCardBody>
                <MBarChart
                    data={datasets}
                    xAxis={{labels: perMonthByCamera.labels}}
                    height={220}
                    showLegend
                    showGrid
                    stacked
                />
            </MCardBody>
        </MCard>
    )
}
