/*
 * Every block's extras in the editor, plain JS on WordPress's own packages (no build):
 * - Visibility: who sees it (logged in or out), from and until when, and the screen sizes it's hidden on
 *   (the marccentVisibility attribute; inc/visibility.php decides on the server).
 * - Copy style / Paste style, in the block's ⋮ menu: its settings but not its words, links or pictures, onto other
 *   blocks of the same kind (a card's colors, padding and layout onto the other cards). Core's own blocks have
 *   WordPress's copy and paste styles too; this is for the Marccent blocks, whose style is their attributes.
 */
( function ( wp ) {
	const { createElement: el, Fragment } = wp.element;
	const { InspectorControls, BlockSettingsMenuControls, store: blockEditorStore } = wp.blockEditor;
	const { PanelBody, SelectControl, TextControl, CheckboxControl, MenuItem } = wp.components;
	const { createHigherOrderComponent } = wp.compose;
	const { addFilter } = wp.hooks;

	addFilter( 'blocks.registerBlockType', 'marccent/visibility', ( settings ) => ( {
		...settings,
		attributes: { ...settings.attributes, marccentVisibility: { type: 'object', default: {} } },
	} ) );

	const SIZES = [ [ 'mobile', 'Phones' ], [ 'tablet', 'Tablets' ], [ 'desktop', 'Computers' ] ];

	const VisibilityPanel = ( { attributes, setAttributes } ) => {
		const v = attributes.marccentVisibility || {};
		const set = ( patch ) => {
			const next = { ...v, ...patch };
			Object.keys( next ).forEach( ( k ) => ( next[ k ] === '' || ( Array.isArray( next[ k ] ) && ! next[ k ].length ) ) && delete next[ k ] );
			setAttributes( { marccentVisibility: next } );
		};
		const hide = v.hide || [];
		const on = Object.keys( v ).length > 0;
		return el( PanelBody, { title: on ? 'Visibility (set)' : 'Visibility', initialOpen: on },
			el( SelectControl, {
				label: 'Who sees it', value: v.who || '',
				options: [ { label: 'Everyone', value: '' }, { label: 'Logged-in visitors', value: 'in' }, { label: 'Logged-out visitors', value: 'out' } ],
				onChange: ( who ) => set( { who } ),
			} ),
			el( TextControl, { label: 'Shown from', type: 'datetime-local', value: v.from || '', onChange: ( from ) => set( { from } ) } ),
			el( TextControl, { label: 'Hidden from', type: 'datetime-local', help: 'The site’s time zone. Empty: always.', value: v.until || '', onChange: ( until ) => set( { until } ) } ),
			el( 'p', { style: { margin: '8px 0 4px', fontSize: '11px', fontWeight: 500, textTransform: 'uppercase' } }, 'Hide on' ),
			SIZES.map( ( [ size, label ] ) => el( CheckboxControl, {
				key: size, label, checked: hide.includes( size ),
				onChange: ( yes ) => set( { hide: yes ? [ ...hide, size ] : hide.filter( ( x ) => x !== size ) } ),
			} ) )
		);
	};

	addFilter( 'editor.BlockEdit', 'marccent/visibility', createHigherOrderComponent( ( BlockEdit ) => ( props ) => el( Fragment, null,
		el( BlockEdit, props ),
		props.isSelected && el( InspectorControls, null, el( VisibilityPanel, props ) )
	), 'withMarccentVisibility' ) );

	// In the editor, a block hidden somewhere looks faded, so it isn't mistaken for gone.
	addFilter( 'editor.BlockListBlock', 'marccent/visibility', createHigherOrderComponent( ( BlockListBlock ) => ( props ) => {
		const v = props.attributes?.marccentVisibility;
		return v && Object.keys( v ).length ? el( BlockListBlock, { ...props, className: [ props.className, 'marccent-has-visibility' ].filter( Boolean ).join( ' ' ) } ) : el( BlockListBlock, props );
	}, 'withMarccentVisibilityClass' ) );

	/* Copy style / Paste style */

	// What isn't style: the words, links, pictures and ids a block holds (by name, and core's "content" role).
	const CONTENT = /^(label|text|title|heading|subtitle|content|body|description|caption|href|url|link|src|alt|image|imageId|mediaId|mediaUrl|id|formId|name|items|options|placeholder|help|anchor|key|metadata|value|values|question|answer|quote|author|cite|code|icon)$/i;
	let copied = null;

	const styleOf = ( block ) => {
		const type = wp.blocks.getBlockType( block.name );
		const out = {};
		Object.entries( block.attributes ).forEach( ( [ k, val ] ) => {
			if ( ! CONTENT.test( k ) && type?.attributes?.[ k ]?.role !== 'content' && type?.attributes?.[ k ]?.__experimentalRole !== 'content' ) out[ k ] = val;
		} );
		return out;
	};

	const notice = ( text ) => wp.data.dispatch( 'core/notices' ).createNotice( 'info', text, { type: 'snackbar', isDismissible: true } );

	wp.element && wp.plugins?.registerPlugin && wp.plugins.registerPlugin( 'marccent-copy-style', {
		render: () => el( BlockSettingsMenuControls, null, ( { selectedClientIds, onClose } ) => {
			const editor = wp.data.select( blockEditorStore );
			const blocks = selectedClientIds.map( ( id ) => editor.getBlock( id ) ).filter( Boolean );
			if ( ! blocks.length || ! blocks[ 0 ].name.startsWith( 'marccent/' ) ) return null;
			const same = copied && blocks.every( ( b ) => b.name === copied.name );
			return el( Fragment, null,
				el( MenuItem, { onClick: () => {
					copied = { name: blocks[ 0 ].name, style: styleOf( blocks[ 0 ] ) };
					notice( 'Style copied.' );
					onClose();
				} }, 'Copy style' ),
				el( MenuItem, { disabled: ! same, onClick: () => {
					wp.data.dispatch( blockEditorStore ).updateBlockAttributes( blocks.map( ( b ) => b.clientId ), copied.style );
					notice( blocks.length > 1 ? `Style pasted onto ${ blocks.length } blocks.` : 'Style pasted.' );
					onClose();
				} }, copied && ! same ? 'Paste style (copied from another kind of block)' : 'Paste style' )
			);
		} ),
	} );
} )( window.wp );
