<?php
/**
 * Orbit: an island (runtime/islands/orbit.js): its Orbit items, saved or from a Loop, which the page shows as text
 * until it's drawn; or the theme's orbit clock or solar system.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$a     = $attributes;
$kind  = in_array( $a['kind'], array( 'items', 'clock', 'solar' ), true ) ? $a['kind'] : 'items';
$props = array( 'kind' => $kind, 'startAngle' => (float) $a['startAngle'], 'autoPlay' => (bool) $a['autoPlay'], 'interval' => max( 1000, (int) $a['interval'] ) );
echo marccent_blocks_island( $block, 'orbit', $props, 'items' === $kind ? marccent_blocks_inner( $block ) : '' ); // phpcs:ignore WordPress.Security.EscapeOutput
