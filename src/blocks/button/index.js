/*
 * Button: the theme's Button (components/ui/button.js) itself, saved as static HTML: a link when it has a URL. The
 * icon is a Font Awesome name ("phone") or full classes ("fa-brands fa-facebook"); none makes it a text link.
 * trace adds the outline the stroke hovers draw (hover-trace, hover-dash).
 */
import { register } from '../../shared/register';
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, ToggleControl } from '@wordpress/components';
import { Button } from 'marccent-theme/components/ui/button';
import { BUTTON_COLORS, HOVERS, cx } from '../../shared/options';
import metadata from './block.json';

const linkProps = ( a ) => ( a.newTab ? { target: '_blank', rel: 'noopener noreferrer' } : {} );

const Edit = ( { attributes: a, setAttributes: set } ) => {
	// In the editor it's a <button> (no href), so clicking edits the label instead of following the link.
	const props = useBlockProps( { className: cx( a.color, a.hover ) } );
	return (
		<>
			<InspectorControls>
				<PanelBody title="Button">
					<TextControl label="Link" help="A page URL, #id, tel:+1… or mailto:…" value={ a.href } onChange={ ( href ) => set( { href } ) } />
					<ToggleControl label="Open in a new tab" checked={ a.newTab } onChange={ ( newTab ) => set( { newTab } ) } />
					<TextControl label="Icon" help="A Font Awesome name (phone), or full classes (fa-brands fa-facebook). Empty: a text link." value={ a.icon } onChange={ ( icon ) => set( { icon } ) } />
					<SelectControl label="Color" value={ a.color } options={ BUTTON_COLORS } onChange={ ( color ) => set( { color } ) } />
					<SelectControl label="Hover effect" value={ a.hover } options={ HOVERS } onChange={ ( hover ) => set( { hover } ) } />
					<ToggleControl label="Outline for stroke hovers" checked={ a.trace } onChange={ ( trace ) => set( { trace } ) } />
				</PanelBody>
			</InspectorControls>
			<Button icon={ a.icon || false } trace={ a.trace } { ...props }>
				<RichText tagName="span" value={ a.label } onChange={ ( label ) => set( { label } ) } placeholder="Label" allowedFormats={ [] } withoutInteractiveFormatting />
			</Button>
		</>
	);
};

const save = ( { attributes: a } ) => (
	<Button icon={ a.icon || false } href={ a.href || undefined } trace={ a.trace } { ...linkProps( a ) } { ...useBlockProps.save( { className: cx( a.color, a.hover ) } ) }>
		{ a.label }
	</Button>
);

register( metadata, { edit: Edit, save } );
