/*
 * Theme editor: the theme's ThemeEditor for the site (runtime/islands/theme-editor.js). It works on the page's theme,
 * so the editor shows a note in its place.
 */
import { islandBlock } from '../../shared/island';
import metadata from './block.json';

islandBlock( metadata, { note: 'The theme editor: the palette, mode, design style, hover effect and more, for visitors. It shows on the page.' } );
