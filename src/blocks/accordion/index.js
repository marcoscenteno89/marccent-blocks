/*
 * Accordion: its items (marccent/accordion-item) in the theme's bordered list; render.php names and opens them.
 */
import { register } from '../../shared/register';
import { InnerBlocks, InspectorControls, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl } from '@wordpress/components';
import metadata from './block.json';

const Edit = ( { attributes: a, setAttributes: set } ) => {
	const props = useInnerBlocksProps( useBlockProps( { className: 'accordion grid cols-1 gap-0 divide border round' } ), {
		allowedBlocks: metadata.allowedBlocks,
		template: [ [ 'marccent/accordion-item', { header: 'A question' } ], [ 'marccent/accordion-item', { header: 'Another question' } ] ],
	} );
	return (
		<>
			<InspectorControls>
				<PanelBody title="Accordion">
					<ToggleControl label="Several open at once" help={ a.multiple ? 'Opening one leaves the others as they are.' : 'Opening one closes the others.' } checked={ a.multiple } onChange={ ( multiple ) => set( { multiple } ) } />
					<RangeControl label="Open at first" help="Which item starts open: 1 the first, 0 none." value={ a.open + 1 } min={ 0 } max={ 20 } onChange={ ( v ) => set( { open: ( v ?? 1 ) - 1 } ) } />
				</PanelBody>
			</InspectorControls>
			<div { ...props } />
		</>
	);
};

register( metadata, { edit: Edit, save: () => <InnerBlocks.Content /> } );
