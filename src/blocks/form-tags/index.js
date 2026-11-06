/*
 * Form tags: the theme's TagInput beside its label (drawn on the page by the form-tags island), and its settings.
 */
import { register } from '../../shared/register';
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl, TextControl } from '@wordpress/components';
import { NameControl } from '../../shared/form-name';
import metadata from './block.json';

const Edit = ( { attributes: a, setAttributes: set, clientId } ) => (
	<>
		<InspectorControls>
			<PanelBody title="Tags">
				<NameControl clientId={ clientId } name={ a.name } onChange={ ( name ) => set( { name } ) } />
				<TextControl label="Suggestions" help="Comma-separated: offered as they type." value={ a.suggestions } onChange={ ( suggestions ) => set( { suggestions } ) } />
				<RangeControl label="At most" help="0: no limit." value={ a.max } min={ 0 } max={ 30 } onChange={ ( max ) => set( { max } ) } />
			</PanelBody>
		</InspectorControls>
		<div { ...useBlockProps( { className: 'field joined sub-x' } ) }>
			<RichText tagName="label" className="field-label" value={ a.label } placeholder="Add a tag" allowedFormats={ [] } onChange={ ( label ) => set( { label } ) } />
			<div className="input tag-input row-wrap"><span className="opacity-75">{ a.suggestions ? `e.g. ${ a.suggestions.split( ',' ).slice( 0, 3 ).join( ', ' ) }` : 'Tags…' }</span></div>
		</div>
	</>
);

register( metadata, { edit: Edit, save: () => null } );
