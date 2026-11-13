/*
 * Atom: the theme's Atom (visuals/effects.js), electrons on three tilted orbits.
 */
import { islandBlock } from '../../shared/island';
import { Atom } from 'marccent-theme/components/visuals/effects';
import metadata from './block.json';

islandBlock( metadata, {
	component: 'Atom',
	overrides: { speed: { type: 'range', min: 1, max: 12, step: 0.5 } },
	preview: ( p ) => <Atom key={ p.speed } { ...p } />,
} );
