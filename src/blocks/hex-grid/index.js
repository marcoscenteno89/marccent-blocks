/*
 * Hex grid: the theme's HexGrid of its Hex flip blocks (or a Loop of them), runtime/islands/hex-grid.js.
 */
import { islandBlock } from '../../shared/island';
import metadata from './block.json';

islandBlock( metadata, {
	component: 'HexGrid',
	overrides: { perRow: { type: 'range', min: 1, max: 8 }, gap: { type: 'range', min: 0.6, max: 1, step: 0.02 } },
	note: 'Hex grid: its hexagons are the Hex flip blocks below, or a Loop of them.',
	inner: { allowedBlocks: metadata.allowedBlocks, template: [ [ 'marccent/hex-flip', { back: 'Design' } ], [ 'marccent/hex-flip', { back: 'Build' } ], [ 'marccent/hex-flip', { back: 'Launch' } ] ] },
} );
