/*
 * Code: the theme's Code (ui/code.js), highlighted with a copy button.
 */
import { islandBlock } from '../../shared/island';
import { Code } from 'marccent-theme/components/ui/code';
import metadata from './block.json';

islandBlock( metadata, {
	fields: [
		{ key: 'code', label: 'Code', type: 'textarea' },
		{ key: 'lang', label: 'Language', type: 'select', options: [ [ 'JavaScript', 'js' ], [ 'JSX', 'jsx' ], [ 'CSS', 'css' ], [ 'HTML', 'html' ], [ 'Shell', 'sh' ] ] },
	],
	preview: ( p ) => <Code key={ p.lang } lang={ p.lang }>{ p.code || '' }</Code>,
} );
