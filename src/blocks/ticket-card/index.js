/*
 * Ticket card: the theme's TicketCard (widgets/layout.js); its blocks (a button) are the action.
 */
import { islandBlock } from '../../shared/island';
import { TicketCard } from 'marccent/components/widgets/layout';
import metadata from './block.json';

islandBlock( metadata, {
	fields: [
		{ key: 'label', label: 'Label', help: 'Small, above the title: a plan, a date.' },
		{ key: 'title', label: 'Title', help: 'The big line: a price, a name.' },
		{ key: 'note', label: 'Note', help: 'After the title: a month, a place.' },
		{ key: 'ribbon', label: 'Ribbon', help: 'A corner ribbon: Popular, Sold out. Empty: none.' },
		{ key: 'items', label: 'The list', type: 'list', itemLabel: 'Line', fields: [ { key: 'text', label: 'Text' }, { key: 'icon', label: 'Icon', type: 'icon', help: 'Empty: a check.' } ] },
	],
	toProps: ( a ) => ( { label: a.label, title: a.title, note: a.note, ribbon: a.ribbon || undefined, items: ( a.items || [] ).filter( ( i ) => i.text ).map( ( i ) => ( { text: i.text, icon: i.icon || 'check' } ) ) } ),
	preview: ( p ) => <TicketCard { ...p } />,
	inner: { allowedBlocks: [ 'marccent/button' ], template: [ [ 'marccent/button', { label: 'Choose', icon: 'arrow-right' } ] ], className: 'row-wrap gap-sm justify-center' },
} );
