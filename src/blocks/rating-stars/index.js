/*
 * Stars: the theme's Stars (ui/rating.js), a score to read.
 */
import { islandBlock } from '../../shared/island';
import { Stars } from 'marccent-theme/components/ui/rating';
import metadata from './block.json';

islandBlock( metadata, {
	component: 'Stars',
	overrides: {
		value: { label: 'Score', type: 'range', min: 0, max: 10, step: 0.1 },
		maximum: { label: 'Out of', type: 'range', min: 1, max: 10 },
		icon: { type: 'icon' },
	},
	preview: ( p ) => <Stars value={ Math.min( p.value, p.maximum ) } maximum={ p.maximum } icon={ p.icon || 'star' } />,
} );
