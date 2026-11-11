/*
 * Orbit: its Orbit item blocks (or a Loop of them) on the theme's Orbit, or its orbit clock or solar system
 * (shared/orbit.js).
 */
import { islandBlock } from '../../shared/island';
import { OrbitOf } from '../../shared/orbit';
import metadata from './block.json';
import deprecated from './deprecated';

islandBlock( metadata, {
	fields: [
		{ key: 'kind', label: 'Show', type: 'select', options: [ [ 'Items on a ring', 'items' ], [ 'The orbit clock', 'clock' ], [ 'A solar system', 'solar' ] ] },
		{ key: 'startAngle', label: 'The first item at (degrees from 3 o’clock)', type: 'range', min: -180, max: 180 },
		{ key: 'autoPlay', label: 'Turns by itself', type: 'toggle' },
		{ key: 'interval', label: 'Each item for (ms)', type: 'range', min: 1000, max: 10000, step: 500 },
	],
	note: 'Orbit: with Items on a ring, its items are the blocks below, or a Loop of them.',
	preview: ( p ) => p.kind !== 'items' && <div style={ { maxWidth: 480 } }><OrbitOf { ...p } /></div>,
	inner: { allowedBlocks: metadata.allowedBlocks, template: [ [ 'marccent/orbit-item', { title: 'Plan', text: 'We listen first.' } ], [ 'marccent/orbit-item', { title: 'Build', text: 'Then we make it.' } ], [ 'marccent/orbit-item', { title: 'Launch', text: 'And ship it.' } ] ] },
	deprecated,
} );
