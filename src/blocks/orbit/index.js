/*
 * Orbit: items on the theme's Orbit, or its orbit clock or solar system (shared/orbit.js).
 */
import { islandBlock } from '../../shared/island';
import { OrbitOf } from '../../shared/orbit';
import metadata from './block.json';

islandBlock( metadata, {
	fields: [
		{ key: 'kind', label: 'Show', type: 'select', options: [ [ 'Items on a ring', 'items' ], [ 'The orbit clock', 'clock' ], [ 'A solar system', 'solar' ] ] },
		{ key: 'items', label: 'Items', type: 'list', itemLabel: 'Item', fields: [
			{ key: 'title', label: 'Title' }, { key: 'text', label: 'Text', type: 'textarea' },
			{ key: 'icon', label: 'Icon', type: 'icon' }, { key: 'image', label: 'Or an image', type: 'image' },
		] },
		{ key: 'startAngle', label: 'The first item at (degrees from 3 o’clock)', type: 'range', min: -180, max: 180 },
		{ key: 'autoPlay', label: 'Turns by itself', type: 'toggle' },
		{ key: 'interval', label: 'Each item for (ms)', type: 'range', min: 1000, max: 10000, step: 500 },
	],
	preview: ( p ) => <div style={ { maxWidth: 480 } }><OrbitOf { ...p } /></div>,
} );
