import {MCard, MCardBody, MCardHeader} from '@banzamel/mineralui-pro/cards'
import {MBarChart} from '@banzamel/mineralui-pro/data'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MBuildingIcon} from '@banzamel/mineralui-pro/icons'
import {MInline, MStack} from '@banzamel/mineralui-pro/layout'
import {MHeading, MText} from '@banzamel/mineralui-pro/typography'

import pl from '../../i18n/pl.json'

interface PhotosByObjectChartProps {
    perObject: {label: string; count: number}[]
}

export function PhotosByObjectChart({perObject}: PhotosByObjectChartProps) {
    const {t} = useMI18n<typeof pl>()
    const labels = perObject.map((o) => o.label)
    const values = perObject.map((o) => o.count)

    return (
        <MCard>
            <MCardHeader>
                <MInline justify={'between'} align={'center'}>
                    <MStack spacing={'xs'}>
                        <MHeading level={4}>{t('dashboard.photos_per_object_title')}</MHeading>
                        <MText tone={'muted'} size={'sm'}>
                            {t('dashboard.photos_per_object_subtitle')}
                        </MText>
                    </MStack>
                    <MBuildingIcon size={28} />
                </MInline>
            </MCardHeader>
            <MCardBody>
                <MBarChart
                    data={[{label: t('dashboard.photos_per_object_series'), data: values, color: 'info'}]}
                    xAxis={{labels}}
                    height={220}
                    showLegend={false}
                    showGrid
                />
            </MCardBody>
        </MCard>
    )
}
