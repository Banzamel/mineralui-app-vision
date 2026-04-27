import {MCardWidget} from '@banzamel/mineralui-pro/cards'
import {MCountUp} from '@banzamel/mineralui-pro/display'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {
    MBuildingIcon,
    MCameraIcon,
    MGalleryIcon,
    MImageIcon,
} from '@banzamel/mineralui-pro/icons'
import {MSimpleGrid} from '@banzamel/mineralui-pro/layout'

import pl from '../../i18n/pl.json'

interface DashboardKpisProps {
    objects: number
    cameras: number
    albums: number
    photos: number
}

export function DashboardKpis({objects, cameras, albums, photos}: DashboardKpisProps) {
    const {t} = useMI18n<typeof pl>()

    // Wrapped in a plain div so we can use MineralUI's `data-m-hidden` utility — MSimpleGrid
     // itself doesn't extend MHiddenProps, but the CSS rule is attribute-driven so any element works.
    return (
        <div data-m-hidden={'md'}>
            <MSimpleGrid columns={4} minItemWidth={'180px'}>
                <MCardWidget
                    title={t('dashboard.kpi_objects')}
                    value={<MCountUp value={objects} duration={900} />}
                    icon={<MBuildingIcon size={28} />}
                />
                <MCardWidget
                    title={t('dashboard.kpi_cameras')}
                    value={<MCountUp value={cameras} duration={900} />}
                    icon={<MCameraIcon size={28} />}
                />
                <MCardWidget
                    title={t('dashboard.kpi_albums')}
                    value={<MCountUp value={albums} duration={900} />}
                    icon={<MGalleryIcon size={28} />}
                />
                <MCardWidget
                    title={t('dashboard.kpi_photos')}
                    value={<MCountUp value={photos} duration={1200} separator={' '} />}
                    icon={<MImageIcon size={28} />}
                />
            </MSimpleGrid>
        </div>
    )
}
