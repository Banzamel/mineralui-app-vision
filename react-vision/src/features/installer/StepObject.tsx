import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MSelect} from '@banzamel/mineralui-pro/dropdowns'
import {MInput} from '@banzamel/mineralui-pro/inputs'
import {MStack} from '@banzamel/mineralui-pro/layout'

import pl from '../../i18n/pl.json'
import type {ObjectType} from '../objects/types'
import type {InstallFirstObject} from './types'

interface Props {
    value: InstallFirstObject
    onChange: (value: InstallFirstObject) => void
}

const OBJECT_TYPES: ObjectType[] = ['block', 'apartment', 'house', 'hangar', 'garage', 'other']

export function StepObject({value, onChange}: Props) {
    const {t} = useMI18n<typeof pl>()

    function patch(partial: Partial<InstallFirstObject>) {
        onChange({...value, ...partial})
    }

    const typeOptions = OBJECT_TYPES.map((type) => ({
        value: type,
        label: t(`object_type.${type}`),
    }))

    return (
        <MStack spacing={'md'}>
            <MInput
                label={t('install.object_name')}
                value={value.name}
                onChange={(e) => patch({name: e.target.value})}
                fullWidth
                required
                helperText={t('install.object_name_hint')}
            />
            <MSelect
                label={t('install.object_type')}
                options={typeOptions}
                value={value.type}
                onChange={(v) => patch({type: (v as ObjectType) || 'block'})}
                fullWidth
                required
            />
        </MStack>
    )
}
