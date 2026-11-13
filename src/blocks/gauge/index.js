/*
 * Gauge: the theme's Gauge (components/ui/gauge.js), one value on a dial. Markup and CSS only, so the server writes it
 * (render.php, marccent_blocks_gauge).
 */
import { islandBlock } from '../../shared/island';
import { Gauge } from 'marccent-theme/components/ui/gauge';
import metadata from './block.json';

islandBlock( metadata, {
	component: 'Gauge',
	overrides: { face: { label: 'Look', options: [ [ 'Ring', 'ring' ], [ 'Speedometer', 'speedometer' ] ] } },
	preview: ( p ) => <Gauge { ...p } value={ Number( p.value ) || 0 } maximum={ Number( p.maximum ) || 100 } />,
} );
