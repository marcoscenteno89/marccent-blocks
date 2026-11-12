/*
 * Marquee: the theme's Marquee (components/widgets/marquee.js): its blocks in a row that scrolls sideways without
 * end. In the editor the row stands still, so its blocks can be edited. The server writes it on the page
 * (render.php, marccent_blocks_marquee).
 */
import { register } from '../../shared/register';
import { InnerBlocks, InspectorControls, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl, TextControl, ToggleControl } from '@wordpress/components';
import metadata from './block.json';

const Edit = ( { attributes: a, setAttributes: set } ) => {
	const props = useInnerBlocksProps( useBlockProps( { className: 'row-wrap gap-lg items-center' } ), {
		template: [ [ 'core/paragraph', { content: 'Northwind' } ], [ 'core/paragraph', { content: 'Contoso' } ], [ 'core/paragraph', { content: 'Fabrikam' } ] ],
		orientation: 'horizontal',
	} );
	return (
		<>
			<InspectorControls>
				<PanelBody title="Marquee">
					<RangeControl label="Seconds per pass" help="More is slower." value={ a.duration } min={ 5 } max={ 120 } onChange={ ( duration ) => set( { duration } ) } />
					<ToggleControl label="Left to right" checked={ a.reverse } onChange={ ( reverse ) => set( { reverse } ) } />
					<TextControl label="Label for screen readers" help="What the row is: Our clients." value={ a.label } onChange={ ( label ) => set( { label } ) } />
				</PanelBody>
			</InspectorControls>
			<div { ...props } />
		</>
	);
};

register( metadata, { edit: Edit, save: () => <InnerBlocks.Content /> } );
