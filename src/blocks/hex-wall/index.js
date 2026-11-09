/*
 * Hex wall: the theme's HexWall (widgets/layout.js) from a list of people.
 */
import { islandBlock } from '../../shared/island';
import { HexWall } from 'marccent/components/widgets/layout';
import metadata from './block.json';

islandBlock( metadata, {
	fields: [
		{ key: 'people', label: 'People', type: 'list', itemLabel: 'Person', fields: [
			{ key: 'name', label: 'Name' }, { key: 'role', label: 'Role' },
			{ key: 'icon', label: 'Icon', type: 'icon' }, { key: 'initials', label: 'Initials', help: 'Instead of an icon.' },
		] },
		{ key: 'perRow', label: 'Per row', type: 'range', min: 1, max: 8 },
	],
	toProps: ( a ) => ( { people: ( a.people || [] ).filter( ( p ) => p.name ), perRow: a.perRow } ),
	preview: ( p ) => <HexWall { ...p } />,
} );
