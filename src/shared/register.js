/*
 * registerBlockType for our blocks: the same, with the block's icon (block.json's dashicon) white on Marccent blue,
 * so in the inserter, the toolbar and List View a Marccent block stands out from core ones.
 */
import { registerBlockType } from '@wordpress/blocks';
import './custom-css'; // every block's Additional CSS in the theme's Code (once, whichever block loads first)

const BRAND = '#1f64cf'; // marccent-theme's default palette (ocean)

export const register = ( metadata, settings ) => registerBlockType( metadata, {
	icon: { src: metadata.icon, foreground: '#fff', background: BRAND },
	...settings,
} );
