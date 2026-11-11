/*
 * Stat card: the theme's StatCard (widgets/layout.js) in a Card, since it has no box of its own; its blocks are buttons
 * at the bottom.
 */
import { islandBlock } from '../../shared/island';
import { StatCard } from 'marccent-theme/components/widgets/layout';
import metadata from './block.json';

islandBlock( metadata, {
	fields: [
		{ key: 'icon', label: 'Icon', type: 'icon' },
		{ key: 'value', label: 'The number (or a name)' },
		{ key: 'label', label: 'Label' },
		{ key: 'text', label: 'Text', type: 'textarea' },
		{ key: 'accent', label: 'Ring and icon in the gradient', type: 'toggle' },
		{ key: 'fill', label: 'Progress ring (0–100; empty: none)', type: 'number' },
	],
	preview: ( p ) => <article className="bg border round pad grid cols-1 content-start gap-sm"><StatCard { ...p } fill={ p.fill ?? undefined } /></article>,
	inner: { allowedBlocks: [ 'marccent/button' ], template: [], className: 'row-wrap gap-sm' },
} );
