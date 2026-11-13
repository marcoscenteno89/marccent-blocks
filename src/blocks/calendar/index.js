/*
 * Calendar: the theme's Calendar (components/ui/calendar.js). Its events are rows: a date, a title and a dot color.
 */
import { islandBlock } from '../../shared/island';
import { Calendar } from 'marccent-theme/components/ui/calendar';
import metadata from './block.json';

const DOTS = [ [ 'Secondary', 'bg-secondary' ], [ 'Primary', 'bg-primary' ], [ 'Danger', 'bg-danger' ], [ 'Success', 'bg-success' ] ];

islandBlock( metadata, {
	component: 'Calendar',
	overrides: {
		mode: { label: 'Pick', options: [ [ 'One day', 'single' ], [ 'A range', 'range' ] ] },
		weekStart: { label: 'Weeks start on', options: [ [ 'Sunday', 0 ], [ 'Monday', 1 ] ] },
		minimum: { help: '2026-11-01; empty: any' },
		events: { itemLabel: 'Event', fields: [
			{ key: 'date', label: 'Date (and time)', help: '2026-11-20, or 2026-11-20T18:30' },
			{ key: 'title', label: 'Title' },
			{ key: 'color', label: 'Dot', type: 'select', options: DOTS, default: 'bg-secondary' },
		] },
	},
	toProps: ( a ) => ( { ...a, weekStart: Number( a.weekStart ) || 0, minimum: a.minimum || undefined, maximum: a.maximum || undefined } ),
	preview: ( p ) => <Calendar { ...p } />,
} );
