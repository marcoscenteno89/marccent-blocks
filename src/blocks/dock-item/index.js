/*
 * Dock item: one of its Dock's icons (the theme's DockItem), in the block or a Loop there.
 */
import { itemBlock } from '../../shared/item';
import metadata from './block.json';

itemBlock( metadata, {
	component: 'DockItem',
	overrides: { icon: { type: 'icon' }, href: { type: 'url' } },
	name: ( a ) => a.label,
} );
