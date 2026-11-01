<?php
/**
 * Compare: an island (runtime/islands/compare.js), the two pictures until it's drawn.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$props = array_intersect_key( $attributes, array_flip( array( 'before', 'after', 'beforeLabel', 'afterLabel', 'start', 'label', 'ratio' ) ) );
echo marccent_blocks_island( $block, 'compare', $props, marccent_blocks_island_text( $props ) ); // phpcs:ignore WordPress.Security.EscapeOutput
