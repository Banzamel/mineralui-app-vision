import {api} from '../../helpers/api'

export interface SavePushSubscriptionPayload {
    endpoint: string
    keys: {
        p256dh: string
        auth: string
    }
    user_agent?: string
}

export const pushApi = {
    save: (payload: SavePushSubscriptionPayload) =>
        api.post<{id: number}>('/vision/push/subscriptions', payload),
}
