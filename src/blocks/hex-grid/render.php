<?php
/**
 * Hex grid: an island (runtime/islands/hex-grid.js) of its Hex flips, saved or from a Loop, which the page shows as
 * text until it's drawn.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

echo marccent_blocks_island( $block, 'hex-grid', array( 'perRow' => max( 1, (int) $attributes['perRow'] ) ), marccent_blocks_inner( $block ) ); // phpcs:ignore WordPress.Security.EscapeOutput
