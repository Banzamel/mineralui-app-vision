import {StrictMode} from 'react'
import {createRoot} from 'react-dom/client'
import {BrowserRouter} from 'react-router-dom'

import {MToastProvider} from '@banzamel/mineralui-pro/feedback'
import {MI18nProvider} from '@banzamel/mineralui-pro/i18n'
import {MThemeProvider} from '@banzamel/mineralui-pro/theme'

import './theme/template.css'
import './theme/theme.css'

import {App} from './App'
import {AuthProvider} from './features/auth/AuthContext'
import en from './i18n/en.json'
import pl from './i18n/pl.json'

// Register the service worker so Chrome / Edge fire `beforeinstallprompt` and the
// PwaInstallButton can appear. The worker itself is a no-op stub — see public/sw.js.
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch((err) => {
            console.warn('SW registration failed', err)
        })
    })
}

createRoot(document.getElementById('root')!).render(
    <StrictMode>
        <BrowserRouter future={{v7_startTransition: true, v7_relativeSplatPath: true}}>
            <MI18nProvider locales={{en, pl}} defaultLocale={'pl'} persist>
                <MThemeProvider mode={'dark'} persist={false}>
                    <MToastProvider position={'top-right'}>
                        <AuthProvider>
                            <App />
                        </AuthProvider>
                    </MToastProvider>
                </MThemeProvider>
            </MI18nProvider>
        </BrowserRouter>
    </StrictMode>,
)
