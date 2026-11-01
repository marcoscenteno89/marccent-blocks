/*
 * Popover: a button and the panel of blocks it opens beside it (render.php); here the panel stays open to be edited.
 */
import { register } from '../../shared/register';
import { InnerBlocks, InspectorControls, RichText, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { SIDES } from '../../shared/sides';
import metadata from './block.json';

const Edit = ( { attributes: a, setAttributes: set } ) => {
	const inner = useInnerBlocksProps( { className: 'bg border round shadow pad-sm', style: { borderStyle: 'dashed' } }, { template: [ [ 'core/paragraph', { placeholder: 'What the panel shows…' } ] ] } );
	return (
		<>
			<InspectorControls>
				<PanelBody title="Popover">
					<TextControl label="The panel's name" help="For screen readers; empty: the button's label." value={ a.label } onChange={ ( label ) => set( { label } ) } />
					<SelectControl label="Where" value={ a.side } options={ SIDES } onChange={ ( side ) => set( { side } ) } />
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps( { className: 'grid cols-1 gap-sm items-start' } ) }>
				<div><RichText tagName="span" className="btn" value={ a.trigger } onChange={ ( trigger ) => set( { trigger } ) } placeholder="Open" allowedFormats={ [] } /></div>
				<div { ...inner } />
			</div>
		</>
	);
};

register( metadata, { edit: Edit, save: () => <InnerBlocks.Content /> } );
