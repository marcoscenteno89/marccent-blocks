/*
 * Animated band as saved before it rendered on the server (0.3.1 and earlier): static markup, frozen here so the
 * editor recognizes old posts; the next save stores the attributes and the inner blocks only.
 */
import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import metadata from './block.json';

const cx = ( ...c ) => c.filter( Boolean ).join( ' ' );
const outer = ( a ) => ( { className: cx( 'wrap wrap-fluid pad-0 stack', a.surface ), style: { minHeight: a.minHeight || undefined } } );
const content = { className: 'col-center pad text-center', style: { position: 'relative', gridTemplateColumns: 'minmax(0, 1fr)' } };

const save = ( { attributes: a } ) => (
	<section { ...useBlockProps.save( outer( a ) ) }>
		<div data-component="animation" data-name={ a.name } data-fps={ a.fps || undefined } data-colors={ a.colors || undefined }
			className="bg w-100 h-100" aria-hidden="true" />
		<div { ...useInnerBlocksProps.save( content ) } />
	</section>
);

export default [ { attributes: metadata.attributes, supports: metadata.supports, save } ];
