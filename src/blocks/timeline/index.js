/*
 * Timeline: the theme's Timeline (widgets/layout.js); its events are Timeline item blocks, or a Loop of them
 * (runtime/islands/timeline.js).
 */
import { islandBlock } from '../../shared/island';
import metadata from './block.json';
import deprecated from './deprecated';

islandBlock( metadata, {
	component: 'Timeline',
	note: 'Timeline: its events are the items below, or a Loop of them.',
	inner: { allowedBlocks: metadata.allowedBlocks, template: [ [ 'marccent/timeline-item', { date: '2024', title: 'Founded', text: 'Where it started.', icon: 'flag' } ], [ 'marccent/timeline-item', { date: '2026', title: 'Today', icon: 'star' } ] ] },
	deprecated,
} );
