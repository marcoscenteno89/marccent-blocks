<?php
/**
 * Accordion item: its header and blocks as a <details>, named and opened as its accordion says (accordion/render.php).
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$stack = &$GLOBALS['marccent_accordion'];
$at    = $stack ? array_key_last( $stack ) : null;
$own   = null === $at ? array( 'name' => null, 'open' => -1, 'i' => 0 ) : $stack[ $at ];
if ( null !== $at ) {
	++$stack[ $at ]['i'];
}

echo marccent_blocks_accordion_item( $attributes['header'], marccent_blocks_inner( $block ), $own['name'], $own['i'] === $own['open'] ); // phpcs:ignore WordPress.Security.EscapeOutput
