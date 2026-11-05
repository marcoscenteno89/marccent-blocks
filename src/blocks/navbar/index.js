/*
 * Navbar: rendered by the server (render.php), which hands the menu and these settings to the navbar island
 * (runtime/islands/navbar.js). The editor shows the server's markup.
 */
import { register } from '../../shared/register';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextareaControl, TextControl, ToggleControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import metadata from './block.json';

const Edit = ( { attributes: a, setAttributes: set } ) => (
	<>
		<InspectorControls>
			<PanelBody title="Navbar">
				<ToggleControl label="Show the site name by the logo" checked={ a.showName } onChange={ ( showName ) => set( { showName } ) } />
				{ a.social && (
					<TextareaControl label="Social links (older setting)" rows={ 4 } value={ a.social } onChange={ ( social ) => set( { social } ) }
						help="Used only while no menu is in the Social location. Put these links in Appearance → Menus → Social instead, then clear this." />
				) }
			</PanelBody>
			<PanelBody title="Call to action">
				<TextControl label="Label" value={ a.ctaLabel } onChange={ ( ctaLabel ) => set( { ctaLabel } ) } />
				<TextControl label="Link" value={ a.ctaHref } onChange={ ( ctaHref ) => set( { ctaHref } ) } />
				<TextControl label="Icon" value={ a.ctaIcon } onChange={ ( ctaIcon ) => set( { ctaIcon } ) } />
			</PanelBody>
		</InspectorControls>
		<div { ...useBlockProps() }><ServerSideRender block={ metadata.name } attributes={ a } /></div>
	</>
);

register( metadata, { edit: Edit } );
