/*
 * Grid as saved before it rendered on the server (0.3.1 and earlier): static markup, frozen here so the editor
 * recognizes old posts; the next save stores the attributes and the inner blocks only.
 */
import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import metadata from './block.json';

const cx = ( ...c ) => c.filter( Boolean ).join( ' ' );
const EQUAL = [ 'cols', 'fixed' ];
const classes = ( a ) => cx(
	'grid',
	a.layout === 'cols' ? `cols-${ a.cols }` : a.layout === 'fixed' ? `fixed cols-${ a.cols }` : a.layout,
	a.gap, a.alignItems, a.center && 'cols-center justify-center text-center'
);
const style = ( a ) => ( a.min && EQUAL.includes( a.layout ) ? { '--min': a.min } : undefined );

const save = ( { attributes: a } ) => <div { ...useInnerBlocksProps.save( useBlockProps.save( { className: classes( a ), style: style( a ) } ) ) } />;

export default [ { attributes: metadata.attributes, supports: metadata.supports, save } ];
