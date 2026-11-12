/*
 * An orbit block's component: its items on the theme's Orbit (each one's icon a Font Awesome icon, or an image), or
 * its OrbitClock or SolarSystem.
 */
import { Orbit, OrbitItem, OrbitClock, SolarSystem } from 'marccent-theme/components/visuals/effects';

export const OrbitOf = ( { kind = 'items', childBlocks = [], startAngle = -90, autoPlay = true, interval = 4000 } ) => {
	if ( kind === 'clock' ) return <OrbitClock />;
	if ( kind === 'solar' ) return <SolarSystem />;
	const items = childBlocks.filter( ( { props } ) => props.title );
	return items.length ? (
		<Orbit startAngle={ startAngle } autoPlay={ autoPlay } interval={ interval }>
			{ items.map( ( { props: { image, icon, title, text } }, i ) => (
				<OrbitItem key={ i } title={ title } text={ text } icon={ image ? <img src={ image } alt="" className="w-100" /> : icon } />
			) ) }
		</Orbit>
	) : null;
};
