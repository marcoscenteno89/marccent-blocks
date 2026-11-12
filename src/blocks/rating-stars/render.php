<?php
/**
 * Stars: an island (runtime/islands/rating-stars.js), the theme's Stars markup until the island draws them.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$max   = max( 1, min( 10, (int) $attributes['maximum'] ) );
$value = max( 0, min( $max, (float) $attributes['value'] ) );
echo marccent_blocks_island( $block, 'rating-stars', array( 'value' => $value, 'maximum' => $max, 'icon' => (string) $attributes['icon'] ?: 'star' ), marccent_blocks_stars( $value, $max, (string) $attributes['icon'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput
