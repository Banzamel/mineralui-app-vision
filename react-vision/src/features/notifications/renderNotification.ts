import {interpolate} from '../../i18n/interpolate'
import type {Notification} from './types'

/**
 * Renders a notification title + message via i18n. When the backend supplies a structured
 * `data` payload, we look up `notifications_center.types.<type>.{title,message}` and
 * interpolate the params. Otherwise (or when the type has no translation) we fall back
 * to `notification.title` / `notification.message` (EN strings persisted by the listener).
 *
 * MineralUI's `t()` returns the key itself when the translation is missing — that's how
 * we detect "no translation" and fall back gracefully.
 */
export function renderNotification(
    n: Notification,
    t: (key: string) => string,
): {title: string; message: string} {
    if (!n.data) {
        return {title: n.title, message: n.message}
    }

    const titleKey = `notifications_center.types.${n.type}.title`
    const messageKey = `notifications_center.types.${n.type}.message`
    const titleTpl = t(titleKey)
    const messageTpl = t(messageKey)

    // When `t()` returns the key unchanged the translation is missing — fall back.
    if (titleTpl === titleKey || messageTpl === messageKey) {
        return {title: n.title, message: n.message}
    }

    const params = n.data as Record<string, string | number>
    return {
        title: interpolate(titleTpl, params),
        message: interpolate(messageTpl, params),
    }
}
