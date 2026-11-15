/*
 * The theme's text formats in the editor (marccent-theme markup/formats.json, #147): part of a line styled with the
 * theme's classes (Muted, Badge…), in the toolbar's ▾ menu. Plain JS on WordPress's own packages (no build); the list
 * is window.marccentFormats (inc/formats.php, from build/formats.json), so a format the theme adds reaches the editor
 * with its pin bump and no change here.
 *
 * A format without a class is a plain tag (Raised: sup), which WordPress's own formats already write
 * (core/superscript): that one is used, not a second one on the same tag, which WordPress refuses. WordPress takes one
 * class per format, so a format of several ("chip bg-primary-tint") is found by its first and writes the others too.
 */
( function ( wp ) {
	const { createElement: el } = wp.element;
	const { registerFormatType, toggleFormat, store: richTextStore } = wp.richText;
	const { RichTextToolbarButton } = wp.blockEditor;
	const taken = ( tagName, className ) => wp.data.select( richTextStore ).getFormatTypes()
		.some( ( f ) => ( className ? f.className === className : f.tagName === tagName && ! f.className ) );

	Object.entries( window.marccentFormats || {} ).forEach( ( [ name, f ] ) => {
		const [ className = null, ...more ] = ( f.className || '' ).split( /\s+/ ).filter( Boolean );
		if ( taken( f.tag, className ) ) {
			return; // core/superscript for a plain sup; another format on the same class
		}
		const type = `marccent/${ name }`;
		const format = more.length ? { type, attributes: { class: more.join( ' ' ) } } : { type };
		registerFormatType( type, {
			title: f.title,
			tagName: f.tag,
			className,
			edit: ( { isActive, value, onChange } ) => el( RichTextToolbarButton, {
				icon: 'editor-textcolor',
				title: f.title,
				isActive,
				onClick: () => onChange( toggleFormat( value, format ) ),
			} ),
		} );
	} );
} )( window.wp );
