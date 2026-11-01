/*
 * Form field: the theme's Field (its label beside its input) as the page shows it, and its settings.
 */
import { register } from '../../shared/register';
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, TextareaControl, ToggleControl } from '@wordpress/components';
import { NameControl, nameOf } from '../../shared/form-name';
import metadata from './block.json';

const TYPES = [ [ 'Text', 'text' ], [ 'Email', 'email' ], [ 'Phone', 'tel' ], [ 'Web address', 'url' ], [ 'Number', 'number' ], [ 'Date', 'date' ], [ 'Several lines', 'textarea' ], [ 'Choice from a list', 'select' ], [ 'Checkbox', 'checkbox' ] ].map( ( [ label, value ] ) => ( { label, value } ) );

const Edit = ( { attributes: a, setAttributes: set, clientId } ) => {
	const control = a.type === 'textarea' ? <textarea className="input" rows={ 3 } placeholder={ a.placeholder } readOnly />
		: a.type === 'select' ? <select className="input" disabled><option>Choose…</option></select>
		: a.type === 'checkbox' ? <span className="input"><input type="checkbox" disabled /></span>
		: <input className="input" type={ a.type } placeholder={ a.placeholder } readOnly />;
	return (
		<>
			<InspectorControls>
				<PanelBody title="Field">
					<SelectControl label="Type" value={ a.type } options={ TYPES } onChange={ ( type ) => set( { type } ) } />
					<NameControl clientId={ clientId } name={ a.name } onChange={ ( name ) => set( { name } ) } />
					<ToggleControl label="Required" checked={ a.required } onChange={ ( required ) => set( { required } ) } />
					{ a.type === 'select' && <TextareaControl label="Choices, one per line" value={ a.options } onChange={ ( options ) => set( { options } ) } /> }
					{ ! [ 'select', 'checkbox', 'date' ].includes( a.type ) && <TextControl label="Placeholder" value={ a.placeholder } onChange={ ( placeholder ) => set( { placeholder } ) } /> }
					<TextControl label="Help under it" value={ a.help } onChange={ ( help ) => set( { help } ) } />
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps( { className: 'field sub-x' } ) }>
				<RichText tagName="label" className="field-label" value={ a.label } placeholder="Label" allowedFormats={ [] }
					onChange={ ( label ) => set( { label, ...( a.name && a.name !== nameOf( a.label ) ? {} : { name: nameOf( label ) } ) } ) } />
				{ control }
			</div>
			{ a.help && <p className="fs-sm opacity-75">{ a.help }</p> }
		</>
	);
};

register( metadata, { edit: Edit, save: () => null } );
