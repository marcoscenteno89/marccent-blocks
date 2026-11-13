/*
 * Atom: the theme's Atom (visuals/effects.js), electrons on three tilted orbits.
 */
import { islandBlock } from '../../shared/island';
import { Atom } from 'marccent-theme/components/visuals/effects';
import metadata from './block.json';

islandBlock( metadata, {
	fields: [
		{ key: 'speed', label: 'Seconds per lap (inner orbit)', type: 'range', min: 1, max: 12, step: 0.5 },
		{ key: 'label', label: 'Label for screen readers' },
	],
	preview: ( p ) => <Atom key={ p.speed } { ...p } />,
} );
