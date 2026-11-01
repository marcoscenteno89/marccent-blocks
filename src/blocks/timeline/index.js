/*
 * Timeline: the theme's Timeline (widgets/layout.js) from a list of events (shared/island.js; runtime/islands/timeline.js).
 */
import { islandBlock } from '../../shared/island';
import { Timeline } from 'marccent-theme/components/widgets/layout';
import metadata from './block.json';

islandBlock( metadata, {
	fields: [ { key: 'events', label: 'Events', type: 'list', itemLabel: 'Event', fields: [
		{ key: 'date', label: 'Date' }, { key: 'title', label: 'Title' }, { key: 'text', label: 'Text', type: 'textarea' }, { key: 'icon', label: 'Icon', type: 'icon' },
	] } ],
	preview: ( p ) => <Timeline events={ p.events || [] } />,
} );
