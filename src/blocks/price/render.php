<?php
/**
 * Price: an island (runtime/islands/price.js), the theme's Price; until it's drawn, the price as screen readers hear it.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$a     = $attributes;
$props = array(
	'amount'   => (float) $a['amount'],
	'currency' => strtoupper( preg_replace( '/[^A-Za-z]/', '', (string) $a['currency'] ) ) ?: 'USD',
	'locale'   => (string) $a['locale'] ?: 'en-US',
);
if ( '' !== (string) $a['per'] ) {
	$props['per'] = (string) $a['per'];
}
if ( isset( $a['was'] ) && is_numeric( $a['was'] ) ) {
	$props['was'] = (float) $a['was'];
}
$said = $props['currency'] . ' ' . ( 0 + $props['amount'] ) . ( isset( $props['per'] ) ? ' / ' . $props['per'] : '' );
echo marccent_blocks_island( $block, 'price', $props, '<span>' . esc_html( $said ) . '</span>' ); // phpcs:ignore WordPress.Security.EscapeOutput
