/*
 * Gauge: the theme's Gauge (components/ui/gauge.js), one value on a dial. Markup and CSS only, so the server writes it
 * (render.php, marccent_blocks_gauge).
 */
import { islandBlock } from '../../shared/island';
import { Gauge } from 'marccent-theme/components/ui/gauge';
import metadata from './block.json';

islandBlock( metadata, {
	fields: [
		{ key: 'face', label: 'Look', type: 'select', options: [ [ 'Ring', 'ring' ], [ 'Speedometer', 'speedometer' ] ] },
		{ key: 'value', label: 'Value', type: 'number' },
		{ key: 'maximum', label: 'Top of the dial', type: 'number' },
		{ key: 'unit', label: 'Unit', help: 'After the number: %, ms' },
		{ key: 'label', label: 'Label', help: 'Under the number, and what screen readers hear it measures.' },
	],
	preview: ( p ) => <Gauge { ...p } value={ Number( p.value ) || 0 } maximum={ Number( p.maximum ) || 100 } />,
} );
