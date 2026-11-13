/*
 * Date: the theme's DateTime (components/ui/date-time.js): a date, a time or a range as <time> elements, or relative.
 */
import { islandBlock } from '../../shared/island';
import { DateTime } from 'marccent-theme/components/ui/date-time';
import metadata from './block.json';

islandBlock( metadata, {
	component: 'DateTime',
	overrides: {
		value: { label: 'Date', help: '2026-11-06, or 2026-11-06T18:30:00-07:00 for a time' },
		end: { label: 'Until (a range)' },
		show: { options: [ [ 'Date', 'date' ], [ 'Date and time', 'date-time' ], [ 'Time', 'time' ], [ 'How long ago / until', 'relative' ] ] },
		timeZone: { label: 'Time zone', help: 'America/Boise; a date alone is always its own day' },
	},
	preview: ( p ) => ( p.value ? <DateTime { ...p } end={ p.end || undefined } /> : <p className="opacity-75">Set its date in the sidebar.</p> ),
} );
