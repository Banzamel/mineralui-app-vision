import {useEffect} from 'react'
import {useNavigate} from 'react-router-dom'

import {MCard, MCardBody, MCardHeader} from '@banzamel/mineralui-pro/cards'
import {MReveal} from '@banzamel/mineralui-pro/display'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MContainer, MInline, MSection, MStack} from '@banzamel/mineralui-pro/layout'
import {MImage} from '@banzamel/mineralui-pro/media'
import {MHeading, MText} from '@banzamel/mineralui-pro/typography'

import {InstallWizard, useInstallStatus} from '../features/installer'
import pl from '../i18n/pl.json'

export function InstallPage() {
    const {t} = useMI18n<typeof pl>()
    const navigate = useNavigate()
    const {installed, loading} = useInstallStatus()

    // Wizard ma sens tylko przed finalize'em. Jeśli instalacja już się odbyła,
    // przekierowujemy na login — żeby ktoś kto wpisze URL bezpośrednio nie próbował
    // uruchamiać wizarda po raz drugi (backend i tak da 410 przez install.gate).
    useEffect(() => {
        if (!loading && installed) {
            navigate('/login', {replace: true})
        }
    }, [loading, installed, navigate])

    if (loading || installed) {
        return null
    }

    return (
        <MSection as={'main'} spacing={'xl'} style={{minHeight: '100dvh'}}>
            <MContainer size={'content'}>
                <MReveal direction={'up'} distance={24} trigger={'mount'}>
                    <MCard>
                        <MCardHeader>
                            <MStack spacing={'sm'}>
                                <MInline justify={'start'}>
                                    <MImage
                                        src={'/vision-logo.png'}
                                        alt={t('navbar.logo_alt')}
                                        height={64}
                                        style={{width: 'auto'}}
                                    />
                                </MInline>
                                <MHeading level={2} tone={'accent'}>{t('install.title')}</MHeading>
                                <MText tone={'muted'}>{t('install.subtitle')}</MText>
                            </MStack>
                        </MCardHeader>
                        <MCardBody>
                            <InstallWizard />
                        </MCardBody>
                    </MCard>
                </MReveal>
            </MContainer>
        </MSection>
    )
}
