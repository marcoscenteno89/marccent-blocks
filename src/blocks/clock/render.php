<?php
/**
 * Clock: an island (runtime/islands/clock.js), the live time. Nothing stands in for it: the server's time would be
 * wrong in the visitor's zone.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$face = in_array( $attributes['face'] ?? '', array( 'analog', 'flip' ), true ) ? $attributes['face'] : 'digital';
echo marccent_blocks_island( $block, 'clock', array( 'face' => $face, 'format' => (string) $attributes['format'] ?: 'h:mm:ss A' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
