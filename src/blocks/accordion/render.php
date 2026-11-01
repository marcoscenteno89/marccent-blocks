<?php
/**
 * Accordion: its items (marccent/accordion-item), each a <details>; they share a name (open one at a time) unless
 * several may be open, and the one at "open" (0 the first, -1 none) starts open. The items read that from the
 * accordion being rendered ($GLOBALS['marccent_accordion'], innermost last), since a name is no attribute.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$GLOBALS['marccent_accordion'][] = array( 'name' => $attributes['multiple'] ? null : wp_unique_id( 'marccent-accordion-' ), 'open' => (int) $attributes['open'], 'i' => 0 );
$items = marccent_blocks_inner( $block );
array_pop( $GLOBALS['marccent_accordion'] );

echo marccent_blocks_accordion( $items, fn( $attrs ) => marccent_blocks_wrapper( $block, $attrs ) ); // phpcs:ignore WordPress.Security.EscapeOutput
