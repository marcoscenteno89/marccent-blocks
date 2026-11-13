/*
 * Step bar: the theme's StepBar (widgets/nav.js); its steps are Step bar step blocks, or a Loop of them, and one is
 * the current step (runtime/islands/step-bar.js).
 */
import { islandBlock } from '../../shared/island';
import metadata from './block.json';
import deprecated from './deprecated';

islandBlock( metadata, {
	component: 'StepBar',
	overrides: { start: { label: 'The current step', help: '0 the first: the ones before it show as done.', type: 'range', min: 0, max: 10 } },
	note: 'Step bar: its steps are the items below, or a Loop of them.',
	inner: { allowedBlocks: metadata.allowedBlocks, template: [ [ 'marccent/step-bar-step', { label: 'Order' } ], [ 'marccent/step-bar-step', { label: 'Pay' } ], [ 'marccent/step-bar-step', { label: 'Ship' } ], [ 'marccent/step-bar-step', { label: 'Done' } ] ] },
	deprecated,
} );
