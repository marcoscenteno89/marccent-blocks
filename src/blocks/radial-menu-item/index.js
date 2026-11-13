/*
 * Radial menu item: one of its Radial menu's icons (the theme's RadialMenuItem), in the block or a Loop there.
 */
import { itemBlock } from '../../shared/item';
import metadata from './block.json';

itemBlock( metadata, {
	component: 'RadialMenuItem',
	overrides: { icon: { type: 'icon' }, href: { type: 'url' } },
	name: ( a ) => a.label,
} );
