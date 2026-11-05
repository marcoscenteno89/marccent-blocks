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

/*
 * The site's social links: the menu in the Social location (Appearance → Menus), one link each, as { name, href, icon }.
 * A link's icon is the Font Awesome classes in its CSS Classes field (Screen Options → CSS Classes: fa-brands
 * fa-tiktok), else the network's own, from the address, else a plain link icon. No menu there: none.
 */
function marccent_blocks_social_links(): array {
	$locations = get_nav_menu_locations();
	$items     = isset( $locations['social'] ) ? wp_get_nav_menu_items( $locations['social'] ) : false;
	$links     = array();
	foreach ( $items ?: array() as $item ) {
		if ( (int) $item->menu_item_parent ) {
			continue; // a flat list: a nested item has no place in a row of buttons
		}
		$classes = implode( ' ', array_filter( (array) $item->classes, fn( $c ) => str_starts_with( $c, 'fa-' ) ) );
		$links[] = array( 'name' => $item->title, 'href' => esc_url_raw( $item->url ), 'icon' => $classes ?: marccent_blocks_social_icon( $item->url ) );
	}
	return $links;
}

// A social network's Font Awesome icon from its address (google.com/maps → Google), else a link.
function marccent_blocks_social_icon( string $url ): string {
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
