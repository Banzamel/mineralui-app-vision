import {useEffect, useState} from 'react'

import {MButton} from '@banzamel/mineralui-pro/controls'
import {useMToast} from '@banzamel/mineralui-pro/feedback'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'

import pl from '../i18n/pl.json'

interface BeforeInstallPromptEvent extends Event {
    prompt: () => Promise<void>
    userChoice: Promise<{outcome: 'accepted' | 'dismissed'}>
}

export function PwaInstallButton() {
    const {t} = useMI18n<typeof pl>()
    const {toast} = useMToast()
    const [deferred, setDeferred] = useState<BeforeInstallPromptEvent | null>(null)
    const [hidden, setHidden] = useState(false)

    useEffect(() => {
        function onPrompt(e: Event) {
            e.preventDefault()
            setDeferred(e as BeforeInstallPromptEvent)
        }
        function onInstalled() {
            setDeferred(null)
            setHidden(true)
            toast({
                title: t('pwa.installed_title'),
                message: t('pwa.installed_message'),
                color: 'success',
            })
        }
        window.addEventListener('beforeinstallprompt', onPrompt)
        window.addEventListener('appinstalled', onInstalled)
        return () => {
            window.removeEventListener('beforeinstallprompt', onPrompt)
            window.removeEventListener('appinstalled', onInstalled)
        }
    }, [t, toast])

    if (hidden || !deferred) return null

    async function handleInstall() {
        if (!deferred) return
        await deferred.prompt()
        const choice = await deferred.userChoice
        if (choice.outcome === 'accepted') setHidden(true)
        setDeferred(null)
    }

    return (
        <MButton variant={'outlined'} color={'primary'} size={'sm'} onClick={handleInstall}>
            {t('pwa.install')}
        </MButton>
    )
}
