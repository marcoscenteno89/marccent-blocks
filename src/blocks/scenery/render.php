<?php
/**
 * Scenery: an island (runtime/islands/scenery.js): the landscape's layers (their names, back to front) as props, its
 * blocks in the slot, shown over it.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$a      = $attributes;
$layers = array_values( array_filter( array_map( fn( $l ) => sanitize_key( $l ), explode( ',', (string) $a['layers'] ) ) ) );
$props  = array( 'layers' => $layers, 'height' => (string) $a['height'] ?: '22rem', 'parallax' => (bool) $a['parallax'], 'min' => (string) $a['min'] );
echo marccent_blocks_island( $block, 'scenery', $props, marccent_blocks_inner( $block ) ); // phpcs:ignore WordPress.Security.EscapeOutput
