/*
 * Icon: the theme's Icon (components/ui/icon.js), alone or centered in a shape of the given width, like the home
 * page's features (a gradient hexagon). Names are Font Awesome's without the prefix: "star", "map-pin".
 */
import { register } from '../../shared/register';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { ExternalLink, PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { Icon } from 'marccent-theme/components/ui/icon';
import { SHAPES, SURFACES, cx } from '../../shared/options';
import metadata from './block.json';

const FAMILIES = [ { label: 'Solid', value: 'fa-solid' }, { label: 'Regular', value: 'fa-regular' }, { label: 'Brands', value: 'fa-brands' } ];

const boxProps = ( a ) => a.shape
	? { className: cx( a.shape, a.surface, 'col-center' ), style: { width: a.size } }
	: { className: 'grid', style: { '--icon-size': a.size } };

const Glyph = ( { a } ) => (
	<>
		<Icon name={ a.icon } family={ a.family } />
		{ a.label && <span className="screen-reader-text">{ a.label }</span> }
	</>
);

const Edit = ( { attributes: a, setAttributes: set } ) => (
	<>
		<InspectorControls>
			<PanelBody title="Icon">
				<TextControl label="Icon name" value={ a.icon } onChange={ ( icon ) => set( { icon: icon.replace( /^fa-/, '' ) } ) }
					help={ <ExternalLink href="https://fontawesome.com/search?ic=free">Find one</ExternalLink> } />
				<SelectControl label="Style" value={ a.family } options={ FAMILIES } onChange={ ( family ) => set( { family } ) } />
				<SelectControl label="Shape" value={ a.shape } options={ SHAPES } onChange={ ( shape ) => set( { shape } ) } />
				{ a.shape && <SelectControl label="Shape color" value={ a.surface } options={ SURFACES } onChange={ ( surface ) => set( { surface } ) } /> }
				<TextControl label="Size" help="Width of the shape, or the icon's size alone." value={ a.size } onChange={ ( size ) => set( { size } ) } />
				<TextControl label="Label for screen readers" help="Leave empty when the icon is decoration." value={ a.label } onChange={ ( label ) => set( { label } ) } />
			</PanelBody>
		</InspectorControls>
		<div { ...useBlockProps( boxProps( a ) ) }><Glyph a={ a } /></div>
	</>
);

const save = ( { attributes: a } ) => <div { ...useBlockProps.save( boxProps( a ) ) }><Glyph a={ a } /></div>;

register( metadata, { edit: Edit, save } );
