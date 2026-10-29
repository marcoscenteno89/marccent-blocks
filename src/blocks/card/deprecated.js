/*
 * Card as saved before it rendered on the server (0.3.1 and earlier): static markup, frozen here so the editor
 * recognizes old posts; the next save stores the attributes and the inner blocks only.
 */
import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import metadata from './block.json';

const cx = ( ...c ) => c.filter( Boolean ).join( ' ' );
const layout = ( a ) => ( a.rows ? `sub rows-${ a.rows }` : cx( 'grid cols-1 content-start', a.gap ) );
const classes = ( a ) => cx( a.surface, a.round && 'round', a.pad, layout( a ), a.hover, a.center && 'text-center justify-center' );

const save = ( { attributes: a } ) => <article { ...useInnerBlocksProps.save( useBlockProps.save( { className: classes( a ) } ) ) } />;

export default [ { attributes: metadata.attributes, supports: metadata.supports, save } ];
