<?php
/**
 * Stars: an island (runtime/islands/rating-stars.js), the score as text until the stars are drawn.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$max   = max( 1, min( 10, (int) $attributes['max'] ) );
$value = max( 0, min( $max, (float) $attributes['value'] ) );
/* translators: 1: a score, 2: the highest score */
echo marccent_blocks_island( $block, 'rating-stars', array( 'value' => $value, 'max' => $max, 'icon' => (string) $attributes['icon'] ?: 'star' ), '<p>' . esc_html( sprintf( __( '%1$s out of %2$s', 'marccent-blocks' ), $value, $max ) ) . '</p>' ); // phpcs:ignore WordPress.Security.EscapeOutput
