/*
 * Code: the theme's Code (ui/code.js), highlighted with a copy button.
 */
import { islandBlock } from '../../shared/island';
import { Code } from 'marccent-theme/components/ui/code';
import { choices } from '../../shared/options';
import metadata from './block.json';

islandBlock( metadata, {
	component: 'Code',
	overrides: {
		code: { label: 'Code', type: 'code', language: ( a ) => a.language },
		language: { options: choices( 'Code', 'language' ).map( ( o ) => [ o.label, o.value ] ) },
	},
	toProps: ( a ) => ( { code: a.code, language: a.language } ),
	preview: ( p ) => <Code key={ p.language } language={ p.language }>{ p.code || '' }</Code>,
} );
