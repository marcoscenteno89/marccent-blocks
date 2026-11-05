<?php
/**
 * Social links: the Social menu's links (marccent_blocks_social_links, inc/menu.php) as the theme's Buttons, side by
 * side; a new tab for each, since they leave the site. Hover at Default takes the site's default hover. Without a
 * Social menu: nothing on the site, a hint in the editor.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$links = marccent_blocks_social_links();
if ( ! $links ) {
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) { // the editor's preview
		printf( '<p %s>%s</p>', get_block_wrapper_attributes(), esc_html__( 'No social links yet: add them in Appearance → Menus, in a menu at the Social location.', 'marccent-blocks' ) );
	}
	return;
}

$buttons = '';
foreach ( $links as $link ) {
	$buttons .= marccent_blocks_button( array(
		'label'   => $link['name'],
		'href'    => $link['href'],
		'icon'    => $link['icon'],
		'classes' => marccent_blocks_cx( $attributes['color'], $attributes['hover'] ?: marccent_blocks_default_hover() ),
		'newTab'  => preg_match( '#^https?:#', $link['href'] ) && ! str_starts_with( $link['href'], home_url() ),
	) );
}
printf( '<nav %s>%s</nav>', get_block_wrapper_attributes( array( 'class' => 'grid auto-cols items-center gap-sm', 'aria-label' => __( 'Social', 'marccent-blocks' ) ) ), $buttons ); // phpcs:ignore WordPress.Security.EscapeOutput -- built escaped
