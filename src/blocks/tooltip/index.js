/*
 * Tooltip: a word or phrase (a link-styled button) and the hint shown beside it (render.php).
 */
import { register } from '../../shared/register';
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextareaControl } from '@wordpress/components';
import { SIDES } from '../../shared/sides';
import metadata from './block.json';

const Edit = ( { attributes: a, setAttributes: set } ) => (
	<>
		<InspectorControls>
			<PanelBody title="Tooltip">
				<TextareaControl label="The hint" value={ a.content } onChange={ ( content ) => set( { content } ) } />
				<SelectControl label="Where" value={ a.side } options={ SIDES } onChange={ ( side ) => set( { side } ) } />
			</PanelBody>
		</InspectorControls>
		<div { ...useBlockProps() }>
			<RichText tagName="span" className="btn-link" value={ a.trigger } onChange={ ( trigger ) => set( { trigger } ) } placeholder="Hover me" allowedFormats={ [] } />
			<span className="tooltip" style={ { position: 'static', display: 'inline-block', marginInlineStart: 8, fontSize: 12 } }>{ a.content }</span>
		</div>
	</>
);

register( metadata, { edit: Edit, save: () => null } );
