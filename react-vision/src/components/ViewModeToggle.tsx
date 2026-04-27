import {MButton} from '@banzamel/mineralui-pro/controls'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MDashboardIcon, MLayoutIcon} from '@banzamel/mineralui-pro/icons'
import {MInline} from '@banzamel/mineralui-pro/layout'

import pl from '../i18n/pl.json'
import type {ViewMode} from '../helpers'

interface ViewModeToggleProps {
    value: ViewMode
    onChange: (mode: ViewMode) => void
}

export function ViewModeToggle({value, onChange}: ViewModeToggleProps) {
    const {t} = useMI18n<typeof pl>()
    return (
        <MInline align={'center'}>
            <MButton
                variant={value === 'cards' ? 'filled' : 'ghost'}
                size={'sm'}
                iconOnly
                startIcon={<MDashboardIcon />}
                aria-label={t('view_mode.cards')}
                onClick={() => onChange('cards')}
            />
            <MButton
                variant={value === 'table' ? 'filled' : 'ghost'}
                size={'sm'}
                iconOnly
                startIcon={<MLayoutIcon />}
                aria-label={t('view_mode.table')}
                onClick={() => onChange('table')}
            />
        </MInline>
    )
}
