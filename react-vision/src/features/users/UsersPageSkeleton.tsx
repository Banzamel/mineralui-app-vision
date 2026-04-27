import {MSkeleton} from '@banzamel/mineralui-pro/feedback'
import {MSimpleGrid, MStack} from '@banzamel/mineralui-pro/layout'

/**
 * Layout-aware placeholder shown while UsersPage is loading. Mirrors the actual
 * structure (4 stat cards + chart + tabs + table) so the page does not visibly
 * shift when real data lands.
 */
export function UsersPageSkeleton() {
    return (
        <MStack spacing={'lg'}>
            <MSimpleGrid columns={2} minItemWidth={'320px'}>
                <MSimpleGrid columns={2} minItemWidth={'320px'}>
                    {Array.from({length: 4}).map((_, i) => (
                        <MSkeleton key={i} variant={'rectangle'} width={'100%'} height={120} radius={12} animate={'shimmer'} />
                    ))}
                </MSimpleGrid>
                <MSkeleton variant={'rectangle'} width={'100%'} height={252} radius={12} animate={'shimmer'} />
            </MSimpleGrid>
            <MStack spacing={'sm'}>
                <MSkeleton variant={'rectangle'} width={220} height={40} radius={8} animate={'shimmer'} />
                {Array.from({length: 5}).map((_, i) => (
                    <MSkeleton key={i} variant={'rectangle'} width={'100%'} height={64} radius={10} animate={'shimmer'} />
                ))}
            </MStack>
        </MStack>
    )
}
