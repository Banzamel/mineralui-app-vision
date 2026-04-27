import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MInput, MInputEmail, MInputPassword} from '@banzamel/mineralui-pro/inputs'
import {MStack} from '@banzamel/mineralui-pro/layout'

import pl from '../../i18n/pl.json'
import type {InstallAdmin} from './types'

interface Props {
    value: InstallAdmin
    onChange: (value: InstallAdmin) => void
}

export function StepAdmin({value, onChange}: Props) {
    const {t} = useMI18n<typeof pl>()

    function patch(partial: Partial<InstallAdmin>) {
        onChange({...value, ...partial})
    }

    const confirmMismatch =
        value.password_confirmation.length > 0 && value.password !== value.password_confirmation

    return (
        <MStack spacing={'md'}>
            <MInput
                label={t('install.admin_name')}
                value={value.name}
                onChange={(e) => patch({name: e.target.value})}
                fullWidth
                required
            />
            <MInputEmail
                label={t('install.admin_email')}
                value={value.email}
                onChange={(e) => patch({email: e.target.value})}
                autoComplete={'username'}
                fullWidth
                required
            />
            <MInputPassword
                label={t('install.admin_password')}
                value={value.password}
                onChange={(e) => patch({password: e.target.value})}
                autoComplete={'new-password'}
                fullWidth
                required
                helperText={t('install.admin_password_hint')}
            />
            <MInputPassword
                label={t('install.admin_password_confirmation')}
                value={value.password_confirmation}
                onChange={(e) => patch({password_confirmation: e.target.value})}
                autoComplete={'new-password'}
                fullWidth
                required
                error={confirmMismatch}
                helperText={confirmMismatch ? t('install.admin_password_mismatch') : undefined}
            />
        </MStack>
    )
}
