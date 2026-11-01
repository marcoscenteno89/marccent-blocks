<?php
/**
 * Countdown: an island (runtime/islands/countdown.js), its first digit until it's drawn.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$from = max( 0, min( 9, (int) $attributes['from'] ) );
echo marccent_blocks_island( $block, 'countdown', array( 'from' => $from ), '<p>' . $from . '</p>' ); // phpcs:ignore WordPress.Security.EscapeOutput
