<?php
/**
 * Hex grid: an island (runtime/islands/hex-grid.js) of its Hex flips, saved or from a Loop, which the page shows as
 * their text until it's drawn.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$per_row = max( 1, min( 12, (int) $attributes['perRow'] ) );
echo marccent_blocks_island( $block, 'hex-grid', array( 'perRow' => $per_row ), marccent_blocks_inner( $block ) ); // phpcs:ignore WordPress.Security.EscapeOutput
