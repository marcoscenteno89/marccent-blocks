/*
 * Form rating: the theme's Rating (radio stars) beside its label, and its settings.
 */
import { register } from '../../shared/register';
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl, TextControl, ToggleControl } from '@wordpress/components';
import { NameControl } from '../../shared/form-name';
import metadata from './block.json';

const Edit = ( { attributes: a, setAttributes: set, clientId } ) => (
	<>
		<InspectorControls>
			<PanelBody title="Rating">
				<NameControl clientId={ clientId } name={ a.name } onChange={ ( name ) => set( { name } ) } />
				<RangeControl label="Stars" value={ a.maximum } min={ 2 } max={ 10 } onChange={ ( maximum ) => set( { maximum } ) } />
				<TextControl label="Icon" help="A Font Awesome name: star, heart, thumbs-up…" value={ a.icon } onChange={ ( icon ) => set( { icon } ) } />
				<ToggleControl label="Required" checked={ a.required } onChange={ ( required ) => set( { required } ) } />
			</PanelBody>
		</InspectorControls>
		<div { ...useBlockProps( { className: 'field joined sub-x' } ) }>
			<RichText tagName="span" className="field-label" value={ a.label } placeholder="Rating" allowedFormats={ [] } onChange={ ( label ) => set( { label } ) } />
			<span className="input"><span className="rating">{ Array.from( { length: a.maximum }, ( _, i ) => <span key={ i } className="rating-star"><i className={ `fa-solid fa-${ a.icon || 'star' }` } aria-hidden="true" /></span> ) }</span></span>
		</div>
	</>
);

register( metadata, { edit: Edit, save: () => null } );
