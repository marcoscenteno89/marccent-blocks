/*
 * Radar: the theme's Radar (components/ui/radar.js), values on axes round a web, an SVG the server writes (render.php,
 * marccent_blocks_radar). Its axes are rows of a label and a value.
 */
import { islandBlock } from '../../shared/island';
import { Radar } from 'marccent-theme/components/ui/radar';
import metadata from './block.json';

islandBlock( metadata, {
	component: 'Radar',
	overrides: {
		axes: { itemLabel: 'Axis', fields: [ { key: 'label', label: 'Label' }, { key: 'value', label: 'Value', type: 'number', default: 50 } ] },
	},
	preview: ( p ) => ( p.axes?.length >= 3 ? <Radar { ...p } maximum={ Number( p.maximum ) || 100 } /> : <p className="pad-sm border round opacity-75">Add three or more axes.</p> ),
} );
