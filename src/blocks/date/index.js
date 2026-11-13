/*
 * Date: the theme's DateTime (components/ui/date-time.js), a date, a time or a range in the reader's language, or
 * how long ago. An island (render.php): the visitor's browser sets it with Intl, so "3 days ago" stays true on a
 * cached page; the server writes the ISO date as a stand-in.
 */
import { islandBlock } from '../../shared/island';
import { DateTime } from 'marccent-theme/components/ui/date-time';
import metadata from './block.json';

const SHOW = [ [ 'Date', 'date' ], [ 'Date and time', 'date-time' ], [ 'Time', 'time' ], [ 'How long ago', 'relative' ] ];

islandBlock( metadata, {
	fields: [
		{ key: 'value', label: 'Date', type: 'text', help: 'ISO: 2026-11-06, or with a time: 2026-11-06T18:30:00-07:00' },
		{ key: 'end', label: 'Until (optional)', type: 'text', help: 'A range’s end, the same way.' },
		{ key: 'show', label: 'Shows', type: 'select', options: SHOW },
		{ key: 'locale', label: 'Language and region', type: 'text', help: 'en-US, de-DE, es-MX' },
		{ key: 'timeZone', label: 'Time zone', type: 'text', help: 'For a date and time: America/Boise. A date alone is its own day.' },
	],
	preview: ( p ) => ( p.value ? <DateTime value={ p.value } end={ p.end || undefined } show={ p.show } locale={ p.locale || 'en-US' } timeZone={ p.timeZone || 'UTC' } />
		: <p style={ { opacity: 0.7 } }>Set a date in the block's settings.</p> ),
} );
