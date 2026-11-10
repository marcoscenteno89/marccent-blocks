/*
 * registerBlockType for our blocks: the same, with the block's icon (block.json's dashicon) white on Marccent blue,
 * so in the inserter, the toolbar and List View a Marccent block stands out from core ones; and a block of a theme
 * component saves its settings version (src/shared/upgrade.js), set when it's inserted.
 */
import { registerBlockType } from '@wordpress/blocks';
import { useEffect } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { currentVersion } from './upgrade';
import './custom-css'; // every block's Additional CSS in the theme's Code (once, whichever block loads first)

const BRAND = '#1f64cf'; // marccent-theme's default palette (ocean)

const withVersion = ( name, Edit ) => {
	const version = currentVersion( name );
	if ( version === undefined ) {
		return Edit;
	}
	return ( props ) => {
		const { __unstableMarkNextChangeAsNotPersistent: quietly } = useDispatch( blockEditorStore );
		useEffect( () => {
			if ( props.attributes.version !== version ) {
				quietly?.();
				props.setAttributes( { version } );
			}
		}, [ props.attributes.version ] );
		return <Edit { ...props } />;
	};
};

export const register = ( metadata, settings ) => registerBlockType( metadata, {
	icon: { src: metadata.icon, foreground: '#fff', background: BRAND },
	...settings,
	edit: withVersion( metadata.name, settings.edit ),
} );
