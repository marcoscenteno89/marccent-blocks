/*
 * Card fan: the theme's CardFan (components/widgets/card-fan.js): its Card blocks stacked, fanned out on hover or
 * focus. In the editor its cards are in a row to edit; the server writes the fan (render.php, marccent_blocks_card_fan).
 */
import { register } from '../../shared/register';
import { InnerBlocks, InspectorControls, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl, TextControl } from '@wordpress/components';
import metadata from './block.json';

const card = ( title ) => [ 'marccent/card', { surface: 'bg-gradient', center: true }, [ [ 'core/paragraph', { content: `<strong>${ title }</strong>` } ] ] ];

const Edit = ( { attributes: a, setAttributes: set } ) => {
	const props = useInnerBlocksProps( useBlockProps( { className: 'row-wrap gap-sm' } ), {
		allowedBlocks: [ 'marccent/card', 'marccent-content/loop' ],
		template: [ card( 'Checking' ), card( 'Savings' ), card( 'Credit' ) ],
		orientation: 'horizontal',
	} );
	return (
		<>
			<InspectorControls>
				<PanelBody title="Card fan">
					<RangeControl label="Degrees between cards when fanned" value={ a.spread } min={ 4 } max={ 30 } onChange={ ( spread ) => set( { spread } ) } />
					<TextControl label="Label for screen readers" help="What the cards are: Our accounts." value={ a.label } onChange={ ( label ) => set( { label } ) } />
				</PanelBody>
			</InspectorControls>
			<div { ...props } />
		</>
	);
};

register( metadata, { edit: Edit, save: () => <InnerBlocks.Content /> } );
