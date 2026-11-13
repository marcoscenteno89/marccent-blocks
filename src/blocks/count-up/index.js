/*
 * Count up: the theme's CountUp (components/ui/count-up.js), a number that counts up as it scrolls into view. The
 * server writes it on the page (render.php, marccent_blocks_count_up); the theme's engine counts it.
 */
import { islandBlock } from '../../shared/island';
import { CountUp } from 'marccent-theme/components/ui/count-up';
import metadata from './block.json';

islandBlock( metadata, {
	component: 'CountUp',
	overrides: {
		decimals: { type: 'range', min: 0, max: 4 },
		locale: { help: 'en-US: 12,480.52; de-DE: 12.480,52' },
	},
	preview: ( p ) => <CountUp key={ JSON.stringify( p ) } { ...p } to={ Number( p.to ) || 0 } />,
} );
