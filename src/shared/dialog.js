/*
 * The editor for a block that is a button opening a dialog (Modal, Drawer): the button as the page shows it, and below
 * it the dialog's title and blocks in a dashed box (on the page it opens over everything, so here it stays in place
 * to be edited). Its settings: the dialog's title and look, the button's label, icon and color, and the dialog's id.
 */
import { InspectorControls, useBlockProps, useInnerBlocksProps, RichText } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { BUTTON_COLORS, cx } from './options';

export const DialogEdit = ( { attributes: a, setAttributes: set, looks, note } ) => {
	const blockProps = useBlockProps( { className: 'grid cols-1 gap-sm' } );
	const inner = useInnerBlocksProps( { className: 'pad grid cols-1' }, { template: [ [ 'core/paragraph', { placeholder: 'What the dialog says…' } ] ] } );
	return (
		<>
			<InspectorControls>
				<PanelBody title="Dialog">
					<SelectControl label={ looks.label } value={ a[ looks.key ] } options={ looks.options } onChange={ ( v ) => set( { [ looks.key ]: v } ) } />
					<TextControl label="Its id (optional)" help={ `Any link or button with data-opens="${ a.dialogId || 'its-id' }" opens it too.` }
						value={ a.dialogId } onChange={ ( dialogId ) => set( { dialogId: dialogId.toLowerCase().replace( /[^a-z0-9_-]/g, '-' ) } ) } />
				</PanelBody>
				<PanelBody title="Button">
					<TextControl label="Icon" help="A Font Awesome name: arrow-right, bars, circle-info…" value={ a.buttonIcon } onChange={ ( buttonIcon ) => set( { buttonIcon } ) } />
					<SelectControl label="Color" value={ a.buttonColor } options={ BUTTON_COLORS } onChange={ ( buttonColor ) => set( { buttonColor } ) } />
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<div>
					<span className={ cx( 'btn', a.buttonColor ) } role="presentation">
						{ a.buttonIcon && <i className={ `fa-solid fa-${ a.buttonIcon } btn-icon` } aria-hidden="true" /> }
						<RichText tagName="span" className="btn-text" value={ a.buttonLabel } onChange={ ( buttonLabel ) => set( { buttonLabel } ) } placeholder="Button" allowedFormats={ [] } />
					</span>
				</div>
				<div className="border round" style={ { borderStyle: 'dashed' } }>
					<div className="header pad-sm" style={ { fontSize: 12, opacity: 0.7 } }>{ note }</div>
					<RichText tagName="h2" className="h5 pad-x" value={ a.title } onChange={ ( title ) => set( { title } ) } placeholder="Title" allowedFormats={ [] } />
					<div { ...inner } />
				</div>
			</div>
		</>
	);
};
