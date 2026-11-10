/*
 * Carousel as saved before it rendered on the server (0.3.1 and earlier): static markup, frozen here so the editor
 * recognizes old posts; the next save stores the attributes and the inner blocks only.
 */
import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import metadata from './block.json';
import { legacyAttributes, upgradeLegacy } from '../../shared/upgrade';

const outer = ( a ) => ( {
	'data-island': 'carousel',
	'data-props': JSON.stringify( { slideWidth: a.slideWidth, label: a.label } ),
	style: { '--slide-width': a.slideWidth },
} );

const save = ( { attributes: a } ) => (
	<div { ...useBlockProps.save( outer( a ) ) }><div { ...useInnerBlocksProps.save( { 'data-slot': '' } ) } /></div>
);

// Its attributes as they were then (old names and defaults), brought to today's by migrate (src/shared/upgrade.js).
export default [ { attributes: legacyAttributes( metadata ), supports: metadata.supports, save, migrate: upgradeLegacy( metadata.name ) } ];
