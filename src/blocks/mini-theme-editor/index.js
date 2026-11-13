/*
 * Mini theme editor: the theme's MiniThemeEditor for the site (runtime/islands/mini-theme-editor.js). It works on the
 * page's theme, so the editor shows a note in its place.
 */
import { islandBlock } from '../../shared/island';
import metadata from './block.json';

islandBlock( metadata, {
	component: 'MiniThemeEditor',
	overrides: {
		align: { label: 'Packed to the', type: 'select', options: [ [ 'Start', 'start' ], [ 'Center', 'center' ], [ 'End', 'end' ] ] },
		visitors: { label: 'What Visitors allows', type: 'toggle', help: 'Appearance → Marccent theme, Visitors decides: a Theme button that opens the whole editor, or single controls (only light / dark, say). For a header.' },
	},
	note: 'The mini theme editor: light / dark, glass, the palette, the design style and reset, for visitors. It shows on the page.',
} );
