/*
 * Contact: the theme's Contact (components/ui/contact.js): a phone number that calls, an email address that emails,
 * or a street address, with its icon. The server writes it on the page (render.php, marccent_blocks_contact).
 */
import { register } from '../../shared/register';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, ToggleControl } from '@wordpress/components';
import { Contact } from 'marccent-theme/components/ui/contact';
import { choices } from '../../shared/options';
import metadata from './block.json';

const KINDS = choices( 'Contact', 'kind' );
const PLACEHOLDER = { phone: '(208) 555-0100', email: 'hello@example.com', address: '123 Main St, Boise, ID 83702' };

const Edit = ( { attributes: a, setAttributes: set } ) => (
	<>
		<InspectorControls>
			<PanelBody title="Contact">
				<SelectControl label="What it is" value={ a.kind } options={ KINDS } onChange={ ( kind ) => set( { kind } ) } />
				<TextControl label="Value" help="As it reads: the number's link is its digits." placeholder={ PLACEHOLDER[ a.kind ] } value={ a.value } onChange={ ( value ) => set( { value } ) } />
				<TextControl label="Reads instead (optional)" help="Call us, Email us. Empty: the value." value={ a.label } onChange={ ( label ) => set( { label } ) } />
				<ToggleControl label="Icon" checked={ a.icon } onChange={ ( icon ) => set( { icon } ) } />
			</PanelBody>
		</InspectorControls>
		<div { ...useBlockProps() }>
			<Contact kind={ a.kind } value={ a.value || PLACEHOLDER[ a.kind ] } label={ a.label } icon={ a.icon } />
		</div>
	</>
);

register( metadata, { edit: Edit, save: () => null } );
