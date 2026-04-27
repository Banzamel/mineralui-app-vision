import {MButton, MButtonGroup} from '@banzamel/mineralui-pro/controls'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MFlagGbIconV2, MFlagPlIconV2} from '@banzamel/mineralui-pro/icons'

import pl from '../../i18n/pl.json'

export function LocaleSwitch() {
    const {t, locale, setLocale} = useMI18n<typeof pl>()

    return (
        <MButtonGroup>
            <MButton startIcon={<MFlagPlIconV2 size={32} />} iconOnly variant={'ghost'} active={locale === 'pl'} onClick={() => setLocale('pl')} />
            <MButton startIcon={<MFlagGbIconV2 size={32} />} iconOnly variant={'ghost'} active={locale === 'en'} onClick={() => setLocale('en')} />
        </MButtonGroup>
    )
}
