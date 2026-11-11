/*
 * A block that is one of the theme's React components (an island: render.php writes data-island and its props, the
 * runtime draws it, src/runtime/islands/<name>.js). Its settings are a list of fields, each an attribute of the
 * block and, as they are, a prop of the component:
 *
 *   { key, label, type, help, options, min, max, step, fields }
 *   type: text, textarea, number, range, toggle, select (options: [ [ label, value ] ]), icon (a Font Awesome name),
 *   image (its URL, from the media library), url, code (the theme's Code as an editor; language: a language, or a function
 *   of the block's attributes), list (rows of fields: tabs, events, items…; itemLabel names one).
 *
 * In the editor the block shows the component itself, drawn from those props (preview), so what's edited is what
 * the page shows; or, for a component whose items are blocks (tabs, a timeline's items, a stat card's buttons), those
 * blocks to edit.
 */
import { Component as ReactComponent } from '@wordpress/element';
import { InnerBlocks, InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { Button, PanelBody, RangeControl, SelectControl, TextControl, TextareaControl, ToggleControl } from '@wordpress/components';
import { register } from './register';
import { CodeField } from './code-field';

const opt = ( [ label, value ] ) => ( { label, value } );

// A component that throws (half-filled props) shows a note instead of taking the editor down.
class Boundary extends ReactComponent {
	constructor( props ) {
		super( props );
		this.state = { error: null };
	}
	static getDerivedStateFromError( error ) {
		return { error };
	}
	componentDidUpdate( prev ) {
		if ( prev.watch !== this.props.watch && this.state.error ) {
			this.setState( { error: null } );
		}
	}
	render() {
		return this.state.error ? <p className="pad-sm border round" style={ { opacity: 0.7 } }>Can’t preview this yet: { String( this.state.error.message || this.state.error ) }</p> : this.props.children;
	}
}

const ImageField = ( { f, value, onChange } ) => (
	<div className="components-base-control" style={ { marginBottom: 16 } }>
		<p style={ { fontWeight: 500, fontSize: 11, textTransform: 'uppercase', margin: '0 0 8px' } }>{ f.label }</p>
		{ value && <img src={ value } alt="" style={ { width: '100%', height: 'auto', marginBottom: 8 } } /> }
		<MediaUploadCheck>
			<MediaUpload allowedTypes={ [ 'image' ] } onSelect={ ( m ) => onChange( m.url ) }
				render={ ( { open } ) => <Button variant="secondary" onClick={ open }>{ value ? 'Replace' : 'Choose an image' }</Button> } />
		</MediaUploadCheck>
		{ value && <Button variant="link" isDestructive onClick={ () => onChange( '' ) } style={ { marginLeft: 8 } }>Remove</Button> }
		<TextControl label="…or its URL" value={ value || '' } onChange={ onChange } />
	</div>
);

export const Field = ( { f, value, onChange, values = {} } ) => {
	const common = { label: f.label, help: f.help };
	switch ( f.type ) {
		case 'code': return <CodeField { ...common } language={ typeof f.language === 'function' ? f.language( values ) : f.language } value={ value ?? '' } onChange={ onChange } />;
		case 'textarea': return <TextareaControl { ...common } value={ value ?? '' } onChange={ onChange } />;
		case 'number': return <TextControl { ...common } type="number" value={ value ?? '' } onChange={ ( v ) => onChange( v === '' ? undefined : Number( v ) ) } />;
		case 'range': return <RangeControl { ...common } value={ value } min={ f.min } max={ f.max } step={ f.step } onChange={ onChange } />;
		case 'toggle': return <ToggleControl { ...common } checked={ !! value } onChange={ onChange } />;
		case 'select': return <SelectControl { ...common } value={ value ?? '' } options={ f.options.map( opt ) } onChange={ onChange } />;
		case 'image': return <ImageField f={ f } value={ value } onChange={ onChange } />;
		case 'icon': return <TextControl { ...common } help={ f.help || 'A Font Awesome name: star, phone, fa-brands fa-github…' } value={ value ?? '' } onChange={ onChange } />;
		case 'url': return <TextControl { ...common } type="url" value={ value ?? '' } onChange={ onChange } />;
		default: return <TextControl { ...common } value={ value ?? '' } onChange={ onChange } />;
	}
};

// A list field: its rows, each folded under its first text, to add, reorder and remove.
const ListField = ( { f, value, onChange } ) => {
	const rows = Array.isArray( value ) ? value : [];
	const put = ( i, row ) => onChange( rows.map( ( r, j ) => ( j === i ? row : r ) ) );
	const move = ( i, by ) => {
		const next = rows.slice();
		next.splice( i + by, 0, next.splice( i, 1 )[ 0 ] );
		onChange( next );
	};
	const title = ( row, i ) => {
		const text = f.fields.map( ( x ) => row?.[ x.key ] ).find( ( v ) => typeof v === 'string' && v.trim() );
		return `${ i + 1 }. ${ text ? text.slice( 0, 40 ) : f.itemLabel || 'Item' }`;
	};
	return (
		<PanelBody title={ `${ f.label } (${ rows.length })` } initialOpen={ false }>
			{ f.help && <p className="components-base-control__help">{ f.help }</p> }
			{ rows.map( ( row, i ) => (
				<PanelBody key={ i } title={ title( row, i ) } initialOpen={ false }>
					{ f.fields.map( ( x ) => <Field key={ x.key } f={ x } value={ row?.[ x.key ] } onChange={ ( v ) => put( i, { ...row, [ x.key ]: v } ) } /> ) }
					<div style={ { display: 'flex', gap: 8 } }>
						<Button variant="secondary" size="small" disabled={ i === 0 } onClick={ () => move( i, -1 ) }>Up</Button>
						<Button variant="secondary" size="small" disabled={ i === rows.length - 1 } onClick={ () => move( i, 1 ) }>Down</Button>
						<Button variant="link" isDestructive onClick={ () => onChange( rows.filter( ( r, j ) => j !== i ) ) }>Remove</Button>
					</div>
				</PanelBody>
			) ) }
			<Button variant="secondary" onClick={ () => onChange( [ ...rows, Object.fromEntries( f.fields.filter( ( x ) => x.default !== undefined ).map( ( x ) => [ x.key, x.default ] ) ) ] ) }>
				Add { ( f.itemLabel || 'item' ).toLowerCase() }
			</Button>
		</PanelBody>
	);
};

// The block's props for the component: its fields' values (toProps reshapes them when the component wants more).
export const propsOf = ( fields, a ) => Object.fromEntries( fields.map( ( f ) => [ f.key, a[ f.key ] ] ) );

export const islandBlock = ( metadata, { fields = [], preview, toProps, inner, note, deprecated } ) => {
	const Edit = ( { attributes: a, setAttributes: set } ) => {
		const props = ( toProps || ( ( x ) => propsOf( fields, x ) ) )( a );
		const blockProps = useBlockProps();
		const innerProps = useInnerBlocksProps( { className: inner?.className ?? 'grid cols-1 gap-sm' }, inner || {} );
		const plain = fields.filter( ( f ) => f.type !== 'list' );
		return (
			<>
				<InspectorControls>
					{ plain.length > 0 && <PanelBody title={ metadata.title }>{ plain.map( ( f ) => <Field key={ f.key } f={ f } value={ a[ f.key ] } values={ a } onChange={ ( v ) => set( { [ f.key ]: v } ) } /> ) }</PanelBody> }
					{ fields.filter( ( f ) => f.type === 'list' ).map( ( f ) => <ListField key={ f.key } f={ f } value={ a[ f.key ] } onChange={ ( v ) => set( { [ f.key ]: v } ) } /> ) }
				</InspectorControls>
				<div { ...blockProps }>
					{ note && <p style={ { fontSize: 12, opacity: 0.7, margin: '0 0 8px' } }>{ note }</p> }
					{ preview && <Boundary watch={ JSON.stringify( props ) }>{ preview( props ) }</Boundary> }
					{ inner && <div { ...innerProps } /> }
				</div>
			</>
		);
	};
	register( metadata, { edit: Edit, save: () => ( inner ? <InnerBlocks.Content /> : null ), deprecated } );
};
