/*
 * Grid: the theme's .grid (layout.css). Equal columns: --cols per row, never narrower than --min, so they wrap to
 * fewer on narrow screens by themselves. The uneven layouts (layout-2-1…) are one column until their container is
 * wide enough. Each inner block is one cell. The server writes it on the page (render.php).
 */
import { register } from '../../shared/register';
import { InnerBlocks, InspectorControls, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl, TextControl, ToggleControl } from '@wordpress/components';
import { LAYOUTS, GAPS, ALIGN_ITEMS, cx } from '../../shared/options';
import metadata from './block.json';
import deprecated from './deprecated';

const EQUAL = [ 'cols', 'fixed' ];

const classes = ( a ) => cx(
	'grid',
	a.layout === 'cols' ? `cols-${ a.cols }` : a.layout === 'fixed' ? `fixed cols-${ a.cols }` : a.layout,
	a.gap, a.alignItems, a.center && 'cols-center justify-center text-center'
);
const style = ( a ) => ( a.min && EQUAL.includes( a.layout ) ? { '--min': a.min } : undefined );

const Edit = ( { attributes: a, setAttributes: set } ) => {
	const props = useInnerBlocksProps( useBlockProps( { className: classes( a ), style: style( a ) } ), {
		template: [ [ 'core/paragraph' ], [ 'core/paragraph' ], [ 'core/paragraph' ] ],
		orientation: a.layout === 'cols' && a.cols === 1 ? 'vertical' : 'horizontal',
	} );
	return (
		<>
			<InspectorControls>
				<PanelBody title="Grid">
					<SelectControl label="Layout" value={ a.layout } options={ LAYOUTS } onChange={ ( layout ) => set( { layout } ) } />
					{ EQUAL.includes( a.layout ) && (
						<>
							<RangeControl label="Columns" value={ a.cols } min={ 1 } max={ 10 } onChange={ ( cols ) => set( { cols } ) } />
							<TextControl label="Narrowest column" help="Below this, columns wrap to the next row (the theme's default is 14rem)." value={ a.min } onChange={ ( min ) => set( { min } ) } />
						</>
					) }
					<SelectControl label="Gap" value={ a.gap } options={ GAPS } onChange={ ( gap ) => set( { gap } ) } />
					<SelectControl label="Align items" value={ a.alignItems } options={ ALIGN_ITEMS } onChange={ ( alignItems ) => set( { alignItems } ) } />
					<ToggleControl label="Center" checked={ a.center } onChange={ ( center ) => set( { center } ) } />
				</PanelBody>
			</InspectorControls>
			<div { ...props } />
		</>
	);
};

register( metadata, { edit: Edit, save: () => <InnerBlocks.Content />, deprecated } );
