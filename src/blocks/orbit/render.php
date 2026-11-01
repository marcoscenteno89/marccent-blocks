<?php
/**
 * Orbit: an island (runtime/islands/orbit.js): items on a ring (their titles and text as a list until it's drawn),
 * or the theme's orbit clock or solar system.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$a     = $attributes;
$kind  = in_array( $a['kind'], array( 'items', 'clock', 'solar' ), true ) ? $a['kind'] : 'items';
$items = array_values( array_filter( (array) $a['items'], fn( $i ) => is_array( $i ) && '' !== trim( (string) ( $i['title'] ?? '' ) ) ) );
$props = array( 'kind' => $kind, 'items' => $items, 'startAngle' => (float) $a['startAngle'], 'autoPlay' => (bool) $a['autoPlay'], 'interval' => max( 1000, (int) $a['interval'] ) );
echo marccent_blocks_island( $block, 'orbit', $props, 'items' === $kind ? marccent_blocks_island_text( $items ) : '' ); // phpcs:ignore WordPress.Security.EscapeOutput
