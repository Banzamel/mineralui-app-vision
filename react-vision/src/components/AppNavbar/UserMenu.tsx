import {useState} from 'react'
import {useNavigate} from 'react-router-dom'

import {useMToast} from '@banzamel/mineralui-pro/feedback'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MBugIcon, MEditIcon, MLockOpenIcon, MMailIcon} from '@banzamel/mineralui-pro/icons'
import {MAvatar} from '@banzamel/mineralui-pro/media'
import {MDropdownGroup, MDropdownItem, MDropdownMenu} from '@banzamel/mineralui-pro/overlays'

import {useAuth} from '../../features/auth/AuthContext'
import {ReportBugModal} from '../../features/support/ReportBugModal'
import {UserProfileModal} from '../../features/users/modals'
import pl from '../../i18n/pl.json'

export function UserMenu() {
    const {t} = useMI18n<typeof pl>()
    const {toast} = useMToast()
    const {user, logout} = useAuth()
    const navigate = useNavigate()
    const [isProfileOpen, setIsProfileOpen] = useState(false)
    const [isReportBugOpen, setIsReportBugOpen] = useState(false)

    if (!user) {
        return null
    }

    function handleLogout() {
        logout()
        toast({
            title: t('navbar.logout_toast_title'),
            message: t('navbar.logout_toast_message'),
            color: 'info',
        })
        navigate('/login')
    }

    return (
        <>
            <MDropdownMenu
                placement={'bottom-end'}
                openOn={'hover'}
                trigger={
                    <MAvatar
                        src={user.avatar ?? undefined}
                        name={user.name}
                        size={36}
                        color={'primary'}
                        presence={'online'}
                    />
                }
            >
                <MDropdownGroup label={user.name}>
                    <MDropdownItem icon={<MMailIcon />} label={user.email} disabled />
                </MDropdownGroup>
                <MDropdownGroup label={t('navbar.settings')}>
                    <MDropdownItem
                        icon={<MEditIcon />}
                        label={t('navbar.edit_profile')}
                        onClick={() => setIsProfileOpen(true)}
                    />
                    <MDropdownItem
                        icon={<MBugIcon />}
                        label={t('navbar.report_bug')}
                        onClick={() => setIsReportBugOpen(true)}
                    />
                    <MDropdownItem
                        icon={<MLockOpenIcon />}
                        label={t('navbar.logout')}
                        onClick={handleLogout}
                    />
                </MDropdownGroup>
            </MDropdownMenu>

            <UserProfileModal open={isProfileOpen} onClose={() => setIsProfileOpen(false)} />
            <ReportBugModal open={isReportBugOpen} onClose={() => setIsReportBugOpen(false)} />
        </>
    )
}
