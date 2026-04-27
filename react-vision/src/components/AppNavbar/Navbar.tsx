import {Link, useLocation} from 'react-router-dom'

import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MInline, MNavbar, MNavs} from '@banzamel/mineralui-pro/layout'
import type {MNavsItem} from '@banzamel/mineralui-pro/layout'
import {MImage} from '@banzamel/mineralui-pro/media'
import {MLink} from '@banzamel/mineralui-pro/typography'

import {useAuth} from '../../features/auth/AuthContext'
import pl from '../../i18n/pl.json'
import {NotificationsDrawer} from '../AppDrawer'
import {LocaleSwitch} from './LocaleSwitch'
import {UserMenu} from './UserMenu'

export function Navbar() {
    const {t} = useMI18n<typeof pl>()
    const {pathname} = useLocation()
    const {hasPermission} = useAuth()

    const items: MNavsItem[] = []

    items.push({
        key: 'dashboard',
        label: t('navbar.home'),
        component: Link,
        to: '/',
        current: pathname === '/',
    })

    items.push({
        key: 'objects',
        label: t('navbar.menu_objects'),
        component: Link,
        to: '/objects',
        current: pathname.startsWith('/objects'),
    })

    items.push({
        key: 'calendar',
        label: t('navbar.menu_calendar'),
        component: Link,
        to: '/calendar',
        current: pathname.startsWith('/calendar'),
    })

    if (hasPermission('users.view')) {
        items.push({
            key: 'users',
            label: t('navbar.menu_users'),
            component: Link,
            to: '/users',
            current: pathname.startsWith('/users'),
        })
    }

    return (
        <MNavbar container={'wide'} sticky tone={'default'} justify={'between'}>
            <MLink component={Link} to={'/'} underline={'none'} aria-label={t('navbar.home')}>
                <MImage src={'/vision-logo.png'} alt={t('navbar.logo_alt')} height={60} />
            </MLink>

            <MNavs items={items} />

            <MInline align={'center'} justify={'end'}>
                <LocaleSwitch />
                <NotificationsDrawer />
                <UserMenu />
            </MInline>
        </MNavbar>
    )
}
