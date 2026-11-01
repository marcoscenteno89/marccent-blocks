<?php
/**
 * Collage: an island (runtime/islands/collage.js), the tiles' photos and text as a list until it's drawn.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$tiles = array_values( array_filter( (array) $attributes['tiles'], 'is_array' ) );
$props = array( 'tiles' => $tiles, 'cols' => max( 1, (int) $attributes['cols'] ), 'row' => (string) $attributes['row'] );
echo marccent_blocks_island( $block, 'collage', $props, marccent_blocks_island_text( $tiles ) ); // phpcs:ignore WordPress.Security.EscapeOutput
