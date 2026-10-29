/*
 * Section as saved before it rendered on the server (0.3.1 and earlier): static markup, frozen here so the editor
 * recognizes old posts; the next save stores the attributes and the inner blocks only.
 */
import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import metadata from './block.json';

const cx = ( ...c ) => c.filter( Boolean ).join( ' ' );
const outer = ( a ) => cx( 'wrap wrap-fluid', a.imageUrl ? 'stack' : a.surface );
const inner = ( a ) => cx( a.width, a.padY, 'grid cols-1', a.gap, a.center && 'text-center justify-center', a.imageUrl && a.surface );
const outerStyle = ( a ) => ( a.minHeight ? { minHeight: a.minHeight, ...( ! a.imageUrl && { display: 'grid', gridTemplateColumns: 'minmax(0, 1fr)' } ) } : undefined );
const innerStyle = ( a ) => {
	if ( a.imageUrl ) return { position: 'relative', alignContent: 'center', color: '#fff' };
	return a.minHeight ? { alignContent: 'center' } : undefined;
};

const save = ( { attributes: a } ) => {
	const Tag = a.tagName;
	const blockProps = useBlockProps.save( { className: outer( a ), style: outerStyle( a ) } );
	const innerProps = useInnerBlocksProps.save( { className: inner( a ), style: innerStyle( a ) } );
	return (
		<Tag { ...blockProps }>
			{ a.imageUrl && (
				<>
					<img className="cover-img" src={ a.imageUrl } alt={ a.imageAlt } loading="lazy" decoding="async" />
					<div aria-hidden="true" style={ { background: `rgb(0 0 0 / ${ a.shade }%)` } } />
				</>
			) }
			<div { ...innerProps } />
		</Tag>
	);
};

export default [ { attributes: metadata.attributes, supports: metadata.supports, save } ];
