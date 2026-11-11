/*
 * Timeline item: one event of its Timeline (the theme's TimelineItem), in the block or a Loop there.
 */
import { itemBlock } from '../../shared/item';
import metadata from './block.json';

itemBlock( metadata, {
	fields: [ { key: 'date', label: 'When', help: 'A year or a date' }, { key: 'title', label: 'Title' }, { key: 'text', label: 'Text', type: 'textarea' }, { key: 'icon', label: 'Icon', type: 'icon' } ],
	name: ( a ) => [ a.date, a.title ].filter( Boolean ).join( ': ' ),
} );
