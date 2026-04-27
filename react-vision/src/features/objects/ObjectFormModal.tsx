import {useEffect, useMemo, useState} from 'react'

import {MButton} from '@banzamel/mineralui-pro/controls'
import {useMToast} from '@banzamel/mineralui-pro/feedback'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MInput, MInputFile, MTextarea} from '@banzamel/mineralui-pro/inputs'
import {MSelect} from '@banzamel/mineralui-pro/dropdowns'
import {MInline, MStack} from '@banzamel/mineralui-pro/layout'
import {MModal} from '@banzamel/mineralui-pro/overlays'

import {fileToDataUrl} from '../../helpers'
import pl from '../../i18n/pl.json'
import {objectsApi} from './api'
import {MAX_OBJECT_DEPTH, getDescendantIds} from './helpers'
import type {ObjectType, VisionObject, VisionObjectPayload} from './types'

const OBJECT_TYPES: ObjectType[] = ['block', 'apartment', 'house', 'hangar', 'garage', 'other']

interface ObjectFormModalProps {
    open: boolean
    object: VisionObject | null
    allObjects: VisionObject[]
    defaultParentId?: number | null
    onClose: () => void
    onSaved: () => void
}

export function ObjectFormModal({
    open,
    object,
    allObjects,
    defaultParentId,
    onClose,
    onSaved,
}: ObjectFormModalProps) {
    const {t} = useMI18n<typeof pl>()
    const {toast} = useMToast()
    const [name, setName] = useState('')
    const [type, setType] = useState<ObjectType>('block')
    const [parentId, setParentId] = useState<number | null>(null)
    const [address, setAddress] = useState('')
    const [description, setDescription] = useState('')
    const [mainPhoto, setMainPhoto] = useState('')
    const [saving, setSaving] = useState(false)

    useEffect(() => {
        if (!open) return
        setName(object?.name ?? '')
        setType(object?.type ?? 'block')
        setParentId(object?.parent_id ?? defaultParentId ?? null)
        setAddress(object?.address ?? '')
        setDescription(object?.description ?? '')
        setMainPhoto(object?.main_photo_url ?? '')
    }, [open, object, defaultParentId])

    const parentOptions = useMemo(() => {
        const forbidden = object ? getDescendantIds(allObjects, object.id) : new Set<number>()
        if (object) forbidden.add(object.id)
        const options = [{value: '', label: t('object_form.parent_none')}]
        for (const o of allObjects) {
            if (forbidden.has(o.id)) continue
            if (o.depth >= MAX_OBJECT_DEPTH) continue
            const indent = '— '.repeat(o.depth)
            options.push({value: String(o.id), label: `${indent}${o.name}`})
        }
        return options
    }, [allObjects, object, t])

    const typeOptions = useMemo(
        () => OBJECT_TYPES.map((tp) => ({value: tp, label: t(`object_type.${tp}`)})),
        [t],
    )

    async function handleMainPhotoChange(files: File[]) {
        const file = files[0]
        if (!file) {
            setMainPhoto('')
            return
        }
        setMainPhoto(await fileToDataUrl(file))
    }

    async function handleSave() {
        setSaving(true)
        try {
            const payload: VisionObjectPayload = {
                parent_id: parentId,
                name,
                type,
                address,
                description: description || undefined,
            }
            const saved = object
                ? await objectsApi.update(object.id, payload)
                : await objectsApi.create(payload)
            // Upload main photo only when the user picked a NEW file (mainPhoto became a data URL).
            // Existing http(s) URLs come from the backend and don't need a re-upload.
            if (mainPhoto && mainPhoto.startsWith('data:')) {
                const blob = await (await fetch(mainPhoto)).blob()
                const file = new File([blob], 'object.png', {type: blob.type || 'image/png'})
                await objectsApi.uploadMainPhoto(saved.data.id, file)
            }
            toast({
                title: t('object_form.saved_title'),
                message: t('object_form.saved_message'),
                color: 'success',
            })
            onSaved()
            onClose()
        } finally {
            setSaving(false)
        }
    }

    return (
        <MModal
            open={open}
            onClose={onClose}
            title={object ? t('object_form.title_edit') : t('object_form.title_create')}
            size={'md'}
            footer={
                <MInline justify={'between'} wrap={'wrap'}>
                    <MButton variant={'ghost'} onClick={onClose} disabled={saving}>
                        {t('object_form.cancel')}
                    </MButton>
                    <MButton
                        variant={'filled'}
                        color={'primary'}
                        onClick={handleSave}
                        loading={saving}
                        disabled={saving || !name.trim() || !address.trim()}
                    >
                        {t('object_form.save')}
                    </MButton>
                </MInline>
            }
        >
            <MStack spacing={'md'}>
                <MInput
                    label={t('object_form.name')}
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    fullWidth
                    required
                />
                <MSelect
                    label={t('object_form.type')}
                    options={typeOptions}
                    value={type}
                    onChange={(v) => setType(v as ObjectType)}
                    fullWidth
                />
                <MSelect
                    label={t('object_form.parent')}
                    options={parentOptions}
                    value={parentId != null ? String(parentId) : ''}
                    onChange={(v) => setParentId(v ? Number(v) : null)}
                    searchable
                    fullWidth
                />
                <MInput
                    label={t('object_form.address')}
                    value={address}
                    onChange={(e) => setAddress(e.target.value)}
                    fullWidth
                    required
                />
                <MTextarea
                    label={t('object_form.description')}
                    value={description}
                    onChange={(e) => setDescription(e.target.value)}
                    fullWidth
                    rows={2}
                />
                <MInputFile
                    label={t('object_form.main_photo')}
                    accept={'image/*'}
                    onChange={handleMainPhotoChange}
                    onClear={() => setMainPhoto('')}
                    preview
                    clearable
                    fullWidth
                    placeholder={t('object_form.main_photo_placeholder')}
                />
            </MStack>
        </MModal>
    )
}
