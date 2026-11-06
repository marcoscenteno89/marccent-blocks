/*
 * Form field: the theme's Field (its label beside its input) as the page shows it, and its settings.
 */
import { register } from '../../shared/register';
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, TextareaControl, ToggleControl } from '@wordpress/components';
import { NameControl, nameOf } from '../../shared/form-name';
import metadata from './block.json';

const TYPES = [ [ 'Text', 'text' ], [ 'Email', 'email' ], [ 'Phone', 'tel' ], [ 'Web address', 'url' ], [ 'Number', 'number' ], [ 'Date', 'date' ], [ 'Several lines', 'textarea' ], [ 'Choice from a list', 'select' ], [ 'Checkbox', 'checkbox' ], [ 'File upload', 'file' ], [ 'Next step (splits the form)', 'step' ] ].map( ( [ label, value ] ) => ( { label, value } ) );

const Edit = ( { attributes: a, setAttributes: set, clientId } ) => {
	if ( a.type === 'step' ) {
		// Where the next step starts: its title is the label.
		return (
			<>
				<InspectorControls>
					<PanelBody title="Step">
						<SelectControl label="Type" value={ a.type } options={ TYPES } onChange={ ( type ) => set( { type } ) } />
						<p>Visitors see the fields before this as one step, with a Next button; the form’s last step has the send button.</p>
					</PanelBody>
				</InspectorControls>
				<div { ...useBlockProps( { style: { gridColumn: '1 / -1', borderTop: '2px dashed currentColor', opacity: 0.6, paddingTop: '4px' } } ) }>
					<RichText tagName="span" value={ a.label } placeholder="Next step’s title" allowedFormats={ [] } onChange={ ( label ) => set( { label } ) } />
				</div>
			</>
		);
	}
	const control = a.type === 'textarea' ? <textarea className="input" rows={ 3 } placeholder={ a.placeholder } readOnly />
		: a.type === 'select' ? <select className="input" disabled><option>Choose…</option></select>
		: a.type === 'checkbox' ? <span className="input"><input type="checkbox" disabled /></span>
		: a.type === 'file' ? <input className="input" type="file" disabled />
		: <input className="input" type={ a.type } placeholder={ a.placeholder } readOnly />;
	return (
		<>
			<InspectorControls>
				<PanelBody title="Field">
					<SelectControl label="Type" value={ a.type } options={ TYPES } onChange={ ( type ) => set( { type } ) } />
					<NameControl clientId={ clientId } name={ a.name } onChange={ ( name ) => set( { name } ) } />
					<ToggleControl label="Required" checked={ a.required } onChange={ ( required ) => set( { required } ) } />
					{ a.type === 'select' && <TextareaControl label="Choices, one per line" value={ a.options } onChange={ ( options ) => set( { options } ) } /> }
					{ ! [ 'select', 'checkbox', 'date', 'file' ].includes( a.type ) && <TextControl label="Placeholder" value={ a.placeholder } onChange={ ( placeholder ) => set( { placeholder } ) } /> }
					{ a.type === 'file' && <TextControl label="Kinds of file" help="Empty: images, PDFs and office documents, up to 5 MB. Or e.g. image/*, or .pdf" value={ a.accept } onChange={ ( accept ) => set( { accept } ) } /> }
					<TextControl label="Help under it" value={ a.help } onChange={ ( help ) => set( { help } ) } />
					<TextControl label="Show only when" help="Another field’s name and value: service=Patios,Driveways (one of), contact!=Phone, or phone (filled in). Hidden, it isn’t required." value={ a.showIf } onChange={ ( showIf ) => set( { showIf } ) } />
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps( { className: 'field joined sub-x' } ) }>
				<RichText tagName="label" className="field-label" value={ a.label } placeholder="Label" allowedFormats={ [] }
					onChange={ ( label ) => set( { label, ...( a.name && a.name !== nameOf( a.label ) ? {} : { name: nameOf( label ) } ) } ) } />
				{ control }
			</div>
			{ a.help && <p className="fs-sm opacity-75">{ a.help }</p> }
		</>
	);
};

register( metadata, { edit: Edit, save: () => null } );
