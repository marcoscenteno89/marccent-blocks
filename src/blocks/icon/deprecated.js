/*
 * Icon as saved before it rendered on the server (0.3.1 and earlier): static markup, frozen here as the theme's Icon
 * (v0.2.4) wrote it, so the editor recognizes old posts whatever the theme does later.
 */
import { useBlockProps } from '@wordpress/block-editor';
import metadata from './block.json';

const cx = ( ...c ) => c.filter( Boolean ).join( ' ' );

const boxProps = ( a ) => a.shape
	? { className: cx( a.shape, a.surface, 'col-center' ), style: { width: a.size } }
	: { className: 'grid', style: { '--icon-size': a.size } };

const save = ( { attributes: a } ) => (
	<div { ...useBlockProps.save( boxProps( a ) ) }>
		<i data-component="icon" className={ cx( a.family, a.icon && `fa-${ a.icon }` ) } aria-hidden="true" />
		{ a.label && <span className="screen-reader-text">{ a.label }</span> }
	</div>
);

export default [ { attributes: metadata.attributes, supports: metadata.supports, save } ];
