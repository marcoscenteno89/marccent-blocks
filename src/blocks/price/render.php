<?php
/**
 * Price: the theme's Price, written by the server (marccent_blocks_price, inc/markup.php). Nothing without an amount.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

if ( ! isset( $attributes['amount'] ) || ! is_numeric( $attributes['amount'] ) ) {
	return;
}
echo marccent_blocks_price( $attributes, fn( $attrs ) => marccent_blocks_wrapper( $block, $attrs ) ); // phpcs:ignore WordPress.Security.EscapeOutput
