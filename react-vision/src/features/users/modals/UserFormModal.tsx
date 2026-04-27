import {useEffect, useRef, useState} from 'react'

import {MButton, MToggle} from '@banzamel/mineralui-pro/controls'
import {MSelect} from '@banzamel/mineralui-pro/dropdowns'
import {useMToast} from '@banzamel/mineralui-pro/feedback'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MUserIconV2} from '@banzamel/mineralui-pro/icons'
import {MInput, MInputEmail, MInputFile, MInputPassword} from '@banzamel/mineralui-pro/inputs'
import {MInline, MStack} from '@banzamel/mineralui-pro/layout'
import {MAvatar} from '@banzamel/mineralui-pro/media'
import {MModal} from '@banzamel/mineralui-pro/overlays'
import {MText} from '@banzamel/mineralui-pro/typography'

import pl from '../../../i18n/pl.json'
import {usersApi} from '../api'
import type {Role, User, UserPayload} from '../types'

interface UserFormModalProps {
    open: boolean
    user: User | null
    roles: Role[]
    onClose: () => void
    onSaved: () => void
}

export function UserFormModal({open, user, roles, onClose, onSaved}: UserFormModalProps) {
    const {t} = useMI18n<typeof pl>()
    const {toast} = useMToast()
    const [name, setName] = useState('')
    const [email, setEmail] = useState('')
    const [password, setPassword] = useState('')
    const [active, setActive] = useState(true)
    const [roleName, setRoleName] = useState('')

    const avatarFileRef = useRef<File | null>(null)
    const [avatarPreview, setAvatarPreview] = useState<string | null>(null)
    const previewUrlRef = useRef<string | null>(null)
    const [saving, setSaving] = useState(false)

    useEffect(() => {
        if (!open) return
        setName(user?.name ?? '')
        setEmail(user?.email ?? '')
        setPassword('')
        setActive(user?.is_active ?? true)
        setRoleName(user?.roles[0]?.name ?? roles[0]?.name ?? '')
        avatarFileRef.current = null
        if (previewUrlRef.current) {
            URL.revokeObjectURL(previewUrlRef.current)
            previewUrlRef.current = null
        }
        setAvatarPreview(user?.avatar_url ?? null)
    }, [open, user, roles])

    useEffect(() => {
        return () => {
            if (previewUrlRef.current) URL.revokeObjectURL(previewUrlRef.current)
        }
    }, [])

    function handleAvatarChange(files: File[]) {
        const file = files[0]
        if (!file) return
        avatarFileRef.current = file

        const url = URL.createObjectURL(file)
        if (previewUrlRef.current) URL.revokeObjectURL(previewUrlRef.current)
        previewUrlRef.current = url
        setAvatarPreview(url)
    }

    async function handleSave() {
        if (!roleName) return
        setSaving(true)
        try {
            const payload: UserPayload = {
                name,
                email,
                password: password || undefined,
                is_active: active,
                role_name: roleName,
            }
            const saved = user
                ? await usersApi.update(user.id, payload)
                : await usersApi.create(payload)

            if (avatarFileRef.current) {
                await usersApi.uploadAvatar(saved.data.id, avatarFileRef.current)
            }
            toast({
                title: t('user_form.saved_title'),
                message: t('user_form.saved_message'),
                color: 'success',
            })
            onSaved()
            onClose()
        } finally {
            setSaving(false)
        }
    }

    const roleOptions = roles.map((role) => ({value: role.name, label: role.name}))

    return (
        <MModal
            open={open}
            onClose={onClose}
            title={user ? t('user_form.title_edit') : t('user_form.title_create')}
            size={'md'}
            footer={
                <MInline justify={'between'} wrap={'wrap'}>
                    <MButton variant={'ghost'} onClick={onClose} disabled={saving}>
                        {t('user_form.cancel')}
                    </MButton>
                    <MButton variant={'filled'} color={'primary'} onClick={handleSave} loading={saving} disabled={saving}>
                        {t('user_form.save')}
                    </MButton>
                </MInline>
            }
        >
            <MStack spacing={'md'}>
                <MInline align={'center'}>
                    <MAvatar src={avatarPreview ?? undefined} name={name || 'U'} size={'lg'} />
                    <MStack spacing={'xs'}>
                        <MText size={'sm'} tone={'muted'}>
                            {t('user_form.avatar_hint')}
                        </MText>
                        <MInputFile
                            accept={'image/*'}
                            crop={{shape: 'circle', outputSize: 256}}
                            onChange={handleAvatarChange}
                            placeholder={t('user_form.avatar_placeholder')}
                        />
                    </MStack>
                </MInline>
                <MInput
                    label={t('user_form.name')}
                    value={name}
                    startIcon={<MUserIconV2 />}
                    onChange={(event) => setName(event.target.value)}
                    fullWidth
                />
                <MInputEmail
                    label={t('user_form.email')}
                    value={email}
                    onChange={(event) => setEmail(event.target.value)}
                    fullWidth
                />
                <MInputPassword
                    label={user ? t('user_form.password_new') : t('user_form.password')}
                    value={password}
                    onChange={(event) => setPassword(event.target.value)}
                    fullWidth
                />
                <MSelect
                    label={t('user_form.role')}
                    options={roleOptions}
                    value={roleName}
                    onChange={(value) => setRoleName(value as string)}
                    fullWidth
                />
                <MToggle
                    label={t('user_form.active')}
                    checked={active}
                    onChange={(event) => setActive(event.target.checked)}
                />
            </MStack>
        </MModal>
    )
}
