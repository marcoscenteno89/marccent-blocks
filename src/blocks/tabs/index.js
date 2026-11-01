/*
 * Tabs: its tabs (marccent/tab) are blocks; on the page the theme's TabBar shows them (runtime/islands/tabs.js).
 */
import { islandBlock } from '../../shared/island';
import metadata from './block.json';

islandBlock( metadata, {
	note: 'Tabs: each tab below is one; on the page they sit side by side over one panel.',
	inner: { allowedBlocks: [ 'marccent/tab' ], template: [ [ 'marccent/tab', { label: 'First' } ], [ 'marccent/tab', { label: 'Second' } ] ] },
} );
