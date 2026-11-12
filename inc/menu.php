<?php
/**
 * Menus in the shape the theme's components take.
 */

defined( 'ABSPATH' ) || exit;

/**
 * A menu location's items as a tree, the shape the theme's Menu takes (marccent_blocks_menu_items). Without a menu
 * there: the top-level pages.
 */
function marccent_blocks_menu_tree( string $location ): array {
	$locations = get_nav_menu_locations();
	$items     = isset( $locations[ $location ] ) ? wp_get_nav_menu_items( $locations[ $location ] ) : false;
	if ( ! $items ) {
		return array_map( fn( $p ) => array( 'name' => get_the_title( $p ), 'href' => esc_url_raw( get_permalink( $p ) ) ), get_pages( array( 'parent' => 0, 'sort_column' => 'menu_order' ) ) );
	}
	return marccent_blocks_menu_items_tree( $items );
}

/**
 * A menu's items (wp_get_nav_menu_items) as a tree: { name, href, icon?, external?, children? }. A dropdown can't be a
 * link itself (it opens on click), so its page comes first among its children. An item's icon is the Font Awesome
 * classes in its CSS Classes field (Screen Options → CSS Classes: fa-brands fa-tiktok), else, for a link to another
 * site, that site's own (marccent_blocks_link_icon); a link to another site opens in a new tab. An item's Description
 * field (Screen Options → Description) is the line under its name; a cols-2 … cols-4 class on a dropdown's item lays
 * its links out in that many columns (the theme's mega menu).
 */
function marccent_blocks_menu_items_tree( array $items ): array {
	$by_parent = array();
	foreach ( $items as $item ) {
		$by_parent[ (int) $item->menu_item_parent ][] = $item;
	}
	$build = function ( $parent ) use ( &$build, $by_parent ) {
		$out = array();
		foreach ( $by_parent[ $parent ] ?? array() as $item ) {
			$href     = esc_url_raw( $item->url );
			$external = (bool) preg_match( '#^https?://#', $href ) && ! str_starts_with( $href, home_url() );
			$icon     = implode( ' ', array_filter( (array) $item->classes, fn( $c ) => str_starts_with( $c, 'fa-' ) ) );
			$columns  = 0;
			foreach ( (array) $item->classes as $c ) {
				if ( preg_match( '/^cols-([2-4])$/', $c, $m ) ) {
					$columns = (int) $m[1];
				}
			}
			$node     = array_filter( array(
				'name'        => $item->title,
				'href'        => $href,
				'icon'        => $icon ?: ( $external || str_starts_with( $href, 'mailto:' ) ? marccent_blocks_link_icon( $href ) : '' ),
				'description' => trim( (string) ( $item->description ?? '' ) ),
				'external'    => $external,
				'columns'     => $columns,
			) );
			$children = $build( (int) $item->ID );
			if ( $children ) {
				$node['children'] = array_merge( array( array( 'name' => sprintf( __( 'All %s', 'marccent-blocks' ), $item->title ), 'href' => $href ) ), $children );
			}
			$out[] = $node;
		}
		return $out;
	};
	return $build( 0 );
}

/**
 * A menu made in Appearance → Menus, by its slug ("social"), as a tree (marccent_blocks_menu_items_tree): what the Menu
 * and Navbar blocks show. No such menu: none.
 */
function marccent_blocks_menu_by_slug( string $slug ): array {
	$menu  = $slug ? wp_get_nav_menu_object( $slug ) : false;
	$items = $menu ? wp_get_nav_menu_items( $menu->term_id ) : false;
	return $items ? marccent_blocks_menu_items_tree( $items ) : array();
}

// A site's Font Awesome icon from its address (google.com/maps → Google), else a link; an email address, an envelope.
function marccent_blocks_link_icon( string $url ): string {
	$host  = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
	$known = array(
		'facebook'  => 'facebook', 'fb.com' => 'facebook', 'instagram' => 'instagram', 'google' => 'google', 'goo.gl' => 'google',
		'linkedin'  => 'linkedin', 'youtube' => 'youtube', 'youtu.be' => 'youtube', 'tiktok' => 'tiktok', 'twitter' => 'x-twitter',
		'x.com'     => 'x-twitter', 'pinterest' => 'pinterest', 'yelp' => 'yelp', 'threads' => 'threads', 'github' => 'github',
		'whatsapp'  => 'whatsapp', 'wa.me' => 'whatsapp', 'houzz' => 'houzz', 'nextdoor' => '', 'bsky' => 'bluesky',
	);
	foreach ( $known as $part => $icon ) {
		if ( $icon && str_contains( $host, $part ) ) {
			return "fa-brands fa-$icon";
		}
	}
	return str_starts_with( $url, 'mailto:' ) ? 'fa-solid fa-envelope' : 'fa-solid fa-link';
}
