/*
 * An orbit block's component: its items on the theme's Orbit (each one's icon a Font Awesome icon, or an image), or
 * its OrbitClock or SolarSystem. The Orbit's settings are the block's (markup/settings.json): where it starts, the wheel
 * and center images, the hub, the wheel's offset, autoPlay, its interval and sync.
 */
import { Orbit, OrbitItem, OrbitClock, SolarSystem } from 'marccent-theme/components/visuals/effects';

export const OrbitOf = ( { kind = 'items', childBlocks = [], start = 0, startAngle = -90, wheel, center, hub = true, offset = 0, autoPlay = true, interval = 4000, sync = false } ) => {
	if ( kind === 'clock' ) return <OrbitClock />;
	if ( kind === 'solar' ) return <SolarSystem />;
	const items = childBlocks.filter( ( { props } ) => props.title );
	return items.length ? (
		<Orbit start={ start } startAngle={ startAngle } wheel={ wheel || undefined } center={ center || undefined } hub={ hub } offset={ offset }
			autoPlay={ autoPlay } interval={ interval } sync={ sync }>
			{ items.map( ( { props: { image, icon, title, text } }, i ) => (
				<OrbitItem key={ i } title={ title } text={ text } icon={ image ? <img src={ image } alt="" className="w-100" /> : icon } />
			) ) }
		</Orbit>
	) : null;
};
