/*
 * Card: the Next site's feature box: .bg border round pad, a 1-column grid packed at the top, and a hover effect.
 * Its blocks stack inside (an icon, a heading, text, a button…).
 * Rows (2+) makes it .sub rows-N: it spans N rows of the grid it sits in and its blocks snap to them, so the headings,
 * text and buttons of cards side by side line up. The grid's gap then spaces its blocks, so its own gap is dropped.
 * Rows 0 saves the plain markup, so cards saved before the option stay valid.
 */
import { register } from '../../shared/register';
import { InspectorControls, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl, ToggleControl } from '@wordpress/components';
import { SURFACES, PADS, GAPS, HOVERS, cx } from '../../shared/options';
import metadata from './block.json';

const layout = ( a ) => ( a.rows ? `sub rows-${ a.rows }` : cx( 'grid cols-1 content-start', a.gap ) );
const classes = ( a ) => cx( a.surface, a.round && 'round', a.pad, layout( a ), a.hover, a.center && 'text-center justify-center' );

const Edit = ( { attributes: a, setAttributes: set } ) => {
	const props = useInnerBlocksProps( useBlockProps( { className: classes( a ) } ), {
		template: [ [ 'marccent/icon' ], [ 'core/heading', { level: 3, placeholder: 'Title' } ], [ 'core/paragraph', { placeholder: 'A line about it.' } ] ],
	} );
	return (
		<>
			<InspectorControls>
				<PanelBody title="Card">
					<SelectControl label="Background" value={ a.surface } options={ SURFACES } onChange={ ( surface ) => set( { surface } ) } />
					<SelectControl label="Padding" value={ a.pad } options={ PADS } onChange={ ( pad ) => set( { pad } ) } />
					<RangeControl label="Rows" help="Line up with the cards beside it: one row per block in the card. 0 is off."
						value={ a.rows } min={ 0 } max={ 10 } onChange={ ( rows ) => set( { rows: rows === 1 ? 2 : rows } ) } />
					{ ! a.rows && <SelectControl label="Space between items" value={ a.gap } options={ GAPS } onChange={ ( gap ) => set( { gap } ) } /> }
					<SelectControl label="Hover effect" value={ a.hover } options={ HOVERS } onChange={ ( hover ) => set( { hover } ) } />
					<ToggleControl label="Rounded corners" checked={ a.round } onChange={ ( round ) => set( { round } ) } />
					<ToggleControl label="Center" checked={ a.center } onChange={ ( center ) => set( { center } ) } />
				</PanelBody>
			</InspectorControls>
			<article { ...props } />
		</>
	);
};

const save = ( { attributes: a } ) => <article { ...useInnerBlocksProps.save( useBlockProps.save( { className: classes( a ) } ) ) } />;

register( metadata, { edit: Edit, save } );
