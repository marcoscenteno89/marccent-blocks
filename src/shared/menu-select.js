/*
 * A menu made in Appearance → Menus, picked by its slug (the same on every site that has a menu by that name), for the
 * Menu and Navbar blocks. Their server writes it (inc/menu.php marccent_blocks_menu_by_slug).
 */
import { SelectControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

export const MenuSelect = ( { label = 'Menu', value, onChange, none = 'Choose a menu', help } ) => {
	const menus = useSelect( ( select ) => select( coreStore ).getMenus( { per_page: -1 } ), [] );
	return (
		<SelectControl label={ label } value={ value } onChange={ onChange }
			help={ help ?? 'Made and edited in Appearance → Menus.' }
			options={ [ { label: menus ? none : 'Loading…', value: '' }, ...( menus ?? [] ).map( ( m ) => ( { label: m.name, value: m.slug } ) ) ] } />
	);
};
