/*
 * Image card: the theme's ImageCard (widgets/layout.js); its blocks are buttons in front of the photo.
 */
import { islandBlock } from '../../shared/island';
import { ImageCard } from 'marccent-theme/components/widgets/layout';
import { CANVASES } from '../../shared/canvases';
import metadata from './block.json';

islandBlock( metadata, {
	fields: [
		{ key: 'src', label: 'Photo', type: 'image' },
		{ key: 'alt', label: 'Alt text', help: 'Empty: decorative.' },
		{ key: 'canvas', label: 'Live animation', help: 'Instead of the photo, or over it.', type: 'select', options: CANVASES },
		{ key: 'label', label: 'Label' },
		{ key: 'title', label: 'Title' },
		{ key: 'text', label: 'Text', type: 'textarea' },
		{ key: 'overlay', label: 'Behind the text', type: 'select', options: [ [ 'A fade', 'fade' ], [ 'Frosted glass', 'glass' ], [ 'Blur below', 'blur' ], [ 'Blur but the middle', 'focus' ], [ 'The gradient', 'tint' ], [ 'Nothing', 'none' ] ] },
		{ key: 'align', label: 'Text at the', type: 'select', options: [ [ 'Bottom', 'end' ], [ 'Middle', 'center' ], [ 'Top', 'start' ] ] },
		{ key: 'badge', label: 'Badge', help: 'A frosted chip in the corner: New, -20%…' },
		{ key: 'big', label: 'Big title', type: 'toggle' },
		{ key: 'ratio', label: 'Shape (aspect ratio)', type: 'select', options: [ [ 'Portrait 4 : 5', '4 / 5' ], [ 'Square', '1 / 1' ], [ 'Landscape 4 : 3', '4 / 3' ], [ 'Wide 16 : 9', '16 / 9' ], [ 'Tall 2 : 3', '2 / 3' ] ] },
		{ key: 'position', label: 'Keep in view when cropped', type: 'select', options: [ [ 'The center', 'center' ], [ 'The top', 'top' ], [ 'The bottom', 'bottom' ], [ 'The left', 'left' ], [ 'The right', 'right' ] ] },
		{ key: 'shape', label: 'Corners', type: 'select', options: [ [ 'Rounded', 'rounded' ], [ 'A circle', 'circle' ] ] },
	],
	toProps: ( a ) => Object.fromEntries( Object.entries( a ).filter( ( [ k, v ] ) => v !== '' && ! [ 'anchor', 'className' ].includes( k ) ) ),
	preview: ( p ) => <ImageCard { ...p } />,
	inner: { allowedBlocks: [ 'marccent/button' ], template: [], className: 'row-wrap gap-sm' },
} );
