/*
 * Radial menu item: one of its Radial menu's icons (the theme's RadialMenuItem), in the block or a Loop there.
 */
import { itemBlock } from '../../shared/item';
import metadata from './block.json';

itemBlock( metadata, {
	fields: [ { key: 'icon', label: 'Icon', type: 'icon' }, { key: 'label', label: 'Name' }, { key: 'href', label: 'Link', type: 'url' } ],
	name: ( a ) => a.label,
} );
