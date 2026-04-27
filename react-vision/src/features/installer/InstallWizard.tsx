import {useEffect, useMemo, useState} from 'react'
import {useNavigate} from 'react-router-dom'

import {MButton} from '@banzamel/mineralui-pro/controls'
import {MProgressBar, MStep, MStepper} from '@banzamel/mineralui-pro/display'
import {useMToast} from '@banzamel/mineralui-pro/feedback'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MInline, MStack} from '@banzamel/mineralui-pro/layout'
import {MText} from '@banzamel/mineralui-pro/typography'

import {interpolate} from '../../i18n/interpolate'
import pl from '../../i18n/pl.json'
import {installerApi} from './api'
import {invalidateInstallStatus} from './useInstallStatus'
import {StepAdmin} from './StepAdmin'
import {StepCamera} from './StepCamera'
import {StepDatabase} from './StepDatabase'
import {StepObject} from './StepObject'
import {StepReview} from './StepReview'
import type {InstallPayload} from './types'

const STEP_COUNT = 5
const LAST_STEP = STEP_COUNT - 1

const INITIAL_PAYLOAD: InstallPayload = {
    database: {host: '127.0.0.1', port: 3306, database: 'vision', username: 'vision', password: ''},
    admin: {name: '', email: '', password: '', password_confirmation: ''},
    first_object: {name: '', type: 'block'},
    first_camera: {name: '', address: '', ip: '', stream_url: '', stream_login: '', stream_password: ''},
}

export function InstallWizard() {
    const navigate = useNavigate()
    const {toast} = useMToast()
    const {t} = useMI18n<typeof pl>()
    const [activeStep, setActiveStep] = useState(0)
    const [payload, setPayload] = useState<InstallPayload>(INITIAL_PAYLOAD)
    const [saving, setSaving] = useState(false)
    const [progressLabel, setProgressLabel] = useState('')
    const [progressPercent, setProgressPercent] = useState(0)

    // Auto-fill pierwszego kroku z backend .env — host/port/database/username.
    // Hasło celowo nie wraca w statusie, user wpisuje je raz ręcznie.
    useEffect(() => {
        let cancelled = false
        installerApi
            .status()
            .then((status) => {
                if (cancelled || !status.database_defaults) return
                setPayload((prev) => ({
                    ...prev,
                    database: {...prev.database, ...status.database_defaults!},
                }))
            })
            .catch(() => {
                // Brak auto-fillu — nie blokujemy kreatora, user może wpisać ręcznie.
            })
        return () => {
            cancelled = true
        }
    }, [])

    const stepValid = useMemo(() => {
        if (activeStep === 0) {
            const {host, port, database, username} = payload.database
            return host.trim() && port > 0 && database.trim() && username.trim()
        }
        if (activeStep === 1) {
            const {name, email, password, password_confirmation} = payload.admin
            return (
                name.trim() &&
                email.trim() &&
                password.length >= 8 &&
                password === password_confirmation
            )
        }
        if (activeStep === 2) {
            return payload.first_object.name.trim().length > 0
        }
        if (activeStep === 3) {
            const {name, ip} = payload.first_camera
            return name.trim().length > 0 && ip.trim().length > 0
        }
        return true
    }, [activeStep, payload])

    function next() {
        if (activeStep < LAST_STEP) setActiveStep(activeStep + 1)
    }

    function back() {
        if (activeStep > 0) setActiveStep(activeStep - 1)
    }

    async function install() {
        setSaving(true)
        const stages: Array<{label: string; run: () => Promise<unknown>}> = [
            {label: t('install.progress_database'), run: () => installerApi.saveDatabase(payload.database)},
            {label: t('install.progress_admin'), run: () => installerApi.createAdmin(payload.admin)},
            {label: t('install.progress_object'), run: () => installerApi.createFirstObject(payload.first_object)},
            {label: t('install.progress_camera'), run: () => installerApi.createFirstCamera(payload.first_camera)},
            {label: t('install.progress_finalize'), run: () => installerApi.finalize()},
        ]
        try {
            for (let i = 0; i < stages.length; i++) {
                setProgressLabel(stages[i].label)
                setProgressPercent(Math.round((i / stages.length) * 100))
                await stages[i].run()
            }
            setProgressPercent(100)
            // Odśwież cache statusu — LoginPage dostanie teraz installed=true i ukryje link do kreatora.
            invalidateInstallStatus()
            toast({
                title: t('install.done_title'),
                message: t('install.done_message'),
                color: 'success',
            })
            navigate('/login')
        } catch (error) {
            const message = error instanceof Error ? error.message : t('install.error_message')
            toast({title: t('install.error_title'), message, color: 'error'})
        } finally {
            setSaving(false)
        }
    }

    return (
        <MStack spacing={'xl'}>
            <MStepper activeStep={activeStep} variant={'horizontal'} color={'primary'}>
                <MStep id={'db'} title={t('install.step_db')} description={t('install.step_db_desc')} />
                <MStep id={'admin'} title={t('install.step_admin')} description={t('install.step_admin_desc')} />
                <MStep id={'object'} title={t('install.step_object')} description={t('install.step_object_desc')} />
                <MStep id={'camera'} title={t('install.step_camera')} description={t('install.step_camera_desc')} />
                <MStep id={'review'} title={t('install.step_review')} description={t('install.step_review_desc')} />
            </MStepper>

            {activeStep === 0 && (
                <StepDatabase
                    value={payload.database}
                    onChange={(database) => setPayload({...payload, database})}
                />
            )}
            {activeStep === 1 && (
                <StepAdmin
                    value={payload.admin}
                    onChange={(admin) => setPayload({...payload, admin})}
                />
            )}
            {activeStep === 2 && (
                <StepObject
                    value={payload.first_object}
                    onChange={(first_object) => setPayload({...payload, first_object})}
                />
            )}
            {activeStep === 3 && (
                <StepCamera
                    value={payload.first_camera}
                    onChange={(first_camera) => setPayload({...payload, first_camera})}
                />
            )}
            {activeStep === 4 && <StepReview payload={payload} />}

            {saving && (
                <MStack spacing={'xs'}>
                    <MText size={'sm'} tone={'muted'}>{progressLabel}</MText>
                    <MProgressBar value={progressPercent} color={'primary'} />
                </MStack>
            )}

            <MInline justify={'between'} wrap={'wrap'}>
                <MButton variant={'ghost'} onClick={back} disabled={activeStep === 0 || saving}>
                    {t('install.back')}
                </MButton>
                <MText tone={'muted'} size={'sm'}>
                    {interpolate(t('install.step_counter'), {current: activeStep + 1, total: STEP_COUNT})}
                </MText>
                {activeStep < LAST_STEP ? (
                    <MButton variant={'filled'} color={'primary'} onClick={next} disabled={!stepValid}>
                        {t('install.next')}
                    </MButton>
                ) : (
                    <MButton
                        variant={'filled'}
                        color={'success'}
                        onClick={install}
                        loading={saving}
                        disabled={saving}
                    >
                        {t('install.submit')}
                    </MButton>
                )}
            </MInline>
        </MStack>
    )
}
