<?php
/**
 * Menus in the shape the theme's components take.
 */

defined( 'ABSPATH' ) || exit;

/**
 * A menu location's items as a tree, the shape the theme's Menu takes: { name, href, children? }. A dropdown can't
 * be a link itself (it opens on click), so its page comes first among its children. Without a menu: the top-level pages.
 */
function marccent_blocks_menu_tree( string $location ): array {
	$locations = get_nav_menu_locations();
	$items     = isset( $locations[ $location ] ) ? wp_get_nav_menu_items( $locations[ $location ] ) : false;
	if ( ! $items ) {
		return array_map( fn( $p ) => array( 'name' => get_the_title( $p ), 'href' => esc_url_raw( get_permalink( $p ) ) ), get_pages( array( 'parent' => 0, 'sort_column' => 'menu_order' ) ) );
	}
	$by_parent = array();
	foreach ( $items as $item ) {
		$by_parent[ (int) $item->menu_item_parent ][] = $item;
	}
	$build = function ( $parent ) use ( &$build, $by_parent ) {
		$out = array();
		foreach ( $by_parent[ $parent ] ?? array() as $item ) {
			$node     = array( 'name' => $item->title, 'href' => esc_url_raw( $item->url ) );
			$children = $build( (int) $item->ID );
			if ( $children ) {
				$node['children'] = array_merge( array( array( 'name' => sprintf( __( 'All %s', 'marccent-blocks' ), $item->title ), 'href' => esc_url_raw( $item->url ) ) ), $children );
			}
			$out[] = $node;
		}
		return $out;
	};
	return $build( 0 );
}
