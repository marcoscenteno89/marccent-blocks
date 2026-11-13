/*
 * Tabs: its tabs (marccent/tab) are blocks, or a Loop of them; on the page the theme's TabBar shows them
 * (runtime/islands/tabs.js).
 */
import { islandBlock } from '../../shared/island';
import metadata from './block.json';

islandBlock( metadata, {
	component: 'TabBar',
	note: 'Tabs: each tab below is one (or a Loop of them); on the page they sit side by side over one panel.',
	inner: { allowedBlocks: metadata.allowedBlocks, template: [ [ 'marccent/tab', { label: 'First' } ], [ 'marccent/tab', { label: 'Second' } ] ] },
} );
