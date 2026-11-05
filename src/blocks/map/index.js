/*
 * Map: rendered by the server (render.php) as the theme's map markup; its engine draws it on the site. The editor shows
 * the server's markup (the Google Maps link until the map draws). Empty fields: the business's (Settings → Business).
 */
import { register } from '../../shared/register';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl, TextControl, ToggleControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import metadata from './block.json';

const Edit = ( { attributes: a, setAttributes: set } ) => (
	<>
		<InspectorControls>
			<PanelBody title="Map">
				<p>Left empty, the map shows the business (Settings → Business).</p>
				<TextControl label="Latitude" value={ a.lat } onChange={ ( lat ) => set( { lat } ) } />
				<TextControl label="Longitude" value={ a.lng } onChange={ ( lng ) => set( { lng } ) } />
				<RangeControl label="Zoom" min={ 3 } max={ 20 } value={ a.zoom } onChange={ ( zoom ) => set( { zoom } ) } />
				<ToggleControl label="A pin on the center" checked={ a.marker } onChange={ ( marker ) => set( { marker } ) } />
				<TextControl label="Pin title" value={ a.title } onChange={ ( title ) => set( { title } ) } />
				<TextControl label="Google Maps link" help="What a click on the pin opens." value={ a.link } onChange={ ( link ) => set( { link } ) } />
			</PanelBody>
		</InspectorControls>
		<div { ...useBlockProps() }><ServerSideRender block={ metadata.name } attributes={ a } /></div>
	</>
);

register( metadata, { edit: Edit } );
