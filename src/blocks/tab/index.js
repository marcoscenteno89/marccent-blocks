/*
 * Tab: its label (and icon) over the blocks it shows (render.php).
 */
import { register } from '../../shared/register';
import { InnerBlocks, InspectorControls, RichText, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import metadata from './block.json';

const Edit = ( { attributes: a, setAttributes: set } ) => {
	const inner = useInnerBlocksProps( { className: 'grid cols-1 gap-sm pad-sm' }, { template: [ [ 'core/paragraph', { placeholder: 'What this tab shows…' } ] ] } );
	return (
		<>
			<InspectorControls>
				<PanelBody title="Tab">
					<TextControl label="Icon (optional)" help="A Font Awesome name: house, gear…" value={ a.icon } onChange={ ( icon ) => set( { icon } ) } />
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps( { className: 'border round' } ) }>
				<div className="header pad-sm fw-bold">
					{ a.icon && <i className={ `fa-solid fa-${ a.icon }` } aria-hidden="true" style={ { marginInlineEnd: 8 } } /> }
					<RichText tagName="span" value={ a.label } onChange={ ( label ) => set( { label } ) } placeholder="Tab" allowedFormats={ [] } />
				</div>
				<div { ...inner } />
			</div>
		</>
	);
};

register( metadata, { edit: Edit, save: () => <InnerBlocks.Content /> } );
