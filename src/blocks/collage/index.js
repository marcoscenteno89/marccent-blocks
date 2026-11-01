/*
 * Collage: tiles of image cards on the theme's Collage (shared/collage.js), photos or live animations.
 */
import { islandBlock } from '../../shared/island';
import { CollageOf } from '../../shared/collage';
import { CANVASES } from '../../shared/canvases';
import metadata from './block.json';

islandBlock( metadata, {
	fields: [
		{ key: 'tiles', label: 'Tiles', type: 'list', itemLabel: 'Tile', fields: [
			{ key: 'src', label: 'Photo', type: 'image' },
			{ key: 'canvas', label: 'Live animation', type: 'select', options: CANVASES },
			{ key: 'size', label: 'Size', type: 'select', default: 'square', options: [ [ 'Square', 'square' ], [ 'Circle', 'circle' ], [ 'Portrait (2 rows)', 'portrait' ], [ 'Landscape (2 columns)', 'landscape' ], [ 'Wide (3 columns)', 'wide' ], [ 'Feature (2 × 2)', 'feature' ], [ 'Panorama (4 columns)', 'panorama' ] ] },
			{ key: 'border', label: 'Border', type: 'select', options: [ [ 'None', '' ], [ 'A line', 'line' ], [ 'A frame', 'frame' ], [ 'The primary', 'accent' ] ] },
			{ key: 'label', label: 'Label' }, { key: 'title', label: 'Title' }, { key: 'text', label: 'Text' },
			{ key: 'overlay', label: 'Behind the text', type: 'select', options: [ [ 'A fade', 'fade' ], [ 'Frosted glass', 'glass' ], [ 'Blur below', 'blur' ], [ 'The gradient', 'tint' ], [ 'Nothing', 'none' ] ] },
			{ key: 'badge', label: 'Badge' },
		] },
		{ key: 'cols', label: 'Columns', type: 'range', min: 2, max: 8 },
		{ key: 'row', label: 'Row height', help: 'e.g. 11rem' },
	],
	preview: ( p ) => <CollageOf { ...p } />,
} );
