/*
 * Form: its fields (and any blocks between them) inside the theme's form layout; render.php writes the real <form>,
 * inc/forms.php checks and keeps what's sent. Its formId ties the page's form to what the server reads back: set once
 * when the block is added, and again if a copy of the block shares it.
 */
import { register } from '../../shared/register';
import { InnerBlocks, InspectorControls, store as blockEditorStore, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, TextareaControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { useEffect } from '@wordpress/element';
import metadata from './block.json';

const LAYOUTS = [ { label: 'Labels beside the fields', value: 'grid gap-sm pairs' }, { label: 'One column', value: 'grid cols-1 gap-sm' } ];
const newId = () => Math.random().toString( 36 ).slice( 2, 10 );

const TEMPLATE = [
	[ 'marccent/form-field', { name: 'name', label: 'Name', required: true } ],
	[ 'marccent/form-field', { name: 'email', label: 'Email', type: 'email', required: true } ],
	[ 'marccent/form-field', { name: 'message', label: 'Message', type: 'textarea' } ],
];

const Edit = ( { attributes: a, setAttributes: set, clientId } ) => {
	// Another Form earlier on the page with the same id (a duplicated block): this one takes a new id.
	const clash = useSelect( ( select ) => {
		const editor = select( blockEditorStore );
		const ids = ( editor.getBlocksByName?.( 'marccent/form' ) ?? editor.__experimentalGetGlobalBlocksByName?.( 'marccent/form' ) ?? [] );
		const before = ids.slice( 0, ids.indexOf( clientId ) );
		return before.some( ( id ) => editor.getBlockAttributes( id )?.formId === a.formId );
	}, [ clientId, a.formId ] );
	useEffect( () => {
		if ( ! a.formId || clash ) {
			set( { formId: newId() } );
		}
	}, [ a.formId, clash ] );

	// The post types entries can be kept in: the site's own (made in Fields), not WordPress's.
	const types = useSelect( ( select ) => ( select( coreStore ).getPostTypes( { per_page: -1 } ) ?? [] )
		.filter( ( t ) => ! [ 'post', 'page', 'attachment', 'marccent_entry' ].includes( t.slug ) && ! t.slug.startsWith( 'wp_' ) && ! t.slug.startsWith( 'nav_' ) )
		.map( ( t ) => ( { label: t.name, value: t.slug } ) ), [] );
	const props = useInnerBlocksProps( useBlockProps( { className: a.layout } ), { template: TEMPLATE } );
	return (
		<>
			<InspectorControls>
				<PanelBody title="Form">
					<SelectControl label="Layout" value={ a.layout } options={ LAYOUTS } onChange={ ( layout ) => set( { layout } ) } />
					<TextControl label="Button" value={ a.submitLabel } onChange={ ( submitLabel ) => set( { submitLabel } ) } />
					<TextControl label="Button icon" help="A Font Awesome name." value={ a.submitIcon } onChange={ ( submitIcon ) => set( { submitIcon } ) } />
					<TextareaControl label="After it’s sent" value={ a.successMessage } onChange={ ( successMessage ) => set( { successMessage } ) } />
				</PanelBody>
				<PanelBody title="Entries">
					<SelectControl label="Keep entries in" help="A post type made in Fields (best “Only in wp-admin”), with a Data (JSON) field for everything sent; a field named as a form field gets that answer too. Empty: Forms → Entries." value={ a.saveTo } options={ [ { label: 'Forms → Entries', value: '' }, ...types ] } onChange={ ( saveTo ) => set( { saveTo } ) } />
				</PanelBody>
				<PanelBody title="Email">
					<TextControl label="Send entries to" type="email" help="Empty: the site’s admin email. Every entry is also kept (Entries, above)." value={ a.emailTo } onChange={ ( emailTo ) => set( { emailTo } ) } />
					<TextControl label="Subject" help="Empty: “New submission: <the page’s title>”." value={ a.emailSubject } onChange={ ( emailSubject ) => set( { emailSubject } ) } />
				</PanelBody>
				<PanelBody title="Send on" initialOpen={ !! a.webhook }>
					<TextControl label="Webhook" type="url" help="An address each entry is posted to as JSON: Zapier, Make, a CRM. Empty: none." value={ a.webhook } onChange={ ( webhook ) => set( { webhook } ) } />
				</PanelBody>
			</InspectorControls>
			<div { ...props }>
				{ props.children }
				<div><span className="btn primary" role="presentation"><i className={ `fa-solid fa-${ a.submitIcon || 'paper-plane' } btn-icon` } aria-hidden="true" /><span className="btn-text">{ a.submitLabel || 'Send' }</span></span></div>
			</div>
		</>
	);
};

register( metadata, { edit: Edit, save: () => <InnerBlocks.Content /> } );
