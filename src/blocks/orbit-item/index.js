/*
 * Orbit item: one of its Orbit's circles (the theme's OrbitItem), in the block or a Loop there.
 */
import { itemBlock } from '../../shared/item';
import metadata from './block.json';

itemBlock( metadata, {
	component: 'OrbitItem',
	overrides: { text: { type: 'textarea' }, icon: { type: 'icon' }, image: { label: 'Or an image', type: 'image' } },
	name: ( a ) => a.title,
} );
