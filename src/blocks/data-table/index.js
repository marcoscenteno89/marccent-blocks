/*
 * Data table: the theme's DataTable (ui/data-table.js) from rows typed as CSV, the first line the headers (render.php
 * reads them the same way for the page).
 */
import { islandBlock } from '../../shared/island';
import { DataTable } from 'marccent/components/ui/data-table';
import metadata from './block.json';

// One CSV line: commas between values, "quoted" values may hold commas and "" for a quote.
const cells = ( line ) => [ ...line.matchAll( /(?:^|,)(?:"((?:[^"]|"")*)"|([^,]*))/g ) ].map( ( m ) => ( m[ 1 ] !== undefined ? m[ 1 ].replace( /""/g, '"' ) : m[ 2 ] ).trim() );

const parse = ( csv ) => {
	const [ head = '', ...lines ] = ( csv || '' ).split( /\r\n|\r|\n/ ).filter( ( l ) => l.trim() );
	const columns = cells( head ).map( ( label, i ) => ( { key: `c${ i }`, label } ) );
	const rows = lines.map( ( l, r ) => Object.fromEntries( [ [ 'id', r ], ...columns.map( ( c, i ) => [ c.key, cells( l )[ i ] ?? '' ] ) ] ) );
	return { columns, rows };
};

islandBlock( metadata, {
	fields: [
		{ key: 'csv', label: 'Rows (CSV)', type: 'textarea', help: 'The first line is the headers; then one row per line, values split by commas ("quote" one that has a comma).' },
		{ key: 'label', label: 'Name (its caption, for screen readers)' },
		{ key: 'pageSize', label: 'Rows per page (0: all)', type: 'range', min: 0, max: 50 },
		{ key: 'search', label: 'A search box', type: 'toggle' },
	],
	toProps: ( a ) => ( { ...parse( a.csv ), label: a.label, pageSize: a.pageSize, search: a.search } ),
	preview: ( p ) => <DataTable { ...p } />,
} );
