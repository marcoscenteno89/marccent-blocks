<?php
/**
 * Hex wall: an island (runtime/islands/hex-wall.js), the people as a list until it's drawn.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$people = array_values( array_filter( (array) $attributes['people'], fn( $p ) => is_array( $p ) && '' !== trim( (string) ( $p['name'] ?? '' ) ) ) );
echo marccent_blocks_island( $block, 'hex-wall', array( 'people' => $people, 'perRow' => max( 1, (int) $attributes['perRow'] ) ), marccent_blocks_island_text( $people ) ); // phpcs:ignore WordPress.Security.EscapeOutput
