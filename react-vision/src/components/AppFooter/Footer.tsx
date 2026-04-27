import {Link} from 'react-router-dom'

import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MCopyrightIcon} from '@banzamel/mineralui-pro/icons'
import {MFooter, MInline} from '@banzamel/mineralui-pro/layout'
import {MImage} from '@banzamel/mineralui-pro/media'
import {MLink, MText} from '@banzamel/mineralui-pro/typography'

import pl from '../../i18n/pl.json'

export function Footer() {
    const {t} = useMI18n<typeof pl>()
    const year = new Date().getFullYear()

    return (
        <MFooter container={'wide'} bordered tone={'surface'}>
            <MInline justify={'between'} align={'center'} wrap={'wrap'} fullWidth>
                <MInline align={'center'}>
                    <MLink component={Link} to={'/'} underline={'none'}>
                        <MImage src={'/vision-logo.png'} alt={t('navbar.logo_alt')} height={32} />
                    </MLink>
                    <MLink
                        href={'https://mineralui.io'}
                        target={'_blank'}
                        rel={'noopener noreferrer'}
                        underline={'none'}
                    >
                        <MImage src={'/mineralui-logo.png'} alt={t('footer.mineralui_alt')} height={24} />
                    </MLink>
                    <MLink
                        href={'https://banzamel.pl'}
                        target={'_blank'}
                        rel={'noopener noreferrer'}
                        underline={'none'}
                    >
                        <MImage src={'/banzamel-logo.png'} alt={t('footer.banzamel_alt')} height={24} />
                    </MLink>
                </MInline>

                <MInline align={'center'}>
                    <MCopyrightIcon size={'sm'} />
                    <MText tone={'muted'} size={'sm'}>
                        {year} Banzamel. {t('footer.rights')}
                    </MText>
                </MInline>
            </MInline>
        </MFooter>
    )
}
