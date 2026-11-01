/*
 * A form field's name (what the server reads it by): from its label until it's set by hand ("First name" → first_name),
 * and a warning when another field in the same form has it (the server keeps the first).
 */
import { store as blockEditorStore } from '@wordpress/block-editor';
import { Notice, TextControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';

export const nameOf = ( s ) => ( s || '' ).toLowerCase().normalize( 'NFD' ).replace( /[̀-ͯ]/g, '' ).replace( /[^a-z0-9]+/g, '_' ).replace( /^_+|_+$/g, '' ).slice( 0, 40 );

export const NameControl = ( { clientId, name, onChange } ) => {
	const taken = useSelect( ( select ) => {
		const editor = select( blockEditorStore );
		const form = editor.getBlockParentsByBlockName( clientId, 'marccent/form', true )[ 0 ];
		const walk = ( ids ) => ids.flatMap( ( id ) => [ id, ...walk( editor.getBlockOrder( id ) ) ] );
		return form ? walk( editor.getBlockOrder( form ) ).filter( ( id ) => id !== clientId ).some( ( id ) => editor.getBlockAttributes( id )?.name === name ) : false;
	}, [ clientId, name ] );
	return (
		<>
			<TextControl label="Name" help="How the entry and the email list it: lowercase, numbers, underscores." value={ name } onChange={ ( v ) => onChange( nameOf( v ) ) } />
			{ ! name && <Notice status="warning" isDismissible={ false }>Give it a name, or it isn’t sent.</Notice> }
			{ taken && <Notice status="warning" isDismissible={ false }>Another field in this form is named “{ name }”: only the first is kept.</Notice> }
		</>
	);
};
