import {MReveal} from '@banzamel/mineralui-pro/display'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MContainer, MSection, MStack} from '@banzamel/mineralui-pro/layout'
import {MHeading, MText} from '@banzamel/mineralui-pro/typography'

import {Loading} from '../components/Loading'
import {AlbumsCalendarWidget, useDashboardData} from '../features/dashboard'
import pl from '../i18n/pl.json'

export function CalendarPage() {
    const {t} = useMI18n<typeof pl>()
    const {derived, loading, error, reload} = useDashboardData()

    return (
        <MSection as={'main'} spacing={'lg'}>
            <MContainer size={'wide'}>
                <MStack spacing={'lg'}>
                    <MStack spacing={'xs'}>
                        <MHeading level={1}>{t('calendar_page.title')}</MHeading>
                        <MText tone={'muted'}>{t('calendar_page.subtitle')}</MText>
                    </MStack>

                    <Loading loading={loading} error={error} onRetry={reload} minHeight={400}>
                        {derived ? (
                            <MReveal>
                                <AlbumsCalendarWidget enriched={derived.enriched} onReload={reload} />
                            </MReveal>
                        ) : null}
                    </Loading>
                </MStack>
            </MContainer>
        </MSection>
    )
}
