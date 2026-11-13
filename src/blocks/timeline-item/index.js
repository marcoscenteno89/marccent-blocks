/*
 * Timeline item: one event of its Timeline (the theme's TimelineItem), in the block or a Loop there.
 */
import { itemBlock } from '../../shared/item';
import metadata from './block.json';

itemBlock( metadata, {
	component: 'TimelineItem',
	overrides: { text: { type: 'textarea' }, icon: { type: 'icon' } },
	name: ( a ) => [ a.date, a.title ].filter( Boolean ).join( ': ' ),
} );
