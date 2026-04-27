import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MInput} from '@banzamel/mineralui-pro/inputs'
import {MGrid, MGridItem, MStack} from '@banzamel/mineralui-pro/layout'
import {MText} from '@banzamel/mineralui-pro/typography'

import pl from '../../i18n/pl.json'
import type {InstallFirstCamera} from './types'

interface Props {
    value: InstallFirstCamera
    onChange: (value: InstallFirstCamera) => void
}

export function StepCamera({value, onChange}: Props) {
    const {t} = useMI18n<typeof pl>()

    function patch(partial: Partial<InstallFirstCamera>) {
        onChange({...value, ...partial})
    }

    return (
        <MStack spacing={'md'}>
            <MText tone={'muted'} size={'sm'}>
                {t('install.camera_hint')}
            </MText>
            <MInput
                label={t('install.camera_name')}
                value={value.name}
                onChange={(e) => patch({name: e.target.value})}
                fullWidth
                required
            />
            <MInput
                label={t('install.camera_address')}
                value={value.address}
                onChange={(e) => patch({address: e.target.value})}
                helperText={t('install.camera_address_hint')}
                fullWidth
            />
            <MGrid type={'row'}>
                <MGridItem sm={12} md={6}>
                    <MInput
                        label={t('install.camera_ip')}
                        value={value.ip}
                        onChange={(e) => patch({ip: e.target.value})}
                        placeholder={'192.168.1.100'}
                        fullWidth
                        required
                    />
                </MGridItem>
                <MGridItem sm={12} md={6}>
                    <MInput
                        label={t('install.camera_stream_url')}
                        value={value.stream_url}
                        onChange={(e) => patch({stream_url: e.target.value})}
                        placeholder={'rtsp://...'}
                        fullWidth
                    />
                </MGridItem>
            </MGrid>
            <MGrid type={'row'}>
                <MGridItem sm={12} md={6}>
                    <MInput
                        label={t('install.camera_login')}
                        value={value.stream_login}
                        onChange={(e) => patch({stream_login: e.target.value})}
                        autoComplete={'off'}
                        fullWidth
                    />
                </MGridItem>
                <MGridItem sm={12} md={6}>
                    <MInput
                        label={t('install.camera_password')}
                        type={'password'}
                        value={value.stream_password}
                        onChange={(e) => patch({stream_password: e.target.value})}
                        autoComplete={'new-password'}
                        fullWidth
                    />
                </MGridItem>
            </MGrid>
        </MStack>
    )
}
