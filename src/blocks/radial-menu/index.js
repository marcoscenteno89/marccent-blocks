/*
 * RadialMenu: the theme's RadialMenu (widgets/nav.js) from a list of items, each a link.
 */
import { islandBlock } from '../../shared/island';
import { RadialMenu } from 'marccent-theme/components/widgets/nav';
import metadata from './block.json';

islandBlock( metadata, {
	fields: [
		{ key: 'items', label: 'Items', type: 'list', itemLabel: 'Item', fields: [
			{ key: 'icon', label: 'Icon', type: 'icon' }, { key: 'label', label: 'Name' }, { key: 'href', label: 'Link', type: 'url' },
		] },
		{ key: 'radius', label: 'How far they fan out', help: 'e.g. 5rem' },
		{ key: 'label', label: 'Its name (for screen readers)' },
	],
	toProps: ( a ) => ( { ...a, items: ( a.items || [] ).filter( ( i ) => i.label && i.icon ) } ),
	preview: ( p ) => p.items.length > 0 && <RadialMenu items={ p.items } label={ p.label || undefined } radius={ p.radius || undefined } />,
} );
