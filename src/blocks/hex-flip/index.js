/*
 * Hex flip: one hexagon of its Hex grid (the theme's HexFlip), in the block or a Loop there.
 */
import { itemBlock } from '../../shared/item';
import metadata from './block.json';

itemBlock( metadata, {
	fields: [
		{ key: 'front', label: 'Front', help: 'Initials or a word' },
		{ key: 'back', label: 'Back', help: 'A name and role' },
		{ key: 'label', label: 'Its name (for screen readers)', help: 'What both faces say, read once' },
		{ key: 'frontClass', label: 'Front colors', help: 'The theme’s classes: bg-gradient, bg-primary…' },
		{ key: 'backClass', label: 'Back colors', help: 'The theme’s classes: bg-primary-darker…' },
	],
	name: ( a ) => [ a.front, a.back ].filter( Boolean ).join( ': ' ),
	icon: null,
} );
