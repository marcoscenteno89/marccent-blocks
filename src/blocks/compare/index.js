/*
 * Compare: the theme's Compare (ui/compare.js), two pictures split by a handle.
 */
import { islandBlock } from '../../shared/island';
import { Compare } from 'marccent-theme/components/ui/compare';
import metadata from './block.json';

islandBlock( metadata, {
	component: 'Compare',
	overrides: {
		before: { label: 'Before (left)', type: 'image' },
		after: { label: 'After (right)', type: 'image' },
		start: { type: 'range', min: 0, max: 100 },
		ratio: { label: 'Shape', type: 'select', options: [ [ 'Wide 16 : 9', '16 / 9' ], [ 'Landscape 4 : 3', '4 / 3' ], [ 'Square', '1 / 1' ], [ 'Portrait 4 : 5', '4 / 5' ] ] },
	},
	note: 'Pick both pictures (the same shape) in the block’s settings.',
	preview: ( p ) => p.before && p.after && <Compare before={ <img src={ p.before } alt={ p.beforeLabel } /> } after={ <img src={ p.after } alt={ p.afterLabel } /> } beforeLabel={ p.beforeLabel } afterLabel={ p.afterLabel } start={ p.start } label={ p.label } style={ { aspectRatio: p.ratio } } />,
} );
