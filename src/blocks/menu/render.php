<?php
/**
 * Menu: the menu picked (Appearance → Menus, by its slug) as the theme's Menu (inc/markup.php marccent_blocks_menu).
 * Its name for screen readers is the label, else the menu's own name. No menu picked or found: nothing, and a note
 * in the editor.
 *
 * @var array $attributes
 */

$items = marccent_blocks_menu_by_slug( (string) $attributes['menu'] );
if ( ! $items ) {
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		printf( '<p %s>%s</p>', get_block_wrapper_attributes(), esc_html__( 'Pick a menu (made in Appearance → Menus) in the block’s settings.', 'marccent-blocks' ) );
	}
	return;
}
$menu  = wp_get_nav_menu_object( (string) $attributes['menu'] );
$label = trim( (string) $attributes['label'] ) ?: $menu->name;
$html  = marccent_blocks_menu( array(
	'items'    => $items,
	'label'    => $label,
	'layout'   => (string) $attributes['layout'],
	'iconOnly' => (bool) $attributes['iconOnly'],
	'fold'     => (bool) $attributes['fold'],
) );
// The block's wrapper classes (its anchor, extra classes) go on the menu's own first element (the <nav>, or the fold
// around it).
$wrapper = get_block_wrapper_attributes();
preg_match( '/class="([^"]*)"/', $wrapper, $class );
preg_match( '/id="([^"]*)"/', $wrapper, $id );
$tags = new WP_HTML_Tag_Processor( $html );
if ( $tags->next_tag() ) {
	foreach ( preg_split( '/\s+/', $class[1] ?? '', -1, PREG_SPLIT_NO_EMPTY ) as $c ) {
		$tags->add_class( $c );
	}
	if ( ! empty( $id[1] ) ) {
		$tags->set_attribute( 'id', html_entity_decode( $id[1] ) );
	}
}
echo $tags->get_updated_html(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in marccent_blocks_menu
