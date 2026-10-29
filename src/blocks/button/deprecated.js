/*
 * Button as saved before it rendered on the server (0.3.1 and earlier): static markup, which the editor needs to
 * recognize old posts. Frozen here as the theme's Button (v0.2.4) wrote it, so later changes to the theme can't make
 * them invalid; the next save stores the attributes only.
 */
import { useBlockProps } from '@wordpress/block-editor';
import metadata from './block.json';

const cx = ( ...c ) => c.filter( Boolean ).join( ' ' );

const Icon = ( { icon } ) => (
	<i data-component="icon" className={ icon.startsWith( 'fa-' ) ? cx( icon, 'btn-icon' ) : `fa-solid fa-${ icon } btn-icon` } aria-hidden="true" />
);

const save = ( { attributes: a } ) => {
	const { className, ...block } = useBlockProps.save( { className: cx( a.color, a.hover ) } );
	const Tag = a.href ? 'a' : 'button';
	const link = a.href ? { href: a.href } : { type: 'button' };
	const tab = a.newTab ? { target: '_blank', rel: 'noopener noreferrer' } : {};
	if ( ! a.icon ) {
		return <Tag { ...link } className={ cx( 'btn-link', className ) } data-component="button" { ...tab } { ...block }>{ a.label }</Tag>;
	}
	const label = a.label ? { 'aria-label': a.label, title: a.label } : {};
	return (
		<Tag { ...link } className={ cx( 'btn', className ) } { ...label } data-component="button" { ...tab } { ...block }>
			<Icon icon={ a.icon } />
			{ a.label && <span className="btn-text">{ a.label }</span> }
			{ a.trace && <svg data-component="trace" className="trace" aria-hidden="true"><rect pathLength="100" /></svg> }
		</Tag>
	);
};

export default [ { attributes: metadata.attributes, supports: metadata.supports, save } ];
