/*
 * RadialMenu: the theme's RadialMenu (widgets/nav.js); its items are Radial menu item blocks, or a Loop of them
 * (runtime/islands/radial-menu.js).
 */
import { islandBlock } from '../../shared/island';
import metadata from './block.json';
import deprecated from './deprecated';

islandBlock( metadata, {
	fields: [
		{ key: 'radius', label: 'How far they fan out', help: 'e.g. 5rem' },
		{ key: 'label', label: 'Its name (for screen readers)' },
	],
	note: 'Radial menu: its icons are the items below, or a Loop of them.',
	inner: { allowedBlocks: metadata.allowedBlocks, template: [ [ 'marccent/radial-menu-item', { icon: 'phone', label: 'Call', href: 'tel:+12085550100' } ], [ 'marccent/radial-menu-item', { icon: 'envelope', label: 'Email', href: 'mailto:hello@example.com' } ], [ 'marccent/radial-menu-item', { icon: 'location-dot', label: 'Directions', href: '#' } ] ] },
	deprecated,
} );
