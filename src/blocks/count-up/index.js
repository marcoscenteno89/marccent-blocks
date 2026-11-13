/*
 * Count up: the theme's CountUp (components/ui/count-up.js), a number that counts up as it scrolls into view. The
 * server writes it on the page (render.php, marccent_blocks_count_up); the theme's engine counts it.
 */
import { islandBlock } from '../../shared/island';
import { CountUp } from 'marccent-theme/components/ui/count-up';
import metadata from './block.json';

islandBlock( metadata, {
	fields: [
		{ key: 'to', label: 'The number', type: 'number' },
		{ key: 'decimals', label: 'Digits after the point', type: 'range', min: 0, max: 4 },
		{ key: 'prefix', label: 'Before it', help: 'A currency sign: $' },
		{ key: 'suffix', label: 'After it', help: '+, M, %' },
		{ key: 'duration', label: 'How long it counts (milliseconds)', type: 'number' },
		{ key: 'locale', label: 'Number format', help: 'en-US: 12,480.52; de-DE: 12.480,52' },
	],
	preview: ( p ) => <CountUp key={ JSON.stringify( p ) } { ...p } to={ Number( p.to ) || 0 } />,
} );
