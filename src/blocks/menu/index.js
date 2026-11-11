/*
 * Menu: rendered by the server (render.php), the theme's Menu with the links of a menu made in Appearance → Menus.
 * The editor shows the server's markup.
 */
import { register } from '../../shared/register';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, ToggleControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { MenuSelect } from '../../shared/menu-select';
import metadata from './block.json';

// The theme's Menu layouts (components/ui/menu.js): what replaces "fixed".
const LAYOUTS = [
	{ label: 'Equal columns, one per link', value: 'fixed' },
	{ label: 'A row, each link its own width', value: 'auto-cols' },
	{ label: 'A row, centered', value: 'auto-cols cols-center' },
	{ label: 'A column', value: 'cols-1' },
];

// The menu locations (marccent-blocks.php register_nav_menus).
const LOCATIONS = [ { label: 'None: the menu picked below', value: '' }, { label: 'Primary', value: 'primary' } ];

const Edit = ( { attributes: a, setAttributes: set } ) => (
	<>
		<InspectorControls>
			<PanelBody title="Menu">
				<SelectControl label="Location" help="The menu in a location instead of one picked below: Primary is the header's main menu (the top-level pages until one is set)." value={ a.location } options={ LOCATIONS } onChange={ ( location ) => set( { location } ) } />
				{ ! a.location && <MenuSelect value={ a.menu } onChange={ ( menu ) => set( { menu } ) } /> }
				<TextControl label="Name for screen readers" help="Empty: the menu's name." value={ a.label } onChange={ ( label ) => set( { label } ) } />
				<SelectControl label="Layout" value={ a.layout } options={ LAYOUTS } onChange={ ( layout ) => set( { layout } ) } />
				<ToggleControl label="Icons only" help="Each link shows only its icon; its name is still read out." checked={ a.iconOnly } onChange={ ( iconOnly ) => set( { iconOnly } ) } />
				<ToggleControl label="Fold behind a menu button" help="Where there isn't room (a header on a phone), a menu button opens the links; where there is, they show in place." checked={ a.fold } onChange={ ( fold ) => set( { fold } ) } />
			</PanelBody>
		</InspectorControls>
		<div { ...useBlockProps() }><ServerSideRender block={ metadata.name } attributes={ a } /></div>
	</>
);

register( metadata, { edit: Edit } );
