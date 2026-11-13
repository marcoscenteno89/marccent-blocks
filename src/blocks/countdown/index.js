/*
 * Countdown: the theme's Countdown (visuals/effects.js), a gooey digit down to zero.
 */
import { islandBlock } from '../../shared/island';
import { Countdown } from 'marccent-theme/components/visuals/effects';
import metadata from './block.json';

islandBlock( metadata, {
	component: 'Countdown',
	overrides: { from: { type: 'range', min: 1, max: 9 } },
	preview: ( p ) => <Countdown key={ p.from } from={ p.from } />,
} );
