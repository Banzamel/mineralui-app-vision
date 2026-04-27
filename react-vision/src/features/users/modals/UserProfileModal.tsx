import {useEffect, useState} from 'react'

import {MButton} from '@banzamel/mineralui-pro/controls'
import {useMToast} from '@banzamel/mineralui-pro/feedback'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MUserIconV2} from '@banzamel/mineralui-pro/icons'
import {MInput, MInputEmail, MInputPassword} from '@banzamel/mineralui-pro/inputs'
import {MInline, MStack} from '@banzamel/mineralui-pro/layout'
import {MModal} from '@banzamel/mineralui-pro/overlays'

import {api} from '../../../helpers/api'
import {useAuth} from '../../auth/AuthContext'
import pl from '../../../i18n/pl.json'

interface UserProfileModalProps {
    open: boolean
    onClose: () => void
}

export function UserProfileModal({open, onClose}: UserProfileModalProps) {
    const {t} = useMI18n<typeof pl>()
    const {toast} = useMToast()
    const {user, refreshMe} = useAuth()
    const [name, setName] = useState(user?.name ?? '')
    const [email, setEmail] = useState(user?.email ?? '')
    const [password, setPassword] = useState('')
    const [saving, setSaving] = useState(false)

    useEffect(() => {
        if (open && user) {
            setName(user.name)
            setEmail(user.email)
            setPassword('')
        }
    }, [open, user])

    async function handleSave() {
        setSaving(true)
        try {
            await api.put('/manage/me', {name, email, password: password || undefined})
            await refreshMe()
            toast({
                title: t('user_modal.saved_title'),
                message: t('user_modal.saved_message'),
                color: 'success',
            })
            onClose()
        } finally {
            setSaving(false)
        }
    }

    return (
        <MModal
            open={open}
            onClose={onClose}
            title={t('user_modal.title')}
            size={'md'}
            footer={
                <MInline justify={'between'} wrap={'wrap'}>
                    <MButton variant={'ghost'} onClick={onClose} disabled={saving}>
                        {t('user_modal.cancel')}
                    </MButton>
                    <MButton variant={'filled'} color={'primary'} onClick={handleSave} loading={saving} disabled={saving}>
                        {t('user_modal.save')}
                    </MButton>
                </MInline>
            }
        >
            <MStack spacing={'md'}>
                <MInput
                    label={t('user_modal.name')}
                    value={name}
                    startIcon={<MUserIconV2 />}
                    onChange={(event) => setName(event.target.value)}
                    fullWidth
                />
                <MInputEmail
                    label={t('user_modal.email')}
                    value={email}
                    onChange={(event) => setEmail(event.target.value)}
                    fullWidth
                />
                <MInputPassword
                    label={t('user_modal.password_new')}
                    value={password}
                    onChange={(event) => setPassword(event.target.value)}
                    fullWidth
                />
            </MStack>
        </MModal>
    )
}
