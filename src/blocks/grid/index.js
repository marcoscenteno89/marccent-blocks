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
	a.layout === 'row-wrap' ? 'row-wrap' : 'grid',
	a.layout === 'cols' ? `cols-${ a.columns }` : a.layout === 'fixed' ? `fixed cols-${ a.columns }` : a.layout !== 'row-wrap' && a.layout,
	a.gap, a.alignItems, a.center && 'cols-center justify-center text-center', a.dense && 'dense', a.minimumRowHeight && 'rows-min'
);
const style = ( a ) => {
	const min = a.minimumColumnWidth && EQUAL.includes( a.layout ) ? { '--min': a.minimumColumnWidth } : {};
	const row = a.minimumRowHeight ? { '--row': a.minimumRowHeight } : {};
	return a.minimumColumnWidth && EQUAL.includes( a.layout ) || a.minimumRowHeight ? { ...min, ...row } : undefined;
};

const Edit = ( { attributes: a, setAttributes: set } ) => {
	const props = useInnerBlocksProps( useBlockProps( { className: classes( a ), style: style( a ) } ), {
		template: [ [ 'core/paragraph' ], [ 'core/paragraph' ], [ 'core/paragraph' ] ],
		orientation: a.layout === 'cols' && a.columns === 1 ? 'vertical' : 'horizontal',
	} );
	return (
		<>
			<InspectorControls>
				<PanelBody title="Grid">
					<SelectControl label="Layout" value={ a.layout } options={ LAYOUTS } onChange={ ( layout ) => set( { layout } ) } />
					{ EQUAL.includes( a.layout ) && (
						<>
							<RangeControl label="Columns" value={ a.columns } min={ 1 } max={ 10 } onChange={ ( columns ) => set( { columns } ) } />
							<TextControl label="Narrowest column" help="Below this, columns wrap to the next row (the theme's default is 14rem)." value={ a.minimumColumnWidth } onChange={ ( minimumColumnWidth ) => set( { minimumColumnWidth } ) } />
						</>
					) }
					<SelectControl label="Gap" value={ a.gap } options={ GAPS } onChange={ ( gap ) => set( { gap } ) } />
					<SelectControl label="Align items" value={ a.alignItems } options={ ALIGN_ITEMS } onChange={ ( alignItems ) => set( { alignItems } ) } />
					<ToggleControl label="Center" checked={ a.center } onChange={ ( center ) => set( { center } ) } />
					<ToggleControl label="Pack densely" help="Items of different sizes fill the holes (a collage)." checked={ a.dense } onChange={ ( dense ) => set( { dense } ) } />
					<TextControl label="Minimum row height" help="Every row at least this tall, growing to fit: 11rem." value={ a.minimumRowHeight } onChange={ ( minimumRowHeight ) => set( { minimumRowHeight } ) } />
				</PanelBody>
			</InspectorControls>
			<div { ...props } />
		</>
	);
};

register( metadata, { edit: Edit, save: () => <InnerBlocks.Content />, deprecated } );
