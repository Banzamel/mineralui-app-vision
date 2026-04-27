import {MReveal} from '@banzamel/mineralui-pro/display'
import {MContainer, MGrid, MSection, MStack} from '@banzamel/mineralui-pro/layout'

import {Loading} from '../components/Loading'
import {
    CameraPreviewsWidget,
    DashboardKpis,
    PhotosByObjectChart,
    PhotosPerDayChart,
    RecentAlbumsWidget,
    UsersSummaryWidget,
    useDashboardData,
} from '../features/dashboard'

export function DashboardPage() {
    const {data, derived, loading, error, reload} = useDashboardData()

    return (
        <MSection as={'main'} spacing={'lg'}>
            <MContainer size={'wide'}>
                <Loading loading={loading} error={error} onRetry={reload} minHeight={400}>
                    {data && derived ? (
                        <MStack spacing={'lg'}>
                            <MReveal>
                                <DashboardKpis
                                    objects={data.objects.length}
                                    cameras={data.cameras.length}
                                    albums={derived.totalAlbums}
                                    photos={derived.totalPhotos}
                                />
                            </MReveal>

                            <MGrid type={'row'}>
                                <MGrid type={'col'} sm={12} md={12} lg={7} xl={7}>
                                    <MReveal>
                                        <CameraPreviewsWidget cameras={derived.featuredCameras} />
                                    </MReveal>
                                </MGrid>
                                <MGrid type={'col'} sm={12} md={12} lg={5} xl={5}>
                                    <MReveal>
                                        <UsersSummaryWidget
                                            users={data.users}
                                            loggedInToday={derived.usersLoggedInToday}
                                            activeCount={derived.usersActive}
                                        />
                                    </MReveal>
                                </MGrid>
                            </MGrid>

                            <MReveal>
                                <RecentAlbumsWidget albums={derived.recentAlbums} />
                            </MReveal>

                            <MGrid type={'row'}>
                                <MGrid type={'col'} sm={12} md={12} lg={8} xl={8}>
                                    <MReveal>
                                        <PhotosPerDayChart perDay={derived.perDay} />
                                    </MReveal>
                                </MGrid>
                                <MGrid type={'col'} sm={12} md={12} lg={4} xl={4}>
                                    <MReveal>
                                        <PhotosByObjectChart perObject={derived.perObject} />
                                    </MReveal>
                                </MGrid>
                            </MGrid>
                        </MStack>
                    ) : null}
                </Loading>
            </MContainer>
        </MSection>
    )
}
