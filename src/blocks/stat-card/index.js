/*
 * Stat card: the theme's StatCard (widgets/layout.js) in a Card, since it has no box of its own; its blocks are buttons
 * at the bottom.
 */
import { islandBlock } from '../../shared/island';
import { StatCard } from 'marccent-theme/components/widgets/layout';
import metadata from './block.json';

islandBlock( metadata, {
	component: 'StatCard',
	overrides: { icon: { type: 'icon' }, text: { type: 'textarea' } },
	preview: ( p ) => <article className="bg border round pad grid cols-1 content-start gap-sm"><StatCard { ...p } fill={ p.fill ?? undefined } /></article>,
	inner: { allowedBlocks: [ 'marccent/button' ], template: [], className: 'row-wrap gap-sm' },
} );
