import {MLoader} from '@banzamel/mineralui-pro/feedback'
import {MSection} from '@banzamel/mineralui-pro/layout'

export function RouteLoader() {
    return (
        <MSection spacing={'lg'}>
            <MLoader label={'Ladowanie widoku Vision...'} />
        </MSection>
    )
}
