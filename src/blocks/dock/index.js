/*
 * Dock: the theme's Dock (widgets/nav.js); its items are Dock item blocks, or a Loop of them (runtime/islands/dock.js).
 */
import { islandBlock } from '../../shared/island';
import metadata from './block.json';
import deprecated from './deprecated';

islandBlock( metadata, {
	component: 'Dock',
	note: 'Dock: its icons are the items below, or a Loop of them.',
	inner: { allowedBlocks: metadata.allowedBlocks, template: [ [ 'marccent/dock-item', { icon: 'house', label: 'Home', href: '/' } ], [ 'marccent/dock-item', { icon: 'envelope', label: 'Contact', href: '/contact/' } ], [ 'marccent/dock-item', { icon: 'fa-brands fa-github', label: 'GitHub', href: 'https://github.com', external: true } ] ] },
	deprecated,
} );
