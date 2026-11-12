/*
 * Orbit item: one of its Orbit's circles (the theme's OrbitItem), in the block or a Loop there.
 */
import { itemBlock } from '../../shared/item';
import metadata from './block.json';

itemBlock( metadata, {
	fields: [ { key: 'title', label: 'Title' }, { key: 'text', label: 'Text', type: 'textarea' }, { key: 'icon', label: 'Icon', type: 'icon' }, { key: 'image', label: 'Or an image', type: 'image' } ],
	name: ( a ) => a.title,
} );
