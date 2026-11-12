<?php
/**
 * Contact: the theme's Contact, written by the server (marccent_blocks_contact, inc/markup.php). Nothing without a
 * value.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

if ( '' === trim( (string) $attributes['value'] ) ) {
	return;
}
echo marccent_blocks_contact( $attributes, fn( $attrs ) => marccent_blocks_wrapper( $block, $attrs ) ); // phpcs:ignore WordPress.Security.EscapeOutput
