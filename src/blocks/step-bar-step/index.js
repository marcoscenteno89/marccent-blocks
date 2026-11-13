/*
 * Step bar step: one of its Step bar's steps (the theme's StepBarStep), in the block or a Loop there.
 */
import { itemBlock } from '../../shared/item';
import metadata from './block.json';

itemBlock( metadata, {
	component: 'StepBarStep',
	name: ( a ) => a.label,
} );
