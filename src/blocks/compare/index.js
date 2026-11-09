/*
 * Compare: the theme's Compare (ui/compare.js), two pictures split by a handle.
 */
import { islandBlock } from '../../shared/island';
import { Compare } from 'marccent/components/ui/compare';
import metadata from './block.json';

islandBlock( metadata, {
	fields: [
		{ key: 'before', label: 'Before (left)', type: 'image' },
		{ key: 'after', label: 'After (right)', type: 'image' },
		{ key: 'beforeLabel', label: 'Before’s tag' },
		{ key: 'afterLabel', label: 'After’s tag' },
		{ key: 'start', label: 'Handle starts at (%)', type: 'range', min: 0, max: 100 },
		{ key: 'label', label: 'Name (for screen readers)' },
		{ key: 'ratio', label: 'Shape', type: 'select', options: [ [ 'Wide 16 : 9', '16 / 9' ], [ 'Landscape 4 : 3', '4 / 3' ], [ 'Square', '1 / 1' ], [ 'Portrait 4 : 5', '4 / 5' ] ] },
	],
	note: 'Pick both pictures (the same shape) in the block’s settings.',
	preview: ( p ) => p.before && p.after && <Compare before={ <img src={ p.before } alt={ p.beforeLabel } /> } after={ <img src={ p.after } alt={ p.afterLabel } /> } beforeLabel={ p.beforeLabel } afterLabel={ p.afterLabel } start={ p.start } label={ p.label } style={ { aspectRatio: p.ratio } } />,
} );
