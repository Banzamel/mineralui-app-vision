import {useState} from 'react'

import {MButton} from '@banzamel/mineralui-pro/controls'
import {MBadge, useMToast} from '@banzamel/mineralui-pro/feedback'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MInput, MInputNumber} from '@banzamel/mineralui-pro/inputs'
import {MGrid, MGridItem, MInline, MStack} from '@banzamel/mineralui-pro/layout'

import pl from '../../i18n/pl.json'
import {installerApi} from './api'
import type {InstallDatabase} from './types'

interface Props {
    value: InstallDatabase
    onChange: (value: InstallDatabase) => void
}

export function StepDatabase({value, onChange}: Props) {
    const {t} = useMI18n<typeof pl>()
    const {toast} = useMToast()
    const [testing, setTesting] = useState(false)
    const [tested, setTested] = useState<'ok' | 'fail' | null>(null)

    async function handleTest() {
        setTesting(true)
        try {
            await installerApi.testDatabase(value)
            setTested('ok')
            toast({title: t('install.test_ok_title'), message: t('install.test_ok_message'), color: 'success'})
        } catch (error) {
            setTested('fail')
            const message = error instanceof Error ? error.message : t('install.test_fail_message')
            toast({title: t('install.test_fail_title'), message, color: 'error'})
        } finally {
            setTesting(false)
        }
    }

    function patch(partial: Partial<InstallDatabase>) {
        setTested(null)
        onChange({...value, ...partial})
    }

    return (
        <MStack spacing={'md'}>
            <MGrid type={'row'}>
                <MGridItem sm={12} md={8}>
                    <MInput
                        label={t('install.db_host')}
                        value={value.host}
                        onChange={(e) => patch({host: e.target.value})}
                        fullWidth
                        required
                        placeholder={'127.0.0.1'}
                    />
                </MGridItem>
                <MGridItem sm={12} md={4}>
                    <MInputNumber
                        label={t('install.db_port')}
                        value={value.port}
                        onChange={(v) => patch({port: Number(v) || 3306})}
                        fullWidth
                        required
                    />
                </MGridItem>
            </MGrid>
            <MInput
                label={t('install.db_database')}
                value={value.database}
                onChange={(e) => patch({database: e.target.value})}
                fullWidth
                required
                placeholder={'vision'}
            />
            <MGrid type={'row'}>
                <MGridItem sm={12} md={6}>
                    <MInput
                        label={t('install.db_username')}
                        value={value.username}
                        onChange={(e) => patch({username: e.target.value})}
                        fullWidth
                        required
                    />
                </MGridItem>
                <MGridItem sm={12} md={6}>
                    <MInput
                        label={t('install.db_password')}
                        type={'password'}
                        value={value.password}
                        onChange={(e) => patch({password: e.target.value})}
                        autoComplete={'new-password'}
                        fullWidth
                    />
                </MGridItem>
            </MGrid>
            <MInline justify={'start'} align={'center'} wrap={'wrap'}>
                <MButton variant={'ghost'} color={'primary'} onClick={handleTest} disabled={testing}>
                    {t('install.test_database')}
                </MButton>
                {tested === 'ok' && <MBadge color={'success'}>{t('install.test_ok_badge')}</MBadge>}
                {tested === 'fail' && <MBadge color={'error'}>{t('install.test_fail_badge')}</MBadge>}
            </MInline>
        </MStack>
    )
}
