import type {ReactNode} from 'react'

import {MButton} from '@banzamel/mineralui-pro/controls'
import {MBanner, MLoader} from '@banzamel/mineralui-pro/feedback'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MRefreshIcon} from '@banzamel/mineralui-pro/icons'

import pl from '../i18n/pl.json'

interface LoadingProps {
    loading: boolean
    error?: Error | null
    fallback?: ReactNode
    onRetry?: () => void
    minHeight?: string | number
    children: ReactNode
}

export function Loading({loading, error, fallback, onRetry, minHeight = 200, children}: LoadingProps) {
    const {t} = useMI18n<typeof pl>()

    if (loading) {
        if (fallback) return <>{fallback}</>
        return <MLoader center minHeight={minHeight} label={t('loading.default')} />
    }

    if (error) {
        return (
            <MBanner
                color={'error'}
                variant={'outlined'}
                action={
                    onRetry ? (
                        <MButton
                            size={'sm'}
                            variant={'ghost'}
                            startIcon={<MRefreshIcon />}
                            onClick={onRetry}
                        >
                            {t('loading.retry')}
                        </MButton>
                    ) : undefined
                }
            >
                {t('loading.error_message')}
            </MBanner>
        )
    }

    return <>{children}</>
}
