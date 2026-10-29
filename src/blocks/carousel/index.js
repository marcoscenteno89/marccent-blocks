/*
 * Carousel: written by the server (render.php) as an island (data-island="carousel", runtime/islands/carousel.js):
 * its slides as plain HTML in [data-slot], shown in a row until the runtime replaces them with the theme's Carousel
 * (Swiper and its pager).
 */
import { register } from '../../shared/register';
import { InnerBlocks, InspectorControls, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import metadata from './block.json';
import deprecated from './deprecated';

const outer = ( a ) => ( {
	'data-island': 'carousel',
	'data-props': JSON.stringify( { slideWidth: a.slideWidth, label: a.label } ),
	style: { '--slide-width': a.slideWidth },
} );

const Edit = ( { attributes: a, setAttributes: set } ) => {
	const slots = useInnerBlocksProps( { 'data-slot': '', style: { overflowX: 'auto' } }, {
		template: [ [ 'core/image' ], [ 'core/image' ], [ 'core/image' ] ],
		orientation: 'horizontal',
	} );
	return (
		<>
			<InspectorControls>
				<PanelBody title="Carousel">
					<TextControl label="Slide width" help="Any CSS length: 17rem, 80%…" value={ a.slideWidth } onChange={ ( slideWidth ) => set( { slideWidth } ) } />
					<TextControl label="Name for screen readers" value={ a.label } onChange={ ( label ) => set( { label } ) } />
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps( outer( a ) ) }><div { ...slots } /></div>
		</>
	);
};

register( metadata, { edit: Edit, save: () => <InnerBlocks.Content />, deprecated } );
