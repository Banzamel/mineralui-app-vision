import {useEffect, useMemo, useState} from 'react'

import {MButton} from '@banzamel/mineralui-pro/controls'
import {MSelect} from '@banzamel/mineralui-pro/dropdowns'
import {useMToast} from '@banzamel/mineralui-pro/feedback'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MInput, MInputFile} from '@banzamel/mineralui-pro/inputs'
import {MInline, MStack} from '@banzamel/mineralui-pro/layout'
import {MModal} from '@banzamel/mineralui-pro/overlays'

import {fileToDataUrl} from '../../helpers'
import pl from '../../i18n/pl.json'
import type {VisionObject} from '../objects/types'
import {camerasApi} from './api'
import type {Camera, CameraPayload} from './types'

interface CameraFormModalProps {
    open: boolean
    camera: Camera | null
    allObjects: VisionObject[]
    defaultObjectId?: number | null
    onClose: () => void
    onSaved: () => void
}

export function CameraFormModal({
    open,
    camera,
    allObjects,
    defaultObjectId,
    onClose,
    onSaved,
}: CameraFormModalProps) {
    const {t} = useMI18n<typeof pl>()
    const {toast} = useMToast()
    const [name, setName] = useState('')
    const [objectId, setObjectId] = useState<number | null>(null)
    const [address, setAddress] = useState('')
    const [ip, setIp] = useState('')
    const [streamUrl, setStreamUrl] = useState('')
    const [streamLogin, setStreamLogin] = useState('')
    const [streamPassword, setStreamPassword] = useState('')
    const [mainPhoto, setMainPhoto] = useState('')
    const [saving, setSaving] = useState(false)

    useEffect(() => {
        if (!open) return
        setName(camera?.name ?? '')
        setObjectId(camera?.object_id ?? defaultObjectId ?? null)
        setAddress(camera?.address ?? '')
        setIp(camera?.ip ?? '')
        setStreamUrl(camera?.stream_url ?? '')
        setStreamLogin(camera?.stream_login ?? '')
        setStreamPassword(camera?.stream_password ?? '')
        setMainPhoto(camera?.main_photo_url ?? '')
    }, [open, camera, defaultObjectId])

    const objectOptions = useMemo(() => {
        return allObjects.map((o) => ({
            value: String(o.id),
            label: `${'— '.repeat(o.depth)}${o.name}`,
        }))
    }, [allObjects])

    async function handleMainPhotoChange(files: File[]) {
        const file = files[0]
        if (!file) {
            setMainPhoto('')
            return
        }
        setMainPhoto(await fileToDataUrl(file))
    }

    async function handleSave() {
        if (objectId == null) return
        setSaving(true)
        try {
            const payload: CameraPayload = {
                object_id: objectId,
                name,
                address,
                ip,
                stream_url: streamUrl,
                stream_login: streamLogin || null,
                stream_password: streamPassword || null,
            }
            const saved = camera
                ? await camerasApi.update(camera.id, payload)
                : await camerasApi.create(payload)
            // Upload only if user selected a new file (data URL); existing http(s) URLs are
            // already in the backend and don't need re-uploading.
            if (mainPhoto && mainPhoto.startsWith('data:')) {
                const blob = await (await fetch(mainPhoto)).blob()
                const file = new File([blob], 'camera.png', {type: blob.type || 'image/png'})
                await camerasApi.uploadMainPhoto(saved.data.id, file)
            }
            toast({
                title: t('camera_form.saved_title'),
                message: t('camera_form.saved_message'),
                color: 'success',
            })
            onSaved()
            onClose()
        } finally {
            setSaving(false)
        }
    }

    const valid = name.trim() && address.trim() && ip.trim() && objectId != null

    return (
        <MModal
            open={open}
            onClose={onClose}
            title={camera ? t('camera_form.title_edit') : t('camera_form.title_create')}
            size={'md'}
            footer={
                <MInline justify={'between'} wrap={'wrap'}>
                    <MButton variant={'ghost'} onClick={onClose} disabled={saving}>
                        {t('camera_form.cancel')}
                    </MButton>
                    <MButton
                        variant={'filled'}
                        color={'primary'}
                        onClick={handleSave}
                        loading={saving}
                        disabled={saving || !valid}
                    >
                        {t('camera_form.save')}
                    </MButton>
                </MInline>
            }
        >
            <MStack spacing={'md'}>
                <MInput
                    label={t('camera_form.name')}
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    fullWidth
                    required
                />
                <MSelect
                    label={t('camera_form.object')}
                    options={objectOptions}
                    value={objectId != null ? String(objectId) : ''}
                    onChange={(v) => setObjectId(v ? Number(v) : null)}
                    searchable
                    fullWidth
                    required
                />
                <MInput
                    label={t('camera_form.address')}
                    value={address}
                    onChange={(e) => setAddress(e.target.value)}
                    fullWidth
                    required
                    helperText={t('camera_form.address_hint')}
                />
                <MInput
                    label={t('camera_form.ip')}
                    value={ip}
                    onChange={(e) => setIp(e.target.value)}
                    fullWidth
                    required
                    placeholder={'192.168.1.100'}
                />
                <MInput
                    label={t('camera_form.stream_url')}
                    value={streamUrl}
                    onChange={(e) => setStreamUrl(e.target.value)}
                    fullWidth
                    placeholder={'rtsp://...'}
                    helperText={t('camera_form.stream_url_hint')}
                />
                <MInline wrap={'wrap'} fullWidth>
                    <MInput
                        label={t('camera_form.stream_login')}
                        value={streamLogin}
                        onChange={(e) => setStreamLogin(e.target.value)}
                        autoComplete={'off'}
                        fullWidth
                    />
                    <MInput
                        label={t('camera_form.stream_password')}
                        type={'password'}
                        value={streamPassword}
                        onChange={(e) => setStreamPassword(e.target.value)}
                        autoComplete={'new-password'}
                        fullWidth
                    />
                </MInline>
                <MInputFile
                    label={t('camera_form.main_photo')}
                    accept={'image/*'}
                    onChange={handleMainPhotoChange}
                    onClear={() => setMainPhoto('')}
                    preview
                    clearable
                    fullWidth
                    placeholder={t('camera_form.main_photo_placeholder')}
                />
            </MStack>
        </MModal>
    )
}
