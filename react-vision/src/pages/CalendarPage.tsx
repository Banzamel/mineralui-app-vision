import {MReveal} from '@banzamel/mineralui-pro/display'
import {MContainer, MSection} from '@banzamel/mineralui-pro/layout'

import {Loading} from '../components/Loading'
import {AlbumsCalendarWidget, useDashboardData} from '../features/dashboard'

export function CalendarPage() {
    const {derived, loading, error, reload} = useDashboardData({withUsers: false})

    return (
        <MSection as={'main'}>
            <MContainer size={'wide'}>
                <Loading loading={loading} error={error} onRetry={reload} minHeight={400}>
                    {derived ? (
                        <MReveal>
                            <AlbumsCalendarWidget enriched={derived.enriched} onReload={reload} />
                        </MReveal>
                    ) : null}
                </Loading>
            </MContainer>
        </MSection>
    )
}
