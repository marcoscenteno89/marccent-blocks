/*
 * Social links: rendered by the server (render.php) from the Social menu (Appearance → Menus); the editor shows the
 * server's markup. Its settings are the buttons' color and hover.
 */
import { register } from '../../shared/register';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { BUTTON_COLORS, HOVERS } from '../../shared/options';
import metadata from './block.json';

const Edit = ( { attributes: a, setAttributes: set } ) => (
	<>
		<InspectorControls>
			<PanelBody title="Social links">
				<p>The links are the Social menu’s: Appearance → Menus.</p>
				<SelectControl label="Color" value={ a.color } options={ BUTTON_COLORS } onChange={ ( color ) => set( { color } ) } />
				<SelectControl label="Hover effect" value={ a.hover } options={ HOVERS } onChange={ ( hover ) => set( { hover } ) } />
			</PanelBody>
		</InspectorControls>
		<div { ...useBlockProps() }><ServerSideRender block={ metadata.name } attributes={ a } /></div>
	</>
);

register( metadata, { edit: Edit } );
