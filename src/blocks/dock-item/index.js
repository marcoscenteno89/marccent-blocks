/*
 * Dock item: one of its Dock's icons (the theme's DockItem), in the block or a Loop there.
 */
import { itemBlock } from '../../shared/item';
import metadata from './block.json';

itemBlock( metadata, {
	fields: [ { key: 'icon', label: 'Icon', type: 'icon' }, { key: 'label', label: 'Name' }, { key: 'href', label: 'Link', type: 'url' }, { key: 'external', label: 'Opens a new tab', type: 'toggle' } ],
	name: ( a ) => a.label,
} );
