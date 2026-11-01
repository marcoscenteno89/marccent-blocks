/*
 * Step bar: the theme's StepBar (widgets/nav.js) from a list of steps; one is the current step.
 */
import { islandBlock } from '../../shared/island';
import { StepBar } from 'marccent-theme/components/widgets/nav';
import metadata from './block.json';

islandBlock( metadata, {
	fields: [
		{ key: 'steps', label: 'Steps', type: 'list', itemLabel: 'Step', fields: [ { key: 'label', label: 'Label' } ] },
		{ key: 'start', label: 'The current step', help: '0 the first: the ones before it show as done.', type: 'range', min: 0, max: 10 },
	],
	toProps: ( a ) => ( { steps: ( a.steps || [] ).map( ( s ) => s.label ).filter( Boolean ), start: a.start } ),
	preview: ( p ) => p.steps.length > 0 && <StepBar key={ `${ p.start }-${ p.steps.join() }` } steps={ p.steps } start={ Math.min( p.start, p.steps.length - 1 ) } />,
} );
