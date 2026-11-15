/*
 * Hex grid: the theme's HexGrid (widgets/layout.js); its hexagons are Hex flip blocks, or a Loop of them
 * (runtime/islands/hex-grid.js).
 */
import { islandBlock } from '../../shared/island';
import metadata from './block.json';

islandBlock( metadata, {
	fields: [ { key: 'perRow', label: 'Hexagons a row', type: 'range', min: 1, max: 12, help: 'Every other row starts half a hexagon in.' } ],
	note: 'Hex grid: its hexagons are the Hex flips below, or a Loop of them.',
	inner: { allowedBlocks: metadata.allowedBlocks, template: [ [ 'marccent/hex-flip', { front: 'AH', back: 'Ava Hansen, Owner', label: 'Ava Hansen, Owner' } ], [ 'marccent/hex-flip', { front: 'BL', back: 'Ben Lee, Designer', label: 'Ben Lee, Designer' } ], [ 'marccent/hex-flip', { front: 'CM', back: 'Cara Moss, Builder', label: 'Cara Moss, Builder' } ] ] },
} );
