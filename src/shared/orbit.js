/*
 * An orbit block's component: items on the theme's Orbit (each one's icon an image, or a Font Awesome icon), or its
 * OrbitClock or SolarSystem.
 */
import { Orbit, OrbitClock, SolarSystem } from 'marccent/components/visuals/effects';
import { Icon } from 'marccent/components/ui/icon';

export const OrbitOf = ( { kind = 'items', items = [], startAngle = -90, autoPlay = true, interval = 4000 } ) => {
	if ( kind === 'clock' ) return <OrbitClock />;
	if ( kind === 'solar' ) return <SolarSystem />;
	const list = items.map( ( { image, icon, title = '', text } ) => ( { title, text, icon: image || ( icon ? <Icon name={ icon } /> : undefined ) } ) );
	return list.length ? <Orbit items={ list } startAngle={ startAngle } autoPlay={ autoPlay } interval={ interval } /> : null;
};
