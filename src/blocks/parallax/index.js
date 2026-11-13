/*
 * Parallax: its blocks, which on the page lag the scroll (the theme's Parallax: runtime/islands/parallax.js).
 */
import { islandBlock } from '../../shared/island';
import metadata from './block.json';

islandBlock( metadata, {
	component: 'Parallax',
	overrides: { speed: { help: 'How much they lag: 0.1 a little, 0.5 a lot; negative moves the other way.', type: 'range', min: -1, max: 1, step: 0.05 } },
	note: 'Parallax: these blocks lag the scroll on the page.',
	inner: { template: [ [ 'core/paragraph', { placeholder: 'Blocks that drift as the page scrolls…' } ] ] },
} );
